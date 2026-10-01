<x-app-layout>
<div class="mt-12 max-w-6xl mx-auto">
    {{-- Header Section --}}
    <div class="flex items-end justify-between mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                Welcome back, {{ auth()->user()->name }} 👋
            </h2>
            <p class="text-gray-500 text-sm mt-1 font-medium">
                Review your projects and personal workspace assignments
            </p>
        </div>
    </div>

    {{-- Stats Band --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
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
            <div class="bg-white border border-gray-200 rounded-3xl p-6 mb-4 hover:shadow-xl hover:border-indigo-300 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 truncate">
                            {{ $project->title }}
                        </h3>
                        <p class="text-gray-500 text-sm leading-relaxed mt-1 line-clamp-1">
                            {{ $project->description ?? 'No description provided for this project.' }}
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-gray-50 text-gray-600 border border-gray-100 uppercase tracking-tight">
                                {{ $project->tasks_count }} total tasks
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-green-50 text-green-600 border border-green-100 uppercase tracking-tight">
                                {{ $project->completed_tasks }} completed
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-bold bg-gray-50 text-gray-500 border border-gray-100 uppercase tracking-tight">
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
                <p class="text-gray-500 text-sm max-w-xs mx-auto">Create your first development sprint to start tracking tasks and deadlines.</p>
            </div>
        @endforelse
    </div>

    {{-- My Tasks Section --}}
    <div class="flex items-end justify-between mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                My Tasks
            </h2>
            <p class="text-gray-500 text-sm mt-1 font-medium">
                Review and manage your personal workspace assignments
            </p>
        </div>

        <div class="hidden sm:flex items-center gap-2 bg-indigo-50 border border-indigo-100 text-indigo-700 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-600"></span>
            </span>
            {{ $tasks->count() }} active tasks
        </div>
    </div>

    @forelse($tasks as $task)
        <div class="group relative bg-white border border-gray-200 rounded-[2rem] p-2 mb-4 hover:border-indigo-400 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500">
            
            {{-- Inner Container for padding control --}}
            <div class="p-6 flex flex-col lg:flex-row lg:items-center gap-6">
                
                {{-- Left: Status & Main Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-2">
                        {{-- Small Icon Status --}}
                        <div class="flex-shrink-0">
                            @if($task->status == 'done')
                                <div class="w-6 h-6 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            @elseif($task->status == 'in_progress')
                                <div class="w-6 h-6 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3"></path></svg>
                                </div>
                            @else
                                <div class="w-6 h-6 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 11h.01M12 15h.01M12 7h.01"></path></svg>
                                </div>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors truncate">
                            {{ $task->title }}
                        </h3>
                    </div>

                    <p class="text-gray-500 text-sm leading-relaxed mb-4 line-clamp-1 group-hover:line-clamp-none transition-all duration-300">
                        {{ $task->description ?? 'No description available for this assignment.' }}
                    </p>

                    {{-- Metadata Tags --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-gray-50 text-gray-600 px-3 py-1 rounded-lg text-[11px] font-bold border border-gray-100 uppercase tracking-tight">
                            <svg class="w-3 h-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" stroke-width="2.5"/></svg>
                            {{ $task->project?->title }}
                        </div>

                        <div class="flex items-center gap-1.5 bg-gray-50 text-gray-500 px-3 py-1 rounded-lg text-[11px] font-bold border border-gray-100 uppercase tracking-tight">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-width="2.5"/></svg>
                            {{ $task->deadline ?? 'Open Schedule' }}
                        </div>
                    </div>
                </div>

                {{-- Right: Status Badge & Primary CTA --}}
                <div class="flex items-center justify-between lg:justify-end gap-6 pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-50">
                    <div class="text-right">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Status</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-[11px] font-black uppercase tracking-tighter
                            @if($task->status == 'done') bg-green-500 text-white 
                            @elseif($task->status == 'in_progress') bg-indigo-600 text-white 
                            @else bg-gray-200 text-gray-700 @endif">
                            {{ str_replace('_', ' ', $task->status) }}
                        </span>
                    </div>

                    <a href="{{ route('projects.show', $task->project) }}"
                       class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gray-900 text-white hover:bg-indigo-600 hover:scale-110 transition-all duration-300 shadow-lg shadow-gray-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>

            </div>
        </div>
    @empty
        {{-- Modern Empty State --}}
        <div class="relative group">
            <div class="absolute inset-0 bg-indigo-50 rounded-[3rem] rotate-1 group-hover:rotate-0 transition-transform"></div>
            <div class="relative bg-white border-2 border-dashed border-gray-200 rounded-[3rem] p-20 text-center">
                <div class="w-16 h-16 mx-auto mb-6 bg-white shadow-xl rounded-2xl flex items-center justify-center text-indigo-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 italic">Inbox Zero!</h3>
                <p class="text-gray-500 text-sm max-w-xs mx-auto">You've cleared your plate. Time to grab a coffee or start a new project.</p>
            </div>
        </div>
    @endforelse
</div>
</x-app-layout>