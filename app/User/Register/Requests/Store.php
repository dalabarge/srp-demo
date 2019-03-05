<?php

namespace App\User\Register\Requests;

class Store extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'    => ['required', 'string', 'email', 'max:128', 'unique:users,email'],
            'name'     => ['required', 'string', 'max:64'],
            'salt'     => ['required', 'string', 'max:255'],
            'verifier' => ['required', 'string'],
        ];
    }
}
