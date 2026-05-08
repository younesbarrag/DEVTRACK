<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $project->title }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 border-l-4 border-indigo-500">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Description</h3>
                <p class="text-gray-600 mb-4">{{ $project->description }}</p>
                
                <div class="text-sm text-gray-500 italic">
                    Deadline: {{ $project->deadline ?? 'No deadline' }}
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800 italic">Project Tasks</h3>
                    
                    <form action="{{ route('tasks.store') }}" method="POST" class="flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <input type="text" name="title" placeholder="New Task Title" class="rounded border-gray-300 text-sm focus:ring-indigo-500" required>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-bold">
                            + Add Task
                        </button>
                    </form>
                </div>

                @if($project->tasks->isEmpty())
                    <p class="text-gray-500 text-center py-4">No tasks added yet for this project.</p>
                @else
                    <div class="space-y-4">
                        @foreach($project->tasks as $task)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full {{ $task->status == 'done' ? 'bg-green-500' : 'bg-yellow-500' }} mr-3"></span>
                                    <p class="text-gray-800 {{ $task->status == 'done' ? 'line-through text-gray-400' : '' }}">
                                        {{ $task->title }}
                                    </p>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>