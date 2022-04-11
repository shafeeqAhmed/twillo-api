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
        $data = $request->validate([
            'name' => 'required|unique:settings,name',
            'value' => 'required'
        ]);
        $data['uuid'] = Str::uuid()->toString();

        Setting::create($data);

        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Setting Has been stored Successfully!',
                'data`' => []
            ]
        ]);
    }
    public function updateSetting(Request $request)
    {
        $data = $request->validate([
            'uuid' => 'required',
            'value' => 'required'
        ]);

        Setting::where('uuid', $request->uuid)->update($data);

        return $this->respond([
            'data' => [
                'status' => true,
                'message' => 'Setting Has been updated Successfully!',
                'data`' => []
            ]
        ]);
    }
}
