<?php

namespace App\Http\Controllers\Api;

use App\Models\Country;
use App\Models\TwilioNumbers;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class DropDownController extends ApiController
{
    public function getCountriesTWillioNumbers()
    {
       
        $data = Country::all();
           return $this->respond([
            'data' => $data
        ]);
    }
}
