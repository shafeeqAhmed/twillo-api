<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PersonalSetting;
use Illuminate\Validation\Rule;

class PersonalSettingController extends ApiController
{
    public function addPersonalSetting(Request $request)
    {

        $data = $request->validate([
            'name' => 'required',
            'value' => 'required'
        ]);
        $data['uuid'] = Str::uuid()->toString();
        $data['user_id'] = $request->user()->id;
        PersonalSetting::updateOrCreate(['name' => $request->name, 'user_id' => $request->user()->id], $data);
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Personal Setting Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function updatePersonalSetting(Request $request)
    {

        $data = $request->validate([
            'uuid' => 'required',
            'name' => [
                'required',
                Rule::unique('personal_settings')->ignore($request->uuid, 'uuid')->where(function ($query) use ($request) {
                    return  $query->where('user_id', $request->user()->id)->where('name', $request->name);
                })
            ],
            'value' => 'required',
            'status' => 'nullable|boolean'
        ]);
        PersonalSetting::where('uuid', $request->uuid)->update($data);
        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Personal Setting Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function getPersonalSetting(Request $request)
    {
        $list = PersonalSetting::where('user_id', $request->user()->id)->orderBy('status', 'desc')->get();
        return $this->respond([

            'data' => [
                'status' => true,
                'message' => '',
                'personalSetting' => $list
            ]
        ]);
    }
}
