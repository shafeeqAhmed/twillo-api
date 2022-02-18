<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MessageLinks;

class LinksController extends Controller
{
    public function redirectUrl(Request $request)
    {
        if($request->has('uuid')){
            $link = MessageLinks::where('message_link_uuid', $request->uuid)->firstOrFail();
            $link['total_visits'] = $link['total_visits'] + 1;
            if(!$link['is_visited']){
                $link['is_visited'] = true;
                $link['visited_date'] = Carbon::now();
            }
            $link->save();
            return redirect()->away($link->link);
        }
    }

}
