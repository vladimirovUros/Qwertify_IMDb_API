<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        return ['user' => $user, 'token' => $this->issueToken($user)];
    }
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return ['user' => $user, 'token' => $this->issueToken($user)];
    }
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
    private function issueToken(User $user): string
    {
        return $user->createToken('api')->plainTextToken;
    }
}
