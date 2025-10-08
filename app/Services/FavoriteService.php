<?php

namespace App\Services;

use App\Models\Favorite;
use App\Repositories\FavoriteRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\PokemonService;

/**
 * Servicio de favoritos (contiene la lógica de negocio)
 */
class FavoriteService
{
    public function __construct(private FavoriteRepository $favoriteRepository)
    {
    }

    public function listForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->favoriteRepository->listByUser($userId, $perPage);
    }

    public function createForUser(int $userId, array $data): Favorite
    {
        // Enriquecer SIEMPRE desde PokéAPI; los datos del cliente no se aceptan
        try {
            /** @var PokemonService $poke */
            $poke = app(PokemonService::class);
            $detail = $poke->detail($data['external_id']);
        } catch (\Throwable $e) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'external_id' => ['No se pudo obtener información desde PokéAPI para el identificador proporcionado.'],
            ]);
        }

        $payload = [
            'user_id' => $userId,
            'external_id' => $data['external_id'],
            'name' => $detail['name'] ?? null,
            'image' => $detail['image'] ?? null,
            'description' => $detail['description'] ?? null,
        ];

        return $this->favoriteRepository->create($payload);
    }

    public function updateForUser(Favorite $favorite, array $data): Favorite
    {
        // external_id no se actualiza por diseño
        unset($data['external_id'], $data['user_id']);
        return $this->favoriteRepository->update($favorite, $data);
    }

    public function delete(Favorite $favorite): void
    {
        $this->favoriteRepository->delete($favorite);
    }
}
