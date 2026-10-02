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
    /**
     * Liste personnelle : uniquement les tâches assignées à l'utilisateur connecté.
     *
     * Le filtrage est fait au niveau de la requête (assigned_to = auth()->id()),
     * pas en post-filtrant une collection, donc aucune tâche d'un autre
     * developer ne peut fuiter dans la réponse.
     */
    public function myTasks(Request $request)
    {
        $user = $request->user();

        // Whitelist : seuls les trois statuts du workflow sont acceptés.
        $statusFilter = $request->query('status');
        if (! in_array($statusFilter, ['todo', 'in_progress', 'done'], true)) {
            $statusFilter = null;
        }

        $tasks = Task::assignedTo($user)
            ->with('project')
            ->when(
                $statusFilter !== null,
                fn ($query) => $query->where('status', $statusFilter),
                // Sans filtre : on masque les tâches terminées.
                fn ($query) => $query->where('status', '!=', 'done'),
            )
            // CASE portable (MySQL / SQLite) : en cours, puis à faire, puis terminé.
            ->orderByRaw("CASE status WHEN 'in_progress' THEN 0 WHEN 'todo' THEN 1 ELSE 2 END")
            ->orderBy('deadline')
            ->orderByDesc('id')
            ->get();

        $statusCounts = Task::assignedTo($user)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $stats = [
            'total' => array_sum($statusCounts),
            'todo' => $statusCounts['todo'] ?? 0,
            'in_progress' => $statusCounts['in_progress'] ?? 0,
            'done' => $statusCounts['done'] ?? 0,
        ];

        return view('tasks.assigned', compact('tasks', 'stats', 'statusFilter'));
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $user = request()->user();

        // Un developer ne voit dans la liste du projet que ses propres tâches ;
        // le lead voit toutes les tâches du projet (comportement inchangé).
        $tasks = $project->tasks()
            ->with('user')
            ->when(
                ! $user->isLead($project),
                fn ($query) => $query->assignedTo($user),
            )
            ->get();

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
