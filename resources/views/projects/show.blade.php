@extends('layouts.app', ['title' => $project->name.' | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <div class="split-header">
            <div>
                <p class="eyebrow">Project workspace</p>
                <h1>{{ $project->name }}</h1>
                <p class="muted">Owner: {{ $project->owner->name }}</p>
            </div>

            <a href="{{ route('projects.index') }}" class="button button-secondary">All projects</a>
        </div>

        @if ($project->description)
            <p class="muted">{{ $project->description }}</p>
        @endif
    </section>

    <section class="dashboard-grid">
        <article class="panel stack">
            <p class="eyebrow">Tasks</p>
            <h2 class="section-title">Project task list</h2>
            <p class="muted">Task management will land in the next roadmap stage and appear in this workspace.</p>
        </article>

        <article class="panel stack">
            <p class="eyebrow">Members</p>
            <h2 class="section-title">Current participants</h2>
            <p class="muted">{{ $project->members->pluck('name')->join(', ') }}</p>
        </article>
    </section>
@endsection
