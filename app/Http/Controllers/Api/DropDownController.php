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

        $data = Country::where('is_active',1)->get();
           return $this->respond([
            'data' => $data
        ]);
    }
}
