<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteStoreRequest extends FormRequest
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
            'namesurname' => 'required',
            'email' => 'required|email|unique:invites|unique:users'
        ];
    }

    public function messages()
    {
        return [
            'namesurname.required' => 'Name Surname is required !',
            'email.required' => 'Email is required !',
            'email.email' => 'Please enter a valid email !',
            'email.unique' => 'This email adress joined before !',
        ];
    }
}
