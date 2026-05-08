<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Http\Resources\ProjectResource;

class ProjectApiController extends Controller
{
    public function index()
    {
        $projects = Project::with('tasks')->get();
        return ProjectResource::collection($projects);
    }

    public function store(Request $request)
    {
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'user_id' => $request->user_id ?? 1,
        ]);

        return response()->json([
            'message' => 'Project created successfully',
            'data' => new ProjectResource($project)
        ], 201);
    }

    public function show(Project $project)
    {
        $project->load('tasks');
        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($request->only(['title', 'description', 'deadline']));

        return response()->json([
            'message' => 'Project updated successfully',
            'data' => new ProjectResource($project)
        ]);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
