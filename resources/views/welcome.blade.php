@extends('layouts.base', ['title' => 'TaskFlow'])

@section('content')
    <section class="panel stack">
        <p class="eyebrow">TaskFlow</p>
        <div>
            <h1>Foundation ready for the MVP build</h1>
            <p class="muted">The base platform is online. Registration is the first guest flow available in this roadmap stage.</p>
        </div>

        <div class="stack">
            <a href="{{ route('register') }}" class="button">Create account</a>
            <a href="{{ route('login') }}" class="button">Sign in</a>
        </div>
    </section>
@endsection
