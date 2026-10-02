<x-app-layout>
    <div class="mt-12 max-w-6xl mx-auto">
        {{-- Header Section --}}
        <div class="flex items-end justify-between mb-8">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Welcome back, {{ auth()->user()->name }}
                </h2>
                <p class="text-gray-500 text-sm mt-1 font-medium">
                    Review your projects and personal workspace assignments
                </p>
            </div>
        </div>

        {{-- Stats Band --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-10">
            <div class="bg-indigo-600 text-white rounded-3xl p-5">
                <p class="text-4xl font-extrabold">{{ $totalProjects }}</p>
                <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mt-1">Projects</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-3xl p-5">
                <p class="text-4xl font-extrabold text-amber-500">{{ $activeTasks }}</p>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Active Tasks</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-3xl p-5">
                <p class="text-4xl font-extrabold text-green-500">{{ $completedTasks }}</p>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Completed Tasks</p>
            </div>
            <div class="bg-white border border-gray-200 rounded-3xl p-5">
                <p class="text-4xl font-extrabold text-orange-900">{{ $assignedTasks }}</p>
                <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Assigned Tasks</p>
            </div>
        </div>

        {{-- Projects Section --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">My Projects</h2>
                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all">
                    New Project
                </a>
            </div>

            @forelse($projects as $project)
                <div
                    class="bg-white border border-gray-200 rounded-3xl p-6 mb-4 hover:shadow-xl hover:border-indigo-300 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 truncate">
                                {{ $project->title }}
                            </h3>
                            <p class="text-gray-500 text-sm leading-relaxed mt-1 line-clamp-1">
                                {{ $project->description ?? 'No description provided for this project.' }}
                            </p>
                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-gray-50 text-gray-600 border border-gray-100 uppercase tracking-tight">
                                    {{ $project->tasks_count }} total tasks
                                </span>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-green-50 text-green-600 border border-green-100 uppercase tracking-tight">
                                    {{ $project->completed_tasks }} completed
                                </span>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-gray-50 text-gray-500 border border-gray-100 uppercase tracking-tight">
                                    Deadline: {{ $project->deadline ?? 'Open' }}
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('projects.show', $project) }}"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-indigo-600 hover:scale-105 transition-all">
                            Open Project
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No projects yet</h3>
                    <p class="text-gray-500 text-sm max-w-xs mx-auto">Create your first development sprint to start tracking
                        tasks and deadlines.</p>
                </div>
            @endforelse
        </div>

        {{-- My Assigned Tasks Section --}}
        <div class="mt-12 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                        My Assigned Tasks
                    </h2>
                    <p class="text-gray-500 text-sm mt-1 font-medium">
                        Tasks currently assigned to you across every project
                    </p>
                </div>

                <a href="{{ route('tasks.assigned') }}"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all">
                    View All My Tasks
                </a>
            </div>

            {{-- Per-status counts --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-900 text-white rounded-3xl p-5">
                    <p class="text-4xl font-extrabold">{{ $assignedTasks }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Total</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-gray-500">{{ $assignedStatusCounts['todo'] ?? 0 }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Todo</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-amber-500">{{ $assignedStatusCounts['in_progress'] ?? 0 }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">In Progress</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-green-500">{{ $assignedStatusCounts['done'] ?? 0 }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Done</p>
                </div>
            </div>

            {{-- Preview of up to 5 assigned tasks --}}
            @forelse($myTasks->take(5) as $task)
                <div class="bg-white border border-gray-200 rounded-3xl p-6 mb-4 hover:shadow-xl hover:border-indigo-300 transition-all">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-tighter
                                            @if($task->status == 'done') bg-green-500 text-white
                                            @elseif($task->status == 'in_progress') bg-indigo-600 text-white
                                            @else bg-gray-200 text-gray-700 @endif">
                                    {{ $task->status_label }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold uppercase tracking-tight
                                            @if($task->priority == 'high') bg-red-50 text-red-600 border border-red-100
                                            @elseif($task->priority == 'medium') bg-amber-50 text-amber-600 border border-amber-100
                                            @else bg-green-50 text-green-600 border border-green-100 @endif">
                                    {{ ucfirst($task->priority) }} Priority
                                </span>
                            </div>

                            <h3 class="text-lg font-bold text-gray-900 truncate">
                                {{ $task->title }}
                            </h3>
                            <p class="text-gray-500 text-sm leading-relaxed mt-1 line-clamp-1">
                                {{ $task->description ?? 'No description available for this task.' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-tight">
                                    {{ $task->project?->title ?? 'No project' }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-gray-50 text-gray-500 border border-gray-100 uppercase tracking-tight">
                                    Deadline: {{ $task->deadline ?? 'Open' }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                            class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-indigo-600 transition-all">
                            View Details
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No tasks assigned to you</h3>
                    <p class="text-gray-500 text-sm max-w-xs mx-auto">
                        When a team lead assigns you a task, it will appear here.
                    </p>
                </div>
            @endforelse

            @if($myTasks->count() > 5)
                <div class="text-center mt-6">
                    <a href="{{ route('tasks.assigned') }}"
                        class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                        View all {{ $assignedTasks }} assigned tasks &rarr;
                    </a>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
