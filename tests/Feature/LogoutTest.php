<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{

    public function test_logout_with_success()
    {
        $userData = [
            'email' => $this->faker->email(),
            'name' => $this->faker->name(),
            'password' => Hash::make($this->faker->password(8))
        ];
        $user = User::create($userData);

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/auth/logout', $userData);

        $response->assertStatus(204);
    }

    public function test_unauthenticated()
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)->assertJsonStructure(['message']);
    }
}
