<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'user_id' => 1, 
        ]);

        return redirect()->route('projects.index');
    }

    public function show(Project $project)
    {
        $project->load('tasks');
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
        ]);

        return redirect()->route('projects.index');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index');
    }

    public function showArchives()
    {
        $archivedProjects = Project::onlyTrashed()->get();
        return view('projects.archives', compact('archivedProjects'));
    }

    public function restore($id)
    {
        Project::withTrashed()->findOrFail($id)->restore();
        return redirect()->route('projects.index');
    }
}