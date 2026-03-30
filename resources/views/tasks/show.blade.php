@extends('layouts.app', ['title' => $task->title.' | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <div class="split-header">
            <div>
                <p class="eyebrow">Task details</p>
                <h1>{{ $task->title }}</h1>
                <p class="muted">Project: {{ $project->name }}</p>
            </div>

            <div class="button-row">
                <a href="{{ route('projects.show', $project) }}" class="button button-secondary">Back to project</a>
                <a href="{{ route('projects.tasks.edit', [$project, $task]) }}" class="button">Edit task</a>
            </div>
        </div>

        @if (session('status'))
            <p class="success-banner">{{ session('status') }}</p>
        @endif

        @if ($task->description)
            <p class="muted">{{ $task->description }}</p>
        @else
            <p class="muted">No description has been added yet.</p>
        @endif
    </section>

    <section class="dashboard-grid">
        <article class="panel stack">
            <p class="eyebrow">Overview</p>
            <h2 class="section-title">Main fields</h2>

            <div class="stack">
                <section class="panel inset-panel stack">
                    <div>
                        <strong>Status</strong>
                        <p class="muted">{{ str($task->status)->replace('_', ' ')->title() }}</p>
                    </div>
                </section>

                <section class="panel inset-panel stack">
                    <div>
                        <strong>Priority</strong>
                        <p class="muted">{{ str($task->priority)->title() }}</p>
                    </div>
                </section>

                <section class="panel inset-panel stack">
                    <div>
                        <strong>Assignee</strong>
                        <p class="muted">{{ $task->assignee?->name ?? 'Unassigned' }}</p>
                    </div>
                </section>

                <section class="panel inset-panel stack">
                    <div>
                        <strong>Due date</strong>
                        <p class="muted">{{ $task->due_date?->toDateString() ?? 'No deadline set' }}</p>
                    </div>
                </section>

                <section class="panel inset-panel stack">
                    <div>
                        <strong>Created by</strong>
                        <p class="muted">{{ $task->creator->name }}</p>
                    </div>
                </section>
            </div>
        </article>

        <article class="panel stack">
            <p class="eyebrow">Comments</p>
            <h2 class="section-title">Discussion</h2>
            <p class="muted">
                Task comments and the add-comment form will appear here in the next roadmap task.
            </p>
        </article>
    </section>
@endsection
