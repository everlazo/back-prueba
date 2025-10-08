<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Personalizar URL de reset de contraseña para entorno API (sin rutas web)
        \Illuminate\Auth\Notifications\ResetPassword::createUrlUsing(function ($user, string $token) {
            $frontend = rtrim(config('app.frontend_url', config('app.url')), '/');
            $email = urlencode($user->email);
            // Enlace que consumirá el frontend (ruta del SPA)
            return "$frontend/reset?token=$token&email=$email";
        });
    }
}
