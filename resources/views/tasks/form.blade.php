@extends('layouts.app', ['title' => $heading.' | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <p class="eyebrow">Tasks</p>
        <div>
            <h1>{{ $heading }}</h1>
            <p class="muted">Manage the main task fields for {{ $project->name }} without leaving the project workspace.</p>
        </div>

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ $formAction }}" class="stack">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="field">
                <label for="title">Task title</label>
                <input id="title" name="title" type="text" value="{{ old('title', $task->title) }}" required autofocus>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (\App\Models\Task::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $task->status) === $status)>
                            {{ str($status)->replace('_', ' ')->title() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    @foreach (\App\Models\Task::PRIORITIES as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $task->priority) === $priority)>
                            {{ str($priority)->title() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="due_date">Due date</label>
                <input
                    id="due_date"
                    name="due_date"
                    type="date"
                    value="{{ old('due_date', $task->due_date?->toDateString()) }}"
                >
            </div>

            <div class="field">
                <label for="assignee_id">Assignee</label>
                <select id="assignee_id" name="assignee_id">
                    <option value="">Unassigned</option>
                    @foreach ($project->members->sortBy('name') as $member)
                        <option
                            value="{{ $member->id }}"
                            @selected((string) old('assignee_id', $task->assignee_id) === (string) $member->id)
                        >
                            {{ $member->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="button-row">
                <button type="submit">{{ $submitLabel }}</button>
                <a href="{{ route('projects.show', $project) }}" class="button button-secondary">Back to project</a>
            </div>
        </form>
    </section>
@endsection
