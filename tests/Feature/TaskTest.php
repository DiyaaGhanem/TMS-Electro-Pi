<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user    = User::factory()->create();
        $this->token   = $this->user->createToken('auth_token')->plainTextToken;
        $this->project = Project::factory()->create(['user_id' => $this->user->id]);
    }

    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // Index Tests
    public function test_user_can_list_tasks_for_own_project(): void
    {
        Task::factory(3)->create(['project_id' => $this->project->id]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['items'],
                'pagination',
            ]);

        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_user_cannot_list_tasks_for_other_user_project(): void
    {
        $otherProject = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$otherProject->id}/tasks");

        $response->assertStatus(403);
    }

    public function test_list_tasks_returns_404_for_nonexistent_project(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/projects/999/tasks');

        $response->assertStatus(404);
    }

    // Filter Tests
    public function test_user_can_filter_tasks_by_status(): void
    {
        Task::factory(2)->create([
            'project_id' => $this->project->id,
            'status'     => 'todo',
        ]);
        Task::factory(1)->create([
            'project_id' => $this->project->id,
            'status'     => 'done',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$this->project->id}/tasks?status=todo");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.total'));
    }

    public function test_user_can_filter_tasks_by_priority(): void
    {
        Task::factory(2)->create([
            'project_id' => $this->project->id,
            'priority'   => 'high',
        ]);
        Task::factory(1)->create([
            'project_id' => $this->project->id,
            'priority'   => 'low',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$this->project->id}/tasks?priority=high");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.total'));
    }

    public function test_user_can_search_tasks_by_title(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'title'      => 'Fix login bug',
        ]);
        Task::factory()->create([
            'project_id' => $this->project->id,
            'title'      => 'Add dashboard',
        ]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$this->project->id}/tasks?search=login");

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('pagination.total'));
    }

    // Store Tests
    public function test_user_can_create_task(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title'       => 'Fix login bug',
                'description' => 'The login button is not working.',
                'priority'    => 'high',
                'status'      => 'todo',
                'due_date'    => now()->addDays(5)->format('Y-m-d'),
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'title', 'description', 'priority', 'status', 'due_date'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title'      => 'Fix login bug',
            'project_id' => $this->project->id,
        ]);
    }

    public function test_create_task_fails_with_missing_title(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/projects/{$this->project->id}/tasks", []);

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'message', 'errors']);
    }

    public function test_create_task_fails_with_invalid_priority(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/projects/{$this->project->id}/tasks", [
                'title'    => 'Fix login bug',
                'priority' => 'invalid_priority',
            ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_create_task_in_other_user_project(): void
    {
        $otherProject = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->postJson("/api/projects/{$otherProject->id}/tasks", [
                'title' => 'Fix login bug',
            ]);

        $response->assertStatus(403);
    }

    // Update Tests
    public function test_user_can_update_task(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/projects/{$this->project->id}/tasks/{$task->id}", [
                'title'  => 'Updated Task',
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title'  => 'Updated Task',
                    'status' => 'in_progress',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id'     => $task->id,
            'title'  => 'Updated Task',
            'status' => 'in_progress',
        ]);
    }

    public function test_user_cannot_update_task_in_other_user_project(): void
    {
        $otherProject = Project::factory()->create();
        $task         = Task::factory()->create(['project_id' => $otherProject->id]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/projects/{$otherProject->id}/tasks/{$task->id}", [
                'title' => 'Hacked Task',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_task_returns_404_for_nonexistent(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/projects/{$this->project->id}/tasks/999", [
                'title' => 'Updated Task',
            ]);

        $response->assertStatus(404);
    }

    // Delete Tests
    public function test_user_can_delete_task(): void
    {
        $task = Task::factory()->create(['project_id' => $this->project->id]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/projects/{$this->project->id}/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 200,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_delete_task_in_other_user_project(): void
    {
        $otherProject = Project::factory()->create();
        $task         = Task::factory()->create(['project_id' => $otherProject->id]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/projects/{$otherProject->id}/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_delete_task_returns_404_for_nonexistent(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/projects/{$this->project->id}/tasks/999");

        $response->assertStatus(404);
    }
}
