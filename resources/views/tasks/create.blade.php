@extends('layouts.app')
@section('title', 'New Task')

@section('content')
<div class="card" style="max-width:500px;">
    <h1>New Task in {{ $project->name }}</h1>
    <form method="POST" action="{{ route('tasks.store', $project) }}">
        @csrf
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required>
            @error('title') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                @foreach($priorities as $p)
                    <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>{{ ucfirst($p->value) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="assignee_id">Assignee</label>
            <select id="assignee_id" name="assignee_id">
                <option value="">Unassigned</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}">
        </div>
        <button type="submit" class="btn btn-primary">Create Task</button>
    </form>
</div>
@endsection
