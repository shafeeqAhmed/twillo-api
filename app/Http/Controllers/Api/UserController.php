<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TwilioNumbers;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInfluencer;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function myDetail(Request $request)
    {
        try {
            $data['my_detail'] = $request->user();
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
    public function getUserDetail($user_uuid)
    {
        try {
            $data['user_detail'] = User::getUser('user_uuid',$user_uuid);
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
	 public function userList()
    {
        try {
            $data['list'] = User::paginate(10);
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    


     public function getInfluencersList()
    {
        try {
            $data['list'] = User::role('Influencer')->with(['country'])->get();
            return response()->json(['status' => true, 'message' => 'List of Influencers given below', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

}
