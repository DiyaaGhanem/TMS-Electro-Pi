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


    /**
     * @group Authentication
     * @summary Register a new user
     * @bodyParam name string required The user's full name. Example: Diyaa Ghanem
     * @bodyParam email string required The user's email. Example: diyaa@example.com
     * @bodyParam password string required Min 8 chars. Example: password123
     * @bodyParam password_confirmation string required Must match password. Example: password123
     * @response 201 scenario="Success" {
     *   "status": 201,
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Diyaa Ghanem",
     *       "email": "diyaa@example.com",
     *       "created_at": "2026-08-02 10:00:00"
     *     },
     *     "token": "1|abc123xyz..."
     *   }
     * }
     * @response 422 scenario="Validation Error" {
     *   "status": 422,
     *   "message": "The email has already been taken.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
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


    /**
     * @group Authentication
     * @summary Login user and get token
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Logged in successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "Diyaa Ghanem",
     *       "email": "diyaa@example.com",
     *       "created_at": "2026-08-02 10:00:00"
     *     },
     *     "token": "1|abc123xyz..."
     *   }
     * }
     * @response 401 scenario="Invalid Credentials" {
     *   "status": 401,
     *   "message": "Invalid credentials"
     * }
     */
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

    /**
     * @group Authentication
     * @summary Logout current user
     * @authenticated
     * @response 200 scenario="Success" {
     *   "status": 200,
     *   "message": "Logged out successfully"
     * }
     * @response 401 scenario="Unauthenticated" {
     *   "message": "Unauthenticated."
     * }
     */
    public function logout()
    {
        $this->authService->logout();

        return $this->success(
            status: 200,
            message: 'Logged out successfully',
        );
    }
}
