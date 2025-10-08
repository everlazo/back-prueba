<?php

namespace App\Http\Requests\Auth;

use App\Rules\RecaptchaRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para registro
 
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // token de reCAPTCHA
            'recaptcha_token' => ['sometimes', 'string', new RecaptchaRule('register', 0.6)],
        ];
    }
}
