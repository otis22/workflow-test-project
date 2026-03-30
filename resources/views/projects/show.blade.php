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

        @if (session('status'))
            <p class="success-banner">{{ session('status') }}</p>
        @endif

        @if ($project->description)
            <p class="muted">{{ $project->description }}</p>
        @endif
    </section>

    <section class="dashboard-grid">
        <article class="panel stack">
            <div class="split-header">
                <div>
                    <p class="eyebrow">Tasks</p>
                    <h2 class="section-title">Project task list</h2>
                </div>

                <a href="{{ route('projects.tasks.create', $project) }}" class="button">Create task</a>
            </div>

            @if ($project->tasks->isEmpty())
                <p class="muted">No tasks yet. Create the first task to start tracking project work.</p>
            @else
                <div class="stack">
                    @foreach ($project->tasks->sortByDesc('created_at') as $task)
                        <article class="panel inset-panel stack">
                            <div>
                                <h3 class="section-title">{{ $task->title }}</h3>
                                <p class="muted">
                                    Status: {{ str($task->status)->replace('_', ' ')->title() }}
                                    · Priority: {{ str($task->priority)->title() }}
                                    @if ($task->assignee)
                                        · Assignee: {{ $task->assignee->name }}
                                    @endif
                                    @if ($task->due_date)
                                        · Due: {{ $task->due_date->toDateString() }}
                                    @endif
                                </p>
                            </div>

                            @if ($task->description)
                                <p class="muted">{{ $task->description }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel stack">
            <p class="eyebrow">Members</p>
            <h2 class="section-title">Current participants</h2>
            <p class="muted">{{ $project->members->pluck('name')->join(', ') }}</p>
        </article>
    </section>
@endsection
