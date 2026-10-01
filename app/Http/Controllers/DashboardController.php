<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $projectIds = collect()
            ->merge(auth()->user()->projects()->pluck('projects.id'))
            ->merge(Project::where('user_id', $userId)->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $projects = Project::whereIn('id', $projectIds)
            ->latest()
            ->withCount('tasks')
            ->withCount(['tasks as completed_tasks' => fn ($q) => $q->where('status', 'done')])
            ->get();

        $tasks = auth()->user()
            ->tasks()
            ->with('project')
            ->latest()
            ->get();

        $totalProjects = count($projectIds);

        $activeTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', '!=', 'done')
            ->count();

        $completedTasks = Task::whereIn('project_id', $projectIds)
            ->where('status', 'done')
            ->count();

        return view('dashboard', compact(
            'projects',
            'totalProjects',
            'activeTasks',
            'completedTasks',
            'tasks'
        ));
    }

    
}