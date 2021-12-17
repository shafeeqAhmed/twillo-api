<?php

namespace App\Http\Controllers\Api;


use Illuminate\Http\Request;
use App\Http\Requests\updateInfluencer;
use App\Models\User;
use App\Models\TwilioNumbers;
use Illuminate\Support\Facades\DB;
use Exception;

class InfluencerController extends ApiController
{
       
   public function updateInfluencer(updateInfluencer $request){
     User::updateUser('user_uuid',$request['user_uuid'],$request->except('user_uuid'));
     return $this->respondUpdated();
   }
}
