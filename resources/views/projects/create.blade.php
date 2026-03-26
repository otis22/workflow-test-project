@extends('layouts.app')
@section('title', 'New Project')

@section('content')
<div class="card" style="max-width:500px;">
    <h1>New Project</h1>
    <form method="POST" action="{{ route('projects.store') }}">
        @csrf
        <div class="form-group">
            <label for="name">Project Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
            @error('description') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
