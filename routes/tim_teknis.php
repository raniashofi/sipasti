<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tim-teknis')->name('tim_teknis.')->middleware(['auth', 'role:tim_teknis'])->group(function () {
    Route::get('/dashboard', fn() => view('tim_teknis.dashboard'))->name('dashboard');
});
