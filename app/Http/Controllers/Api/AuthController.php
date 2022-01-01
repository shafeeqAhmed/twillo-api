<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
// use Spatie\Permission\Models\
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\EmailVerificationRequest;
use Exception;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Twilio\Rest\Client;
use App\Models\FanClub;


class AuthController extends Controller
{

    private $client;

    public function __construct()
    {
        $sid = config('general.twilio_sid');
        $token = config('general.twilio_token');
        $this->client = new Client($sid, $token);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
//        $fan_club = new FanClub;
//        $from = $user->phone_no;
//        $messages = $this->client->messages
//            ->read(
//                [
//                    "from" => $from,
//                ],
//                100
//            );

       /* foreach ($messages as $index => $record) {

            $mess = $this->client->messages($record->sid)
                ->fetch();


     $chat_user_record=FanClub::create([
            'fan_uuid'=>Str::uuid()->toString(),
            'user_id'=> $user->id,
            'local_number'=> $mess->to,
             'fan_id'=> 0
           ]);

          
        }*/


    
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $detail = collect($user)->only(['user_uuid','name','email','phone_no', 'profile_photo_path']);
        if (count($user->getRoleNames()) > 0) {
            $detail['scope'] = $user->getRoleNames();
        } else {
            $detail['scope'] = array('influencer');
        }

        $token = $user->createToken('twilio-chat-app')->plainTextToken;

        $data['userData'] = $detail;
        $data['accessToken'] = $token;
        $data['refreshToken'] = $token;
        return response()->json(['status' => true, 'message' => '', 'data' => $data]);
    }

    public function register(Request $request)
    {

        $auth = new CreateNewUser();
        $data = $auth->create($request->all());
        if(isset($data['is_valid_reference']) && $data['is_valid_reference'] == false) {
            return response()->json(['status' => false, 'message' => 'Your Reference Link Expired', 'data' => $data]);
        }
//        $data->assignRole('fan');
        return response()->json(['status' => true, 'message' => 'You have been register successfully', 'data' => $data]);
    }
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? new JsonResponse('', 204)
                : redirect()->intended(config('fortify.home'));
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['status' => true, 'message' => 'Verfication email has been send to you, please check your mail Inbox', 'data' => []]);
    }

    public function VerificationEmail(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['status' => true, 'message' => 'Your email has been verified Successfully', 'data' => []]);
        } else {
            $request->fulfill();

            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['status' => true, 'message' => 'Your email has been verified Successfully', 'data' => []]);
            } else {
                return response()->json(['status' => false, 'message' => 'Your email has been verified Successfully', 'data' => []]);
            }
        }
    }
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink(
            $request->only('email')
        );
        if ($status === Password::RESET_LINK_SENT) {

            return response()->json(['status' => true, 'message' => __($status), 'data' => []]);
        } else {
            return response()->json(['status' => false, 'message' => __($status), 'data' => []]);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['status' => true, 'message' => __($status), 'data' => []]);
        } else {
            return response()->json(['status' => false, 'message' => __($status), 'data' => []]);
        }
    }
    public function isVerifiedEmail(Request $request) {
        $verify = User::where('id', $request->user()->id)->value('email_verified_at');
        $data['isVerified'] = !is_null($verify);

        return response()->json(['status' => true, 'message' => '', 'data' => $data]);
    }
    public function logout(Request $request) {

        $request->user()->currentAccessToken()->delete();
    }
}
