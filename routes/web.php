<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response('TaskFlow bootstrap ready', 200)
    ->header('Content-Type', 'text/plain; charset=UTF-8'));
