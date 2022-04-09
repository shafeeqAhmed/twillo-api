<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoMessage;
use Illuminate\Http\Request;

class AutoMessageController extends ApiController
{
    public function addAutoMessage(Request $request)
    {
        $data = $request->validate([
            'type' => 'required',
            'text' => 'required',
        ]);
        $data['user_id'] = $request->user()->id;
        $data['keyword'] = $request->has('keyword') ? $request->keyword : null;

        AutoMessage::where('user_id', $request->user()->id)->where('type', $request->type)->update(['status' => false]);
        AutoMessage::create($data);

        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Auto Message Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function getAutoMessage(Request $request)
    {
        $list = AutoMessage::where('user_id', $request->user()->id)->orderBy('status', 'desc')->get();
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => '',
                'data`' => $list
            ]
        ]);
    }
}
