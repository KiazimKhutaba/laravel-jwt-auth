<?php

namespace Devkit2026\JwtAuth\Http\Middleware;

use Closure;
use Devkit2026\JwtAuth\DTO\UserDto;
use Devkit2026\JwtAuth\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JwtAuthenticate
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => ['code' => 'ERR_TOKEN_MISSING', 'message' => 'Token not provided']], 401);
        }

        try {
            $payload = $this->jwtService->decode($token);
            $userId = $payload['user_id'] ?? null;

            if (!$userId) {
                return response()->json(['error' => ['code' => 'ERR_INVALID_TOKEN', 'message' => 'Invalid token payload']], 401);
            }

            $userModel = config('jwt_auth.user_model');
            $user = $userModel::find($userId);

            if (!$user) {
                return response()->json(['error' => ['code' => 'ERR_USER_NOT_FOUND', 'message' => 'User not found']], 404);
            }

            $authUserType = config('jwt_auth.auth_user_type', 'dto');
            
            if ($authUserType === 'dto') {
                // Set UserDto as authenticated user
                $userDto = UserDto::fromModel($user);
                // Store the DTO in request attributes for later retrieval
                $request->attributes->set('auth_user_dto', $userDto);
                // Also set the model for Laravel's Auth facade
                Auth::setUser($user);
            } else {
                // Set Laravel Auth user model
                Auth::setUser($user);
            }

        } catch (\Exception $e) {
             if ($e instanceof \Devkit2026\JwtAuth\Exceptions\JwtAuthException) {
                 return $e->render($request);
             }

             return response()->json(['error' => ['code' => 'ERR_INVALID_TOKEN', 'message' => $e->getMessage()]], 401);
        }

        return $next($request);
    }
}
