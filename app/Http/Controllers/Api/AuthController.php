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


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('pizza_app_token')->plainTextToken;

        $data['userData'] = collect($user)->only(['user_uuid','name','email','phone_no','created_at']);
        $data['token'] = $token;
        return response()->json(['status' => true, 'message' => '', 'data' => $data]);
    }

    public function register(Request $request)
    {
        $auth = new CreateNewUser();
        $data = $auth->create($request->all());
         
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
