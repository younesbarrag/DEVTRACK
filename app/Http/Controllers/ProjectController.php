<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Http\Requests\AddProjectMemberRequest;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::where('user_id', auth()->id())->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'user_id' => auth()->id(),
        ]);

        $project->users()->attach(auth()->id(), ['role' => 'lead']);

        return redirect()->route('projects.index');
    }

    public function show(Project $project)
    {
        $project->load(['tasks.user', 'users']);
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

    // Ajoute un membre (développeur) au projet
    public function addMember(AddProjectMemberRequest $request, Project $project)
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();

        $project->users()->attach($user->id, [
            'role' => $request->validated('role', 'developer'),
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "{$user->name} was added to the project team.");
    }

    // Retire un membre du projet
    public function removeMember(Request $request, Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        if ($user->id === $project->user_id) {
            return back()->with('error', 'The project lead cannot be removed.');
        }

        $project->users()->detach($user->id);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', "{$user->name} was removed from the project team.");
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