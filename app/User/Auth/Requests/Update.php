<?php

namespace App\User\Auth\Requests;

class Update extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email', 'max:128'],
            'key'   => ['required', 'string'],
            'proof' => ['required', 'string'],
        ];
    }
}
