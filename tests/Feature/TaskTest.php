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

    public function test_add_unauthenticated()
    {
        $taskData = [
            'body' => $this->faker->text(),
            'done' => 0
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(401);
    }

    public function test_add_no_input()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/tasks');

        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_add_invalid_input()
    {
        $data = [
            'body' => $this->faker->text()
        ];

        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/tasks', $data);
        $response->assertStatus(422)->assertJsonStructure(['errors']);
    }

    public function test_get_all_with_success()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        for ($i = 0; $i < 5; $i++) {
            Task::create([
                'body' => $this->faker->text(),
                'done' => $this->faker->boolean(),
                'user_id' => $user->id
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/tasks');
        $response->assertStatus(201)->assertJsonStructure(['tasks']);
    }

    public function test_get_all_unauthenticated()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    public function test_delete_with_success()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->deleteJson('/api/tasks/' . $task->id);
        $response->assertStatus(200);
    }

    public function test_delete_unauthenticated()
    {
        $user = $this->createUser();

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
    }

    public function test_delete_no_access()
    {
        $this->createUser();
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => 1
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->deleteJson('/api/tasks/' . $task->id);
        $response->assertStatus(403);
    }

    public function test_delete_doesnt_exist()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->deleteJson('/api/tasks/' . 1);
        $response->assertStatus(404);
    }

    public function test_get_one_with_success()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/tasks/' . $task->id);
        $response->assertStatus(200);
    }

    public function test_get_one_unauthenticated()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $response = $this->getJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
    }

    public function test_get_one_no_access()
    {
        $this->createUser();
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => 1
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/tasks/' . $task->id);
        $response->assertStatus(403);
    }

    public function test_get_one_doesnt_exist()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');


        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/tasks/2');
        $response->assertStatus(404);
    }

    public function test_update_with_success()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $newData = [
            'content' => $this->faker->text(),
            'done' => $this->faker->boolean()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->putJson('/api/tasks/' . $task->id, $newData);
        $response->assertStatus(200);
    }

    public function test_update_unauthenticated()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => $user->id
        ]);

        $newData = [
            'content' => $this->faker->text(),
            'done' => $this->faker->boolean()
        ];

        $response = $this->putJson('/api/tasks/' . $task->id, $newData);
        $response->assertStatus(401);
    }

    public function test_update_no_access()
    {
        $this->createUser();
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $task = Task::create([
            'body' => $this->faker->text(),
            'done' => $this->faker->boolean(),
            'user_id' => 1
        ]);

        $newData = [
            'content' => $this->faker->text(),
            'done' => $this->faker->boolean()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->putJson('/api/tasks/' . $task->id, $newData);
        $response->assertStatus(403);
    }

    public function test_update_doesnt_exist()
    {
        $user = $this->createUser();

        $token = $user->createToken($user->email)->plainTextToken;

        $this->actingAs($user, 'api');

        $newData = [
            'content' => $this->faker->text(),
            'done' => $this->faker->boolean()
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)->putJson('/api/tasks/2', $newData);
        $response->assertStatus(404);
    }
}
