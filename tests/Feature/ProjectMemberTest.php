<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMemberTest extends TestCase
{
    use RefreshDatabase;

    private function makeProject(): array
    {
        $lead = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $lead->id]);
        $project->users()->attach($lead->id, ['role' => 'lead']);

        return [$lead, $project];
    }

    public function test_lead_can_add_a_developer_to_the_project(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();

        $this->actingAs($lead)
            ->post(route('projects.members.store', $project), [
                'email' => $developer->email,
                'role' => 'developer',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $developer->id,
            'role' => 'developer',
        ]);

        $this->assertTrue($developer->isMember($project));
    }

    public function test_added_developer_appears_in_the_task_assignment_dropdown(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create(['name' => 'Zoe Developer']);
        $outsider = User::factory()->create(['name' => 'Mallory Outsider']);

        $this->actingAs($lead)
            ->post(route('projects.members.store', $project), ['email' => $developer->email])
            ->assertRedirect();

        $response = $this->actingAs($lead)
            ->get(route('projects.tasks.create', $project))
            ->assertOk();

        $response->assertSee('Zoe Developer');
        $response->assertDontSee('Mallory Outsider');

        // The lead and the newly added developer are both assignable.
        $this->assertCount(2, $project->fresh()->users);
    }

    public function test_cannot_add_a_user_with_an_unknown_email(): void
    {
        [$lead, $project] = $this->makeProject();

        $this->actingAs($lead)
            ->from(route('projects.show', $project))
            ->post(route('projects.members.store', $project), [
                'email' => 'nobody@example.com',
            ])
            ->assertRedirect(route('projects.show', $project))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('project_user', 1);
    }

    public function test_cannot_add_the_same_member_twice(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();
        $project->users()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($lead)
            ->post(route('projects.members.store', $project), ['email' => $developer->email])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('project_user', 2);
    }

    public function test_lead_cannot_add_themselves_again(): void
    {
        [$lead, $project] = $this->makeProject();

        $this->actingAs($lead)
            ->post(route('projects.members.store', $project), ['email' => $lead->email])
            ->assertSessionHasErrors('email');
    }

    public function test_developer_cannot_add_members_to_the_project(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();
        $newcomer = User::factory()->create();
        $project->users()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($developer)
            ->post(route('projects.members.store', $project), ['email' => $newcomer->email])
            ->assertForbidden();

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $newcomer->id,
        ]);
    }

    public function test_outsider_cannot_add_members_to_the_project(): void
    {
        [$lead, $project] = $this->makeProject();
        $outsider = User::factory()->create();
        $newcomer = User::factory()->create();

        $this->actingAs($outsider)
            ->post(route('projects.members.store', $project), ['email' => $newcomer->email])
            ->assertForbidden();
    }

    public function test_guest_cannot_add_members(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();

        $this->post(route('projects.members.store', $project), ['email' => $developer->email])
            ->assertRedirect('/login');
    }

    public function test_lead_can_remove_a_developer(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();
        $project->users()->attach($developer->id, ['role' => 'developer']);

        $this->actingAs($lead)
            ->delete(route('projects.members.destroy', [$project, $developer]))
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseMissing('project_user', [
            'project_id' => $project->id,
            'user_id' => $developer->id,
        ]);
    }

    public function test_lead_cannot_be_removed_from_the_project(): void
    {
        [$lead, $project] = $this->makeProject();

        $this->actingAs($lead)
            ->delete(route('projects.members.destroy', [$project, $lead]))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $lead->id,
        ]);
    }

    public function test_developer_cannot_remove_a_member(): void
    {
        [$lead, $project] = $this->makeProject();
        $developer = User::factory()->create();
        $other = User::factory()->create();
        $project->users()->attach([
            $developer->id => ['role' => 'developer'],
            $other->id => ['role' => 'developer'],
        ]);

        $this->actingAs($developer)
            ->delete(route('projects.members.destroy', [$project, $other]))
            ->assertForbidden();

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $other->id,
        ]);
    }
}
