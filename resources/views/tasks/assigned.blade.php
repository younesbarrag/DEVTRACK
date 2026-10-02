{{--
    Tâches assignées à l'utilisateur connecté — TaskController@myTasks (GET /my/tasks)

    $tasks  → uniquement des tâches où assigned_to = auth()->id()
    $stats  → ['total', 'todo', 'in_progress', 'done']
--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My Assigned Tasks
            </h2>
            <a href="{{ route('dashboard') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

            {{-- Stats band --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-indigo-600 text-white rounded-3xl p-5">
                    <p class="text-4xl font-extrabold">{{ $stats['total'] }}</p>
                    <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mt-1">Total Assigned</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-gray-500">{{ $stats['todo'] }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Todo</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-amber-500">{{ $stats['in_progress'] }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">In Progress</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-green-500">{{ $stats['done'] }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Done</p>
                </div>
            </div>

            {{-- Status filter tabs --}}
            <div class="flex flex-wrap items-center gap-2 mb-6">
                <a href="{{ route('tasks.assigned') }}"
                   class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all
                          {{ $statusFilter === null ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    Active
                </a>
                @foreach(['todo' => 'Todo', 'in_progress' => 'In Progress', 'done' => 'Done'] as $key => $label)
                    <a href="{{ route('tasks.assigned', ['status' => $key]) }}"
                       class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all
                              {{ $statusFilter === $key ? 'bg-gray-900 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Task list --}}
            @forelse($tasks as $task)
                <div class="bg-white border border-gray-200 rounded-3xl p-6 mb-4 hover:shadow-xl hover:border-indigo-300 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">

                        {{-- Left: status + title + description --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
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

                            <h3 class="text-lg font-bold text-gray-900 truncate">{{ $task->title }}</h3>

                            <p class="text-gray-500 text-sm leading-relaxed mt-1 line-clamp-2">
                                {{ $task->description ?? 'No description available for this task.' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-2 mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 uppercase tracking-tight">
                                    {{ $task->project?->title ?? 'No project' }}
                                </span>
                            </div>
                        </div>

                        {{-- Right: deadline + CTA --}}
                        <div class="flex items-center justify-between lg:justify-end gap-6 pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-50">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 002 2z" />
                                </svg>
                                @if($task->status == 'done')
                                    <span class="text-sm font-bold text-green-600">Completed</span>
                                @elseif($task->deadline_status == 'overdue')
                                    <span class="text-sm font-bold text-red-500">Overdue &middot; {{ $task->deadline }}</span>
                                @elseif($task->deadline_status == 'urgent')
                                    <span class="text-sm font-bold text-amber-500">Urgent &middot; {{ $task->deadline }}</span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $task->deadline ?? 'Open Schedule' }}</span>
                                @endif
                            </div>

                            <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}"
                               class="inline-flex items-center justify-center px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-indigo-600 transition-all">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        {{ $statusFilter ? 'No tasks with this status' : 'No active tasks assigned to you' }}
                    </h3>
                    <p class="text-gray-500 text-sm max-w-xs mx-auto">
                        @if($statusFilter)
                            Try another status filter to see your other assignments.
                        @else
                            You have nothing to work on right now. Enjoy the calm, or check the Done tab.
                        @endif
                    </p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>