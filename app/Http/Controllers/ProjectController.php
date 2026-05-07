<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

    public function show($id)
    {
        $project = Project::with('tasks')->findOrFail($id);
        return view('projects.show', compact('project'));
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('projects.index');
    }

  public function showArchives()
{
    $archivedProjects = Project::onlyTrashed()->get();
    
    return view('projects.archives', compact('archivedProjects'));
}
}