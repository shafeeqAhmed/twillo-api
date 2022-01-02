<?php

namespace App\Actions\Fortify;

use App\Models\FanClub;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Str;

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_no' => ['required', 'string', 'max:255'],
            'reference' => ['required'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();

//         check fan reference exist or not?
       // $fan_club = FanClub::where('temp_id',$input['reference'])->where('is_active',0)->first();
       // if(!$fan_club) {
       //     $data['is_valid_reference'] = false;
       //    return $data;
      //  }

        $user = User::create([
            'user_uuid' => Str::uuid()->toString(),
            'name' => $input['first_name'].' '. $input['last_name'],
            'fname' => $input['first_name'],
            'lname' => $input['last_name'],
            'email' => $input['email'],
            'country_id' => $input['country_id'],
            'city' => $input['city'],
            'gender' => $input['gender'],
            'phone_no' => $input['phone_no'],
            'dob' => $input['dob'],
            'instagram' => $input['instagram'],
            'twitter' => $input['twitter'],
            'ticktok' => $input['ticktok'],
            'password' => Hash::make($input['password']),
        ]);
        //if user register successfully add him into his fan club
//        dd($user->id);
      //  $fan_club->update(['fan_id'=>$user->id,'is_active'=>1]);
        return $user;
    }
}
