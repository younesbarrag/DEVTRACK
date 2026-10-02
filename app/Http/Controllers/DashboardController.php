<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $projectIds = collect()
            ->merge($user->projects()->pluck('projects.id'))
            ->merge(Project::where('user_id', $userId)->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $projects = Project::whereIn('id', $projectIds)
            ->latest()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks' => fn ($q) => $q->where('status', 'done')])
            ->get();

        $totalProjects = count($projectIds);

        $activeTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->count();

        $completedTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->count();

        // Tâches assignées à l'utilisateur connecté (User::tasks() = hasMany(Task, 'assigned_to')).
        // Le décompte par statut est fait en base : pas de chargement duataset complet.
        $myTasks = $user->tasks()
            ->with('project')
            ->latest()
            ->get();

        $assignedTasks = $user->tasks()->count();

        $assignedStatusCounts = Task::assignedTo($user)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        return view('dashboard', compact(
            'projects',
            'totalProjects',
            'activeTasks',
            'completedTasks',
            'myTasks',
            'assignedTasks',
            'assignedStatusCounts'
        ));
    }
}