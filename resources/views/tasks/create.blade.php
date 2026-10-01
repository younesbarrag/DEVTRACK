<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                New Task — {{ $project->title }}
            </h2>
            <a href="{{ route('projects.show', $project) }}"
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                Back to Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            {{-- Description strip --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border-l-4 border-indigo-500">
                <p class="text-gray-600 mb-2">{{ $project->description }}</p>
                <div class="text-sm text-gray-500 italic">
                    Deadline: {{ $project->deadline ?? 'No deadline' }}
                </div>
            </div>

            {{-- Create task form --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-3xl border border-gray-200 p-8">
                <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight mb-6">Create a Task</h3>

                <form method="POST" action="{{ route('projects.tasks.store', $project) }}" class="space-y-6">
                    @csrf

                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700" for="title">Title <span class="text-red-500">*</span></label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title') }}"
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
                        >{{ old('description') }}</textarea>
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
                                value="{{ old('deadline') }}"
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
                                <option value="">— Choose —</option>
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Assignee — project members only --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-700" for="assigned_to">Assigned To <span class="text-red-500">*</span></label>
                        <select id="assigned_to" name="assigned_to" required {{ $members->isEmpty() ? 'disabled' : '' }}
                                class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">— Choose a developer —</option>
                            @forelse($members as $member)
                                <option value="{{ $member->id }}" {{ (int) old('assigned_to') === $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ ucfirst($member->pivot->role) }})
                                </option>
                            @empty
                                <option value="" disabled>No members in this project</option>
                            @endforelse
                        </select>
                        @if($members->isEmpty())
                            <p class="mt-1 text-sm text-amber-600">
                                Add members to this project before creating tasks.
                            </p>
                        @endif
                        @error('assigned_to')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Form actions --}}
                    <div class="flex items-center justify-between pt-4">
                        <a href="{{ route('projects.show', $project) }}"
                           class="inline-flex items-center px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-all">
                            Cancel
                        </a>
                        <button type="submit" {{ $members->isEmpty() ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            Create Task
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>