{{--
    Task detail — TaskController@show (GET projects/{project}/tasks/{task})

    Accessible par :
      - le lead du projet (TaskPolicy@view)
      - le developer assigné à la tâche (TaskPolicy@view)

    Le formulaire de statut réutilise projects.tasks.update : pour un developer,
    UpdateTaskRequest n'autorise que le champ "status".
--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $task->title }}
            </h2>
            <a href="{{ route('tasks.assigned') }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                My Tasks
            </a>
        </div>
    </x-slot>

    @php
        $isLead = auth()->user()->isLead($project);
        $canChangeStatus = auth()->user()->can('updateStatus', $task);
        $canEdit = auth()->user()->can('update', $task);
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Header card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="border-l-4 border-indigo-500 px-6 py-5">
                    <div class="flex flex-wrap items-center gap-2 mb-3">
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

                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $task->title }}</h1>

                    <p class="text-gray-500 text-sm font-medium mt-1">
                        {{ $project->title }}
                    </p>
                </div>

                {{-- Meta grid --}}
                <div class="border-t border-gray-100 grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                    <div class="px-6 py-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Assigned To</p>
                        <p class="text-sm font-bold text-gray-800">{{ $task->user?->name ?? 'Unassigned' }}</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Deadline</p>
                        @if($task->status == 'done')
                            <p class="text-sm font-bold text-green-600">Completed</p>
                        @elseif($task->deadline_status == 'overdue')
                            <p class="text-sm font-bold text-red-500">Overdue &middot; {{ $task->deadline }}</p>
                        @elseif($task->deadline_status == 'urgent')
                            <p class="text-sm font-bold text-amber-500">Urgent &middot; {{ $task->deadline }}</p>
                        @else
                            <p class="text-sm font-semibold text-gray-600">{{ $task->deadline ?? 'Open Schedule' }}</p>
                        @endif
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Created By</p>
                        <p class="text-sm font-bold text-gray-800">{{ $task->creator?->name ?? 'Unknown' }}</p>
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-5">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">
                        {{ $task->description ?? 'No description provided for this task.' }}
                    </p>
                </div>
            </div>

            {{-- Status workflow — TODO → IN_PROGRESS → DONE --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-5">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-1">Status Workflow</h3>

                    @if($canChangeStatus || $isLead)
                        <form method="POST" action="{{ route('projects.tasks.update', [$project, $task]) }}"
                              class="flex flex-col sm:flex-row sm:items-end gap-3 mt-4">
                            @csrf
                            @method('PATCH')

                            <div class="flex-1">
                                <label for="status" class="block text-sm font-bold text-gray-700 mb-1">New Status</label>
                                <select id="status" name="status" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="todo" {{ $task->status === 'todo' ? 'selected' : '' }}>À faire</option>
                                    <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>En cours</option>
                                    <option value="done" {{ $task->status === 'done' ? 'selected' : '' }}>Terminé</option>
                                </select>
                            </div>

                            @if($isLead)
                                {{-- Le lead passe par le formulaire complet : ces champs sont requis. --}}
                                <input type="hidden" name="title" value="{{ $task->title }}">
                                <input type="hidden" name="priority" value="{{ $task->priority }}">
                                <input type="hidden" name="assigned_to" value="{{ $task->assigned_to }}">
                            @endif

                            <button type="submit"
                                    class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                                Update Status
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-gray-500 italic mt-2">
                            Only the assigned developer or the project lead can change this status.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('tasks.assigned') }}"
                   class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all">
                    Back to My Tasks
                </a>

                <div class="flex items-center gap-3">
                    @can('update', $task)
                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-indigo-600 transition-all">
                            Edit Task
                        </a>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</x-app-layout>