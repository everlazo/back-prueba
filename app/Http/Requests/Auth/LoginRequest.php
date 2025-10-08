<?php

namespace App\Http\Requests\Auth;

use App\Rules\RecaptchaRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para login
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            // token de reCAPTCHA
            'recaptcha_token' => ['sometimes', 'string', new RecaptchaRule('login', 0.5)],
        ];
    }
}
