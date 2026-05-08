<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project)
    {
        $tasks = $project->tasks;
        return view('tasks.index', compact('project', 'tasks'));
    }

    public function create(Project $project)
    {
        $members = $project->users;
        return view('tasks.create', compact('project', 'members'));
    }

    public function store(Request $request, Project $project)
    {
        $project->tasks()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => 'todo',
            'priority'    => $request->priority ?? 'medium',
            'user_id'     => $request->assigned_to ?? auth()->id(),
            'deadline'    => $request->deadline,
        ]);

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project, Task $task)
    {
        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task)
    {
        $members = $project->users;
        return view('tasks.edit', compact('project', 'task', 'members'));
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $task->update([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->status ?? $task->status,
            'priority'    => $request->priority,
            'user_id'     => $request->assigned_to,
            'deadline'    => $request->deadline,
        ]);

        return redirect()->route('projects.show', $project);
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();
        return redirect()->route('projects.show', $project);
    }
}
