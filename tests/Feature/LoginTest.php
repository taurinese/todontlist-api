<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{

    public function test_login_with_success()
    {
        $password = $this->faker->password(8);
        $userData = [
            'email' => $this->faker->email(),
            'password' => Hash::make($password),
            'name' => $this->faker->name()
        ];
        $user = User::create($userData);

        $formData = [
            'email' => $user->email,
            'password' => $password
        ];
        $this->assertDatabaseHas('users', $userData);
        $response = $this->postJson('/api/auth/login', $formData);
        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'email', 'name', 'created_at'])
            ->assertJson(['email' => $user->email]);
    }

    public function test_no_input()
    {
        $response = $this->postJson('/api/auth/login');
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_invalid_input()
    {
        $data = [
            'email' => $this->faker->name(),
            'password' => $this->faker->password(6)
        ];

        $response = $this->postJson('/api/auth/login', $data);
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_invalid_credentials()
    {
        $data = [
            'email' => $this->faker->email(),
            'password' => $this->faker->password()
        ];

        $response = $this->postJson('/api/auth/login', $data);
        $response->assertStatus(401)->assertJsonStructure(['errors']);
    }
}
