<x-app-layout>
    <div class="py-12 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Page Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Projects</h1>
                    <p class="text-gray-500 mt-1">Manage your team's development pipeline and deadlines.</p>
                    {{-- @foreach ($projects as $project)
                        <p>{{ $project->title }}</p>
                    @endforeach --}}
                </div>
                @if(!$projects->isEmpty())
                    <a href="{{ route('projects.create') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-100 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Project
                    </a>
                @endif
            </div>

            @if($projects->isEmpty())
                {{-- Enhanced Empty State --}}
                <div
                    class="relative block w-full border-2 border-gray-300 border-dashed rounded-3xl p-12 text-center hover:border-gray-400 transition-colors bg-white">
                    <div class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Your workspace is quiet</h2>
                    <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">
                        You haven't created any projects yet. Get started by setting up your first development sprint.
                    </p>
                    <div class="mt-8">
                        <a href="{{ route('projects.create') }}"
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all">
                            Create Project
                        </a>
                    </div>

                    {{-- <p>{{ $projects }}</p> --}}
                </div>
            @else

                {{-- Projects Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($projects as $project)
                        <div
                            class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden">

                            {{-- Top Accent Line --}}
                            <div class="h-1.5 w-full bg-indigo-500"></div>

                            <div class="p-6 flex-1 flex flex-col">
                                {{-- Card Header --}}
                                <div class="flex items-start justify-between mb-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                        Active
                                    </span>
                                </div>

                                <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-2">
                                    {{ $project->title }}
                                </h3>

                                <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-2 flex-1">
                                    {{ $project->description ?? 'No description provided for this project.' }}
                                </p>

                                {{-- Deadline --}}
                                <div class="flex items-center pb-4 border-b border-gray-50 mb-4">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Deadline</p>
                                    <span class="ml-auto text-sm font-semibold text-gray-700">
                                        {{ $project->deadline ?? 'Open' }}
                                    </span>
                                </div>

                                {{-- Card Footer Actions --}}
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('projects.show', $project) }}"
                                        class="inline-flex items-center px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-all">
                                        Show Details
                                    </a>

                                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                                        onsubmit="return confirm('Archive this project?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors"
                                            title="Archive Project">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                            </svg>
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