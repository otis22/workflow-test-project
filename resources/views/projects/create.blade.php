@extends('layouts.app', ['title' => 'Create Project | TaskFlow'])

@section('app-content')
    <section class="panel stack">
        <p class="eyebrow">Projects</p>
        <div>
            <h1>Create a project</h1>
            <p class="muted">Start a workspace for the tasks and collaborators that belong together.</p>
        </div>

        @if ($errors->any())
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('projects.store') }}" class="stack">
            @csrf

            <div class="field">
                <label for="name">Project name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="6">{{ old('description') }}</textarea>
            </div>

            <div class="button-row">
                <button type="submit">Create project</button>
                <a href="{{ route('projects.index') }}" class="button button-secondary">Back to projects</a>
            </div>
        </form>
    </section>
@endsection
