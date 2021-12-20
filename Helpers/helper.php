<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

if (!function_exists('uploadImage')) {
    function uploadImage($key, $directory, int $width, int $height)
    {
        if (request($key)) {
            $data2 = file_get_contents(request($key));
            $type = request($key)->getClientOriginalExtension();
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data2);
            $img = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $imageName = Str::random(30) . '.' . $type;
            $path = storage_path() . '/app/public/' . $directory;
            if (!File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true, true);
            }
            File::put($path . '/' . $imageName, base64_decode($img));
            $folder = 'public/storage/' . $directory . '/' . $imageName;
            if (isFileExist($directory, $folder)) {
                $image = Image::make($folder)->resize($width, $height)->save();
            }
            return url("/$folder");
        } else {
            return '';
        }
    }
}
if (!function_exists('removeImage')) {
    function removeImage($directory, $old_img_url)
    {
        $arr = explode('/', $old_img_url);
        $path = 'public/' . $directory . '/' . end($arr);
        if (Storage::exists($path)) {
            Storage::delete($path);
        }
    }
}
if (!function_exists('isFileExist')) {
    function isFileExist($directory, $url)
    {
        $arr = explode('/', $url);
        $path = 'public/' . $directory . '/' . end($arr);
        return Storage::exists($path);
    }
}
