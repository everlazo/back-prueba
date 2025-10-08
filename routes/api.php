<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FavoriteController;
use App\Http\Controllers\API\PokemonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas API para la aplicación. Estas rutas son
| cargadas por el RouteServiceProvider y están asignadas al middleware
| grupo "api". ¡Disfruta construyendo tu API!
|
*/

// Rutas públicas de autenticación
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
// Refrescar token (requiere token en Authorization, pero responde de forma amigable si falta)
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Rutas públicas a PokéAPI (proxy controlado)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/pokemon', [PokemonController::class, 'index']);
    Route::get('/pokemon/{idOrName}', [PokemonController::class, 'show']);
});

// Cerrar sesión
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Rutas protegidas con JWT
Route::middleware(['jwt.auth', 'single.session'])->group(function () {
    // Información del usuario actual
    Route::get('/user', function (Request $request) {
        $user = auth('api')->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ]);
    });
    
    // CRUD de favoritos
    Route::apiResource('/favorites', FavoriteController::class)->except(['update']);
});

// Ruta de health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});