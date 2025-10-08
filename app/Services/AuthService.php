<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Servicio de autenticación con JWT
 * - Registro, login, logout, refresh
 * - Control de sesiones con JWT tokens
 */
class AuthService
{
    public function __construct(private UserRepository $users)
    {
    }

    /**
     * Registrar un nuevo usuario y devolverlo
     */
    public function register(array $data): User
    {
        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }

    /**
     * Autenticar y emitir token JWT
     * - Implementa "solo una sesión activa" incrementando token_version
     */
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        $credentials = ['email' => $email, 'password' => $password];
        
        try {
            // Validar credenciales y autenticar
            if (! $attemptToken = JWTAuth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'email' => [__('auth.failed')],
                ]);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages([
                'email' => ['Could not create token'],
            ]);
        }

        // Usuario autenticado
        $user = JWTAuth::user();

        // Siempre establecer una NUEVA sesión activa (invalidando la anterior)
        $ttlMinutes = (int) JWTAuth::factory()->getTTL();
        $sessionId = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes($ttlMinutes);
        $this->users->updateSession($user, $sessionId, $expiresAt);

        // Incrementar token_version en la capa de repositorio para invalidar tokens previos
        $user = $this->users->incrementTokenVersion($user);

        // Emitir un nuevo token con claims actualizados
        $token = JWTAuth::fromUser($user);
        
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60
        ];
    }

    /**
     * Cerrar sesión del usuario actual (invalidar token JWT)
     */
    public function logout(): void
    {
        try {
            $user = JWTAuth::user();
            JWTAuth::invalidate(JWTAuth::getToken());
            if ($user) {
                $this->users->updateSession($user, null, null);
            }
        } catch (JWTException $e) {
            throw ValidationException::withMessages([
                'token' => ['Failed to logout, please try again'],
            ]);
        }
    }

    /**
     * Refrescar token JWT
     */
    public function refresh(): array
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            $user = JWTAuth::setToken($token)->toUser();

            // Extender vigencia de la sesión activa
            $ttlMinutes = (int) JWTAuth::factory()->getTTL();
            $this->users->updateSession($user, $user->current_session_id, Carbon::now()->addMinutes($ttlMinutes));
            
            return [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ];
        } catch (JWTException $e) {
            throw ValidationException::withMessages([
                'token' => ['Token cannot be refreshed, please login again'],
            ]);
        }
    }

    /**
     * Enviar correo para recuperar contraseña
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Resetear contraseña con token
     */
    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                // Delegar persistencia a la capa de repositorio
                $this->users->updatePassword($user, Hash::make($password));
                // Al resetear, no podemos invalidar JWT específicos fácilmente
                // pero el cambio de contraseña hará que los claims no coincidan
            }
        );

        return $status;
    }
}
