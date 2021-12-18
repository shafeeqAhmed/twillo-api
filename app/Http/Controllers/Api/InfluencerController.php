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
     User::updateUser('user_uuid',$request['user_uuid'],$request->except('user_uuid'));
     return $this->respondUpdated();
   }
    public function createInfluencer(StoreInfluencer $request){
        $input = $request->validated();
        $input['password'] = Hash::make($input['password']);
        $input['user_uuid'] = Str::uuid()->toString();
        $data= User::create($input);

        // assign him influencer role
        $data->assignRole('Influencer');
        return $this->respondCreated();
    }
}
