<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin-helpdesk')->name('admin_helpdesk.')->middleware(['auth', 'role:admin_helpdesk'])->group(function () {
    Route::get('/dashboard', fn() => view('admin_helpdesk.dashboard'))->name('dashboard');
});
