<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Registro de usuarios
     */
    public function register(RegisterRequest $request, AuthService $authService): JsonResponse
    {
        $user = $authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Login con emisión de token (una sesión activa)
     */
    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        $data = $authService->login(
            $request->validated()['email'],
            $request->validated()['password']
        );

        return response()->json([
            'message' => 'Logged in successfully',
            'token' => $data['token'],
            'token_type' => $data['token_type'],
            'expires_in' => $data['expires_in'],
            'user' => [
                'id' => $data['user']->id,
                'name' => $data['user']->name,
                'email' => $data['user']->email,
            ],
        ]);
    }

    /**
     * Logout del usuario actual (revoca token actual)
     */
    public function logout(AuthService $authService): JsonResponse
    {
        try {
            if (! JWTAuth::getToken()) {
                return response()->json([
                    'message' => 'Token not provided',
                    'error' => 'token_absent'
                ], 401);
            }

            $authService->logout();
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalid or already logged out',
                'error' => 'token_invalid'
            ], 401);
        }
    }

    /**
     * Refrescar token JWT
     */
    public function refresh(AuthService $authService): JsonResponse
    {
        try {
            // Verificar si hay token en la cabecera Authorization
            if (! JWTAuth::getToken()) {
                return response()->json([
                    'message' => 'Token not provided',
                    'error' => 'token_absent'
                ], 401);
            }

            $data = $authService->refresh();

            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $data['token'],
                'token_type' => $data['token_type'],
                'expires_in' => $data['expires_in'],
                'user' => [
                    'id' => $data['user']->id,
                    'name' => $data['user']->name,
                    'email' => $data['user']->email,
                ],
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalid or cannot be refreshed',
                'error' => 'token_invalid'
            ], 401);
        }
    }

    /**
     * Enviar email de recuperación
     */
    public function forgotPassword(ForgotPasswordRequest $request, AuthService $authService): JsonResponse
    {
        $status = $authService->sendPasswordResetLink($request->validated()['email']);

        return response()->json([
            'message' => 'Si existe una cuenta con ese email, recibirás un enlace de recuperación.',
            'status' => $status
        ]);
    }

    /**
     * Reset de contraseña con token
     */
    public function resetPassword(ResetPasswordRequest $request, AuthService $authService): JsonResponse
    {
        $status = $authService->resetPassword($request->validated());

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente.',
            'status' => $status
        ]);
    }
}
