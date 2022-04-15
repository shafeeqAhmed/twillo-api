<?php

namespace App\Actions\Fortify;

use App\Models\Fan;
use App\Models\FanClub;
use App\Models\PersonalSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            // 'name' => ['required', 'string', 'max:255'],
            //            'email' => ['required', 'string', 'email', 'max:255',],
            //             'phone_no' => ['required', 'string', 'max:255'],
            'reference' => ['required'],
            //            'password' => $this->passwordRules(),
            //            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

        //         check fan reference to exist or not?
        $fan_club = FanClub::where('temp_id', $input['reference'])
            ->where('is_active', 0)
            ->with('user')
            ->first();
        if (!$fan_club) {
            $data['is_valid_reference'] = false;
            return $data;
        }

        $minAge = PersonalSetting::where('name', 'min_age')->value('value');
        $age = Carbon::parse($input['dob'])->diff(Carbon::now())->y;

        //check fan minimum a age
        if (!($age > $minAge)) {
            $data['min_age_error'] = false;
            $data['fan_age'] = $age;
            $data['min_fan_age'] = $minAge;
            return $data;
        }

        $fan_data = [
            'fan_uuid' => Str::uuid()->toString(),
            'fname' => $input['first_name'],
            'lname' => $input['last_name'],
            'email' => $input['email'],
            'profile_photo_path' => asset('storage/users/profile/default.png'),
            'country_id' => $input['country_id'],
            'city' => $input['city'],
            'gender' => $input['gender'],
            'phone_no' => $fan_club->local_number,
            'dob' => $input['dob'],
            'instagram' => $input['instagram'],
            'twitter' => $input['twitter'],
            'ticktok' => $input['ticktok'],
            'latitude' => $input['latitude'],
            'longitude' => $input['longitude'],

        ];
        DB::beginTransaction();
        $fan = Fan::create($fan_data);

        $result = FanClub::updateFanClub('temp_id', $input['reference'], ['fan_id' => $fan->id, 'is_active' => 1]);
        if ($result) {
            // sendSms($fan_club['user']['phone_no'], $fan_club->local_number, 'Hey you are officially saved in my contacts!! Quick info your carrier’s Msg&Data rates may apply. Reply HELP for help, STOP to cancel.');
            $message = getSignupConfirmationMessage($fan_club['user']['id']);
            sendSms($fan_club['user']['phone_no'], $fan_club->local_number, $message);
            DB::commit();
        } else {
            DB::rollBack();
        }
        return $fan;
    }
}
