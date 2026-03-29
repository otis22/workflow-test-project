@extends('layouts.base', ['title' => 'Dashboard | TaskFlow'])

@section('content')
    <section class="panel stack">
        <p class="eyebrow">Dashboard</p>
        <div>
            <h1>Welcome to TaskFlow</h1>
            <p class="muted">
                Signed in as {{ auth()->user()->name }}. Project, task, and personal workspace widgets will be added in the next roadmap tasks.
            </p>
        </div>
    </section>
@endsection
