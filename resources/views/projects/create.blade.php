<x-layouts.app>
    <x-slot:title>Create project — TaskFlow</x-slot:title>

    <section class="mx-auto max-w-xl py-8">
        <h1 class="mb-6 text-2xl font-semibold text-gray-900">Create a new project</h1>

        <form method="POST" action="{{ route('projects.store') }}" class="flex flex-col gap-4">
            @csrf

            <x-form-field label="Name" name="name" :error="$errors->first('name')">
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none"
                    autofocus>
            </x-form-field>

            <x-form-field label="Description" name="description" :error="$errors->first('description')">
                <textarea id="description" name="description" rows="4"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">{{ old('description') }}</textarea>
            </x-form-field>

            <div class="mt-2 flex items-center gap-3">
                <x-button type="submit">Create project</x-button>
                <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</x-layouts.app>
