<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class updateInfluencer extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Request $request)
    {
           return [
            'email' =>  'required|unique:users,email,' . $this->user_uuid.',user_uuid',
            'fname' => 'required|string|max:50',
            'lname' =>  'required|string|max:50',
            'phone_no' =>  'required|string|max:15',
            'country_id' =>  'required|integer',
            'twilo_id' =>  'required|integer',
        ];   
    }
}
