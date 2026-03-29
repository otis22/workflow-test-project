@extends('layouts.app', ['title' => 'Projects | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <div class="split-header">
            <div>
                <p class="eyebrow">Projects</p>
                <h1>Your projects</h1>
                <p class="muted">Browse your current workspaces and create a new one when you need to group tasks.</p>
            </div>

            <a href="{{ route('projects.create') }}" class="button">New project</a>
        </div>

        @if (session('status'))
            <p class="success-banner">{{ session('status') }}</p>
        @endif

        @if ($projects->isEmpty())
            <div class="panel inset-panel stack">
                <h2 class="section-title">No projects yet</h2>
                <p class="muted">Create your first project to start organising work in TaskFlow.</p>
            </div>
        @else
            <div class="stack">
                @foreach ($projects as $project)
                    <article class="panel inset-panel stack">
                        <div>
                            <h2 class="section-title">{{ $project->name }}</h2>
                            <p class="muted">Owner: {{ $project->owner->name }}</p>
                        </div>

                        @if ($project->description)
                            <p class="muted">{{ $project->description }}</p>
                        @endif

                        <div>
                            <a href="{{ route('projects.show', $project) }}" class="button button-secondary">Open project</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
