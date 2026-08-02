<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    private function authHeader(): array
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    // Index Tests
    public function test_user_can_list_own_projects(): void
    {
        Project::factory(3)->create(['user_id' => $this->user->id]);
        Project::factory(2)->create(); // for other user

        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['items'],
                'pagination',
            ]);

        $this->assertEquals(3, $response->json('pagination.total'));
    }

    public function test_list_projects_fails_without_auth(): void
    {
        $response = $this->getJson('/api/projects');
        $response->assertStatus(401);
    }

    // Store Tests
    public function test_user_can_create_project(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/projects', [
                'name'        => 'Test Project',
                'description' => 'Test Description',
                'status'      => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'description', 'status', 'created_at'],
            ]);

        $this->assertDatabaseHas('projects', [
            'name'    => 'Test Project',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_project_fails_with_missing_name(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['status', 'message', 'errors']);
    }

    public function test_create_project_fails_with_invalid_status(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->postJson('/api/projects', [
                'name'   => 'Test Project',
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    // Show Tests
    public function test_user_can_view_own_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'name', 'description', 'status', 'tasks'],
            ]);
    }

    public function test_user_cannot_view_other_user_project(): void
    {
        $project = Project::factory()->create(); // for other user

        $response = $this->withHeaders($this->authHeader())
            ->getJson("/api/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_show_project_returns_404_for_nonexistent(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->getJson('/api/projects/999');

        $response->assertStatus(404);
    }

    // Update Tests
    public function test_user_can_update_own_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/projects/{$project->id}", [
                'name'   => 'Updated Project',
                'status' => 'completed',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name'   => 'Updated Project',
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('projects', [
            'id'   => $project->id,
            'name' => 'Updated Project',
        ]);
    }

    public function test_user_cannot_update_other_user_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->putJson("/api/projects/{$project->id}", [
                'name' => 'Hacked Project',
            ]);

        $response->assertStatus(403);
    }

    public function test_update_project_returns_404_for_nonexistent(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->putJson('/api/projects/999', [
                'name' => 'Updated Project',
            ]);

        $response->assertStatus(404);
    }

    // Delete Tests
    public function test_user_can_delete_own_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 200,
                'message' => 'Project deleted successfully',
            ]);

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_user_cannot_delete_other_user_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeader())
            ->deleteJson("/api/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_delete_project_returns_404_for_nonexistent(): void
    {
        $response = $this->withHeaders($this->authHeader())
            ->deleteJson('/api/projects/999');

        $response->assertStatus(404);
    }
}
