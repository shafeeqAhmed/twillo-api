<?php

namespace App\Http\Controllers\Api;

use App\Models\AutoMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutoMessageController extends ApiController
{
    public function addAutoMessage(Request $request)
    {
        $data = $request->validate([
            'text' => 'required',
            'keyword' => 'required|unique:auto_messages'
        ]);
        $data['uuid'] = Str::uuid()->toString();
        $data['user_id'] = $request->user()->id;
        $data['keyword'] = $request->keyword;

        AutoMessage::create($data);

        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Auto Message Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function updateAutoMessage(Request $request)
    {
        $request->validate([
            'uuid' => 'required',
            'text' => 'required',
            'keyword' => ['required', 'unique:auto_messages,uuid,' . $request->uuid],
            'status' => 'nullable|boolean'
        ]);


        AutoMessage::where('uuid', $request->uuid)->where('user_id', $request->user()->id)->update($request->all());
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Auto Message Has been Updated Successfully!',
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
                'autoMessageList' => $list
            ]
        ]);
    }
}
