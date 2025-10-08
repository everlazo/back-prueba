<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\StoreFavoriteRequest;
use App\Models\Favorite;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $favoriteService)
    {
    }

    /**
     * Listar favoritos del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = $this->favoriteService->listForUser($request->user()->id);
        return response()->json($favorites);
    }

    /**
     * Crear un nuevo favorito
     */
    public function store(StoreFavoriteRequest $request): JsonResponse
    {
        // Solo aceptamos external_id; el resto se completa desde PokéAPI
        $favorite = $this->favoriteService->createForUser($request->user()->id, $request->validated());
        return response()->json([
            'message' => 'Favorite created',
            'data' => $favorite,
        ], 201);
    }

    /**
     * Mostrar un favorito por ID (sólo del usuario)
     */
    public function show(Request $request, string $key): JsonResponse
    {
        // Buscar por id interno (numérico) o por external_id (texto/id/nombre)
        $query = Favorite::query()->where('user_id', $request->user()->id);
        if (ctype_digit($key)) {
            $favorite = $query->where('id', (int) $key)->first();
        } else {
            $favorite = $query->where('external_id', $key)->first();
        }
        if (! $favorite) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($favorite);
    }

    /**
     * Actualizar un favorito
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Los datos provenientes de PokéAPI son inmutables; no se permite actualizar
        return response()->json([
            'message' => 'Favorites are immutable. Update operation is not allowed.'
        ], 405);
    }

    /**
     * Eliminar un favorito
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $favorite = Favorite::where('user_id', $request->user()->id)->find($id);
        if (! $favorite) {
            return response()->json(['message' => 'Not found'], 404);
        }
        $this->favoriteService->delete($favorite);
        return response()->json(['message' => 'Favorite deleted']);
    }
}
