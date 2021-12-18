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
          $data['list'] = User::role('Influencer')->with(['country'])->get();
           return $this->respond([
            'data' => $data
        ]);
    }

}
