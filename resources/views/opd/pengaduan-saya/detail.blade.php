<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Tiket — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
        .field-box {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 9px 13px;
            font-size: 13px;
            color: #374151;
            background: #F9FAFB;
            min-height: 38px;
        }
        .field-box-area { min-height: 72px; line-height: 1.6; }
        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6B7280;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

@php
    $allStatuses   = $tiket->statusTiket; // already ordered asc by controller
    $latest        = $tiket->latestStatus;
    $currentStatus = $latest?->status_tiket ?? 'verifikasi_admin';
    $statusList    = $allStatuses->pluck('status_tiket')->toArray();

    // Untuk keperluan display, dibuka_kembali diperlakukan seperti selesai
    $displayStatus = $currentStatus === 'dibuka_kembali' ? 'selesai' : $currentStatus;

    /* ── Diagnosa: dari judul KB yang dipakai saat diagnosis mandiri ── */
    $diagnosa = $tiket->kb?->nama_artikel_sop;

    /* ── Progress bar labels ── */
    $step1Label = $displayStatus === 'perlu_revisi' ? 'Perlu Revisi' : 'Verifikasi Admin';

    $step2Label = match(true) {
        in_array('panduan_remote',   $statusList) => 'Panduan Remote',
        in_array('perbaikan_teknis', $statusList) => 'Perbaikan Teknis',
        $displayStatus === 'panduan_remote'        => 'Panduan Remote',
        $displayStatus === 'perbaikan_teknis'      => 'Perbaikan Teknis',
        default                                    => 'Tindak Lanjut',
    };

    $step3Label = (in_array('rusak_berat', $statusList) || $displayStatus === 'rusak_berat')
        ? 'Gagal/Rusak Berat'
        : 'Selesai';

    /* ── Step states ── */
    $step1Done   = in_array($displayStatus, ['panduan_remote','perbaikan_teknis','rusak_berat','selesai']);
    $step1Error  = $displayStatus === 'perlu_revisi';
    $step1Active = in_array($displayStatus, ['verifikasi_admin','perlu_revisi']);

    $step2Done   = in_array($displayStatus, ['rusak_berat','selesai']);
    $step2Active = in_array($displayStatus, ['panduan_remote','perbaikan_teknis']);

    $step3Active = in_array($displayStatus, ['rusak_berat','selesai']);
    $step3Error  = $displayStatus === 'rusak_berat';

    /* ── Catatan untuk ditampilkan di panel kiri ── */
    $catatanDisplay = null;
    if (in_array($displayStatus, ['perlu_revisi','rusak_berat'])) {
        $raw = $latest?->catatan;
        $catatanDisplay = preg_replace('/^\[Analisis\]\s*/', '', $raw ?? '');
        if (str_contains($catatanDisplay, ' — ')) {
            $parts = explode(' — ', $catatanDisplay, 2);
            $catatanDisplay = trim($parts[0]);
        }
        $catatanDisplay = trim($catatanDisplay) ?: null;
    }

    /* ── Chat visibility ── */
    $showAdminChat    = in_array($displayStatus, ['panduan_remote','perbaikan_teknis','rusak_berat','selesai']);
    $adminChatActive  = $displayStatus === 'panduan_remote';
    $showTeknisChat   = in_array($displayStatus, ['perbaikan_teknis','rusak_berat','selesai']);
    $teknisChatActive = $displayStatus === 'perbaikan_teknis';
@endphp

