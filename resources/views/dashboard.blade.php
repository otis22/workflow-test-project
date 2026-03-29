@extends('layouts.app', ['title' => 'Dashboard | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <p class="eyebrow">Dashboard</p>
        <div>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p class="muted">
                Use this shell as the entry point for your assigned work, project navigation, and deadline awareness while the rest of the MVP is added.
            </p>
        </div>
    </section>

    <section class="dashboard-grid">
        <article class="panel stack">
            <p class="eyebrow">My work</p>
            <h2 class="section-title">Assigned work snapshot</h2>
            <p class="muted">Your active tasks will appear here once project and task management are implemented.</p>
        </article>

        <article class="panel stack">
            <p class="eyebrow">Upcoming deadlines</p>
            <h2 class="section-title">Near-term due dates</h2>
            <p class="muted">Deadline visibility is reserved here so the dashboard can become the user's default focus screen.</p>
        </article>

        <article class="panel stack">
            <p class="eyebrow">Projects</p>
            <h2 class="section-title">Project navigation</h2>
            <p class="muted">Projects will be listed here after the next roadmap stage introduces project creation and membership.</p>
        </article>
    </section>
@endsection
