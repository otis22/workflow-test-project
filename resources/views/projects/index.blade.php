<x-layouts.app>
    <x-slot:title>Projects — TaskFlow</x-slot:title>

    <section class="py-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Your projects</h1>
            <a href="{{ route('projects.create') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Create project
            </a>
        </div>

        @if (empty($projects))
            <div class="rounded-md border border-dashed border-gray-300 p-8 text-center">
                <p class="text-gray-600">You have no projects yet.</p>
                <a href="{{ route('projects.create') }}" class="mt-4 inline-block text-sm font-medium text-gray-900 hover:underline">
                    Create your first project
                </a>
            </div>
        @else
            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                @foreach ($projects as $project)
                    <li class="flex items-center justify-between p-4">
                        <div>
                            {{-- Placeholder href — project detail page wired in 5.3 --}}
                            <a href="#" class="text-base font-medium text-gray-900 hover:underline">
                                {{ $project->name }}
                            </a>
                            @if ($project->description !== '')
                                <p class="text-sm text-gray-600">{{ $project->description }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
