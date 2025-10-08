<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para reset de contraseña
 
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => [
                'required', 
                'string', 
                'email',
                'max:255',
                'exists:users,email'
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            // Importante: incluir password_confirmation para que forme parte de validated()
            'password_confirmation' => ['required'],
        ];
    }

    /**
     * Mensajes personalizados de validación
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'No encontramos un usuario con ese correo electrónico.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
