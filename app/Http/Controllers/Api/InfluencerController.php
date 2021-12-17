<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\updateInfluencer;
use App\Models\User;
use App\Models\TwilioNumbers;
use Illuminate\Support\Facades\DB;
use Exception;
class InfluencerController extends Controller
{
   public function updateInfluencer(updateInfluencer $request){
        
      try {
            User::updateUser('user_uuid',$request['user_uuid'],$request->except('user_uuid'));
            return response()->json(['status' => true, 'message' => 'User has been updated successfully', 'data' => []]);
        } catch (Exception $e) {
             DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }

    }
}
