<nav class="border-b border-gray-200 bg-white">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">
            {{ config('app.name', 'TaskFlow') }}
        </a>

        <div class="flex items-center gap-4 text-sm font-medium">
            @guest
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-md bg-gray-900 px-3 py-1.5 text-white hover:bg-gray-800">Register</a>
            @endguest

            @auth
                <a href="#" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                <a href="#" class="text-gray-600 hover:text-gray-900">Projects</a>
                {{-- Logout placeholder: real POST form + route wired in 4.4. --}}
                <button type="button" class="text-gray-600 hover:text-gray-900" disabled>Logout</button>
            @endauth
        </div>
    </div>
</nav>
