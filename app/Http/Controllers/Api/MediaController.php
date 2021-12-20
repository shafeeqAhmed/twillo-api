<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function uploadSingleFile(Request $request)
    {
        if (request()->hasFile('file')) {
            $imageUrl = uploadImage('file', 'users/profile', 300, 300);
            // $oldFile = BusinessSetting::where('type', $key)->value('value');
            // removeImage('admin/website', $oldFile);
            return $this->respond([
                'data' => ['url' => $imageUrl]
            ]);
        }
    }
}
