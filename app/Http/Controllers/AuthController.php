<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->auth->register($request->validated());

        return $this->tokenResponse($result, Response::HTTP_CREATED);
    }
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->login(
            $request->validated('email'),
            $request->validated('password'),
        );

        return $this->tokenResponse($result, Response::HTTP_OK);
    }
    public function logout(Request $request): Response
    {
        $this->auth->logout($request->user());

        return response()->noContent();
    }
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
    private function tokenResponse(array $result, int $status): JsonResponse
    {
        return (new UserResource($result['user']))
            ->additional(['token' => $result['token']])
            ->response()
            ->setStatusCode($status);
    }
}
