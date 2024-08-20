<?php

namespace App\Http\Requests;

use Doctrine\Inflector\Rules\English\Rules;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
    public function rules()
    {
        return [
            'first_name'=> ["required", "string", "max:40"],
            'last_name'=> ["required", "string", "max:40"],
            'email'=> ["required", "email", "max:100", "unique:users"],
            'password' => ["required", "string"],
            'image' => ["required", "string"],
            'used_google_oauth' => ["boolean"],
            'gender' => ["required", "string", "in:male,female"],
            'nin_number'=> ["required", "string", "max:12"],
            'city'=> ["required", "string", "max:40"],
            'zip_code'=> ["required", "string", "max:40"],
            'address'=> ["required", "string", "max:255"],
        ];
    }
}
