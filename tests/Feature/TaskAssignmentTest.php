<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Lead #1 owns the project; two developers are members.
     */
    private function makeProjectWithDevelopers(): array
    {
        $lead = User::factory()->create(['name' => 'Lead Person']);
        $devA = User::factory()->create(['name' => 'Dev Alpha']);
        $devB = User::factory()->create(['name' => 'Dev Beta']);
        $outsider = User::factory()->create(['name' => 'Not A Member']);

        $project = Project::factory()->create(['user_id' => $lead->id]);
        $project->users()->attach([
            $lead->id => ['role' => 'lead'],
            $devA->id => ['role' => 'developer'],
            $devB->id => ['role' => 'developer'],
        ]);

        return [$lead, $devA, $devB, $outsider, $project];
    }

    public function test_assignment_dropdown_lists_every_project_member(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $response = $this->actingAs($lead)
            ->get(route('projects.tasks.create', $project))
            ->assertOk();

        $response->assertSee('Dev Alpha');
        $response->assertSee('Dev Beta');
        $response->assertSee('Lead Person');
        $response->assertDontSee('Not A Member');
    }

    public function test_lead_can_assign_a_task_to_another_developer(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Write the docs',
                'priority' => 'high',
                'assigned_to' => $devB->id,
            ])
            ->assertRedirect(route('projects.show', $project));

        $task = Task::firstWhere('title', 'Write the docs');

        $this->assertNotNull($task);
        $this->assertSame($devB->id, $task->assigned_to, 'assigned_to must be the chosen developer');
        $this->assertSame($lead->id, $task->user_id, 'user_id must remain the task creator');
        $this->assertSame($project->id, $task->project_id);
        $this->assertSame('todo', $task->status);
    }

    public function test_lead_can_assign_a_task_to_the_other_developer_not_only_themselves(): void
    {
        [$lead, $devA, , , $project] = $this->makeProjectWithDevelopers();

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Assigned to Alpha',
                'priority' => 'medium',
                'assigned_to' => $devA->id,
            ]);

        $task = Task::firstWhere('title', 'Assigned to Alpha');
        $this->assertNotSame($lead->id, $task->assigned_to);
        $this->assertSame($devA->id, $task->assigned_to);
    }

    public function test_assignment_to_a_non_member_is_rejected(): void
    {
        [$lead, , , $outsider, $project] = $this->makeProjectWithDevelopers();

        $this->actingAs($lead)
            ->post(route('projects.tasks.store', $project), [
                'title' => 'Should not be created',
                'priority' => 'low',
                'assigned_to' => $outsider->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_lead_can_reassign_an_existing_task_to_another_developer(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Reassign me',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($lead)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Reassign me',
                'priority' => 'medium',
                'status' => 'in_progress',
                'assigned_to' => $devB->id,
            ])
            ->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this->assertSame($devB->id, $task->assigned_to);
        $this->assertSame('in_progress', $task->status);
    }

    public function test_editing_a_task_never_changes_the_creator(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Created by lead',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($lead)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Renamed by lead',
                'priority' => 'high',
                'status' => 'done',
                'assigned_to' => $devB->id,
            ]);

        $task->refresh();

        $this->assertSame($lead->id, $task->user_id, 'user_id must still be the original creator');
        $this->assertSame('Renamed by lead', $task->title);
    }

    public function test_task_creator_is_preserved_even_when_a_different_lead_edits(): void
    {
        [$lead, $devA, , , $project] = $this->makeProjectWithDevelopers();
        $secondLead = User::factory()->create(['name' => 'Second Lead']);
        $project->users()->attach($secondLead->id, ['role' => 'lead']);

        $task = $project->tasks()->create([
            'title' => 'Owned by first lead',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($secondLead)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Edited by second lead',
                'priority' => 'low',
                'status' => 'todo',
                'assigned_to' => $devA->id,
            ]);

        $this->assertSame($lead->id, $task->fresh()->user_id);
    }

    public function test_assigned_developer_can_update_the_status_only(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Status only change',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($devA)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this->assertSame('in_progress', $task->status);
        $this->assertSame('Status only change', $task->title, 'title must be untouched');
        $this->assertSame('medium', $task->priority, 'priority must be untouched');
        $this->assertSame($devA->id, $task->assigned_to, 'assignee must be untouched');
    }

    public function test_assigned_developer_cannot_reassign_the_task_to_someone_else(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'No reassigning',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        // A non-lead only gets status-only validation rules, so any other
        // field submitted alongside is simply ignored by the controller.
        $this->actingAs($devA)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Hijacked',
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => $devB->id,
            ])
            ->assertRedirect(route('projects.show', $project));

        $task->refresh();

        $this->assertSame($devA->id, $task->assigned_to, 'assignee must not change');
        $this->assertSame('No reassigning', $task->title, 'title must not change');
        $this->assertSame('medium', $task->priority, 'priority must not change');
        $this->assertSame('in_progress', $task->status, 'status change is still allowed');
    }

    public function test_assigned_developer_cannot_submit_an_invalid_status(): void
    {
        [$lead, $devA, , , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Bad status',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($devA)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'status' => 'not-a-status',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_developer_who_is_not_assigned_cannot_update_the_task(): void
    {
        [$lead, $devA, $devB, , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Not for dev beta',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($devB)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'status' => 'done',
            ])
            ->assertForbidden();

        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_outsider_cannot_update_a_task(): void
    {
        [$lead, $devA, , $outsider, $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Private task',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($outsider)
            ->put(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Tampered',
                'priority' => 'high',
                'assigned_to' => $outsider->id,
            ])
            ->assertForbidden();

        $this->assertSame('Private task', $task->fresh()->title);
    }

    public function test_developer_cannot_delete_a_task(): void
    {
        [$lead, $devA, , , $project] = $this->makeProjectWithDevelopers();

        $task = $project->tasks()->create([
            'title' => 'Lead only delete',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $devA->id,
        ]);

        $this->actingAs($devA)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_outsider_cannot_view_the_task_list_of_a_project(): void
    {
        [$lead, , , $outsider, $project] = $this->makeProjectWithDevelopers();

        $this->actingAs($outsider)
            ->get(route('projects.tasks.index', $project))
            ->assertForbidden();
    }

    public function test_developer_can_view_the_project_task_list(): void
    {
        [$lead, $devA, , , $project] = $this->makeProjectWithDevelopers();

        $this->actingAs($devA)
            ->get(route('projects.tasks.index', $project))
            ->assertOk();
    }
}
