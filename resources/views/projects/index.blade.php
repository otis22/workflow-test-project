@extends('layouts.app')
@section('title', 'Projects')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
    <h1>Projects</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary">New Project</a>
</div>

@forelse($projects as $project)
    <div class="card">
        <h2><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></h2>
        <p>{{ $project->description }}</p>
    </div>
@empty
    <p>No projects yet. Create your first project!</p>
@endforelse
@endsection
