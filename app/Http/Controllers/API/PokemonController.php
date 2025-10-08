<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\PokemonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PokemonController extends Controller
{
    public function __construct(private PokemonService $pokemonService)
    {
    }

    /**
     * Listar pokémon desde PokéAPI (limit, offset)
     */
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 50);
        $offset = (int) $request->query('offset', 0);
        $limit = max(1, min($limit, 200)); // limitar a 200 por seguridad

        $list = $this->pokemonService->list($limit, $offset);
        return response()->json($list);
    }

    /**
     * Detalle por id o nombre
     */
    public function show(string $idOrName): JsonResponse
    {
        $detail = $this->pokemonService->detail($idOrName);
        return response()->json($detail);
    }
}