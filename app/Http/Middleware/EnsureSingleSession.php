<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class EnsureSingleSession
{
    /**
     * Asegurar una sola sesión activa por usuario con JWT usando token_version
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // No dependas de $request->user(); obtén el usuario desde el token para garantizar consistencia
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $user = JWTAuth::parseToken()->toUser();
            if (! $user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            $tokenVersionClaim = (int) $payload->get('tv', 1);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token is required', 'error' => 'token_absent'], 401);
        }

        // Comparar claim 'tv' vs columna actual del usuario
        if ($tokenVersionClaim !== (int) ($user->token_version ?? 1)) {
            return response()->json([
                'message' => 'Token revoked due to a new login on this account',
                'error' => 'token_revoked'
            ], 401);
        }

        // Validar que el 'sid' del token coincida con la sesión vigente
        $sid = $payload->get('sid', null);
        if ($sid && $user->current_session_id && $sid !== $user->current_session_id) {
            return response()->json([
                'message' => 'Another session is active for this account',
                'error' => 'session_conflict'
            ], 401);
        }

        return $next($request);
    }
}
