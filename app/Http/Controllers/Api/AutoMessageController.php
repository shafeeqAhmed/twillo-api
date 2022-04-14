<?php

namespace App\Http\Controllers\Api;

use App\Models\AutoMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AutoMessageController extends ApiController
{
    public function addAutoMessage(Request $request)
    {
        $data = $request->validate([
            'text' => 'required',
            'keyword' => 'required|unique:auto_messages',
            'status' => 'nullable|boolean'
        ]);
        $data['uuid'] = Str::uuid()->toString();
        $data['user_id'] = $request->user()->id;

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
            'keyword' => ['required', Rule::unique('auto_messages')->ignore($request->uuid, 'uuid')],
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
    public function deleteAutoMessage(Request $request)
    {
        $request->validate([
            'uuid' => 'required',
        ]);


        AutoMessage::where('uuid', $request->uuid)->where('user_id', $request->user()->id)->delet();
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Auto Message Has been Updated Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function getAutoMessageDetail($uuid, Request $request)
    {
        $detail = AutoMessage::where('uuid', $uuid)->where('user_id', $request->user()->id)->first();
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => '',
                'detail' => $detail
            ]
        ]);
    }
}
