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
        return view('tasks.create', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $project->tasks()->create([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => 'todo',
            'priority'    => $request->priority,
            'user_id'     => $request->assigned_to,
            'deadline'    => $request->deadline,
        ]);

        return redirect()->route('projects.tasks.index', $project);
    }

    public function show(Project $project, Task $task)
    {
        return view('tasks.show', compact('project', 'task'));
    }

    public function edit(Project $project, Task $task)
    {
        return view('tasks.edit', compact('project', 'task'));
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

        return redirect()->route('projects.tasks.show', [$project, $task]);
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();
        return redirect()->route('projects.tasks.index', $project);
    }
}
