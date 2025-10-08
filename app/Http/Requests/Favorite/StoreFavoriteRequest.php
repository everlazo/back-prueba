<?php

namespace App\Http\Requests\Favorite;

use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para este request
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Normalizar datos antes de validar
     */
    protected function prepareForValidation(): void
    {
        $ext = $this->input('external_id');
        if ($ext !== null) {
            $this->merge(['external_id' => (string) $ext]);
        }
    }

    /**
     * Reglas de validación para crear favorito
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'external_id' => [
                'required',
                'string',
                'max:255',
                // Evitar duplicados para el usuario actual
                'unique:favorites,external_id,NULL,id,user_id,' . auth()->id(),
            ],
        ];
    }

    /**
     * Mensajes personalizados de validación
     */
    public function messages(): array
    {
        return [
            'external_id.unique' => 'Ya tienes este elemento en tus favoritos.',
        ];
    }
}
