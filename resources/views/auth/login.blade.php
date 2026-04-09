<x-layouts.app>
    <x-slot:title>Sign in — TaskFlow</x-slot:title>

    <section class="mx-auto max-w-md py-8">
        <h1 class="mb-6 text-2xl font-semibold text-gray-900">Sign in to TaskFlow</h1>

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
            @csrf

            <x-form-field label="Email" name="email" :error="$errors->first('email')">
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none"
                    autofocus>
            </x-form-field>

            <x-form-field label="Password" name="password" :error="$errors->first('password')">
                <input id="password" name="password" type="password"
                    class="rounded-md border border-gray-300 px-3 py-2 focus:border-gray-900 focus:outline-none">
            </x-form-field>

            <x-button type="submit" class="mt-2">Sign in</x-button>

            <p class="mt-2 text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-gray-900 hover:underline">Register</a>
            </p>
        </form>
    </section>
</x-layouts.app>
