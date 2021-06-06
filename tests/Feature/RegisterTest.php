<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_register_with_success()
    {
        $data = [
            'email' => $this->faker->email(),
            'password' => $this->faker->password(6),
            'name' => $this->faker->name()
        ];

        $response = $this->postJson('/api/auth/register', $data);
        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'name', 'email', 'created_at'])
            ->assertJson(['name' => $data['name'], 'email' => $data['email']]);
    }

    public function test_no_input()
    {

        $response = $this->postJson('/api/auth/register');
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_invalid_input()
    {
        $data = [
            'email' => $this->faker->name(),
            'password' => $this->faker->password(6),
            'name' => $this->faker->name()
        ];

        $response = $this->postJson('/api/auth/register', $data);
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_already_registered()
    {
        $password = $this->faker->password(8);
        $userData = [
            'email' => $this->faker->email(),
            'password' => Hash::make($password),
            'name' => $this->faker->name()
        ];
        $user = User::create($userData);
        $this->assertDatabaseHas('users', $userData);

        $formData = [
            'email' => $user->email,
            'password' => Hash::make($password),
            'name' => $this->faker->name()
        ];
        $response = $this->postJson('/api/auth/register', $formData);
        $response->assertStatus(409)
            ->assertJsonStructure(['errors']);
    }
}
