<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Projects') }}
            </h2>
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition duration-150">
                + New Project
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($projects->isEmpty())
                <div class="bg-white p-8 rounded-lg shadow text-center">
                    <p class="text-gray-500 text-lg font-medium">No projects found. Let's start building!</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 hover:shadow-lg transition duration-300">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-xl font-bold text-indigo-700 truncate">
                                        {{ $project->title }}
                                    </h3>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>

                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                    {{ $project->description }}
                                </p>

                                <div class="flex items-center text-gray-400 text-xs mb-4 italic">
                                    Deadline: {{ $project->deadline ?? 'No deadline' }}
                                </div>

                                <div class="flex justify-between items-center border-t pt-4">
                                    <a href="{{ route('projects.show', $project->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-sm">
                                        View Details →
                                    </a>
                                    
                                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this project?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                            Archive
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>