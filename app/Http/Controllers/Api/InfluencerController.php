<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\StoreInfluencer;
use Illuminate\Http\Request;
use App\Http\Requests\updateInfluencer;
use App\Models\User;
use App\Models\TwilioNumbers;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InfluencerController extends ApiController
{

   public function updateInfluencer(updateInfluencer $request){
        $data =  $this->influencerUpdateInput($request->except('user_uuid'));
        User::updateUser('user_uuid',$request['user_uuid'],$data);
        return $this->respondUpdated();
   }
   public function influencerUpdateInput($params) {
       if(isset($params['password'])) {
           $params['password'] = Hash::make($params['password']);
       }
       return $params;
   }
    public function createInfluencer(StoreInfluencer $request){
        $input = $request->validated();
        $input['password'] = Hash::make($input['password']);
        $input['user_uuid'] = Str::uuid()->toString();
        $input['fname'] = $input['fname'];
        $input['lname'] = $input['lname'];
        $input['name'] = $input['fname'] . ' ' . $input['lname'];
        $data= User::create($input);

        // assign him influencer role
        $data->assignRole($input['role']);
        return $this->respondCreated();
    }
}
