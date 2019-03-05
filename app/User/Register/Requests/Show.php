<?php

namespace App\User\Register\Requests;

class Show extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['nullable', 'string', 'email', 'max:128'],
            'name'  => ['nullable', 'string', 'max:64'],
        ];
    }
}
