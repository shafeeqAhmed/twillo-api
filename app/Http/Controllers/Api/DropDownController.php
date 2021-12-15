<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\TwilioNumbers;
use App\Models\User;
use Illuminate\Http\Request;

class DropDownController extends Controller
{
    public function getCountriesTWillioNumbers()
    {
        try {
            $data['countries'] = Country::all();
            $data['twillio_numbers'] = TwilioNumbers::where('status','Influencer')->get();
            return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'data' => []]);
        }
    }
}
