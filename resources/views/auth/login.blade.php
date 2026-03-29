@extends('layouts.base', ['title' => 'Login | TaskFlow'])

@section('content')
    <section class="hero">
        <div class="panel stack">
            <p class="eyebrow">TaskFlow</p>
            <div>
                <h1>Sign in to TaskFlow</h1>
                <p class="muted">Continue to your workspace, projects, and upcoming tasks.</p>
            </div>
        </div>

        <div class="panel">
            @if ($errors->any())
                <ul class="errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="stack">
                @csrf

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <button type="submit">Sign in</button>
            </form>
        </div>
    </section>
@endsection
