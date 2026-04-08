<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\KnowledgeBaseController;
use App\Http\Controllers\SuperAdmin\KonfigurasiSistemController;
use App\Http\Controllers\SuperAdmin\ManajemenPenggunaController;
use Illuminate\Support\Facades\Route;

Route::prefix('super-admin')->name('super_admin.')->middleware(['auth', 'role:super_admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Manajemen Pengguna
    Route::prefix('pengguna')->name('pengguna.')->group(function () {
        Route::get('/opd',         [ManajemenPenggunaController::class, 'indexOpd'])->name('opd');
        Route::post('/opd',        [ManajemenPenggunaController::class, 'storeOpd'])->name('opd.store');
        Route::put('/opd/{id}',    [ManajemenPenggunaController::class, 'updateOpd'])->name('opd.update');
        Route::delete('/opd/{id}', [ManajemenPenggunaController::class, 'destroyOpd'])->name('opd.destroy');
        Route::get('/internal',              [ManajemenPenggunaController::class, 'indexInternal'])->name('internal');
        // Tim Teknis
        Route::post('/internal/tim-teknis',        [ManajemenPenggunaController::class, 'storeTimTeknis'])->name('internal.tt.store');
        Route::put('/internal/tim-teknis/{id}',    [ManajemenPenggunaController::class, 'updateTimTeknis'])->name('internal.tt.update');
        Route::delete('/internal/tim-teknis/{id}', [ManajemenPenggunaController::class, 'destroyTimTeknis'])->name('internal.tt.destroy');
        // Admin Helpdesk
        Route::post('/internal/admin-helpdesk',        [ManajemenPenggunaController::class, 'storeAdminHelpdesk'])->name('internal.ah.store');
        Route::put('/internal/admin-helpdesk/{id}',    [ManajemenPenggunaController::class, 'updateAdminHelpdesk'])->name('internal.ah.update');
        Route::delete('/internal/admin-helpdesk/{id}', [ManajemenPenggunaController::class, 'destroyAdminHelpdesk'])->name('internal.ah.destroy');
        // Pimpinan
        Route::post('/internal/pimpinan',        [ManajemenPenggunaController::class, 'storePimpinan'])->name('internal.pimpinan.store');
        Route::put('/internal/pimpinan/{id}',    [ManajemenPenggunaController::class, 'updatePimpinan'])->name('internal.pimpinan.update');
        Route::delete('/internal/pimpinan/{id}', [ManajemenPenggunaController::class, 'destroyPimpinan'])->name('internal.pimpinan.destroy');
    });

    // Konfigurasi Sistem (diagnosis tree & kategori)
    Route::prefix('konfigurasi')->name('konfigurasi.')->group(function () {
        Route::get('/konfigurasi-sistem', [KonfigurasiSistemController::class, 'index'])->name('konfigurasiSistem');
        // Kategori
        Route::post('/kategori',        [KonfigurasiSistemController::class, 'storeKategori'])->name('kategori.store');
        Route::put('/kategori/{id}',    [KonfigurasiSistemController::class, 'updateKategori'])->name('kategori.update');
        Route::delete('/kategori/{id}', [KonfigurasiSistemController::class, 'destroyKategori'])->name('kategori.destroy');
        // Node Diagnosis
        Route::post('/node',        [KonfigurasiSistemController::class, 'storeNode'])->name('node.store');
        Route::put('/node/{id}',    [KonfigurasiSistemController::class, 'updateNode'])->name('node.update');
        Route::delete('/node/{id}', [KonfigurasiSistemController::class, 'destroyNode'])->name('node.destroy');
    });

    // Pustaka Pengetahuan (Knowledge Base)
    Route::prefix('pustaka')->name('pustaka.')->group(function () {
        Route::get('/opd',                [KnowledgeBaseController::class, 'indexOpd'])->name('opd');
        Route::get('/internal',           [KnowledgeBaseController::class, 'indexInternal'])->name('internal');
        Route::get('/tambah',             [KnowledgeBaseController::class, 'create'])->name('create');
        Route::post('/',                  [KnowledgeBaseController::class, 'store'])->name('store');
        Route::get('/{id}/edit',          [KnowledgeBaseController::class, 'edit'])->name('edit');
        Route::put('/{id}',               [KnowledgeBaseController::class, 'update'])->name('update');
        Route::delete('/{id}',            [KnowledgeBaseController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/preview',       [KnowledgeBaseController::class, 'preview'])->name('preview');
        Route::post('/upload-image',      [KnowledgeBaseController::class, 'uploadImage'])->name('upload-image');
    });

    // Keamanan & Audit
    Route::get('/audit',            [ActivityLogController::class, 'showAudit'])->name('audit');
    Route::get('/audit/export-csv', [ActivityLogController::class, 'exportCsv'])->name('audit.export');
});
