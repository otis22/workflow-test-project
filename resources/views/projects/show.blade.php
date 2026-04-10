<x-layouts.app>
    <x-slot:title>{{ $project->name }} — TaskFlow</x-slot:title>

    <section class="py-8">
        <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
            &larr; Back to projects
        </a>

        <div class="mt-4 mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
                @if ($project->description !== '')
                    <p class="mt-2 text-gray-600">{{ $project->description }}</p>
                @endif
            </div>
            {{-- Placeholder href — wired in 6.1 --}}
            <a href="#" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Create task
            </a>
        </div>

        @php
            $statusFilters = [
                ['value' => null, 'label' => 'All'],
                ['value' => 'todo', 'label' => 'Todo'],
                ['value' => 'in_progress', 'label' => 'In progress'],
                ['value' => 'done', 'label' => 'Done'],
            ];
            $currentStatus = $activeStatus?->value;
        @endphp

        <nav class="mb-6 flex gap-2 text-sm">
            @foreach ($statusFilters as $filter)
                @php
                    $url = $filter['value'] === null
                        ? route('projects.show', $project->id)
                        : route('projects.show', $project->id).'?status='.$filter['value'];
                    $isActive = $currentStatus === $filter['value'];
                @endphp
                <a href="{{ $url }}"
                    class="rounded-md px-3 py-1.5 {{ $isActive ? 'bg-gray-900 text-white' : 'border border-gray-300 text-gray-700 hover:bg-gray-50' }}">
                    {{ $filter['label'] }}
                </a>
            @endforeach
        </nav>

        @if (empty($tasks))
            <div class="rounded-md border border-dashed border-gray-300 p-8 text-center">
                <p class="text-gray-600">No tasks in this project yet.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-200 rounded-md border border-gray-200 bg-white">
                @foreach ($tasks as $task)
                    <li class="flex items-center justify-between p-4">
                        <div>
                            <p class="text-base font-medium text-gray-900">{{ $task->title }}</p>
                            @if ($task->description !== '')
                                <p class="text-sm text-gray-600">{{ $task->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 text-xs font-medium text-gray-600">
                            <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->status->value }}</span>
                            <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->priority->value }}</span>
                            @if ($task->dueDate !== null)
                                <span class="rounded-full bg-gray-100 px-2 py-1">
                                    due {{ $task->dueDate->value->format('Y-m-d') }}
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
