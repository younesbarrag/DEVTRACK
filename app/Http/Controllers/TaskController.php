<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
// use Illuminate\Auth\AuthManager;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()->with('user')->get();
        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project)
    {
        $this->authorize('create', $project);

        $members = $project->users()->orderBy('name')->get();
        return view('tasks.create', compact('project', 'members'));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        $this->authorize('create', $project);

        $validated = $request->validated();

        $project->tasks()->create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status'      => 'todo',
            'user_id' => $request->user()->id,
            'priority'    => $validated['priority'] ?? 'medium', 
            'assigned_to' => $validated['assigned_to'] ?? null,
            'deadline'    => $validated['deadline'] ?? null,
        ]);

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task)
    {
        $this->authorize('view', $task);

        $members = $project->users()->orderBy('name')->get();
        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task)
    {
        // Le lead gère la tâche ; un développeur assigné ne peut que changer le statut.
        if ($request->user()->isLead($project)) {
            $this->authorize('update', $task);

            $validated = $request->validated();

            $task->update([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status'      => $validated['status'] ?? $task->status,
                'priority'    => $validated['priority'],
                'assigned_to' => $validated['assigned_to'],
                'deadline'    => $validated['deadline'] ?? null,
            ]);
        } else {
            $this->authorize('updateStatus', $task);

            $task->update(['status' => $request->validated('status', $task->status)]);
        }

        return redirect()->route('projects.show', $project);
    }

    public function destroy(Project $project, Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();
        return redirect()->route('projects.show', $project);
    }
}
