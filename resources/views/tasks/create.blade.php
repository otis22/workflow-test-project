<x-layouts.app>
    <x-slot:title>Create task — {{ $project->name }} — TaskFlow</x-slot:title>

    <section class="mx-auto max-w-xl py-8">
        <a href="{{ route('projects.show', $project->id) }}" class="text-sm text-gray-600 hover:text-gray-900">
            &larr; Back to {{ $project->name }}
        </a>

        <h1 class="mt-4 mb-6 text-2xl font-semibold text-gray-900">Create a new task</h1>

        <form method="POST" action="{{ route('tasks.store', $project->id) }}" class="flex flex-col gap-4">
            @csrf

            <x-form-field label="Title" name="title" :error="$errors->first('title')">
                <input id="title" name="title" type="text" value="{{ old('title') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none"
                    autofocus>
            </x-form-field>

            <x-form-field label="Description" name="description" :error="$errors->first('description')">
                <textarea id="description" name="description" rows="4"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">{{ old('description') }}</textarea>
            </x-form-field>

            <div class="grid grid-cols-2 gap-4">
                <x-form-field label="Status" name="status" :error="$errors->first('status')">
                    <select id="status" name="status"
                        class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
                        @foreach ($statuses as $s)
                            <option value="{{ $s->value }}" @selected(old('status', 'todo') === $s->value)>
                                {{ ucfirst(str_replace('_', ' ', $s->value)) }}
                            </option>
                        @endforeach
                    </select>
                </x-form-field>

                <x-form-field label="Priority" name="priority" :error="$errors->first('priority')">
                    <select id="priority" name="priority"
                        class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}" @selected(old('priority', 'medium') === $p->value)>
                                {{ ucfirst($p->value) }}
                            </option>
                        @endforeach
                    </select>
                </x-form-field>
            </div>

            <x-form-field label="Due date" name="due_date" :error="$errors->first('due_date')">
                <input id="due_date" name="due_date" type="date" value="{{ old('due_date') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
            </x-form-field>

            <div class="mt-2 flex items-center gap-3">
                <x-button type="submit">Create task</x-button>
                <a href="{{ route('projects.show', $project->id) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layouts.app>
