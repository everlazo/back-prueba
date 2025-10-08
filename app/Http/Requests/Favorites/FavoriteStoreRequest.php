<?php

namespace App\\Http\\Requests\\Favorites;

use Illuminate\\Foundation\\Http\\FormRequest;
use Illuminate\\Validation\\Rule;

class FavoriteStoreRequest extends FormRequest
{
    /**
     * Autorizar este request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para creación de favoritos
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'external_id' => [
                'required', 'string',
                // Único por usuario
                Rule::unique('favorites', 'external_id')->where(fn ($q) => $q->where('user_id', $this->user()->id)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
        ];
    }
}
