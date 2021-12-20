<?php

namespace App\Http\Controllers\Api;

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

        $data['user_detail'] = User::getUser('user_uuid',$user_uuid);
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
        $data['fname'] = $request->fname;
        $data['lname'] = $request->lname;
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }
        if (request()->hasFile('previewImage')) {
                $imageUrl = uploadImage('previewImage', 'users/profile', 300, 300);
                $oldFile = $request->user()->profile_photo_path;
                removeImage('users/profile', $oldFile);

                $data['profile_photo_path'] = $imageUrl;
            }
         User::where('id',$request->user()->id)->update($data);
         $user = User::where('id',$request->user()->id)->first();
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



}
