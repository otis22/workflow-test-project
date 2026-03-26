@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<h1>Dashboard</h1>
<p>Welcome, {{ auth()->user()->name }}!</p>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.5rem;">
    <div>
        <h2>My Tasks</h2>
        @forelse($my_tasks as $task)
            <div class="card">
                <h3><a href="{{ route('tasks.show', [$task->project_id, $task]) }}">{{ $task->title }}</a></h3>
                <p>{{ $task->project->name }} &middot; {{ ucfirst(str_replace('_', ' ', $task->status->value)) }}</p>
            </div>
        @empty
            <p>No tasks assigned to you.</p>
        @endforelse
    </div>

    <div>
        <h2>Upcoming Deadlines</h2>
        @forelse($upcoming as $task)
            <div class="card">
                <h3><a href="{{ route('tasks.show', [$task->project_id, $task]) }}">{{ $task->title }}</a></h3>
                <p>{{ $task->project->name }} &middot; Due: {{ $task->due_date->format('Y-m-d') }}</p>
            </div>
        @empty
            <p>No upcoming deadlines.</p>
        @endforelse
    </div>
</div>

<h2 style="margin-top:1.5rem;">Projects</h2>
@forelse($projects as $project)
    <div class="card">
        <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a>
    </div>
@empty
    <p>No projects yet. <a href="{{ route('projects.create') }}">Create one</a>.</p>
@endforelse
@endsection
