<?php

namespace Devkit2026\JwtAuth\Http\Controllers;

use Devkit2026\JwtAuth\DTO\UserDto;
use Devkit2026\JwtAuth\Exceptions\EmailNotVerifiedException;
use Devkit2026\JwtAuth\Exceptions\InvalidCredentialsException;
use Devkit2026\JwtAuth\Http\Requests\LoginRequest;
use Devkit2026\JwtAuth\Http\Requests\RegisterRequest;
use Devkit2026\JwtAuth\Http\Traits\ApiResponse;
use Devkit2026\JwtAuth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $userDto = $this->authService->registerByEmailPassword($request->toDto());

        return $this->successResponse([
            'message' => 'User registered successfully. Please verify your email.',
            'user' => $userDto->toArray(),
        ], 201);
    }

    /**
     * @throws EmailNotVerifiedException
     * @throws InvalidCredentialsException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->toDto());
        
        $responseData = [
            'access_token' => $result['access_token'],
            'token_type' => 'bearer',
            'expires_in' => config('jwt_auth.access_ttl') * 60,
            'user' => $result['user']->toArray(),
        ];

        $refreshMethod = config('jwt_auth.refresh_token_method', 'cookie');

        if ($refreshMethod === 'body') {
            // Include refresh token in response body
            $responseData['refresh_token'] = $result['refresh_token'];
            return $this->successResponse($responseData);
        }

        $cookie = $this->makeRefreshCookie($result['refresh_token']);

        return $this->successResponse($responseData)->withCookie($cookie);
    }

    public function refresh(Request $request): JsonResponse
    {
        $refreshMethod = config('jwt_auth.refresh_token_method', 'cookie');
        
        if ($refreshMethod === 'body') {
            $refreshToken = $request->input('refresh_token');
        } else {
            $refreshToken = $request->cookie(config('jwt_auth.refresh_cookie_name', 'refresh_token'));
        }

        if (!$refreshToken) {
            return $this->errorResponse('ERR_TOKEN_MISSING', 'Refresh token missing', 401);
        }

        $result = $this->authService->refresh($refreshToken);

        $responseData = [
            'access_token' => $result['access_token'],
            'token_type' => 'bearer',
            'expires_in' => config('jwt_auth.access_ttl') * 60,
            'user' => $result['user']->toArray(),
        ];

        if ($refreshMethod === 'body') {
            $responseData['refresh_token'] = $result['refresh_token'];
            return $this->successResponse($responseData);
        }

        // Send new refresh token as cookie
        $cookie = $this->makeRefreshCookie($result['refresh_token']);

        return $this->successResponse($responseData)->withCookie($cookie);
    }

    public function logout(Request $request): JsonResponse
    {
        $refreshMethod = config('jwt_auth.refresh_token_method', 'cookie');
        
        if ($refreshMethod === 'body') {
            $refreshToken = $request->input('refresh_token');
        } else {
            $refreshToken = $request->cookie(config('jwt_auth.refresh_cookie_name', 'refresh_token'));
        }

        if ($refreshToken) {
            $this->authService->logout($refreshToken);
        }

        if ($refreshMethod === 'cookie') {
            $cookie = Cookie::forget(config('jwt_auth.refresh_cookie_name', 'refresh_token'));
            return $this->successResponse(['message' => 'Logged out successfully'])->withCookie($cookie);
        }

        return $this->successResponse(['message' => 'Logged out successfully']);
    }

    public function makeRefreshCookie(string $refresh_token): SymfonyCookie
    {
        // Default: send refresh token as httpOnly cookie
        $cookieName = config('jwt_auth.refresh_cookie_name', 'refresh_token');

        return Cookie::make(
            $cookieName,
            $refresh_token,
            config('jwt_auth.refresh_ttl'), // minutes
            '/',
            null,
            true, // secure
            true, // httpOnly
            false, // raw
            'Strict' // sameSite
        );
    }

    public function me(Request $request): JsonResponse
    {
        $authUserType = config('jwt_auth.auth_user_type', 'dto');
        
        if ($authUserType === 'dto') {
            // Return UserDto from request attributes
            $userDto = $request->attributes->get('auth_user_dto');
            if ($userDto) {
                return $this->successResponse([
                    'user' => $userDto->toArray(),
                ]);
            }
            // Fallback: create DTO from authenticated user
            $user = $request->user();
            if ($user) {
                return $this->successResponse([
                    'user' => UserDto::fromModel($user)->toArray(),
                ]);
            }
        }

        // Return Laravel Auth user model
        return $this->successResponse([
            'user' => $request->user(),
        ]);
    }
}
