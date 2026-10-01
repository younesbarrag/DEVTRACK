<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->title }}
            </h2>
            <div class="flex space-x-2">
                {{-- <a href="{{ route('projects.tasks.index', $project) }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm transition">
                    Show All Tasks
                </a> --}}
                <a href="{{ route('projects.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Project description strip --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8 border-l-4 border-indigo-500">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $project->title }}</h3>
                <p class="text-gray-600 mb-4">{{ $project->description }}</p>
                <div class="text-sm text-gray-500 italic">
                    Deadline: {{ $project->deadline ?? 'No deadline' }}
                </div>
            </div>


            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm font-semibold text-green-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm font-semibold text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Project team --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
                    <h3 class="text-lg font-extrabold text-gray-900 tracking-tight">Project Team</h3>

                    @can('manageMembers', $project)
                        <form action="{{ route('projects.members.store', $project) }}" method="POST"
                            class="flex items-center gap-2 flex-wrap">
                            @csrf
                            <input type="email" name="email" value="{{ old('email') }}"
                                placeholder="developer@example.com"
                                class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                required>
                            <select name="role" class="rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="developer" selected>Developer</option>
                                <option value="lead">Lead</option>
                            </select>
                            <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition">
                                Add Member
                            </button>
                        </form>
                    @endcan
                </div>

                @if($project->users->isEmpty())
                    <p class="text-gray-500 text-sm py-2">
                        No team members yet. Add a developer by email so tasks can be assigned to them.
                    </p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($project->users as $member)
                            <div
                                class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-lg px-3 py-1.5">
                                <div
                                    class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase">
                                    {{ substr($member->name, 0, 1) }}
                                </div>
                                <div class="leading-tight">
                                    <p class="text-xs font-semibold text-gray-800">{{ $member->name }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">
                                        {{ $member->pivot->role }}
                                    </p>
                                </div>
                                @can('manageMembers', $project)
                                    @if($member->id !== $project->user_id)
                                        <form action="{{ route('projects.members.destroy', [$project, $member]) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Remove member"
                                                class="text-red-400 hover:text-red-600 text-xs font-bold px-1">
                                                &times;
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- All tasks section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @php
                    $tasks = $project->tasks;
                    $total = $tasks->count();
                    $done = $tasks->where('status', 'done')->count();
                    $active = $total - $done;
                @endphp

                <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Project Tasks</h3>

                    <form action="{{ route('projects.tasks.store', $project) }}" method="POST"
                        class="flex items-center space-x-2 flex-wrap gap-2">
                        @csrf
                        <input type="text" name="title" placeholder="New Task Title"
                            class="rounded border-gray-300 text-sm focus:ring-indigo-500" required>
                        <select name="priority" class="rounded border-gray-300 text-sm">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                        <select name="assigned_to" required
                            class="rounded border-gray-300 text-sm focus:ring-indigo-500">
                            <option value="">Assign to…</option>
                            @foreach($project->users as $member)
                                <option value="{{ $member->id }}" {{ (int) old('assigned_to') === $member->id ? 'selected' : '' }}>
                                    {{ $member->name }} ({{ ucfirst($member->pivot->role) }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-bold">
                            + Add Task
                        </button>
                    </form>
                </div>

                {{-- Stats band --}}
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-indigo-600 text-white rounded-2xl p-4">
                        <p class="text-3xl font-extrabold">{{ $total }}</p>
                        <p class="text-indigo-200 text-xs font-bold uppercase tracking-wider mt-1">Total</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-2xl p-4">
                        <p class="text-3xl font-extrabold text-amber-500">{{ $active }}</p>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Active</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-2xl p-4">
                        <p class="text-3xl font-extrabold text-green-500">{{ $done }}</p>
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-wider mt-1">Completed</p>
                    </div>
                    <div class="w-full h-full">
                        <div class="w-full flex justify-center items-center h-full"><a href="{{ route('projects.tasks.index', $project) }}" class="px-4 py-2 rounded text-sm font-bold bg-yellow-600 text-gray-900 ">Show All Tasks</a></div>
                    </div>
                </div>

                {{-- Task list --}}
                @if($tasks->isEmpty())
                    <p class="text-gray-500 text-center py-4">No tasks added yet for this project.</p>
                @else
                    <div class="space-y-4">
                        @foreach($tasks as $task)
                            <div
                                class="flex flex-col lg:flex-row lg:items-center gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                {{-- Left: status + info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase tracking-tighter
                                                    @if($task->status == 'done') bg-green-500 text-white
                                                    @elseif($task->status == 'in_progress') bg-indigo-600 text-white
                                                    @else bg-gray-200 text-gray-700 @endif">
                                            {{ $task->status_label }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold uppercase tracking-tight
                                                    @if($task->priority == 'high') bg-red-50 text-red-600 border border-red-100
                                                    @elseif($task->priority == 'medium') bg-amber-50 text-amber-600 border border-amber-100
                                                    @else bg-green-50 text-green-600 border border-green-100 @endif">
                                            {{ ucfirst($task->priority) }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-gray-800 font-semibold {{ $task->status == 'done' ? 'line-through text-gray-400' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                    <p class="text-gray-500 text-sm mt-0.5 line-clamp-1">
                                        {{ $task->description ?? 'No description available.' }}
                                    </p>
                                </div>

                                {{-- Right: assignee, deadline, actions --}}
                                <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                    <div
                                        class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5">
                                        <div
                                            class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase">
                                            {{ substr($task->user?->name ?? 'U', 0, 1) }}
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700">
                                            {{ $task->user?->name ?? 'Unassigned' }}
                                        </span>
                                    </div>

                                    <div
                                        class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5">
                                        @if($task->status == 'done')
                                            <span class="text-xs font-bold text-green-600">Completed</span>
                                        @elseif($task->deadline_status == 'overdue')
                                            <span class="text-xs font-bold text-red-500">Overdue · {{ $task->deadline }}</span>
                                        @elseif($task->deadline_status == 'urgent')
                                            <span class="text-xs font-bold text-amber-500">Urgent · {{ $task->deadline }}</span>
                                        @else
                                            <span class="text-xs text-gray-500">{{ $task->deadline ?? 'Open Schedule' }}</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('projects.tasks.edit', [$project, $task]) }}"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-white bg-gray-900 rounded-lg hover:bg-indigo-600 transition-all">
                                            Edit
                                        </a>
                                        <form action="{{ route('projects.tasks.destroy', [$project, $task]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>