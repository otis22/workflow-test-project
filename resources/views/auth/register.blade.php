@extends('layouts.base', ['title' => 'Register | TaskFlow'])

@section('content')
    <section class="hero">
        <div class="panel stack">
            <p class="eyebrow">TaskFlow</p>
            <div>
                <h1>Create your TaskFlow account</h1>
                <p class="muted">Start with a personal workspace and move into project and task management from the same account.</p>
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

            <form method="POST" action="{{ route('register.store') }}" class="stack">
                @csrf

                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" required>
                </div>

                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required>
                </div>

                <button type="submit">Create account</button>
            </form>
        </div>
    </section>
@endsection
