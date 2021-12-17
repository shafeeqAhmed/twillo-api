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
        try {
            $data['my_detail'] = $request->user();
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
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
        try {
            $data['list'] = User::paginate(10);
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

    public function createInfluencer(StoreInfluencer $request){
        $input = $request->validated();
       
        $data= User::create([
            'user_uuid' => Str::uuid()->toString(),
            'fname' => $input['fname'],
            'lname' => $input['lname'],
            'name' => $input['fname'].' '.$input['lname'],
            'email' => $input['email'],
            'phone_no' => $input['phone_no'],
            'country_id' => $input['country_id'],
            'password' => Hash::make($input['password']),
        ]);

        // assign him influencer role
        $data->assignRole('Influencer');
       return $this->respondCreated();
    }
    

     public function getInfluencersList()
    {
          $data['list'] = User::role('Influencer')->with(['country'])->get();
           return $this->respond([
            'data' => $data
        ]);
    }

}
