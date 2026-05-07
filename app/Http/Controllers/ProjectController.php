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
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */public function __construct()
    {
        $this->middleware('auth');
    }

    // Dashboard : projets actifs
    public function index()
    {
        $projects = auth()->user()->projects()
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function ($q) {
                $q->where('status', 'done');
            }])
            ->whereNull('projects.deleted_at')
            ->get();

        return view('projects.index', compact('projects'));
    }

    // Archives
    public function archives()
    {
        $projects = Project::onlyTrashed()
            ->where('user_id', auth()->id())
            ->withCount('tasks')
            ->get();

        return view('projects.archives', compact('projects'));
    }

    // Formulaire création
    public function create()
    {
        return view('projects.create');
    }

    // Créer un projet
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'user_id' => auth()->id(),
        ]);

        // Le créateur est automatiquement lead
        $project->users()->attach(auth()->id(), ['role' => 'lead']);

        return redirect()->route('projects.index')->with('success', 'Projet créé !');
    }

    // Voir un projet
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['users', 'tasks' => function ($q) {
            $q->with('user')->orderBy('deadline');
        }]);

        return view('projects.show', compact('project'));
    }

    // Formulaire édition
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        return view('projects.edit', compact('project'));
    }

    // Modifier
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return redirect()->route('projects.show', $project)->with('success', 'Projet mis à jour !');
    }

    // Archiver (soft delete)
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Projet archivé !');
    }

    // Restaurer
    public function restore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('restore', $project);

        $project->restore();

        return redirect()->route('projects.archives')->with('success', 'Projet restauré !');
    }

    // Suppression définitive
    public function forceDelete($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $project);

        $project->forceDelete();

        return redirect()->route('projects.archives')->with('success', 'Projet supprimé définitivement !');
    }

    // Ajouter un membre
    public function addMember(Request $request, Project $project)
    {
        $this->authorize('manageMembers', $project);

        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($project->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur est déjà membre.');
        }

        $project->users()->attach($user->id, ['role' => 'developer']);

        return back()->with('success', 'Membre ajouté !');
    }

    // Retirer un membre
    public function removeMember(Project $project, User $user)
    {
        $this->authorize('manageMembers', $project);

        // Ne pas retirer le lead
        if ($user->id === $project->user_id) {
            return back()->with('error', 'Impossible de retirer le lead.');
        }

        $project->users()->detach($user->id);

        return back()->with('success', 'Membre retiré !');
    }
}