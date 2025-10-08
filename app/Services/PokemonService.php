<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para consumir PokéAPI
 */
class PokemonService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.pokeapi.base_url', 'https://pokeapi.co/api/v2'), '/');
    }

    /**
     * Listado paginado desde PokéAPI.
     * Devuelve una estructura con id, name e image (sprite por id) para cada resultado.
     */
    public function list(int $limit = 10, int $offset = 0): array
    {
        $cacheKey = "pokeapi:list:limit={$limit}:offset={$offset}";
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($limit, $offset) {
            $url = $this->baseUrl . "/pokemon?limit={$limit}&offset={$offset}";
            $response = $this->http()->get($url);
            $data = $response->throw()->json();

            $results = [];
            foreach ($data['results'] ?? [] as $row) {
                $id = $this->extractIdFromUrl($row['url'] ?? '');
                $results[] = [
                    'id' => $id,
                    'name' => $row['name'] ?? null,
                    // sprite por convención
                    'image' => $this->spriteUrl((string) $id),
                    'url' => $row['url'] ?? null,
                ];
            }

            return [
                'count' => $data['count'] ?? 0,
                'next' => $data['next'] ?? null,
                'previous' => $data['previous'] ?? null,
                'results' => $results,
            ];
        });
    }

    /**
     * Detalle de un Pokémon. idOrName puede ser id numérico o nombre.
     * Intenta obtener imagen desde sprites.front_default y una descripción corta desde species.
     */
    public function detail(string $idOrName): array
    {
        $cacheKey = "pokeapi:detail:" . strtolower($idOrName);
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($idOrName) {
            $url = $this->baseUrl . '/pokemon/' . urlencode($idOrName);
            $response = $this->http()->get($url);
            $data = $response->throw()->json();

            $id = (string) ($data['id'] ?? $this->extractIdFromUrl($data['species']['url'] ?? ''));
            $image = $data['sprites']['front_default'] ?? $this->spriteUrl($id);

            // Intentar obtener una descripción corta desde species (en español o inglés)
            $description = null;
            try {
                if (!empty($data['species']['url'])) {
                    $species = $this->http()->get($data['species']['url'])->throw()->json();
                    $flavors = $species['flavor_text_entries'] ?? [];
                    // Buscar primero español, luego inglés
                    $descEs = collect($flavors)->firstWhere('language.name', 'es')['flavor_text'] ?? null;
                    $descEn = collect($flavors)->firstWhere('language.name', 'en')['flavor_text'] ?? null;
                    $description = $this->cleanFlavorText($descEs ?? $descEn);
                }
            } catch (\Throwable $e) {
                Log::warning('PokéAPI species fetch failed', ['error' => $e->getMessage()]);
            }

            return [
                'id' => (int) $id,
                'name' => $data['name'] ?? null,
                'image' => $image,
                'height' => $data['height'] ?? null,
                'weight' => $data['weight'] ?? null,
                'types' => array_map(fn ($t) => $t['type']['name'] ?? null, $data['types'] ?? []),
                'abilities' => array_map(fn ($a) => $a['ability']['name'] ?? null, $data['abilities'] ?? []),
                'description' => $description,
            ];
        });
    }

    /**
     * Construir URL del sprite por id
     */
    private function spriteUrl(string $id): string
    {
        return 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' . $id . '.png';
    }

    /**
     * Extraer id desde una URL de recurso de PokéAPI (termina con /{id}/)
     */
    private function extractIdFromUrl(string $url): ?int
    {
        if (preg_match('#/pokemon/(\d+)/?$#', $url, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    /**
     * Normalizar texto de species (eliminar saltos raros y \f)
     */
    private function cleanFlavorText(?string $text): ?string
    {
        if ($text === null) return null;
        return trim(preg_replace('/[\n\r\f]+/', ' ', $text));
    }

    /**
     * Construir cliente HTTP con configuración de SSL según entorno
     * - Si POKEAPI_VERIFY_SSL=false en .env, deshabilita verificación (solo desarrollo)
     */
    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(10);
        $verify = env('POKEAPI_VERIFY_SSL', true);
        if (is_string($verify)) {
            $verify = filter_var($verify, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $verify = $verify === null ? true : $verify;
        }
        if (! $verify) {
            $client = $client->withoutVerifying();
        }
        return $client;
    }
}
