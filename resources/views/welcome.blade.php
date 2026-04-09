<x-layouts.app>
    <x-slot:title>TaskFlow — Manage your work</x-slot:title>

    <section class="flex flex-col items-center gap-6 py-12 text-center">
        <h1 class="text-4xl font-semibold text-gray-900">
            Welcome to TaskFlow
        </h1>

        <p class="max-w-xl text-gray-600">
            A minimal project and task tracker for small teams.
            Create projects, assign tasks, track deadlines and keep the
            conversation close to the work.
        </p>

        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                Sign in
            </a>
            <a href="{{ route('register') }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Register
            </a>
        </div>
    </section>
</x-layouts.app>
