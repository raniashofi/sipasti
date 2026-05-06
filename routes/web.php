<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Redirect ke dashboard sesuai role — dipertahankan agar RedirectIfAuthenticated
// tetap punya target route 'dashboard' saat session masih aktif.
Route::get('/dashboard', function () {
    $user = Auth::user();
    return match ($user?->role) {
        'super_admin'    => redirect()->route('super_admin.dashboard'),
        'admin_helpdesk' => redirect()->route('admin_helpdesk.dashboard'),
        'tim_teknis'     => redirect()->route('tim_teknis.dashboard'),
        'opd'            => redirect()->route('opd.dashboard'),
        'pimpinan'       => redirect()->route('pimpinan.dashboard'),
        default          => redirect('/'),
    };
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Notifikasi (semua role kecuali pimpinan) ──
    Route::prefix('notif')->name('notif.')->group(function () {
        Route::get('/',            [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all',   [NotificationController::class, 'markAllRead'])->name('readAll');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/opd.php';
require __DIR__.'/super_admin.php';
require __DIR__.'/admin_helpdesk.php';
require __DIR__.'/tim_teknis.php';
require __DIR__.'/pimpinan.php';
