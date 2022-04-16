<?php

namespace App\Http\Controllers\Api;

use App\Models\FanClub;
use App\Models\TwilioNumbers;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInfluencer;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{
    public function myDetail(Request $request)
    {

        $data['my_detail'] = $request->user();
        return $this->respond([
            'data' => $data
        ]);
    }

    public function getUserDetail($user_uuid)
    {

        $data['user_detail'] = User::getUser('user_uuid', $user_uuid);
        return $this->respond([
            'data' => $data
        ]);
    }

    public function userList()
    {
        $data['list'] = User::paginate(10);
        return $this->respond([
            'data' => $data
        ]);
    }


    public function getInfluencersList()
    {
        $data['list'] = User::role('influencer')->with(['country'])->get();
        return $this->respond([
            'data' => $data
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user_record = User::where('user_uuid', $request->user_uuid)->first();

        $data['fname'] = $request->fname;
        $data['lname'] = $request->lname;
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        if (request()->hasFile('previewImage')) {
            $imageUrl = uploadImage('previewImage', 'users/profile', 300, 300);
            $oldFile = $user_record['profile_photo_path'];

            if ($oldFile) {
                removeImage('users/profile', $oldFile);
            }

            $data['profile_photo_path'] = $imageUrl;
        }

        User::where('id', $user_record['id'])->update($data);
        $user = User::where('id', $user_record['id'])->first();
        $detail = collect($user)->only(['user_uuid', 'name', 'email', 'phone_no', 'profile_photo_path']);
        if (count($user->getRoleNames()) > 0) {
            $detail['scope'] = $user->getRoleNames();
        } else {
            $detail['scope'] = array('influencer');
        }

        return $this->respond([
            'data' => ['user' => $detail]
        ]);
    }

    public function isValidReference($reference)
    {
        $fan_club = FanClub::where('temp_id', $reference)
            ->where('is_active', 0)
            ->first();
        $status = false;
        if ($fan_club) {
            $status = true;
        }
        return $this->respond([
            'data' => $status
        ]);
    }

    public function getInfluencerDashboardInfo(Request $request)
    {
        $data['user'] = User::where('id', $request->user()->id)->select('send_message_count', 'received_message_count')->first();
        $total_contact = FanClub::with('fan')->latest()
            ->select('id', 'fan_club_uuid', 'local_number', 'fan_id', 'temp_id', 'created_at')
            ->where('is_active', 1)
            ->groupBy('local_number')->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')->get();
        $data['total_contacts'] = count($total_contact);
        return $this->respond([
            'data' => $data
        ]);
    }
}
