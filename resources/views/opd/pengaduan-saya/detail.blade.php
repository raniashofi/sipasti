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

    $statusSebelumTutup = $currentStatus === 'tiket_ditutup'
        ? $allStatuses->whereIn('status_tiket', ['selesai', 'rusak_berat'])->last()
        : null;
    $displayStatus = match($currentStatus) {
        'dibuka_kembali' => 'selesai',
        'tiket_ditutup'  => ($statusSebelumTutup?->status_tiket ?? 'selesai'),
        default          => $currentStatus,
    };

    $sudahDikonfirmasi = $tiket->penilaian !== null;

    /* ── Deadline konfirmasi ── */
    $batasKonfirmasiHari = 7;
    $statusMenunggu = $allStatuses
        ->whereIn('status_tiket', ['selesai', 'rusak_berat'])
        ->sortByDesc('created_at')
        ->first();
    $deadlineKonfirmasi = $statusMenunggu
        ? \Carbon\Carbon::parse($statusMenunggu->created_at)->addDays($batasKonfirmasiHari)
        : null;
    $sisaHari = $deadlineKonfirmasi ? (int) now()->diffInDays($deadlineKonfirmasi, false) : null;
    $tampilkanPeringatan = in_array($currentStatus, ['selesai', 'rusak_berat'])
        && !$sudahDikonfirmasi
        && $deadlineKonfirmasi !== null;

    /* ── Diagnosa ── */
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

    $step2Done   = in_array($displayStatus, ['rusak_berat','selesai']);
    $step2Active = in_array($displayStatus, ['panduan_remote','perbaikan_teknis']);

    $step3Active = in_array($displayStatus, ['rusak_berat','selesai']);
    $step3Error  = $displayStatus === 'rusak_berat';

    /* ── Catatan singkat (perlu_revisi / rusak_berat) ── */
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

    /* ── Chat visibility berdasarkan riwayat status ──
       Admin chat  : tampil jika tiket PERNAH di panduan_remote
       Teknis chat : tampil jika tiket PERNAH di perbaikan_teknis / rusak_berat
    ── */
    $hadPanduanRemote   = in_array('panduan_remote',   $statusList);
    $hadPerbaikanTeknis = in_array('perbaikan_teknis', $statusList)
                       || in_array('rusak_berat',      $statusList);

    $showAdminChat    = $hadPanduanRemote;
    $adminChatActive  = $displayStatus === 'panduan_remote';
    $showTeknisChat   = $hadPerbaikanTeknis;
    $teknisChatActive = $displayStatus === 'perbaikan_teknis' || $currentStatus === 'dibuka_kembali';

    /* ── Re-open limit ── */
    $sudahPernahDibukakembali = $allStatuses->where('status_tiket', 'dibuka_kembali')->isNotEmpty();
    $bisaBukaKembali          = $currentStatus === 'selesai' && !$sudahPernahDibukakembali;

    /* ── Cek apakah tiket ditutup otomatis atau manual OPD ── */
    $ditutupOtomatis = false;
    if ($currentStatus === 'tiket_ditutup') {
        $statusTutup = $allStatuses->where('status_tiket', 'tiket_ditutup')->last();
        $ditutupOtomatis = $statusTutup && str_contains($statusTutup->catatan ?? '', 'otomatis');
    }
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

    {{-- ── Banner Peringatan Deadline Konfirmasi ── --}}
    @if($tampilkanPeringatan)
    @php
        $warnaPeringatan = $sisaHari <= 1
            ? ['bg' => '#FEF2F2', 'border' => '#FECACA', 'text' => '#991B1B', 'icon' => '#DC2626', 'label' => '#DC2626']
            : ($sisaHari <= 3
                ? ['bg' => '#FFF7ED', 'border' => '#FED7AA', 'text' => '#92400E', 'icon' => '#EA580C', 'label' => '#EA580C']
                : ['bg' => '#FEFCE8', 'border' => '#FDE68A', 'text' => '#713F12', 'icon' => '#CA8A04', 'label' => '#CA8A04']);
        $pesanSisa = $sisaHari <= 0
            ? 'Batas konfirmasi hampir habis! Tiket akan ditutup otomatis hari ini.'
            : ($sisaHari === 1
                ? 'Sisa <strong>1 hari</strong> lagi untuk mengkonfirmasi atau membuka kembali tiket ini.'
                : 'Sisa <strong>' . $sisaHari . ' hari</strong> lagi untuk mengkonfirmasi atau membuka kembali tiket ini.');
    @endphp
    <div class="rounded-2xl border px-5 py-4 mb-6 flex items-start gap-4"
         style="background:{{ $warnaPeringatan['bg'] }}; border-color:{{ $warnaPeringatan['border'] }};">
        <div class="shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="{{ $warnaPeringatan['icon'] }}" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-bold mb-0.5" style="color:{{ $warnaPeringatan['label'] }};">
                Konfirmasi Tiket Diperlukan
            </p>
            <p class="text-sm leading-relaxed" style="color:{{ $warnaPeringatan['text'] }};">{!! $pesanSisa !!}
                Jika tidak dikonfirmasi dalam
                <strong>{{ $batasKonfirmasiHari }} hari</strong> sejak tiket diselesaikan,
                tiket akan <strong>ditutup otomatis</strong> oleh sistem dan Anda hanya dapat memberikan penilaian.
            </p>
            @if($deadlineKonfirmasi)
            <p class="text-xs font-semibold mt-1.5" style="color:{{ $warnaPeringatan['label'] }};">
                Batas waktu: {{ $deadlineKonfirmasi->locale('id')->isoFormat('D MMM YYYY, HH:mm') }} WIB
            </p>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Banner Tiket Ditutup ── --}}
    @if($currentStatus === 'tiket_ditutup' && $ditutupOtomatis)
    <div class="rounded-2xl border border-gray-300 px-5 py-4 mb-6 flex items-start gap-4" style="background:#F9FAFB;">
        <div class="shrink-0 mt-0.5">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-bold text-gray-700 mb-0.5">Tiket Ditutup Otomatis</p>
            <p class="text-sm text-gray-500 leading-relaxed">
                Tiket ini telah ditutup secara otomatis oleh sistem karena tidak dikonfirmasi dalam
                <strong>{{ $batasKonfirmasiHari }} hari</strong>.
                Anda masih dapat memberikan <strong>penilaian layanan</strong> di bawah ini.
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

        {{-- ══════════════════════════════════════════════════
             KIRI: Progres + Info + Catatan Penyelesaian
        ══════════════════════════════════════════════════ --}}
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

            {{-- ── Info: Kategori, Diagnosa, Catatan singkat ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5 space-y-3">

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

            {{-- ── Artikel Solusi Terdeteksi ── --}}
            @if($tiket->kb)
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-blue-100" style="background:#EEF3F9;">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                             style="background:#01458E;">
                            <svg class="w-5 h-5" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-[#01458E] uppercase tracking-widest">Solusi yang Disarankan</p>
                            <p class="text-xs text-gray-500">Berdasarkan diagnosis mandiri Anda saat mengajukan tiket</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm font-bold text-gray-800 leading-snug mb-1.5">{{ $tiket->kb->nama_artikel_sop }}</p>
                    @if($tiket->kb->deskripsi_singkat)
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $tiket->kb->deskripsi_singkat }}</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Rusak Berat: Catatan Rekomendasi Teknis ── --}}
            @if($displayStatus === 'rusak_berat')
            <div class="rounded-2xl border border-red-200 overflow-hidden" style="background:#FEF2F2;">
                <div class="flex items-center gap-2.5 px-6 py-3.5 border-b border-red-200" style="background:#FEE2E2;">
                    <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                    <p class="text-xs font-bold text-red-700 uppercase tracking-wide">Catatan Rekomendasi Teknis</p>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-red-800 leading-relaxed">
                        Berdasarkan analisis teknis, perangkat dinyatakan mengalami kerusakan fatal dan tidak dapat diperbaiki (<em>unrepairable</em>).
                        Berikut adalah catatan resmi dari Tim Teknis sebagai dasar tindak lanjut.
                    </p>
                    @if($latest?->catatan)
                    <div>
                        <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-1">Analisis Kerusakan</p>
                        <p class="text-sm text-red-900 leading-relaxed bg-white/60 rounded-xl px-4 py-3 border border-red-100">{{ $latest->catatan }}</p>
                    </div>
                    @endif
                    @if($latest?->spesifikasi_perangkat_rusak)
                    <div>
                        <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-1">Spesifikasi Perangkat Rusak</p>
                        <p class="text-sm text-red-900 leading-relaxed bg-white/60 rounded-xl px-4 py-3 border border-red-100">{{ $latest->spesifikasi_perangkat_rusak }}</p>
                    </div>
                    @endif
                    @if($latest?->rekomendasi)
                    <div>
                        <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-1">Rekomendasi Tindak Lanjut</p>
                        <p class="text-sm text-red-900 leading-relaxed bg-white/60 rounded-xl px-4 py-3 border border-red-100">{{ $latest->rekomendasi }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ── Selesai / Dibuka Kembali: Catatan Penyelesaian ── --}}
            @if(in_array($currentStatus, ['selesai', 'dibuka_kembali']))
            @php
                $statusSelesai  = $allStatuses->where('status_tiket', 'selesai')->last();
                $rawSelesai     = $statusSelesai?->catatan ?? '';
                $catatanSelesai = str_contains($rawSelesai, ' — ')
                    ? trim(explode(' — ', $rawSelesai, 2)[1])
                    : preg_replace('/^\[Analisis\]\s*/', '', $rawSelesai);
                $waktuSelesai   = $statusSelesai?->created_at
                    ? \Carbon\Carbon::parse($statusSelesai->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
                    : null;
                $labelCatatanSelesai = $hadPerbaikanTeknis ? 'Catatan Teknis' : 'Catatan Penyelesaian Admin';
            @endphp
            @if($catatanSelesai || $waktuSelesai)
            <div class="rounded-2xl border border-green-200 px-6 py-5" style="background:#F0FDF4;">
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide mb-2">{{ $labelCatatanSelesai }}</p>
                @if($waktuSelesai)
                <p class="text-xs font-semibold text-green-700 mb-1">Waktu Selesai: {{ $waktuSelesai }}</p>
                @endif
                @if($catatanSelesai)
                <p class="text-sm text-green-800 leading-relaxed">{{ $catatanSelesai }}</p>
                @endif
            </div>
            @endif
            @endif

            {{-- ── Tiket Ditutup: Catatan Penyelesaian ── --}}
            @if($currentStatus === 'tiket_ditutup')
            @php
                $tampilCatatanTutup = $statusSebelumTutup?->status_tiket === 'selesai';
                if ($tampilCatatanTutup) {
                    $rawTutup       = $statusSebelumTutup->catatan ?? '';
                    $catatanTutup   = str_contains($rawTutup, ' — ')
                        ? trim(explode(' — ', $rawTutup, 2)[1])
                        : preg_replace('/^\[Analisis\]\s*/', '', $rawTutup);
                    $waktuTutup     = $statusSebelumTutup->created_at
                        ? \Carbon\Carbon::parse($statusSebelumTutup->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
                        : null;
                    $labelCatatanTutup = $hadPerbaikanTeknis ? 'Catatan Teknis' : 'Catatan Penyelesaian Admin';
                }
            @endphp
            @if(!empty($tampilCatatanTutup) && $tampilCatatanTutup)
            <div class="rounded-2xl border border-green-200 px-6 py-5" style="background:#F0FDF4;">
                <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide mb-2">{{ $labelCatatanTutup }}</p>
                @if(!empty($waktuTutup))
                <p class="text-xs font-semibold text-green-700 mb-1">Waktu Selesai: {{ $waktuTutup }}</p>
                @endif
                @if(!empty($catatanTutup))
                <p class="text-sm text-green-800 leading-relaxed">{{ $catatanTutup }}</p>
                @endif
            </div>
            @endif
            @endif

            {{-- ── Dibuka Kembali: Alasan OPD ── --}}
            @php $statusBukaKembali = $allStatuses->where('status_tiket', 'dibuka_kembali')->last(); @endphp
            @if($currentStatus === 'dibuka_kembali' && $statusBukaKembali)
            <div class="rounded-2xl border border-red-200 px-6 py-5" style="background:#FEF2F2;">
                <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-2">Alasan Anda Membuka Kembali</p>
                @php
                    $waktuBuka = $statusBukaKembali->created_at
                        ? \Carbon\Carbon::parse($statusBukaKembali->created_at)->locale('id')->isoFormat('D MMM YYYY, HH:mm') . ' WIB'
                        : null;
                @endphp
                @if($waktuBuka)
                <p class="text-xs font-semibold text-red-700 mb-1">Waktu Dibuka: {{ $waktuBuka }}</p>
                @endif
                <p class="text-sm text-red-800 leading-relaxed">{{ $statusBukaKembali->catatan ?? '—' }}</p>
                @if($statusBukaKembali->file_bukti)
                <div class="mt-3">
                    <p class="text-[10px] font-semibold text-red-600 uppercase tracking-wide mb-2">Bukti Foto</p>
                    <a href="{{ Storage::url($statusBukaKembali->file_bukti) }}" target="_blank">
                        <img src="{{ Storage::url($statusBukaKembali->file_bukti) }}"
                             alt="Bukti Buka Kembali"
                             class="w-full max-h-48 object-cover rounded-xl border border-red-200">
                    </a>
                </div>
                @endif
            </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════════════════
             KANAN: Formulir Pengaduan + Riwayat Chat + Aksi
        ══════════════════════════════════════════════════ --}}
        <div class="lg:col-span-3 space-y-4">

            {{-- ── Formulir Pengaduan (read-only) ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                <div class="px-7 py-5 border-b border-gray-100" style="background:#ffffff;">
                    <p class="text-xs font-bold" style="color:#01458E;">#{{ $tiket->id }}</p>
                    <h2 class="text-base font-bold text-gray-900 mt-0.5">Formulir Pengaduan</h2>
                </div>

                <div class="px-7 py-6 space-y-4">

                    <div>
                        <label class="field-label">Subjek Masalah</label>
                        <div class="field-box">{{ $tiket->subjek_masalah ?? '—' }}</div>
                    </div>

                    <div>
                        <label class="field-label">Kronologi & Detail Masalah</label>
                        <div class="field-box field-box-area">{{ $tiket->detail_masalah ?? '—' }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Spesifikasi Perangkat</label>
                            <div class="field-box">{{ $tiket->spesifikasi_perangkat ?? '—' }}</div>
                        </div>
                        <div>
                            <label class="field-label">Lokasi Fisik Perangkat</label>
                            <div class="field-box">{{ $tiket->lokasi ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- Foto Bukti --}}
                    @php $fotos = is_array($tiket->foto_bukti) ? array_values(array_filter($tiket->foto_bukti)) : []; @endphp
                    <div>
                        <label class="field-label">Foto Bukti</label>
                        @if(count($fotos) > 0)
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($fotos as $idx => $foto)
                                <div class="relative aspect-square">
                                    <img src="{{ Storage::url($foto) }}"
                                         alt="Foto Bukti {{ $idx + 1 }}"
                                         class="w-full h-full object-cover rounded-xl border border-gray-200 shadow-sm">
                                    <span class="absolute bottom-1 left-1.5 text-[9px] bg-black/50 text-white px-1.5 py-0.5 rounded-md font-bold">{{ $idx + 1 }}</span>
                                </div>
                                @endforeach
                            </div>
                            @if($currentStatus === 'perlu_revisi')
                            <a href="{{ route('opd.tiket.edit', $tiket->id) }}"
                               class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200
                                      rounded-lg text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                </svg>
                                Ganti Foto
                            </a>
                            @endif
                        @else
                            <div class="flex items-center justify-center h-28 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50">
                                <p class="text-sm text-gray-400">Tidak ada foto yang dilampirkan</p>
                            </div>
                        @endif
                    </div>

                </div>

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



            {{-- ── Riwayat Chat Admin Helpdesk ── --}}
            {{-- Tampil hanya jika tiket pernah melalui panduan_remote --}}
            @if($showAdminChat)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         style="background:#EEF3F9;">
                        <svg class="w-4 h-4" fill="none" stroke="#01458E" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800">Riwayat Chat — Admin Helpdesk</p>
                        <p class="text-[11px] text-gray-400">{{ $adminChatActive ? 'Sesi panduan remote aktif' : 'Komunikasi panduan remote' }}</p>
                    </div>
                    @if($adminChatActive)
                    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full text-white" style="background:#059669;">Aktif</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    @if($adminChatActive)
                        Admin Helpdesk siap memandu perbaikan jarak jauh. Silakan buka fitur chat untuk berdiskusi dan menyelesaikan kendala Anda lebih cepat.
                    @else
                        Tinjau kembali instruksi atau komunikasi sebelumnya bersama Admin Helpdesk dalam sesi panduan remote.
                    @endif
                </p>
                <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=admin"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                          transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                    {{ $adminChatActive ? 'Chat Admin' : 'Lihat Riwayat Chat' }}
                </a>
            </div>
            @endif

            {{-- ── Riwayat Chat Tim Teknis ── --}}
            {{-- Tampil hanya jika tiket pernah melalui perbaikan_teknis / rusak_berat --}}
            @if($showTeknisChat)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                         style="background:#EEF3F9;">
                        <svg class="w-4 h-4" fill="none" stroke="#01458E" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-800">Riwayat Chat — Tim Teknis</p>
                        <p class="text-[11px] text-gray-400">{{ $teknisChatActive ? 'Sesi perbaikan teknis aktif' : 'Komunikasi perbaikan teknis' }}</p>
                    </div>
                    @if($teknisChatActive)
                    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full text-white" style="background:#059669;">Aktif</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 leading-relaxed mb-4">
                    @if($teknisChatActive)
                        @if($currentStatus === 'dibuka_kembali')
                            Tiket Anda telah dibuka kembali dan Tim Teknis sedang menangani kendala. Silakan buka chat untuk berkomunikasi langsung dengan teknisi.
                        @else
                            Tiket Anda telah diteruskan ke Tim Teknis. Saat ini petugas sedang melakukan analisis atau mempersiapkan kunjungan ke lokasi Anda.
                        @endif
                    @else
                        Tinjau kembali instruksi atau komunikasi sebelumnya bersama Tim Teknis dalam sesi perbaikan.
                    @endif
                </p>
                <a href="{{ route('opd.tiket.chat', $tiket->id) }}?type=teknis"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                          transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                    {{ $teknisChatActive ? 'Chat Tim Teknis' : 'Lihat Riwayat Chat' }}
                </a>
            </div>
            @endif

            {{-- ── Aksi: Rusak Berat — Penilaian ── --}}
            @if($displayStatus === 'rusak_berat')
                @if(!$sudahDikonfirmasi)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5"
                     x-data="{ rating: 0, ratingHover: 0 }">
                    <p class="text-xs font-semibold text-gray-600 text-center mb-3">Berikan penilaian layanan Tim Teknis</p>
                    <form action="{{ route('opd.tiket.konfirm', $tiket->id) }}" method="POST">
                        @csrf
                        <div class="flex justify-center gap-2 mb-4">
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
                        <button type="submit"
                                :disabled="rating === 0"
                                :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 active:scale-95'"
                                class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2"
                                style="background:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                            KIRIM PENILAIAN
                        </button>
                    </form>
                </div>
                @else
                <div class="rounded-2xl border border-green-200 px-6 py-4 flex items-center gap-3" style="background:#F0FDF4;">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-800 font-semibold">Penilaian Anda telah dikirim. Terima kasih!</p>
                </div>
                @endif
            @endif

            {{-- ── Aksi: Tiket Ditutup Otomatis — Penilaian ── --}}
            @if($currentStatus === 'tiket_ditutup')
                @if(!$sudahDikonfirmasi)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5"
                     x-data="{ rating: 0, ratingHover: 0 }">
                    <div class="text-center mb-4">
                        <p class="text-sm font-bold text-gray-800 mb-1">Berikan Penilaian Layanan</p>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            Tiket ini telah ditutup otomatis. Bantu kami meningkatkan layanan dengan memberikan penilaian.
                        </p>
                    </div>
                    <form action="{{ route('opd.tiket.konfirm', $tiket->id) }}" method="POST">
                        @csrf
                        <div class="flex justify-center gap-2 mb-4">
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
                        <button type="submit"
                                :disabled="rating === 0"
                                :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 active:scale-95'"
                                class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2"
                                style="background:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                            KIRIM PENILAIAN
                        </button>
                    </form>
                </div>
                @else
                <div class="rounded-2xl border border-green-200 px-6 py-4 flex items-center gap-3" style="background:#F0FDF4;">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-800 font-semibold">Penilaian Anda telah dikirim. Terima kasih!</p>
                </div>
                @endif
            @endif

            {{-- ── Aksi: Selesai / Dibuka Kembali ── --}}
            @if(in_array($currentStatus, ['selesai', 'dibuka_kembali']))

                @if($currentStatus === 'dibuka_kembali')
                {{-- Tiket sedang ditangani kembali oleh Tim Teknis --}}
                <div class="rounded-2xl border border-amber-200 px-6 py-5 flex items-center gap-3" style="background:#FFFBEB;">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-amber-800 font-semibold">Tim Teknis sedang menangani kendala yang Anda laporkan.</p>
                </div>

                @elseif($sudahDikonfirmasi)
                <div class="rounded-2xl border border-green-200 px-6 py-5 flex items-center gap-3" style="background:#F0FDF4;">
                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-green-800 font-semibold">Tiket telah dikonfirmasi dan ditutup oleh Anda.</p>
                </div>

                @elseif($sudahPernahDibukakembali)
                {{-- Tiket selesai ke-2: langsung beri penilaian --}}
                <div class="bg-white rounded-2xl border border-blue-100 shadow-sm px-6 py-5"
                     x-data="{ rating: 0, ratingHover: 0 }">
                    <div class="text-center mb-5">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center mx-auto mb-3" style="background:#EEF3F9;">
                            <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 mb-1">Tiket Selesai Diperbaiki</p>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Tiket ini telah ditangani kembali oleh Tim Teknis dan dinyatakan selesai.
                            Jika perangkat masih mengalami gangguan, kami sarankan untuk mengajukan tiket pengaduan baru.
                        </p>
                    </div>
                    <form action="{{ route('opd.tiket.konfirm', $tiket->id) }}" method="POST">
                        @csrf
                        <p class="text-xs font-semibold text-gray-600 text-center mb-2">Berikan penilaian layanan Tim Teknis</p>
                        <div class="flex justify-center gap-2 mb-4">
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
                        <button type="submit"
                                :disabled="rating === 0"
                                :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 active:scale-95'"
                                class="w-full py-2.5 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2"
                                style="background:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                            KIRIM PENILAIAN
                        </button>
                    </form>
                </div>

                @else
                {{-- Konfirmasi normal: Buka Kembali / Tutup Tiket --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-5"
                     x-data="{ showTutup: false, showBuka: false, rating: 0, ratingHover: 0 }">
                    <p class="text-sm font-semibold text-gray-800 text-center mb-4">
                        Apakah layanan IT / perangkat Anda sudah berfungsi normal kembali?
                    </p>
                    <div class="flex gap-3">
                        @if($bisaBukaKembali)
                        <button type="button" @click="showBuka = true"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95"
                                style="background:#DC2626;">
                            BELUM, BUKA KEMBALI
                        </button>
                        @endif
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

                            <form action="{{ route('opd.tiket.konfirm', $tiket->id) }}" method="POST" class="px-6 py-5 space-y-5">
                                @csrf
                                <p class="text-sm text-gray-600 text-center leading-relaxed">
                                    Terima kasih! Tiket akan ditutup. Seberapa puas Anda dengan
                                    kecepatan dan hasil perbaikan dari tim kami?
                                </p>
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

                            <form action="{{ route('opd.tiket.bukaKembali', $tiket->id) }}" method="POST"
                                  enctype="multipart/form-data" class="px-6 py-5 space-y-4">
                                @csrf
                                <p class="text-sm text-gray-500 leading-relaxed">
                                    Mohon maaf jika masalah Anda belum sepenuhnya teratasi.
                                    Jelaskan kendala apa yang masih terjadi agar tim teknis dapat
                                    melakukan pengecekan ulang.
                                </p>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                        Kendala yang masih terjadi <span class="text-red-400">*</span>
                                    </label>
                                    <textarea name="alasan" rows="4" required
                                              placeholder="Ceritakan permasalahan yang masih terjadi..."
                                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:border-[#DC2626] placeholder-gray-300 transition-colors"
                                              style="--tw-ring-color:#DC2626;"></textarea>
                                </div>
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

            @endif

        </div>

    </div>
</main>

<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

</body>
</html>
