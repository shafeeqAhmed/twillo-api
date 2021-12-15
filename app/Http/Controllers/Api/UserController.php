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

    public function createInfluencer(StoreInfluencer $request){
        $input = $request->validated();
        try {
            DB::beginTransaction();
            $data= User::create([
                'user_uuid' => Str::uuid()->toString(),
                'fname' => $input['fname'],
                'lname' => $input['lname'],
                'name' => $input['fname'].' '.$input['lname'],
                'email' => $input['email'],
                'phone_no' => $input['phone_no'],
                'country_id' => $input['country_id'],
                'twilo_id' => $input['twilo_id'],
                'password' => Hash::make('12345678'),
            ]);
            // assign him influencer role
            $data->assignRole('Influencer');
            //in activate the twilo numbert so that we can not assign this number to other user
            TwilioNumbers::updateTwilo('id',$input['twilo_id'],['status'=>'inactive']);

            DB::commit();
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }


    }
    public function updateInfluencer(StoreInfluencer $request){
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
            $data['list'] = User::role('admin')->get();
            return response()->json(['status' => true, 'message' => 'List of Influencers given below', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }

}
