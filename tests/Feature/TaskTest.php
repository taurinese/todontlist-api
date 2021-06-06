<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TaskTest extends TestCase
{

    public function createUser()
    {
        $userData = [
            'email' => $this->faker->email(),
            'name' => $this->faker->name(),
            'password' => Hash::make($this->faker->password(8))
        ];
        return User::create($userData);
    }
    public function test_add_with_success()
    {

        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $taskData = [
            'body' => $this->faker->text(),
            'done' => 0
        ];

        // $task = Task::create($taskData);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/tasks', $taskData);

        $this->assertDatabaseHas('tasks', $taskData);
        $response->assertStatus(201)->assertJsonStructure(['id', 'created_at', 'updated_at', 'body', 'done', 'user_id']);
    }
}
