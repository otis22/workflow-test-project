<x-layouts.app>
    <x-slot:title>Register — TaskFlow</x-slot:title>

    <section class="mx-auto max-w-md py-8">
        <h1 class="mb-6 text-2xl font-semibold text-gray-900">Create your account</h1>

        <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
            @csrf

            <x-form-field label="Name" name="name" :error="$errors->first('name')">
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none"
                    autofocus>
            </x-form-field>

            <x-form-field label="Email" name="email" :error="$errors->first('email')">
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
            </x-form-field>

            <x-form-field label="Password" name="password" :error="$errors->first('password')">
                <input id="password" name="password" type="password"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
            </x-form-field>

            <x-form-field label="Confirm password" name="password_confirmation">
                <input id="password_confirmation" name="password_confirmation" type="password"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
            </x-form-field>

            <x-button type="submit" class="mt-2">Register</x-button>
        </form>
    </section>
</x-layouts.app>
