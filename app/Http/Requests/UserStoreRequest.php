<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            'email' => 'required|email|unique:users',
            'password' => 'min:8|required_with:password-confirm|same:password-confirm',
            'password-confirm' => 'min:8',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ];
    }
}
