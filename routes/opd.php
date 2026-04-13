<?php

use App\Http\Controllers\Opd\BantuanController;
use App\Http\Controllers\Opd\ChatController;
use App\Http\Controllers\Opd\DashboardController;
use App\Http\Controllers\Opd\DiagnosisMandiriController;
use App\Http\Controllers\Opd\PengaduanSayaController;
use Illuminate\Support\Facades\Route;

Route::prefix('opd')->name('opd.')->middleware(['auth', 'role:opd'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Buat Pengaduan — alur diagnosis mandiri
    Route::prefix('buat-pengaduan')->name('diagnosis.')->group(function () {
        Route::get('/',                         [DiagnosisMandiriController::class, 'index'])->name('index');
        Route::get('/{kategoriId}/mulai',       [DiagnosisMandiriController::class, 'mulai'])->name('mulai');
        Route::get('/node/{nodeId}',            [DiagnosisMandiriController::class, 'showNode'])->name('node');
        Route::get('/tiket',                    [DiagnosisMandiriController::class, 'showTiket'])->name('tiket');
        Route::post('/tiket',                   [DiagnosisMandiriController::class, 'storeTiket'])->name('tiket.store');
    });

    // Pengaduan Saya
    Route::prefix('pengaduan-saya')->name('tiket.')->group(function () {
        Route::get('/',              [PengaduanSayaController::class, 'index'])->name('index');
        Route::get('/{id}',          [PengaduanSayaController::class, 'show'])->name('show');
        Route::get('/{id}/chat',      [ChatController::class, 'show'])->name('chat');
        Route::post('/{id}/chat/send', [ChatController::class, 'send'])->name('chat.send');
        Route::post('/{id}/konfirm',       [PengaduanSayaController::class, 'konfirm'])->name('konfirm');
        Route::post('/{id}/buka-kembali',  [PengaduanSayaController::class, 'bukaKembali'])->name('bukaKembali');
        Route::get('/{id}/edit',           [PengaduanSayaController::class, 'edit'])->name('edit');
        Route::put('/{id}',                [PengaduanSayaController::class, 'update'])->name('update');
    });

    // Pusat Bantuan
    Route::get('/bantuan',                        [BantuanController::class, 'index'])->name('bantuan');
    Route::get('/bantuan/kategori/{id}',          [BantuanController::class, 'kategori'])->name('bantuan.kategori');
    Route::get('/bantuan/artikel/{id}',           [BantuanController::class, 'artikel'])->name('bantuan.artikel');
    Route::post('/bantuan/artikel/{id}/rating',   [BantuanController::class, 'rating'])->name('bantuan.rating');

    // Profil
    Route::get('/profil',     fn() => view('opd.dashboard'))->name('profile');
});
