<?php

namespace App\Http\Requests\Mail;

class MailRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
