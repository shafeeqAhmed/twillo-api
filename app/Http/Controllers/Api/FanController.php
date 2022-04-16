<?php

namespace App\Http\Controllers\Api;

use App\Models\FanClub;
use App\Models\MessageLinks;
use App\Models\Messages;
use Illuminate\Http\Request;

class FanController extends ApiController
{
    public function getFans(Request $request)
    {
        $list =
            FanClub::join('fans as f', 'fan_clubs.fan_id', '=', 'f.id')
            ->select('fan_clubs.fan_club_uuid', 'fan_clubs.local_number', 'fan_clubs.is_active', 'f.fname', 'f.lname', 'f.email', 'f.gender', 'f.dob')
            ->where('fan_clubs.user_id', $request->user()->id)
            ->orderBy('fan_clubs.is_active', 'desc')->get();
        return $this->respond([

            'data' => [
                'status' => true,
                'message' => '',
                'fans' => $list
            ]
        ]);
    }
    public function blockFan(Request $request)
    {
        $request->validate([
            'fan_club_uuid' => 'required',
        ]);

        $fansClub = FanClub::where("fan_club_uuid", $request->fan_club_uuid)->first();
        $fan_id = $fansClub->fan_id;
        $fan_club_id = $fansClub->id;
        $fansClub->update(['is_active' => 0]);

        Messages::where('fan_id', $fan_id)->where('user_id', $request->user()->id)->update(['is_active' => 0]);
        MessageLinks::where('influencer_id', $request->user()->id)->where('fanclub_id', $fan_club_id)->update(['is_active' => 0]);
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Fan Has Been Blocked Successfully!',
                'data`' => []
            ]
        ]);
    }
}
