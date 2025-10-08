<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RecaptchaRule;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para solicitud de recuperación
 
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255',
                'exists:users,email'
            ],
            // token de reCAPTCHA
'recaptcha_token' => ['sometimes', 'string', new RecaptchaRule('forgot', 0.5)],
        ];
    }

    /**
     * Mensajes personalizados de validación
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'No encontramos un usuario con ese correo electrónico.',
        ];
    }
}
