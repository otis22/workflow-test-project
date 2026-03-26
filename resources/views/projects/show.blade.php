@extends('layouts.app')
@section('title', $project->name)

@section('content')
<h1>{{ $project->name }}</h1>
<p>{{ $project->description }}</p>

<div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;">
    <h2>Tasks</h2>
    <a href="{{ route('tasks.create', $project) }}" class="btn btn-primary">New Task</a>
</div>

<div style="margin:0.5rem 0 1rem;">
    <a href="{{ route('projects.show', $project) }}" style="{{ !request('status') ? 'font-weight:bold' : '' }}">All</a>
    @foreach(\App\Domain\Task\TaskStatus::cases() as $s)
        | <a href="{{ route('projects.show', ['project' => $project, 'status' => $s->value, 'due' => request('due')]) }}"
             style="{{ request('status') === $s->value ? 'font-weight:bold' : '' }}">{{ ucfirst(str_replace('_', ' ', $s->value)) }}</a>
    @endforeach
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="{{ route('projects.show', ['project' => $project, 'status' => request('status'), 'due' => 'upcoming']) }}"
       style="{{ request('due') === 'upcoming' ? 'font-weight:bold' : '' }}">Upcoming</a>
    | <a href="{{ route('projects.show', ['project' => $project, 'status' => request('status'), 'due' => 'overdue']) }}"
         style="{{ request('due') === 'overdue' ? 'font-weight:bold' : '' }}">Overdue</a>
</div>

@php
    $tasks = $project->tasks()
        ->when(request('status'), fn($q) => $q->where('status', request('status')))
        ->when(request('due') === 'upcoming', fn($q) => $q->whereNotNull('due_date')->where('due_date', '>=', now())->orderBy('due_date'))
        ->when(request('due') === 'overdue', fn($q) => $q->whereNotNull('due_date')->where('due_date', '<', now()))
        ->latest()
        ->get();
@endphp

@forelse($tasks as $task)
    <div class="card">
        <div style="display:flex;justify-content:space-between;">
            <h3><a href="{{ route('tasks.show', [$project, $task]) }}">{{ $task->title }}</a></h3>
            <span>{{ ucfirst($task->priority->value) }}</span>
        </div>
        <p>
            <strong>{{ ucfirst(str_replace('_', ' ', $task->status->value)) }}</strong>
            @if($task->assignee) &middot; {{ $task->assignee->name }} @endif
            @if($task->due_date) &middot; Due: {{ $task->due_date->format('Y-m-d') }} @endif
        </p>
    </div>
@empty
    <p>No tasks yet.</p>
@endforelse
@endsection
