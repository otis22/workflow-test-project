@extends('layouts.base', ['title' => $title ?? 'TaskFlow'])

@section('content')
    <div class="app-shell">
        <aside class="panel sidebar stack">
            <div class="stack">
                <p class="eyebrow">TaskFlow</p>
                <div>
                    <h1 class="app-title">Work hub</h1>
                    <p class="muted">The first authenticated shell for projects, personal tasks, and deadlines.</p>
                </div>
            </div>

            <nav class="stack nav-links" aria-label="Primary">
                <a href="{{ route('dashboard') }}" class="nav-link{{ request()->routeIs('dashboard') ? ' is-active' : '' }}">
                    Dashboard
                </a>
                <span class="nav-link nav-link--muted">Projects</span>
                <span class="nav-link nav-link--muted">My work</span>
            </nav>

            <div class="panel profile-card stack">
                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <p class="muted profile-email">{{ auth()->user()->email }}</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            </div>
        </aside>

        <section class="stack content-area">
            @yield('app-content')
        </section>
    </div>
@endsection
