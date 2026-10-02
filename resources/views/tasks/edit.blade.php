{{--
    US10 — Edit task form
    Controller: TaskController@edit (GET) + TaskController@update (PATCH)
    Route: GET projects/{project}/tasks/{task}/edit

    Variables available:
        $task    → Task model (current values to pre-fill)
        $members → Collection<User> — project members for the assignee dropdown

    A non-lead assigned developer may open this page, but the controller only
    applies their status change — so the form is reduced to status for them.
--}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Task — {{ $task->project->title }}
            </h2>
            <a href="{{ route('projects.show', $task->project) }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                Back to Project
            </a>
        </div>
    </x-slot>

    @php
        $isLead = auth()->user()->isLead($task->project);
    @endphp

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Current status indicator (read-only indicator, editable only by the assigned developer) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border-l-4 border-indigo-500">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-bold text-gray-700">Current status</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase tracking-tighter
                                @if($task->status == 'done') bg-green-500 text-white
                                @elseif($task->status == 'in_progress') bg-indigo-600 text-white
                                @else bg-gray-200 text-gray-700 @endif">
                        {{ $task->status_label }}
                    </span>
                    <span class="text-xs text-gray-500 italic ml-auto">
                        Assigned to {{ $task->user?->name ?? 'nobody' }}
                    </span>
                </div>
            </div>

            {{-- Edit form --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-gray-200 p-8">
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-6">Edit Task</h3>

                <form method="POST" action="{{ route('projects.tasks.update', [$task->project, $task]) }}"
                      class="space-y-6">
                    @csrf
                    @method('PATCH')

                    {{-- Status — always available --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700" for="status">Status</label>
                        <select id="status" name="status"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="todo" {{ old('status', $task->status) === 'todo' ? 'selected' : '' }}>À faire</option>
                            <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>En cours</option>
                            <option value="done" {{ old('status', $task->status) === 'done' ? 'selected' : '' }}>Terminé</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($isLead)
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700" for="title">Title <span class="text-red-500">*</span></label>
                            <input
                                id="title"
                                name="title"
                                type="text"
                                value="{{ old('title', $task->title) }}"
                                placeholder="e.g. Implement user authentication"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                maxlength="255"
                                autofocus
                                required
                            >
                            @error('title')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700" for="description">Description</label>
                            <textarea
                                id="description"
                                name="description"
                                rows="4"
                                placeholder="Details, acceptance criteria, useful resources…"
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                            >{{ old('description', $task->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deadline + Priority --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700" for="deadline">Deadline</label>
                                <input
                                    id="deadline"
                                    name="deadline"
                                    type="date"
                                    value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                @error('deadline')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700" for="priority">Priority <span class="text-red-500">*</span></label>
                                <select id="priority" name="priority" required
                                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Assignee — project members only --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700" for="assigned_to">Assigned To <span class="text-red-500">*</span></label>
                            <select id="assigned_to" name="assigned_to" required
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @forelse($members as $member)
                                    <option value="{{ $member->id }}" {{ (int) old('assigned_to', $task->assigned_to) === $member->id ? 'selected' : '' }}>
                                        {{ $member->name }} ({{ ucfirst($member->pivot->role) }})
                                    </option>
                                @empty
                                    <option value="" disabled>No members in this project</option>
                                @endforelse
                            </select>
                            @error('assigned_to')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                            As an assigned developer you can only update the status of this task.
                            Ask the project lead to change the title, priority, deadline or assignee.
                        </p>
                    @endif

                    {{-- Form actions --}}
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('projects.show', $task->project) }}"
                           class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>