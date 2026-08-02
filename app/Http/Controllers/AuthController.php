<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\Responses;

class AuthController extends Controller
{
    use Responses;

    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->success(
            status: 201,
            message: 'User registered successfully',
            data: [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        if (!$result) {
            return $this->error(
                status: 401,
                message: 'Invalid credentials',
            );
        }

        return $this->success(
            status: 200,
            message: 'Logged in successfully',
            data: [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    public function logout()
    {
        $this->authService->logout();

        return $this->success(
            status: 200,
            message: 'Logged out successfully',
        );
    }
}
