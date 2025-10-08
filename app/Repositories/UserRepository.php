<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Crear un nuevo usuario persistiendo en base de datos
     */
    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    /**
     * Buscar usuario por email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Incrementar token_version y devolver el usuario actualizado
     */
    public function incrementTokenVersion(User $user): User
    {
        $user->increment('token_version');
        return $user->fresh();
    }

    /**
     * Actualizar la contraseña del usuario con un hash ya calculado
     */
    public function updatePassword(User $user, string $hashedPassword): void
    {
        $user->forceFill(['password' => $hashedPassword])->save();
    }

    public function updateSession(User $user, ?string $sessionId, ?\Carbon\CarbonInterface $expiresAt): void
    {
        $user->forceFill([
            'current_session_id' => $sessionId,
            'current_session_expires_at' => $expiresAt,
        ])->save();
    }
}
