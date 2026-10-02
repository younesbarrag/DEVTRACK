<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperAssignedTasksTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $name): User
    {
        return User::factory()->create(['name' => $name]);
    }

    private function makeProject(User $lead, array $members = []): Project
    {
        $project = Project::create([
            'title' => 'Project '.uniqid(),
            'description' => 'Test project',
            'user_id' => $lead->id,
        ]);

        $project->users()->attach($lead->id, ['role' => 'lead']);
        foreach ($members as $member) {
            $project->users()->attach($member->id, ['role' => 'developer']);
        }

        return $project;
    }

    private function makeTask(Project $project, User $creator, ?User $assignee, string $status = 'todo'): Task
    {
        return $project->tasks()->create([
            'title' => 'Task '.uniqid(),
            'description' => 'Description',
            'status' => $status,
            'priority' => 'medium',
            'deadline' => now()->addWeek(),
            'user_id' => $creator->id,
            'assigned_to' => $assignee?->id,
        ]);
    }

    // ---------------------------------------------------------------
    // 1. Developer task list
    // ---------------------------------------------------------------

    public function test_developer_sees_only_their_own_assigned_tasks(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');

        $project = $this->makeProject($lead, [$devA, $devB]);

        $mine = $this->makeTask($project, $lead, $devA, 'todo');
        $theirs = $this->makeTask($project, $lead, $devB, 'todo');
        $unassigned = $this->makeTask($project, $lead, null, 'todo');

        $response = $this->actingAs($devA)->get(route('tasks.assigned'));

        $response->assertOk();
        $response->assertViewIs('tasks.assigned');
        $response->assertViewHas('tasks', function ($tasks) use ($mine, $theirs, $unassigned) {
            $ids = $tasks->pluck('id')->all();

            return $ids === [$mine->id]
                && ! in_array($theirs->id, $ids, true)
                && ! in_array($unassigned->id, $ids, true);
        });
    }

    public function test_developer_list_shows_required_task_details(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        $task = $this->makeTask($project, $lead, $dev, 'in_progress');

        $response = $this->actingAs($dev)->get(route('tasks.assigned'));

        $response->assertOk();
        $response->assertSee($task->title);
        $response->assertSee('Description');
        $response->assertSee($project->title);
        $response->assertSee('Medium Priority');
        $response->assertSee('En cours');
        $response->assertSee($task->deadline->toDateString());
    }

    public function test_guest_is_redirected_from_assigned_tasks(): void
    {
        $this->get(route('tasks.assigned'))->assertRedirect(route('login'));
    }

    public function test_assigned_tasks_can_be_filtered_by_status(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        $todo = $this->makeTask($project, $lead, $dev, 'todo');
        $done = $this->makeTask($project, $lead, $dev, 'done');

        $response = $this->actingAs($dev)->get(route('tasks.assigned', ['status' => 'done']));

        $response->assertOk();
        $response->assertViewHas('tasks', fn ($tasks) => $tasks->pluck('id')->all() === [$done->id]);
        $response->assertDontSee($todo->title);
    }

    public function test_invalid_status_filter_is_ignored(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        $this->makeTask($project, $lead, $dev, 'todo');

        $response = $this->actingAs($dev)->get(route('tasks.assigned', ['status' => "'; DROP TABLE tasks; --"]));

        $response->assertOk();
        $response->assertViewHas('statusFilter', null);
        $this->assertSame(1, Task::count());
    }

    // ---------------------------------------------------------------
    // 2. Dashboard integration
    // ---------------------------------------------------------------

    public function test_dashboard_shows_assigned_task_counts_per_status(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        $this->makeTask($project, $lead, $dev, 'todo');
        $this->makeTask($project, $lead, $dev, 'in_progress');
        $this->makeTask($project, $lead, $dev, 'in_progress');
        $this->makeTask($project, $lead, $dev, 'done');

        $response = $this->actingAs($dev)->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('assignedTasks', 4);
        $response->assertViewHas('assignedStatusCounts', function ($counts) {
            return $counts['todo'] === 1
                && $counts['in_progress'] === 2
                && $counts['done'] === 1;
        });
        $response->assertSee('My Assigned Tasks');
        $response->assertSee(route('tasks.assigned'));
    }

    public function test_dashboard_counts_assignments_not_creations(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        // Le lead crée la tâche mais elle est assignée au developer.
        $this->makeTask($project, $lead, $dev, 'todo');
        // Tâche du lead sur lui-même : ne doit pas compter pour le developer.
        $this->makeTask($project, $lead, $lead, 'todo');

        $response = $this->actingAs($dev)->get(route('dashboard'));

        $response->assertViewHas('assignedTasks', 1);
        $response->assertViewHas('myTasks', fn ($tasks) => $tasks->count() === 1);
    }

    // ---------------------------------------------------------------
    // 3. Task details
    // ---------------------------------------------------------------

    public function test_assigned_developer_can_view_task_details(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $response = $this->actingAs($dev)->get(route('projects.tasks.show', [$project, $task]));

        $response->assertOk();
        $response->assertViewIs('tasks.show');
        $response->assertSee($task->title);
    }

    public function test_lead_can_view_any_task_details(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $this->actingAs($lead)
            ->get(route('projects.tasks.show', [$project, $task]))
            ->assertOk();
    }

    public function test_developer_cannot_view_another_developers_task_by_url(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);

        $taskOfB = $this->makeTask($project, $lead, $devB);

        $this->actingAs($devA)
            ->get(route('projects.tasks.show', [$project, $taskOfB]))
            ->assertForbidden();
    }

    public function test_non_member_cannot_view_task_details(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $outsider = $this->makeUser('Outsider');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $this->actingAs($outsider)
            ->get(route('projects.tasks.show', [$project, $task]))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 4. Status update workflow
    // ---------------------------------------------------------------

    public function test_assigned_developer_can_advance_status_todo_to_in_progress(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev, 'todo');

        $this->actingAs($dev)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }

    public function test_assigned_developer_can_mark_task_done(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev, 'in_progress');

        $this->actingAs($dev)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'done'])
            ->assertRedirect();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'done']);
    }

    public function test_developer_cannot_modify_protected_fields(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $other = $this->makeUser('Other');
        $project = $this->makeProject($lead, [$dev, $other]);
        $task = $this->makeTask($project, $lead, $dev, 'todo');

        $protected = ['title', 'description', 'priority', 'assigned_to', 'project_id', 'user_id'];
        $original = $task->only($protected);
        $originalDeadline = (string) $task->deadline;

        $this->actingAs($dev)->patch(route('projects.tasks.update', [$project, $task]), [
            'status' => 'done',
            'title' => 'Hacked title',
            'description' => 'Hacked description',
            'priority' => 'high',
            'deadline' => now()->addYear()->toDateString(),
            'assigned_to' => $other->id,
            'project_id' => $project->id,
            'user_id' => $dev->id,
        ])->assertRedirect();

        $task->refresh();

        $this->assertSame('done', $task->status);
        $this->assertSame($original, $task->only($protected));
        $this->assertSame(
            $originalDeadline,
            (string) $task->getRawOriginal('deadline'),
            'deadline should not change',
        );
    }

    public function test_developer_cannot_reassign_task_to_someone_else(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $other = $this->makeUser('Other');
        $project = $this->makeProject($lead, [$dev, $other]);
        $task = $this->makeTask($project, $lead, $dev, 'todo');

        $this->actingAs($dev)
            ->patch(route('projects.tasks.update', [$project, $task]), [
                'status' => 'done',
                'title' => $task->title,
                'priority' => $task->priority,
                'assigned_to' => $other->id,
            ])
            ->assertRedirect();

        $this->assertSame($dev->id, $task->fresh()->assigned_to);
    }

    public function test_developer_cannot_move_task_backwards_with_invalid_status(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev, 'done');

        $this->actingAs($dev)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'not_a_status'])
            ->assertSessionHasErrors('status');

        $this->assertSame('done', $task->fresh()->status);
    }

    // ---------------------------------------------------------------
    // 5. Authorization on update
    // ---------------------------------------------------------------

    public function test_developer_cannot_update_task_assigned_to_someone_else(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);
        $taskOfB = $this->makeTask($project, $lead, $devB, 'todo');

        $this->actingAs($devA)
            ->patch(route('projects.tasks.update', [$project, $taskOfB]), ['status' => 'done'])
            ->assertForbidden();

        $this->assertSame('todo', $taskOfB->fresh()->status);
    }

    public function test_non_member_cannot_update_any_task(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $outsider = $this->makeUser('Outsider');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev, 'todo');

        $this->actingAs($outsider)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'done'])
            ->assertForbidden();

        $this->assertSame('todo', $task->fresh()->status);
    }

    public function test_developer_cannot_delete_task(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $this->actingAs($dev)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    public function test_developer_cannot_open_edit_form_for_another_developers_task(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);
        $taskOfB = $this->makeTask($project, $lead, $devB);

        $this->actingAs($devA)
            ->get(route('projects.tasks.edit', [$project, $taskOfB]))
            ->assertForbidden();
    }

    public function test_developer_cannot_see_others_tasks_in_project_index(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);

        $mine = $this->makeTask($project, $lead, $devA);
        $theirs = $this->makeTask($project, $lead, $devB);

        $response = $this->actingAs($devA)->get(route('projects.tasks.index', $project));

        $response->assertOk();
        $response->assertViewHas('tasks', fn ($tasks) => $tasks->pluck('id')->all() === [$mine->id]);
        $response->assertDontSee($theirs->title);
    }

    public function test_lead_still_sees_all_tasks_in_project_index(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);

        $t1 = $this->makeTask($project, $lead, $devA);
        $t2 = $this->makeTask($project, $lead, $devB);

        $response = $this->actingAs($lead)->get(route('projects.tasks.index', $project));

        $response->assertOk();
        $response->assertViewHas('tasks', function ($tasks) use ($t1, $t2) {
            return $tasks->count() === 2
                && in_array($t1->id, $tasks->pluck('id')->all(), true)
                && in_array($t2->id, $tasks->pluck('id')->all(), true);
        });
    }

    // ---------------------------------------------------------------
    // 6. Lead permissions unchanged
    // ---------------------------------------------------------------

    public function test_lead_can_still_update_all_task_fields(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev, 'todo');

        $this->actingAs($lead)
            ->patch(route('projects.tasks.update', [$project, $task]), [
                'title' => 'Updated by lead',
                'description' => 'New description',
                'priority' => 'high',
                'deadline' => now()->addDays(10)->toDateString(),
                'status' => 'in_progress',
                'assigned_to' => $dev->id,
            ])
            ->assertRedirect();

        $task->refresh();

        $this->assertSame('Updated by lead', $task->title);
        $this->assertSame('high', $task->priority);
        $this->assertSame('in_progress', $task->status);
        $this->assertSame($lead->id, $task->user_id);
    }

    public function test_lead_can_delete_task(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $this->actingAs($lead)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertRedirect();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_from_another_project_cannot_be_reached(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');

        $projectA = $this->makeProject($lead, [$devA]);
        $projectB = $this->makeProject($lead, [$devB]);

        $taskB = $this->makeTask($projectB, $lead, $devB);

        // devA est membre de projectA seulement : il n'est pas membre de projectB.
        $this->actingAs($devA)
            ->get(route('projects.tasks.show', [$projectB, $taskB]))
            ->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Model helpers
    // ---------------------------------------------------------------

    public function test_scope_assigned_to_filters_by_user(): void
    {
        $lead = $this->makeUser('Lead');
        $devA = $this->makeUser('DevA');
        $devB = $this->makeUser('DevB');
        $project = $this->makeProject($lead, [$devA, $devB]);

        $mine = $this->makeTask($project, $lead, $devA);
        $this->makeTask($project, $lead, $devB);
        $this->makeTask($project, $lead, null);

        $ids = Task::assignedTo($devA)->pluck('id')->all();

        $this->assertSame([$mine->id], $ids);
    }

    public function test_creator_relation_returns_task_author(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);
        $task = $this->makeTask($project, $lead, $dev);

        $this->assertTrue($task->creator->is($lead));
        $this->assertTrue($task->user->is($dev));
    }

    public function test_task_without_deadline_is_not_urgent(): void
    {
        $lead = $this->makeUser('Lead');
        $dev = $this->makeUser('Dev');
        $project = $this->makeProject($lead, [$dev]);

        $task = $project->tasks()->create([
            'title' => 'No deadline',
            'status' => 'todo',
            'priority' => 'medium',
            'user_id' => $lead->id,
            'assigned_to' => $dev->id,
        ]);

        $this->assertNull($task->deadline);
        $this->assertFalse($task->isUrgent());
    }
}