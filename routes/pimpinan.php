<?php

use Illuminate\Support\Facades\Route;

Route::prefix('pimpinan')->name('pimpinan.')->middleware(['auth', 'role:pimpinan'])->group(function () {
    Route::get('/dashboard', fn() => view('pimpinan.dashboard'))->name('dashboard');
});
