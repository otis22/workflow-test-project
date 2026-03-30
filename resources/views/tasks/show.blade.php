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

            <form method="POST" action="{{ route('projects.tasks.comments.store', [$project, $task]) }}" class="stack">
                @csrf

                <div class="field">
                    <label for="body">Add comment</label>
                    <textarea id="body" name="body" required>{{ old('body') }}</textarea>
                </div>

                @error('body')
                    <ul class="errors">
                        <li>{{ $message }}</li>
                    </ul>
                @enderror

                <div>
                    <button type="submit">Post comment</button>
                </div>
            </form>

            @if ($task->comments->isEmpty())
                <p class="muted">No comments yet. Add the first note for this task.</p>
            @else
                <div class="stack">
                    @foreach ($task->comments as $comment)
                        <section class="panel inset-panel stack">
                            <div>
                                <strong>{{ $comment->author->name }}</strong>
                                <p class="muted">{{ $comment->created_at?->format('M j, Y H:i') }}</p>
                            </div>
                            <p class="muted">{{ $comment->body }}</p>
                        </section>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
