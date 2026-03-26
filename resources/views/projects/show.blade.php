@extends('layouts.app')
@section('title', $project->name)

@section('content')
<h1>{{ $project->name }}</h1>
<p>{{ $project->description }}</p>

<h2 style="margin-top:1.5rem;">Tasks</h2>
<p>No tasks yet.</p>
@endsection
