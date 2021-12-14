<?php

use Illuminate\Support\Facades\File;

if (!function_exists('uploadImage')) {
    function uploadImage($key, $directory)
    {
        if (request($key)) {
            $data2 = file_get_contents(request($key));
            $type = request($key)->getClientOriginalExtension();
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data2);
            $img = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $imageName = \Str::random(30) . '.' . $type;
            $path = storage_path() . '/app/public/' . $directory;
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            File::put($path . '/' . $imageName, base64_decode($img));
            $folder = 'public/storage/' . $directory . '/' . $imageName;
            return url("/$folder");
        } else {
            return '';
        }
    }
}
