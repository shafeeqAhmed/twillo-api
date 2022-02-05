<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;
use App\Models\User;
use App\Models\FanClub;
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
if (!function_exists('sendSms')) {
    function sendSms($from, $to,$body)
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $client = new Client($sid, $token);
        $client->messages->create(
            $to,
            [
                "body" => $body,
                "from" =>  $from,
                "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
        );
    }
}

if (!function_exists('sendSms')) {
    function sendSms($from, $to,$body)
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $client = new Client($sid, $token);
        $client->messages->create(
            $to,
            ["body" => $body, "from" =>  $from, "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
        );
    }
}

if (!function_exists('sendAndReceiveSms')) {
    function sendAndReceiveSms($user_id,$type)
    {
       if($type == 'send') {
           User::find($user_id)->increment('send_message_count');
       }
       if($type == 'receive') {
           User::find($user_id)->increment('received_message_count');
       }

    }
}
if (!function_exists('fanSendAndReceiveSms')) {
    function fanSendAndReceiveSms($fan_club_id,$type)
    {
    $fanClub = FanClub::where('id',$fan_club_id)->first();
        if($fanClub) {
            if($type == 'send') {
                $fanClub->where('id',$fan_club_id)->increment('send_count');
            }
            if($type == 'receive') {
                $fanClub->where('id',$fan_club_id)->increment('received_count');
            }
        }
    }
}
