<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->title }} — Tasks
            </h2>
            <a href="{{ route('projects.show', $project) }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                Back to Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Project description strip --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border-l-4 border-indigo-500">
                <p class="text-gray-600 mb-2">{{ $project->description }}</p>
                <div class="text-sm text-gray-500 italic">
                    Deadline: {{ $project->deadline ?? 'No deadline' }}
                </div>
            </div>

            {{-- Stats band --}}
            @php
                $total    = $tasks->count();
                $done     = $tasks->where('status', 'done')->count();
                $active   = $total - $done;
                $urgent   = $tasks->filter(fn ($t) => $t->isUrgent())->count();
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
                <div class="bg-indigo-600 text-white rounded-3xl p-5">
                    <p class="text-4xl font-extrabold">{{ $total }}</p>
                    <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mt-1">Total Tasks</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-amber-500">{{ $active }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Active</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-green-500">{{ $done }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Completed</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-3xl p-5">
                    <p class="text-4xl font-extrabold text-red-500">{{ $urgent }}</p>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Urgent</p>
                </div>
            </div>

            {{-- Toolbar --}}
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Project Tasks</h3>
                @if(!$tasks->isEmpty())
                    <a href="{{ route('projects.tasks.create', $project) }}"
                       class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                        + New Task
                    </a>
                @endif
            </div>

            {{-- Task list --}}
            @forelse($tasks as $task)
                <div class="bg-white border border-gray-200 rounded-3xl p-6 mb-4 hover:shadow-xl hover:border-indigo-300 transition-all">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-6">

                        {{-- Left: status + main info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">

                                {{-- Status badge --}}
                                <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-tighter
                                    @if($task->status == 'done') bg-green-500 text-white
                                    @elseif($task->status == 'in_progress') bg-indigo-600 text-white
                                    @else bg-gray-200 text-gray-700 @endif">
                                    {{ $task->status_label }}
                                </span>

                                {{-- Priority badge --}}
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
                        </div>

                        {{-- Right: assignee, deadline, actions --}}
                        <div class="flex flex-col sm:flex-row lg:flex-col lg:w-64 gap-4 lg:gap-3 lg:items-end pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-50">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 uppercase">
                                    {{ substr($task->user?->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold">Assigned To</p>
                                    <p class="text-sm font-semibold text-gray-700">{{ $task->user?->name ?? 'Unassigned' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                @if($task->status == 'done')
                                    <span class="text-sm font-bold text-green-600">Completed</span>
                                @elseif($task->deadline_status == 'overdue')
                                    <span class="text-sm font-bold text-red-500">Overdue · {{ $task->deadline }}</span>
                                @elseif($task->deadline_status == 'urgent')
                                    <span class="text-sm font-bold text-amber-500">Urgent · {{ $task->deadline }}</span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $task->deadline ?? 'Open Schedule' }}</span>
                                @endif
                            </div>

                            {{-- Actions — visibles selon les droits réels sur la tâche --}}
                            <div class="flex items-center gap-2">
                                @can('view', $task)
                                    <a href="{{ route('tasks.assigned', [$project, $task]) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all">
                                        Assinged To
                                    </a>
                                @endcan

                                @can('update', $task)
                                    <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-white bg-gray-900 rounded-lg hover:bg-indigo-600 transition-all">
                                        Edit
                                    </a>
                                @endcan

                                @can('delete', $task)
                                    <form action="{{ route('projects.tasks.destroy', [$project, $task]) }}" method="POST"
                                          onsubmit="return confirm('Delete this task?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-bold text-red-500 border border-red-200 rounded-lg hover:bg-red-50 transition-all">
                                            Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty state --}}
                <div class="bg-white border-2 border-dashed border-gray-200 rounded-3xl p-16 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No tasks yet</h3>
                    <p class="text-gray-500 text-sm max-w-xs mx-auto mb-8">
                        This project has no tasks. Create the first one to start tracking work.
                    </p>
                    <a href="{{ route('projects.tasks.create', $project) }}"
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                        Create Task
                    </a>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>