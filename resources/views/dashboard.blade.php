@extends('layouts.app', ['title' => 'Dashboard | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <p class="eyebrow">Dashboard</p>
        <div>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p class="muted">
                Use this personal overview to stay on top of assigned work, near-term deadlines, and your active projects.
            </p>
        </div>
    </section>

    <section class="dashboard-grid">
        <article class="panel stack">
            <p class="eyebrow">My work</p>
            <h2 class="section-title">Assigned work snapshot</h2>
            @if ($assignedTasks->isEmpty())
                <p class="muted">No active tasks are assigned to you yet.</p>
            @else
                <div class="stack">
                    @foreach ($assignedTasks as $task)
                        <section class="panel inset-panel stack">
                            <div>
                                <strong>{{ $task->title }}</strong>
                                <p class="muted">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    @if ($task->due_date)
                                        · due {{ $task->due_date->format('M j, Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="button-row">
                                <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="button button-secondary">View task</a>
                                <a href="{{ route('projects.show', $task->project) }}" class="button button-secondary">
                                    {{ $task->project->name }}
                                </a>
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel stack">
            <p class="eyebrow">Upcoming deadlines</p>
            <h2 class="section-title">Near-term due dates</h2>
            @if ($nearTermDeadlines->isEmpty())
                <p class="muted">Nothing is due in the next week.</p>
            @else
                <div class="stack">
                    @foreach ($nearTermDeadlines as $task)
                        <section class="panel inset-panel stack">
                            <div>
                                <strong>{{ $task->title }}</strong>
                                <p class="muted">
                                    {{ $task->project->name }} · due {{ $task->due_date?->format('M j, Y') }}
                                </p>
                            </div>
                            <div class="button-row">
                                <p class="muted">Assigned to you</p>
                                <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="button button-secondary">View task</a>
                            </div>
                        </section>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel stack">
            <p class="eyebrow">Projects</p>
            <h2 class="section-title">Project navigation</h2>
            @if ($projects->isEmpty())
                <p class="muted">Create your first project to start tracking work.</p>
                <a href="{{ route('projects.create') }}" class="button">Create project</a>
            @else
                <div class="stack">
                    @foreach ($projects as $project)
                        <section class="panel inset-panel stack">
                            <div>
                                <strong>{{ $project->name }}</strong>
                                <p class="muted">{{ $project->open_tasks_count }} active tasks</p>
                            </div>
                            <a href="{{ route('projects.show', $project) }}" class="button button-secondary">Open project</a>
                        </section>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