<main class="flex-1 max-w-screen-lg w-full mx-auto px-5 md:px-8 py-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-7">
        <a href="{{ route('opd.tiket.index') }}" class="hover:text-[#01458E] transition-colors">Pengaduan Saya</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium">Detail Tiket #{{ $tiket->id }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

        {{-- ════════════════════════════════════════════════
             KIRI: Progres Pengaduan
        ════════════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- ── Progress Bar ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-5">Progres Pengaduan</p>

                <div class="flex items-center">

                    {{-- Step 1 --}}
                    <div class="flex flex-col items-center gap-1.5 shrink-0">
                        @if($step1Done)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                                 style="background:#01458E;">1</div>
                        @elseif($step1Error)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                                 style="background:#DC2626;">1</div>
                        @else
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold border-2"
                                 style="border-color:#01458E; color:#01458E; background:#EEF3F9;">1</div>
                        @endif
                        <span class="text-[10px] font-semibold text-center leading-tight"
                              style="color:{{ $step1Error ? '#DC2626' : '#01458E' }}; max-width:56px;">
                            {{ $step1Label }}
                        </span>
                    </div>

                    {{-- Connector 1→2 --}}
                    <div class="flex-1 h-0.5 mx-1 {{ $step1Done ? '' : 'border-t-2 border-dashed border-gray-200' }}"
                         style="{{ $step1Done ? 'background:#01458E;' : '' }}"></div>

                    {{-- Step 2 --}}
                    <div class="flex flex-col items-center gap-1.5 shrink-0">
                        @if($step2Done)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                                 style="background:#01458E;">2</div>
                        @elseif($step2Active)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold border-2"
                                 style="border-color:#01458E; color:#01458E; background:#EEF3F9;">2</div>
                        @else
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold border-2 border-gray-200 text-gray-400 bg-gray-50">2</div>
                        @endif
                        <span class="text-[10px] font-semibold text-center leading-tight"
                              style="color:{{ $step2Done || $step2Active ? '#01458E' : '#9CA3AF' }}; max-width:64px;">
                            {{ $step2Label }}
                        </span>
                    </div>

                    {{-- Connector 2→3 --}}
                    <div class="flex-1 h-0.5 mx-1 {{ $step2Done ? '' : 'border-t-2 border-dashed border-gray-200' }}"
                         style="{{ $step2Done ? 'background:#01458E;' : '' }}"></div>

                    {{-- Step 3 --}}
                    <div class="flex flex-col items-center gap-1.5 shrink-0">
                        @if($step3Active && !$step3Error)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                                 style="background:#01458E;">3</div>
                        @elseif($step3Error)
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                                 style="background:#DC2626;">3</div>
                        @else
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold border-2 border-gray-200 text-gray-400 bg-gray-50">3</div>
                        @endif
                        <span class="text-[10px] font-semibold text-center leading-tight"
                              style="color:{{ $step3Error ? '#DC2626' : ($step3Active ? '#01458E' : '#9CA3AF') }}; max-width:64px;">
                            {{ $step3Label }}
                        </span>
                    </div>

                </div>
            </div>

            {{-- ── Info: Kategori, Diagnosa, Catatan ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 space-y-3">

                {{-- Kategori --}}
                @if($tiket->kategori)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                         style="background:#EEF3F9;">
                        <svg class="w-4 h-4" fill="none" stroke="#01458E" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Kategori</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $tiket->kategori?->nama_kategori ?? '—' }}</p>
                    </div>
                </div>
                @endif

                {{-- Diagnosa (dari judul solusi KB) --}}
                @if($diagnosa)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                         style="background:#EEF3F9;">
                        <svg class="w-4 h-4" fill="none" stroke="#01458E" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21a48.25 48.25 0 01-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Diagnosa</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $diagnosa }}</p>
                    </div>
                </div>
                @endif

                {{-- Catatan Admin (perlu_revisi) --}}
                @if($displayStatus === 'perlu_revisi' && $catatanDisplay)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                         style="background:#FEF3C7;">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-amber-500 uppercase tracking-wide">Catatan Admin</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $catatanDisplay }}</p>
                    </div>
                </div>
                @endif

                {{-- Catatan Teknis (rusak_berat) --}}
                @if($displayStatus === 'rusak_berat' && $catatanDisplay)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                         style="background:#FEE2E2;">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold text-red-500 uppercase tracking-wide">Catatan Teknis</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ $catatanDisplay }}</p>
                    </div>
                </div>
                @endif

            </div>

            {{-- ── Card Chat Admin Helpdesk ── --}}
            @if($showAdminChat)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex flex-col items-center text-center gap-3">
                @if($adminChatActive)
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Admin Helpdesk siap memandu perbaikan jarak jauh. Silakan buka fitur chat untuk berdiskusi dan menyelesaikan kendala Anda lebih cepat tanpa menunggu kunjungan teknisi.
                    </p>
                    <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=admin"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                              transition hover:-translate-y-0.5 hover:shadow-md"
                       style="background:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        Chat Admin
                    </a>
                @else
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Tinjau kembali instruksi perbaikan atau komunikasi sebelumnya dengan Admin Helpdesk
                    </p>
                    <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=admin"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                              transition hover:-translate-y-0.5 hover:shadow-md"
                       style="background:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        Lihat Chat
                    </a>
                @endif
            </div>
            @endif

            {{-- ── Card Chat Tim Teknis ── --}}
            @if($showTeknisChat)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 flex flex-col items-center text-center gap-3">
                @if($teknisChatActive)
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Tiket pengaduan Anda telah diteruskan (eskalasi) ke Tim Teknis terkait karena membutuhkan penanganan khusus. Saat ini petugas sedang melakukan analisis mendalam atau mempersiapkan kunjungan ke lokasi Anda. Mohon tunggu pembaruan status selanjutnya.
                    </p>
                    <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=teknis"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                              transition hover:-translate-y-0.5 hover:shadow-md"
                       style="background:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        Chat Tim Teknis
                    </a>
                @else
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Tinjau kembali instruksi perbaikan atau komunikasi sebelumnya dengan Teknisi
                    </p>
                    <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=teknis"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                              transition hover:-translate-y-0.5 hover:shadow-md"
                       style="background:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        Lihat Chat
                    </a>
                @endif
            </div>
            @endif

            {{-- ── Rusak Berat: notice + unduh PDF ── --}}
            @if($displayStatus === 'rusak_berat')
            <div class="rounded-2xl border border-red-200 px-6 py-5" style="background:#FEF2F2;">
                <p class="text-sm text-red-800 leading-relaxed mb-4">
                    Berdasarkan analisis teknis, perangkat mengalami kerusakan fatal dan tidak dapat diperbaiki (unrepairable).
                    Kami telah menerbitkan Surat Rekomendasi Teknis sebagai dasar administrasi pengadaan unit baru.
                    Silakan unduh dokumen tersebut melalui tombol di bawah.
                </p>
                @php $fileRek = $latest?->file_rekomendasi; @endphp
                <a href="{{ $fileRek ? Storage::url($fileRek) : '#' }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                          transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#DC2626;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Unduh PDF
                </a>
            </div>
            @endif

            {{-- ── Selesai: catatan teknis + konfirmasi ── --}}
            @if(in_array($currentStatus, ['selesai', 'dibuka_kembali']))
            @php
                // Selalu ambil record status 'selesai' terakhir untuk catatan teknis
                $statusSelesai = $allStatuses->where('status_tiket', 'selesai')->last();
                $rawSelesai    = $statusSelesai?->catatan ?? '';
                $catatanSelesai = str_contains($rawSelesai, ' — ')
                    ? trim(explode(' — ', $rawSelesai, 2)[1])
                    : preg_replace('/^\[Analisis\]\s*/', '', $rawSelesai);
                $waktuSelesai = $statusSelesai?->created_at
                    ? \Carbon\Carbon::parse($statusSelesai->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
                    : null;
            @endphp
            <div class="rounded-2xl border border-green-200 px-6 py-5" style="background:#F0FDF4;">
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide mb-2">Catatan Teknis</p>
                @if($waktuSelesai)
                <p class="text-xs font-semibold text-green-700 mb-1">Waktu Selesai: {{ $waktuSelesai }}</p>
                @endif
                @if($catatanSelesai)
                <p class="text-sm text-green-800 leading-relaxed">{{ $catatanSelesai }}</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5"
                 x-data="{ showTutup: false, showBuka: false, rating: 0, ratingHover: 0 }">
                <p class="text-sm font-semibold text-gray-800 text-center mb-4">
                    Apakah layanan IT / perangkat Anda sudah berfungsi normal kembali?
                </p>
                <div class="flex gap-3">
                    {{-- Buka Kembali --}}
                    <button type="button" @click="showBuka = true"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95"
                            style="background:#DC2626;">
                        BELUM, BUKA KEMBALI
                    </button>
                    {{-- Tutup Tiket --}}
                    <button type="button" @click="showTutup = true"
                            class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95"
                            style="background:#059669;">
                        YA, TUTUP TIKET
                    </button>
                </div>

                {{-- ══ MODAL: Tutup Tiket + Penilaian ══ --}}
                <div x-show="showTutup"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click.self="showTutup = false"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

                    <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 flex flex-col overflow-hidden"
                         @click.stop>

                        {{-- Header --}}
                        <div class="px-6 py-4 text-white shrink-0" style="background:#059669;border-radius:1.5rem 1.5rem 0 0;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm">Konfirmasi & Penilaian Layanan</p>
                                        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,.75);">Tiket #{{ $tiket->id }}</p>
                                    </div>
                                </div>
                                <button @click="showTutup = false" class="hover:text-white/60 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Body --}}
                        <form action="{{ route('opd.tiket.konfirm', $tiket->id) }}" method="POST" class="px-6 py-5 space-y-5">
                            @csrf

                            <p class="text-sm text-gray-600 text-center leading-relaxed">
                                Terima kasih! Tiket akan ditutup. Seberapa puas Anda dengan
                                kecepatan dan hasil perbaikan dari tim kami?
                            </p>

                            {{-- Bintang --}}
                            <div class="flex justify-center gap-2">
                                <template x-for="i in 5" :key="i">
                                    <button type="button"
                                            @click="rating = i"
                                            @mouseenter="ratingHover = i"
                                            @mouseleave="ratingHover = 0"
                                            class="text-4xl transition-transform hover:scale-110 focus:outline-none">
                                        <span :class="(ratingHover || rating) >= i ? 'text-yellow-400' : 'text-gray-200'">★</span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="penilaian" :value="rating">

                            {{-- Komentar --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Kolom Komentar <span class="font-normal text-gray-400">(Opsional)</span>
                                </label>
                                <textarea name="komentar_penutupan" rows="3"
                                          placeholder="Ketik ulasan Anda (Opsional)..."
                                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:border-[#059669] placeholder-gray-300 transition-colors"
                                          style="--tw-ring-color:#059669;"></textarea>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    :disabled="rating === 0"
                                    :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 active:scale-95'"
                                    class="w-full py-3 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2"
                                    style="background:#059669;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                </svg>
                                KIRIM ULASAN
                            </button>
                        </form>

                    </div>
                </div>

                {{-- ══ MODAL: Buka Kembali ══ --}}
                <div x-show="showBuka"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @click.self="showBuka = false"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4"
                     style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

                    <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 flex flex-col overflow-hidden"
                         @click.stop>

                        {{-- Header --}}
                        <div class="px-6 py-4 text-white shrink-0" style="background:#DC2626;border-radius:1.5rem 1.5rem 0 0;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm">Buka Kembali Tiket Pengaduan</p>
                                        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,.75);">Tiket #{{ $tiket->id }}</p>
                                    </div>
                                </div>
                                <button @click="showBuka = false" class="hover:text-white/60 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- Body --}}
                        <form action="{{ route('opd.tiket.bukaKembali', $tiket->id) }}" method="POST"
                              enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                            @csrf

                            <p class="text-sm text-gray-500 leading-relaxed">
                                Mohon maaf jika masalah Anda belum sepenuhnya teratasi.
                                Jelaskan kendala apa yang masih terjadi agar tim teknis dapat
                                melakukan pengecekan ulang.
                            </p>

                            {{-- Alasan --}}
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Kendala yang masih terjadi <span class="text-red-400">*</span>
                                </label>
                                <textarea name="alasan" rows="4" required
                                          placeholder="Ceritakan permasalahan yang masih terjadi..."
                                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:border-[#DC2626] placeholder-gray-300 transition-colors"
                                          style="--tw-ring-color:#DC2626;"></textarea>
                            </div>

                            {{-- Upload bukti --}}
                            <div x-data="{ fileName: '' }">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Upload Bukti <span class="font-normal text-gray-400">(Opsional)</span>
                                </label>
                                <div class="flex items-center gap-3">
                                    <label class="cursor-pointer flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                        </svg>
                                        Upload
                                        <input type="file" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf"
                                               class="sr-only"
                                               @change="fileName = $event.target.files[0]?.name ?? ''">
                                    </label>
                                    <span class="text-xs text-gray-400" x-text="fileName || 'Choose Images'"></span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-1">JPG, JPEG, PNG dan PDF. Max 10 MB</p>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="w-full py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95 flex items-center justify-center gap-2"
                                    style="background:#DC2626;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                                Buka Kembali Tiket
                            </button>
                        </form>

                    </div>
                </div>

            </div>
            @endif

        </div>

        {{-- ════════════════════════════════════════════════
             KANAN: Formulir Pengaduan (read-only)
        ════════════════════════════════════════════════ --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-7 py-5 border-b border-gray-100"
                 style="background:#ffffff;">
                <p class="text-xs font-bold" style="color:#01458E;">#{{ $tiket->id }}</p>
                <h2 class="text-base font-bold text-gray-900 mt-0.5">Formulir Pengaduan</h2>
            </div>

            {{-- Fields --}}
            <div class="px-7 py-6 space-y-4">

                <div>
                    <label class="field-label">Subjek Masalah</label>
                    <div class="field-box">{{ $tiket->subjek_masalah ?? '—' }}</div>
                </div>

                <div>
                    <label class="field-label">Kronologi & Detail Masalah</label>
                    <div class="field-box field-box-area">{{ $tiket->detail_masalah ?? '—' }}</div>
                </div>

                <div>
                    <label class="field-label">Spesifikasi Perangkat <span class="font-normal text-gray-400">(kosongkan jika masalah website/aplikasi)</span></label>
                    <div class="field-box">{{ $tiket->spesifikasi_perangkat ?? '—' }}</div>
                </div>

                <div>
                    <label class="field-label">Lokasi Fisik Perangkat <span class="font-normal text-gray-400">(kosongkan jika masalah website/aplikasi)</span></label>
                    <div class="field-box field-box-area">{{ $tiket->lokasi ?? '—' }}</div>
                </div>

                {{-- Foto Bukti --}}
                <div>
                    <label class="field-label">Unggah Foto Bukti</label>
                    @if($tiket->foto_bukti)
                        <div class="relative">
                            <img src="{{ Storage::url($tiket->foto_bukti) }}"
                                 alt="Foto Bukti"
                                 class="w-full max-h-56 object-cover rounded-xl border border-gray-200">
                            @if($currentStatus === 'perlu_revisi')
                            {{-- Overlay upload button untuk perlu_revisi --}}
                            <a href="{{ route('opd.tiket.edit', $tiket->id) }}"
                               class="absolute bottom-3 left-3 inline-flex items-center gap-1.5 px-3 py-1.5
                                      bg-white border border-gray-200 rounded-lg text-xs font-semibold text-gray-700
                                      shadow-sm hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                Upload
                            </a>
                            @endif
                        </div>
                        @if($currentStatus === 'perlu_revisi')
                        <p class="text-[11px] text-gray-400 mt-1.5">JPG, JPEG, dan PNG. Max 10 MB</p>
                        @endif
                    @else
                        <div class="flex items-center justify-center h-32 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                            <p class="text-sm text-gray-400">Tidak ada foto yang dilampirkan</p>
                        </div>
                    @endif
                </div>

            </div>

            {{-- Footer: EDIT TIKET button (hanya perlu_revisi) --}}
            @if($currentStatus === 'perlu_revisi')
            <div class="px-7 py-4 border-t border-gray-100 flex justify-end">
                <a href="{{ route('opd.tiket.edit', $tiket->id) }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-bold
                          transition hover:-translate-y-0.5 hover:shadow-lg active:scale-95"
                   style="background:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                    </svg>
                    EDIT TIKET
                </a>
            </div>
            @endif

        </div>

    </div>
</main>

<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

</body>
</html>
