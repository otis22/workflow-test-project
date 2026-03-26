@extends('layouts.app')
@section('title', 'Edit Task')

@section('content')
<div class="card" style="max-width:500px;">
    <h1>Edit: {{ $task->title }}</h1>
    <form method="POST" action="{{ route('tasks.update', [$project, $task]) }}">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $task->title) }}" required>
            @error('title') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description', $task->description) }}</textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach($statuses as $s)
                    <option value="{{ $s->value }}" {{ old('status', $task->status->value) === $s->value ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s->value)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}" {{ old('priority', $task->priority->value) === $p->value ? 'selected' : '' }}>{{ ucfirst($p->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="assignee_id">Assignee</label>
            <select id="assignee_id" name="assignee_id">
                <option value="">Unassigned</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" {{ old('assignee_id', $task->assignee_id) == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
@endsection
