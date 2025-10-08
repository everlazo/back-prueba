<?php

namespace App\Repositories;

use App\Models\Favorite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repositorio para gestionar persistencia de favoritos
 */
class FavoriteRepository
{
    public function listByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Favorite::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByIdForUser(int $id, int $userId): ?Favorite
    {
        return Favorite::query()
            ->where('user_id', $userId)
            ->where('id', $id)
            ->first();
    }

    public function findByExternalIdForUser(string $externalId, int $userId): ?Favorite
    {
        return Favorite::query()
            ->where('user_id', $userId)
            ->where('external_id', $externalId)
            ->first();
    }

    public function create(array $data): Favorite
    {
        // user_id debe venir en $data
        return Favorite::create($data);
    }

    public function update(Favorite $favorite, array $data): Favorite
    {
        $favorite->fill($data);
        $favorite->save();
        return $favorite;
    }

    public function delete(Favorite $favorite): void
    {
        $favorite->delete();
    }
}
