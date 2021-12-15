<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInfluencer;
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
    public function getUserDetail($id)
    {
        try {
            $data['user_detail'] = User::where('id',$id)->select('id','name','email','phone_no','created_at')->first();
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

    public function createInfluencer(StoreInfluencer $request){
         $input = $request->validated();
    
          try {
          
          $data= User::create([
            'user_uuid' => Str::uuid()->toString(),
            'fname' => $input['fname'],
            'lname' => $input['lname'],
            'name' => $input['fname'].' '.$input['lname'],
            'email' => $input['email'],
            'phone_no' => $input['phone_no'],
            'country_id' => $input['country_id'],
            'twilio_number' => $input['twilio_number'],
            'password' => Hash::make('12345678'),
        ]);

         $data->assignRole('Influencer');
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }

      
    }


     public function getInfluencersList()
    {
        try {
            $data = User::role('Influencer')->get();
            return response()->json(['status' => true, 'message' => 'List of Influencers given below', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

}
