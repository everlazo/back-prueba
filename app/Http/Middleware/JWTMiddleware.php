<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JWTMiddleware
{
    /**
     * Manejar una petición entrante - Validar token JWT
 
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
            
        } catch (TokenExpiredException $e) {
            return response()->json([
                'message' => 'Token has expired',
                'error' => 'token_expired'
            ], 401);
            
        } catch (TokenInvalidException $e) {
            return response()->json([
                'message' => 'Token is invalid',
                'error' => 'token_invalid'
            ], 401);
            
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token is required',
                'error' => 'token_absent'
            ], 401);
        }
        
        return $next($request);
    }
}
