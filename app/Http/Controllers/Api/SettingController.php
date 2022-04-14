<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends ApiController
{

    public function addSetting(Request $request)
    {
        if (!in_array('admin', $request->user()->getRoleNames()->toArray())) {
            return $this->respondInvalidRequest('Access Denied');
        }

        $data = $request->validate([
            'name' => 'required',
            'value' => 'required'
        ]);
        $data['uuid'] = Str::uuid()->toString();
        Setting::updateOrCreate(['name' => $request->name], $data);


        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Setting Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function getSetting(Request $request)
    {
        $list = Setting::get();
        return $this->respond([

            'data' => [
                'status' => true,
                'message' => '',
                'setting' => $list
            ]
        ]);
    }
}
