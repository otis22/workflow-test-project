<x-layouts.app>
    <x-slot:title>Dashboard — TaskFlow</x-slot:title>

    <section class="py-8">
        <h1 class="mb-6 text-2xl font-semibold text-gray-900">Dashboard</h1>

        <div class="grid gap-8 lg:grid-cols-3">
            {{-- My tasks --}}
            <div class="lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-gray-900">My tasks</h2>
                @if (empty($myTasks))
                    <p class="text-sm text-gray-500">No tasks assigned to you.</p>
                @else
                    <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                        @foreach ($myTasks as $task)
                            <li class="flex items-center justify-between p-4">
                                <a href="{{ route('tasks.show', $task->id) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                    {{ $task->title }}
                                </a>
                                <div class="flex items-center gap-2 text-xs text-gray-600">
                                    <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->status->value }}</span>
                                    <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->priority->value }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Sidebar: upcoming deadlines + projects --}}
            <div class="flex flex-col gap-8">
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Upcoming deadlines</h2>
                    @if (empty($upcomingDeadlines))
                        <p class="text-sm text-gray-500">No upcoming deadlines.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($upcomingDeadlines as $task)
                                <li class="flex items-center justify-between rounded-md border border-gray-200 bg-white p-3">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                        {{ $task->title }}
                                    </a>
                                    <span class="text-xs text-gray-600">
                                        {{ $task->dueDate->value->format('M j') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">My projects</h2>
                    @if (empty($projects))
                        <p class="text-sm text-gray-500">No projects yet.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($projects as $project)
                                <li>
                                    <a href="{{ route('projects.show', $project->id) }}" class="text-sm font-medium text-gray-900 hover:underline">
                                        {{ $project->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
