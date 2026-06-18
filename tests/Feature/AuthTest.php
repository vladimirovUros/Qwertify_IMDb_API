<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nikola',
            'email' => 'nikola@gmail.com',
            'password' => 'sifra1234!',
            'password_confirmation' => 'sifra1234!',
        ]);
        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id', 'name', 'email'], 'token']);
        $this->assertDatabaseHas('users', ['email' => 'nikola@gmail.com']);
    }
    public function test_registration_validates_its_input(): void
    {
        $this->postJson('/api/register', ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }
    public function test_registration_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/register', [
            'name' => 'Someone',
            'email' => 'taken@example.com',
            'password' => 'sifra1234!',
            'password_confirmation' => 'sifra1234!',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
    public function test_a_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create(['password' => 'sifra1234!']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'sifra1234!',
        ])->assertOk()->assertJsonStructure(['data' => ['id'], 'token']);
    }
    public function test_login_fails_with_wrong_credentials(): void
    {
        $user = User::factory()->create(['password' => 'sifra1234!']);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422)->assertJsonValidationErrors(['email']);
    }
    public function test_protected_routes_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/watchlist')->assertUnauthorized();
    }
    public function test_a_user_can_logout_and_the_current_token_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertNoContent();

        $this->assertCount(0, $user->fresh()->tokens);
    }
}
