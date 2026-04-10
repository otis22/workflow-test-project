<x-layouts.app>
    <x-slot:title>{{ $task->title }} — TaskFlow</x-slot:title>

    <section class="py-8">
        @if ($project)
            <a href="{{ route('projects.show', $project->id) }}" class="text-sm text-gray-600 hover:text-gray-900">
                &larr; Back to {{ $project->name }}
            </a>
        @endif

        <div class="mt-4 mb-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $task->title }}</h1>
                <a href="{{ route('tasks.edit', $task->id) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Edit
                </a>
            </div>
            <div class="mt-2 flex items-center gap-3 text-xs font-medium text-gray-600">
                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->status->value }}</span>
                <span class="rounded-full bg-gray-100 px-2 py-1">{{ $task->priority->value }}</span>
                @if ($task->dueDate !== null)
                    <span class="rounded-full bg-gray-100 px-2 py-1">
                        due {{ $task->dueDate->value->format('Y-m-d') }}
                    </span>
                @endif
            </div>
            @if ($task->description !== '')
                <p class="mt-4 text-gray-700">{{ $task->description }}</p>
            @endif
        </div>

        <hr class="my-6 border-gray-200">

        <h2 class="mb-4 text-lg font-semibold text-gray-900">Comments</h2>

        @if (empty($comments))
            <p class="text-sm text-gray-500">No comments yet.</p>
        @else
            <ul class="mb-6 space-y-4">
                @foreach ($comments as $comment)
                    <li class="rounded-md border border-gray-200 bg-white p-4">
                        <p class="text-sm text-gray-800">{{ $comment->body }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $comment->createdAt->format('Y-m-d H:i') }}</p>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('tasks.comments.store', $task->id) }}" class="mt-4 flex flex-col gap-3">
            @csrf

            <x-form-field label="Add a comment" name="body" :error="$errors->first('body')">
                <textarea id="body" name="body" rows="3"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">{{ old('body') }}</textarea>
            </x-form-field>

            <div>
                <x-button type="submit">Post comment</x-button>
            </div>
        </form>
    </section>
</x-layouts.app>
