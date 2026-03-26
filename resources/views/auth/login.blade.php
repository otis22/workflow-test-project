@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="card" style="max-width:400px;margin:0 auto;">
    <h1>Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <p style="margin-top:1rem">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
</div>
@endsection
