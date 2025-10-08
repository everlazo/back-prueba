<?php

namespace App\\Http\\Requests\\Favorites;

use Illuminate\\Foundation\\Http\\FormRequest;

class FavoriteUpdateRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualización de favoritos
     * (external_id no se actualiza)
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
