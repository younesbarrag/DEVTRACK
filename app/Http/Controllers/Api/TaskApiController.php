<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;

class TaskApiController extends Controller
{
    public function index(Project $project)
    {
        $tasks = $project->tasks;
        return TaskResource::collection($tasks);
    }

    public function store(Request $request, Project $project)
    {
        $task = $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'todo',
            'priority' => $request->priority,
            'user_id' => $request->user_id,
            'deadline' => $request->deadline,
        ]);

        return response()->json([
            'message' => 'Task created successfully',
            'data' => new TaskResource($task)
        ], 201);
    }

    public function show(Project $project, Task $task)
    {
        return new TaskResource($task);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        $task->update($request->only(['title', 'description', 'status', 'priority', 'deadline', 'user_id']));

        return response()->json([
            'message' => 'Task updated successfully',
            'data' => new TaskResource($task)
        ]);
    }

    public function destroy(Project $project, Task $task)
    {
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
