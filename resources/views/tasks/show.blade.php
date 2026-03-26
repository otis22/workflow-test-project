@extends('layouts.app')
@section('title', $task->title)

@section('content')
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h1>{{ $task->title }}</h1>
        <a href="{{ route('tasks.edit', [$project, $task]) }}" class="btn btn-primary">Edit</a>
    </div>
    <p><strong>Project:</strong> <a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></p>
    <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $task->status->value)) }}</p>
    <p><strong>Priority:</strong> {{ ucfirst($task->priority->value) }}</p>
    <p><strong>Creator:</strong> {{ $task->creator->name }}</p>
    <p><strong>Assignee:</strong> {{ $task->assignee?->name ?? 'Unassigned' }}</p>
    <p><strong>Due Date:</strong> {{ $task->due_date?->format('Y-m-d') ?? 'None' }}</p>
    @if($task->description)
        <p style="margin-top:1rem;">{{ $task->description }}</p>
    @endif
</div>

<h2 style="margin-top:1.5rem;">Comments</h2>

@forelse($task->comments as $comment)
    <div class="card">
        <strong>{{ $comment->author->name }}</strong> <small>{{ $comment->created_at->diffForHumans() }}</small>
        <p>{{ $comment->body }}</p>
    </div>
@empty
    <p>No comments yet.</p>
@endforelse

<div class="card" style="margin-top:1rem;">
    <form method="POST" action="{{ route('comments.store', [$project, $task]) }}">
        @csrf
        <div class="form-group">
            <label for="body">Add Comment</label>
            <textarea id="body" name="body" rows="2" required>{{ old('body') }}</textarea>
            @error('body') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Post Comment</button>
    </form>
</div>
@endsection
