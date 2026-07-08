<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Product;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFlowTest extends TestCase
{
    use RefreshDatabase;

    private function getAuthHeader(User $user): array
    {
        $token = $user->createToken('test')->plainTextToken;
        return ['Authorization' => 'Bearer ' . $token];
    }

    public function test_create_project(): void
    {
        $user = User::factory()->create();
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->postJson('/api/projects', [
            'name' => 'Test Project',
            'description' => 'A test project',
            'status' => 'planificacion',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Project']);
    }

    public function test_list_projects(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create();
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->getJson('/api/projects');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_create_requirement_for_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->postJson("/api/projects/{$project->id}/requirements", [
            'title' => 'Test Requirement',
            'description' => 'A test requirement',
            'priority' => 'alta',
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Requirement']);
    }

    public function test_create_activity_for_requirement(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $requirement = Requirement::factory()->create(['project_id' => $project->id]);
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->postJson("/api/requirements/{$requirement->id}/activities", [
            'title' => 'Test Activity',
            'description' => 'A test activity',
            'tiempo_estimado_horas' => 10,
            'asignado_a' => $user->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Activity']);
    }

    public function test_create_product_for_activity(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $requirement = Requirement::factory()->create(['project_id' => $project->id]);
        $activity = Activity::factory()->create(['requirement_id' => $requirement->id]);
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->postJson("/api/activities/{$activity->id}/products", [
            'name' => 'Test Product',
            'type' => 'documento',
            'description' => 'A test product',
            'created_by' => $user->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Product']);
    }

    public function test_project_progress_calculation(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        Requirement::factory()->create(['project_id' => $project->id, 'status' => 'completado']);
        Requirement::factory()->create(['project_id' => $project->id, 'status' => 'pendiente']);
        Requirement::factory()->create(['project_id' => $project->id, 'status' => 'pendiente']);
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->getJson("/api/projects/{$project->id}");

        $response->assertStatus(200);
        // 1 of 3 completed = 33.33%
        $this->assertEquals(33.33, round($response->json('progreso'), 2));
    }

    public function test_dashboard_stats_endpoint(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(2)->create();
        $header = $this->getAuthHeader($user);

        $response = $this->withHeaders($header)->getJson('/api/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure(['projects_total', 'projects_active', 'requirements_total', 'activities_total']);
    }
}
