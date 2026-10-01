<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCreationTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(): array
    {
        $lead = User::factory()->create();
        $developer = User::factory()->create();
        $outsider = User::factory()->create();

        $project = Project::factory()->create(['user_id' => $lead->id]);
        $project->users()->attach([
            $lead->id => ['role' => 'lead'],
            $developer->id => ['role' => 'developer'],
        ]);

        return [$lead, $developer, $outsider, $project];
    }

    public function test_lead_can_view_the_create_task_form(): void
    {
        [$lead, , , $project] = $this->makeProject();

        $this->actingAs($lead)
            ->get(route('projects.tasks.create', $project))
            ->assertOk()
            ->assertSee('Create a Task')
            ->assertSee($project->title);
    }

    public function test_lead_can_create_and_assign_a_task_to_a_project_developer(): void
    {
        [$lead, $developer, , $project] = $this->makeProject();

        $response = $this->actingAs($lead)->post(route('projects.tasks.store', $project), [
            'title' => 'Implement user authentication',
            'description' => 'Login, logout, and password reset flows.',
            'deadline' => now()->addDays(7)->format('Y-m-d'),
            'priority' => 'high',
            'assigned_to' => $developer->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Implement user authentication',
            'assigned_to' => $developer->id,
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    public function test_project_member_who_is_not_lead_cannot_access_create_form(): void
    {
        [$lead, $developer, , $project] = $this->makeProject();

        $this->actingAs($developer)
            ->get(route('projects.tasks.create', $project))
            ->assertForbidden();

        $this->actingAs($developer)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Unauthorized task',
                'priority' => 'medium',
                'assigned_to' => $developer->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_user_who_is_not_a_member_cannot_create_tasks(): void
    {
        [$lead, , $outsider, $project] = $this->makeProject();

        $this->actingAs($outsider)
            ->get(route('projects.tasks.create', $project))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Intruder task',
                'priority' => 'medium',
                'assigned_to' => $outsider->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        [$lead, , , $project] = $this->makeProject();

        $this->get(route('projects.tasks.create', $project))->assertRedirect('/login');
        $this->post(route('projects.tasks.store', $project), [
            'title' => 'Guest task',
            'priority' => 'medium',
            'assigned_to' => $lead->id,
        ])->assertRedirect('/login');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_cannot_assign_task_to_a_user_who_is_not_a_project_member(): void
    {
        [$lead, , $outsider, $project] = $this->makeProject();

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Bad assignee',
                'priority' => 'medium',
                'assigned_to' => $outsider->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_title_and_priority_are_required(): void
    {
        [$lead, $developer, , $project] = $this->makeProject();

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'priority' => 'low',
                'assigned_to' => $developer->id,
            ])
            ->assertSessionHasErrors('title');

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Missing priority',
                'assigned_to' => $developer->id,
            ])
            ->assertSessionHasErrors('priority');

        $this->assertDatabaseCount('tasks', 0);
    }
}