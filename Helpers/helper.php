<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;
use App\Models\User;
use App\Models\FanClub;
use App\Models\MessageLinks;
use App\Models\Messages;
use App\Models\PersonalSetting;
use App\Models\Setting;
use Carbon\Carbon;

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
    function sendSms($from, $to, $body)
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $client = new Client($sid, $token);
        $client->messages->create(
            $to,
            [
                "body" => $body,
                "from" =>  $from,
                "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"
            ]
        );
    }
}

// if (!function_exists('sendSms')) {
//     function sendSms($from, $to, $body)
//     {
//         $sid = config('general.twilio_sid');
//         $token = config('general.twilio_token');
//         $client = new Client($sid, $token);
//         $client->messages->create(
//             $to,
//             ["body" => $body, "from" =>  $from, "statusCallback" => "https://text-app.tkit.co.uk/twillo-api/api/twilio_webhook"]
//         );
//     }
// }
//for influener send and received sms
if (!function_exists('sendAndReceiveSms')) {
    function sendAndReceiveSms($user_id, $type, $fan_id = null)
    {
        if ($type == 'send') {
            User::find($user_id)->increment('send_message_count');
        }
        if ($type == 'receive') {
            User::find($user_id)->increment('received_message_count');
        }
    }
}
//for fan send and received sms
if (!function_exists('fanSendAndReceiveSms')) {
    function fanSendAndReceiveSms($fan_club_id, $type, $influencer_id = null)
    {
        $fanClub = FanClub::where('id', $fan_club_id)->first();
        if ($fanClub) {
            if ($type == 'send') {
                $fanClub->where('id', $fan_club_id)->increment('send_count');
            }
            if ($type == 'receive') {
                $fanClub->where('id', $fan_club_id)->increment('received_count');
            }
        }
    }
}
if (!function_exists('getLinkColumn')) {
    function getLinkColumn($message, $colum)
    {
        $arr = explode('?uuid=', $message);
        if (count($arr) == 2) {
            return  MessageLinks::where('message_link_uuid', $arr[1])->value($colum);
        }
        return null;
    }
}

if (!function_exists('updateLocalMessage')) {
    function updateLocalMessage($fan_id, $user_id, $type, $message, $status, $broadcast_id, $twilio_msg_id, $stander_time)
    {
        Messages::create([
            'fan_id' => $fan_id,
            'user_id' => $user_id,
            'type' => $type,
            'message' => $message,
            'status' => $status,
            'broadcast_id' => $broadcast_id,
            'twilio_msg_id' => $twilio_msg_id,
            'stander_time' => $stander_time
        ]);
    }
}
if (!function_exists('updateFanReplies')) {
    function updateFanReplies($fan_id, $user_id)
    {
        Messages::where('fan_id', $fan_id)
            ->where('user_id', $user_id)
            ->where('type', 'send')
            ->where('status', 'delivered')
            ->where('is_replied', 0)
            ->update(['is_replied' => 1, 'updated_at' => Carbon::now()]);
    }
}
if (!function_exists('getWelcomeMessage')) {
    function getWelcomeMessage($user_id, $link)
    {
        $welcome =  PersonalSetting::where('user_id', $user_id)
            ->where('name', 'welcome')
            ->value('value');
        if (empty($welcome)) {
            $welcome =  Setting::where('name', 'default_welcome_message')->value('value');
        }

        $terms = Setting::where('name', 'term_and_condition')->value('value');
        return "$welcome:-\n$link\n" . $terms;
    }
}

if (!function_exists('getSignupConfirmationMessage')) {
    function getSignupConfirmationMessage($user_id)
    {
        $confirmation_message =  PersonalSetting::where('user_id', $user_id)
            ->where('name', 'signup_confirmation')
            ->value('value');
        if (empty($confirmation_message)) {
            $confirmation_message =  Setting::where('name', 'default_signup_message')->value('value');
        }

        return $confirmation_message;
    }
}
