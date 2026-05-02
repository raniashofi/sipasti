<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Pimpinan — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .fade-up {
            animation: fadeUp 0.55s ease-out forwards;
            opacity: 0; transform: translateY(14px);
        }
        .delay-1 { animation-delay: 60ms; }
        .delay-2 { animation-delay: 130ms; }
        .delay-3 { animation-delay: 200ms; }
        .delay-4 { animation-delay: 280ms; }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        /* Progress bar */
        .kpi-bar-track { background:#E5E7EB; border-radius:999px; height:8px; overflow:hidden; }
        .kpi-bar-fill  { height:100%; border-radius:999px; transition:width 1s ease; }

        /* Thin scrollbar */
        .thin-scroll { scrollbar-width: thin; scrollbar-color: #E5E7EB transparent; }
        .thin-scroll::-webkit-scrollbar { width: 4px; }
        .thin-scroll::-webkit-scrollbar-thumb { background:#E5E7EB; border-radius:4px; }

        /* ─── Action badges (mirror super admin audit) ─── */
        .action-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 5px;
            font-size: 11px; font-weight: 700; font-family: 'Courier New', monospace;
            white-space: nowrap; letter-spacing: .01em;
        }
        .ab-create   { background:#f0fdf4; color:#16a34a; }
        .ab-update   { background:#fffbeb; color:#d97706; }
        .ab-delete   { background:#fef2f2; color:#dc2626; }
        .ab-escalate { background:#ecfeff; color:#0891b2; }
        .ab-approve  { background:#ede9fe; color:#7c3aed; }
        .ab-reject   { background:#fff7ed; color:#ea580c; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarPimpinan')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- ── Top Bar ── --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Dashboard Pimpinan</h1>
                <p class="text-sm text-gray-400 mt-0.5">Ringkasan kinerja sistem helpdesk IT — {{ now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            </div>
        </header>

        <main class="flex-1 px-8 py-8 space-y-8">

            {{-- ══════════════════════════════════════════════════════════
                 SECTION 1 — Stat Cards Utama
            ══════════════════════════════════════════════════════════ --}}
            @php
            $mainCards = [
                [
                    'label'  => 'Total Tiket',
                    'value'  => number_format($totalTiket),
                    'sub'    => number_format($tiketBulanIni) . ' masuk bulan ini',
                    'color'  => '#01458E',
                    'bg'     => '#EEF3F9',
                    'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                ],
                [
                    'label'  => 'Tiket Aktif',
                    'value'  => number_format($tiketAktif),
                    'sub'    => 'Belum selesai ditangani',
                    'color'  => '#D97706',
                    'bg'     => '#FEF3C7',
                    'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label'  => 'Tiket Selesai',
                    'value'  => number_format($tiketSelesai),
                    'sub'    => number_format($selesaiBulanIni) . ' selesai bulan ini',
                    'color'  => '#059669',
                    'bg'     => '#D1FAE5',
                    'icon'   => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                ],
                [
                    'label'  => 'Rata-rata Kepuasan',
                    'value'  => $avgKepuasan > 0 ? number_format($avgKepuasan, 1) : '—',
                    'sub'    => 'Dari 5 bintang maksimum',
                    'color'  => '#7C3AED',
                    'bg'     => '#EDE9FE',
                    'icon'   => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
                ],
                [
                    'label'  => 'Total OPD',
                    'value'  => number_format($totalOpd),
                    'sub'    => 'Instansi terdaftar',
                    'color'  => '#0263C8',
                    'bg'     => '#EBF3FF',
                    'icon'   => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                ],
                [
                    'label'  => 'Artikel KB Aktif',
                    'value'  => number_format($totalKb),
                    'sub'    => 'Artikel terbit & tersedia',
                    'color'  => '#0891B2',
                    'bg'     => '#ECFEFF',
                    'icon'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                ],
            ];
            @endphp

            <div class="grid grid-cols-2 xl:grid-cols-3 gap-5 fade-up">
                @foreach($mainCards as $card)
                <div class="bg-white rounded-2xl p-6 flex items-center gap-5 shadow-sm border border-gray-100
                            hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group cursor-default">
                    <div class="absolute -right-5 -top-5 w-20 h-20 rounded-full opacity-15 group-hover:scale-150 transition-transform duration-500"
                         style="background-color:{{ $card['color'] }};"></div>
                    <div class="w-13 h-13 w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 relative z-10 group-hover:scale-110 transition-transform duration-300"
                         style="background-color:{{ $card['bg'] }};">
                        <svg class="w-6 h-6" style="color:{{ $card['color'] }};"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="relative z-10 min-w-0">
                        <p class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $card['value'] }}</p>
                        <p class="text-sm font-semibold text-gray-700 mt-0.5">{{ $card['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $card['sub'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 2 — KPI / Goal Tracking
            ══════════════════════════════════════════════════════════ --}}
            <div class="fade-up delay-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#EEF3F9;">
                        <svg class="w-4 h-4 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-gray-800">KPI & Pencapaian Target</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach($kpiData as $kpi)
                    @php
                        $pct = $kpi['unit'] === '%'
                            ? min($kpi['actual'], 100)
                            : ($kpi['target'] > 0 ? min(round(($kpi['actual'] / $kpi['target']) * 100), 100) : 0);
                        $met = $kpi['actual'] >= $kpi['target'];
                    @endphp
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                     style="background-color:{{ $kpi['bg'] }};">
                                    <svg class="w-5 h-5" style="color:{{ $kpi['color'] }};"
                                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-gray-700">{{ $kpi['label'] }}</p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                         {{ $met ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600' }}">
                                {{ $met ? 'Tercapai' : 'Belum' }}
                            </span>
                        </div>

                        <div class="flex items-baseline gap-2 mb-3">
                            <span class="text-3xl font-extrabold text-gray-900">{{ $kpi['actual'] }}</span>
                            <span class="text-sm text-gray-400">{{ $kpi['unit'] }}</span>
                        </div>

                        <div class="kpi-bar-track mb-2">
                            <div class="kpi-bar-fill" style="width:{{ $pct }}%; background-color:{{ $kpi['color'] }};"></div>
                        </div>

                        <div class="flex items-center justify-between text-xs text-gray-400">
                            <span>Aktual</span>
                            <span>Target: <strong class="text-gray-600">{{ $kpi['target'] }} {{ $kpi['unit'] }}</strong></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 2.5 — Laporan & Filter Periode
            ══════════════════════════════════════════════════════════ --}}
            <div class="fade-up delay-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#FEF3C7;">
                        <svg class="w-4 h-4 text-[#D97706]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-gray-800">Laporan & Filter Data</h2>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-7 py-6">

                    {{-- FORM 1: FILTER PERIODE --}}
                    <form method="GET" action="{{ route('pimpinan.dashboard') }}" id="periodFilterForm">
                        <div class="space-y-5">
                            {{-- Pilihan Periode --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3 block">Pilih Periode Laporan</label>
                                <div class="flex flex-wrap gap-3">
                                    @php
                                        $periods = [
                                            'daily' => ['label' => 'Harian', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            'weekly' => ['label' => 'Mingguan', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                                            'monthly' => ['label' => 'Bulanan', 'icon' => 'M9 12l2 2 4-4M7 20H5a2 2 0 01-2-2V9.414a1 1 0 00-.293-.707l.586-.586A1 1 0 005.414 8H7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H9a2 2 0 01-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H7a2 2 0 01-2-2z'],
                                            'yearly' => ['label' => 'Tahunan', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                                            'custom' => ['label' => 'Custom', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        ];
                                    @endphp

                                    @foreach($periods as $key => $data)
                                    <label class="flex items-center gap-2 px-4 py-2.5 rounded-xl border-2 cursor-pointer transition-all
                                                  {{ $period === $key ? 'border-[#01458E] bg-[#EEF3F9]' : 'border-gray-200 hover:border-gray-300' }}">
                                        <input type="radio" name="period" value="{{ $key }}"
                                               {{ $period === $key ? 'checked' : '' }}
                                               class="w-4 h-4 cursor-pointer"
                                               onchange="document.getElementById('periodFilterForm').submit()">
                                        <span class="text-sm font-semibold {{ $period === $key ? 'text-[#01458E]' : 'text-gray-700' }}">
                                            {{ $data['label'] }}
                                        </span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Custom date range --}}
                            @if($period === 'custom')
                            <div class="pt-4 border-t border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Rentang Tanggal Custom</p>
                                <div class="flex flex-wrap items-end gap-4">
                                    {{-- Dari tanggal --}}
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Dari Tanggal</label>
                                        <input type="date" name="date_from" id="dateFrom"
                                            value="{{ $dateFrom }}"
                                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8]
                                                    focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    </div>

                                    {{-- Sampai tanggal --}}
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Sampai Tanggal</label>
                                        <input type="date" name="date_to" id="dateTo"
                                            value="{{ $dateTo }}"
                                            min="{{ $dateFrom }}" {{-- Tambahkan batas minimal awal dari server --}}
                                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8]
                                                    focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    </div>

                                    {{-- Tombol Apply --}}
                                    <button type="submit"
                                            class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                            style="background-color:#01458E;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                            @endif

                            {{-- Informasi periode yang ditampilkan --}}
                            <div class="pt-4 border-t border-gray-100 bg-blue-50 rounded-lg p-4">
                                <p class="text-xs text-gray-600">
                                    <span class="font-semibold">Menampilkan data:</span>
                                    @if($period === 'daily')
                                        Hari ini ({{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMMM YYYY') }})
                                    @elseif($period === 'weekly')
                                        Minggu ini: {{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMMM YYYY') }} — {{ \Carbon\Carbon::parse($dateTo)->locale('id')->isoFormat('D MMMM YYYY') }}
                                    @elseif($period === 'monthly')
                                        Bulan {{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('MMMM YYYY') }}
                                    @elseif($period === 'yearly')
                                        Tahun {{ \Carbon\Carbon::parse($dateFrom)->format('Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($dateFrom)->locale('id')->isoFormat('D MMMM YYYY') }} — {{ \Carbon\Carbon::parse($dateTo)->locale('id')->isoFormat('D MMMM YYYY') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </form>

                    {{-- FORM 2: EXPORT CSV (Dipisah dari Form Filter) --}}
                    <div class="pt-5 mt-5 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Export Data</p>
                        <form method="GET" action="{{ route('pimpinan.export.csv') }}" id="exportForm" class="inline">
                            <input type="hidden" name="period" value="{{ $period }}">
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                            <button type="submit"
                                    class="flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                    style="background-color:#059669;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export Data CSV
                            </button>
                            <span class="text-xs text-gray-400 italic ml-3">File CSV dapat dibuka langsung di Microsoft Excel</span>
                        </form>
                    </div>

                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 3 — Charts: Tren Tiket + Distribusi Status
            ══════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 fade-up delay-2">

                {{-- Line chart: Tren tiket --}}
                <div class="xl:col-span-3 bg-white rounded-2xl p-7 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            @if($period === 'daily')
                            <h2 class="text-base font-bold text-gray-800">Tren Tiket — Harian (Per Jam)</h2>
                            <p class="text-xs text-gray-400 mt-1">Perbandingan tiket masuk vs. diselesaikan per jam hari ini</p>
                            @elseif($period === 'weekly')
                            <h2 class="text-base font-bold text-gray-800">Tren Tiket — Mingguan (Per Hari)</h2>
                            <p class="text-xs text-gray-400 mt-1">Perbandingan tiket masuk vs. diselesaikan per hari minggu ini</p>
                            @elseif($period === 'monthly')
                            <h2 class="text-base font-bold text-gray-800">Tren Tiket — Bulanan (Per Hari)</h2>
                            <p class="text-xs text-gray-400 mt-1">Perbandingan tiket masuk vs. diselesaikan per hari bulan ini</p>
                            @elseif($period === 'yearly')
                            <h2 class="text-base font-bold text-gray-800">Tren Tiket — Tahunan (Per Bulan)</h2>
                            <p class="text-xs text-gray-400 mt-1">Perbandingan tiket masuk vs. diselesaikan per bulan tahun ini</p>
                            @else
                            <h2 class="text-base font-bold text-gray-800">Tren Tiket — Custom Range</h2>
                            <p class="text-xs text-gray-400 mt-1">Perbandingan tiket masuk vs. diselesaikan dalam rentang yang dipilih</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 text-xs text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-1.5 rounded-full inline-block" style="background:#01458E;"></span>Masuk
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-1.5 rounded-full inline-block" style="background:#059669;"></span>Selesai
                            </span>
                        </div>
                    </div>
                    <canvas id="trendChart" height="200"></canvas>
                </div>

                {{-- Donut: Distribusi Status --}}
                <div class="xl:col-span-2 bg-white rounded-2xl p-7 shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-col">
                    <div>
                        <h2 class="text-base font-bold text-gray-800">Status Tiket</h2>
                        <p class="text-xs text-gray-400 mt-1 mb-5">
                            @if($period === 'daily')
                            Status dalam periode hari ini
                            @elseif($period === 'weekly')
                            Status dalam periode minggu ini
                            @elseif($period === 'monthly')
                            Status dalam periode bulan ini
                            @elseif($period === 'yearly')
                            Status dalam periode tahun ini
                            @else
                            Status dalam periode yang dipilih
                            @endif
                        </p>
                    </div>

                    @if(array_sum($tiketPerStatus) > 0)
                    <div class="flex justify-center mb-5">
                        <canvas id="statusChart" width="180" height="180" class="max-h-[180px]"></canvas>
                    </div>
                    @php
                    $statusColors = ['#01458E','#0263C8','#38BDF8','#D97706','#059669','#DC2626','#7C3AED'];
                    $si = 0;
                    @endphp
                    <div class="space-y-2.5 mt-auto">
                        @foreach($tiketPerStatus as $lbl => $cnt)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $statusColors[$si] ?? '#6B7280' }};"></span>
                                <span class="text-xs text-gray-600">{{ $lbl }}</span>
                            </div>
                            <span class="text-xs font-bold text-gray-800 bg-gray-50 px-2 py-0.5 rounded-md">{{ $cnt }}</span>
                        </div>
                        @php $si++; @endphp
                        @endforeach
                    </div>
                    @else
                    <div class="flex-1 flex items-center justify-center text-gray-300 text-sm">
                        Belum ada data tiket dalam periode ini
                    </div>
                    @endif
                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 4 — Distribusi per Bidang
            ══════════════════════════════════════════════════════════ --}}
            @if($tiketPerBidang->count() > 0)
            <div class="fade-up delay-2">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#EBF3FF;">
                        <svg class="w-4 h-4 text-[#0263C8]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-gray-800">Distribusi Tiket per Bidang</h2>
                </div>

                <div class="bg-white rounded-2xl p-7 shadow-sm border border-gray-100">
                    @php $maxBidang = $tiketPerBidang->max('total') ?: 1; @endphp
                    <div class="space-y-4">
                        @foreach($tiketPerBidang as $i => $bidang)
                        @php
                        $bidangColors = ['#01458E','#0263C8','#0891B2'];
                        $bc = $bidangColors[$i % count($bidangColors)];
                        $pctBidang = round(($bidang['total'] / $maxBidang) * 100);
                        @endphp
                        <div class="flex items-center gap-4">
                            <div class="w-44 shrink-0">
                                <p class="text-sm font-semibold text-gray-700 truncate">{{ $bidang['nama'] }}</p>
                                <p class="text-xs text-gray-400">{{ $bidang['total'] }} tiket</p>
                            </div>
                            <div class="flex-1 kpi-bar-track">
                                <div class="kpi-bar-fill" style="width:{{ $pctBidang }}%; background:{{ $bc }};"></div>
                            </div>
                            <span class="text-sm font-bold text-gray-700 w-10 text-right">{{ $bidang['total'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 5 — Performa Tim
            ══════════════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 fade-up delay-3">

                {{-- Admin Helpdesk Performance --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="px-7 py-5 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#EEF3F9;">
                                <svg class="w-4 h-4 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">Performa Admin Helpdesk</h2>
                                <p class="text-xs text-gray-400">Produktivitas penanganan tiket</p>
                            </div>
                        </div>
                    </div>

                    @if($performanceAdmin->isNotEmpty())
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500">Admin</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">Bidang</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500">Ditangani</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500">Selesai</th>
                                    <th class="px-5 py-3 text-center text-xs font-bold text-gray-500">Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceAdmin as $admin)
                                @php
                                $rateColor = $admin['rate'] >= 80 ? 'text-green-600 bg-green-50' :
                                             ($admin['rate'] >= 50 ? 'text-yellow-600 bg-yellow-50' : 'text-red-600 bg-red-50');
                                @endphp
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <p class="text-sm font-semibold text-gray-800">{{ $admin['nama'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-gray-500">{{ $admin['bidang'] }}</td>
                                    <td class="px-4 py-3.5 text-center text-sm font-bold text-gray-800">{{ $admin['ditangani'] }}</td>
                                    <td class="px-4 py-3.5 text-center text-sm font-bold text-gray-800">{{ $admin['diselesaikan'] }}</td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full {{ $rateColor }}">
                                            {{ $admin['rate'] }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="flex-1 flex items-center justify-center py-12 text-gray-400 text-sm">
                        Belum ada data admin helpdesk.
                    </div>
                    @endif
                </div>

                {{-- Tim Teknis Workload --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="px-7 py-5 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#D1FAE5;">
                                <svg class="w-4 h-4 text-[#059669]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">Beban Kerja Tim Teknis</h2>
                                <p class="text-xs text-gray-400">Alokasi & status penugasan</p>
                            </div>
                        </div>
                    </div>

                    @if($workloadTeknis->isNotEmpty())
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="px-5 py-3 text-left text-xs font-bold text-gray-500">Teknisi</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500">Bidang</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500">Aktif</th>
                                    <th class="px-5 py-3 text-center text-xs font-bold text-gray-500">Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($workloadTeknis as $teknis)
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <p class="text-sm font-semibold text-gray-800">{{ $teknis['nama'] }}</p>
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-gray-500">{{ $teknis['bidang'] }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($teknis['status'] === 'online')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Online
                                        </span>
                                        @elseif($teknis['status'] === 'busy')
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Sibuk
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-50 text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Offline
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="text-sm font-bold {{ $teknis['tugas_aktif'] > 3 ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ $teknis['tugas_aktif'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center text-sm font-bold text-gray-800">{{ $teknis['tugas_selesai'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="flex-1 flex items-center justify-center py-12 text-gray-400 text-sm">
                        Belum ada data tim teknis.
                    </div>
                    @endif
                </div>
            </div>


            {{-- ══════════════════════════════════════════════════════════
                 SECTION 6 — Audit Trail (Internal Roles Only)
            ══════════════════════════════════════════════════════════ --}}
            @php
            $auditRows = $auditLog->map(function ($log) {
                $badgeMap = [
                    'create'   => ['css' => 'ab-create',   'icon' => '✦', 'label' => 'CREATE'],
                    'update'   => ['css' => 'ab-update',   'icon' => '✎', 'label' => 'UPDATE'],
                    'delete'   => ['css' => 'ab-delete',   'icon' => '✕', 'label' => 'DELETE'],
                    'escalate' => ['css' => 'ab-escalate', 'icon' => '↑', 'label' => 'ESCALATE'],
                    'approve'  => ['css' => 'ab-approve',  'icon' => '✓', 'label' => 'APPROVE'],
                    'reject'   => ['css' => 'ab-reject',   'icon' => '✗', 'label' => 'REJECT'],
                ];
                $roleMap = [
                    'super_admin'    => ['pill' => 'bg-[#EEF3F9] text-[#01458E]', 'label' => 'Super Admin'],
                    'admin_helpdesk' => ['pill' => 'bg-[#EBF3FF] text-[#0263C8]', 'label' => 'Admin Helpdesk'],
                    'tim_teknis'     => ['pill' => 'bg-[#D1FAE5] text-[#059669]', 'label' => 'Tim Teknis'],
                ];
                $badge = $badgeMap[$log->jenis_aktivitas] ?? ['css' => 'ab-update', 'icon' => '•', 'label' => strtoupper($log->jenis_aktivitas)];
                $role  = $roleMap[$log->role_pelaku]      ?? ['pill' => 'bg-gray-100 text-gray-600', 'label' => $log->role_pelaku];
                return [
                    'nama'       => $log->user?->name ?? 'Sistem',
                    'inisial'    => strtoupper(substr($log->user?->name ?? 'S', 0, 1)),
                    'rolePill'   => $role['pill'],
                    'roleLabel'  => $role['label'],
                    'badgeCss'   => $badge['css'],
                    'badgeIcon'  => $badge['icon'],
                    'badgeLabel' => $badge['label'],
                    'detail'     => $log->detail_tindakan ?? '—',
                    'waktu'      => $log->waktu_eksekusi?->locale('id')->diffForHumans() ?? '—',
                ];
            })->values()->toArray();
            @endphp

            <div class="fade-up delay-3"
                 x-data="{
                     rows:    {{ Js::from($auditRows) }},
                     perPage: 10,
                     page:    1,
                     get totalPages() { return Math.max(1, Math.ceil(this.rows.length / this.perPage)); },
                     get paged()      { return this.rows.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
                     get from()       { return this.rows.length === 0 ? 0 : (this.page - 1) * this.perPage + 1; },
                     get to()         { return Math.min(this.page * this.perPage, this.rows.length); },
                     prev() { if (this.page > 1) this.page--; },
                     next() { if (this.page < this.totalPages) this.page++; },
                     go(p)  { this.page = p; },
                 }">

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#EDE9FE;">
                            <svg class="w-4 h-4 text-[#7C3AED]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-800">Audit Trail — Aktivitas Internal</h2>
                            <p class="text-xs text-gray-400">Hanya mencakup aktivitas Super Admin, Admin Helpdesk, dan Tim Teknis</p>
                        </div>
                    </div>
                    {{-- Info total --}}
                    <span class="text-xs text-gray-400 shrink-0"
                          x-text="rows.length > 0 ? `Menampilkan ${from}–${to} dari ${rows.length} aktivitas` : ''">
                    </span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    @if($auditLog->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-gray-400">
                        <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm font-medium">Belum ada aktivitas tercatat</p>
                    </div>
                    @else

                    {{-- Tabel --}}
                    <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 w-8">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500">Pengguna</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500">Peran</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-gray-500">Aktivitas</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500">Detail</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-500">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, idx) in paged" :key="(page - 1) * perPage + idx">
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    {{-- No --}}
                                    <td class="px-6 py-3.5 text-sm text-gray-400" x-text="(page - 1) * perPage + idx + 1"></td>
                                    {{-- Pengguna --}}
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                                 style="background:#01458E;" x-text="row.inisial"></div>
                                            <span class="text-sm font-semibold text-gray-800" x-text="row.nama"></span>
                                        </div>
                                    </td>
                                    {{-- Peran --}}
                                    <td class="px-4 py-3.5">
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full"
                                              :class="row.rolePill" x-text="row.roleLabel"></span>
                                    </td>
                                    {{-- Aktivitas --}}
                                    <td class="px-4 py-3.5 text-center">
                                        <span class="action-badge" :class="row.badgeCss">
                                            <span x-text="row.badgeIcon"></span>
                                            <span x-text="row.badgeLabel"></span>
                                        </span>
                                    </td>
                                    {{-- Detail --}}
                                    <td class="px-4 py-3.5 text-sm text-gray-500 max-w-xs truncate" x-text="row.detail"></td>
                                    {{-- Waktu --}}
                                    <td class="px-6 py-3.5 text-right text-xs text-gray-400 whitespace-nowrap" x-text="row.waktu"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    </div>

                    {{-- ── Pagination Footer ── --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100 bg-gray-50/40">

                        {{-- Kiri: info halaman --}}
                        <p class="text-xs text-gray-400">
                            Halaman <span class="font-semibold text-gray-600" x-text="page"></span>
                            dari <span class="font-semibold text-gray-600" x-text="totalPages"></span>
                        </p>

                        {{-- Tengah: nomor halaman --}}
                        <div class="flex items-center gap-1">
                            {{-- Prev --}}
                            <button @click="prev"
                                    :disabled="page === 1"
                                    :class="page === 1 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100'"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            {{-- Nomor halaman --}}
                            <template x-for="p in totalPages" :key="p">
                                <button @click="go(p)"
                                        :class="p === page
                                            ? 'text-white font-bold'
                                            : 'text-gray-500 hover:bg-gray-100'"
                                        :style="p === page ? 'background-color:#01458E;' : ''"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-xs transition-colors"
                                        x-text="p">
                                </button>
                            </template>

                            {{-- Next --}}
                            <button @click="next"
                                    :disabled="page === totalPages"
                                    :class="page === totalPages ? 'opacity-30 cursor-not-allowed' : 'hover:bg-gray-100'"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Kanan: total data --}}
                        <p class="text-xs text-gray-400">
                            <span class="font-semibold text-gray-600" x-text="rows.length"></span> total aktivitas
                        </p>
                    </div>

                    @endif
                </div>
            </div>


        </main>
    </div>

    <script>
    // ── Validasi Tanggal (Custom Range) ─────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');

        if (dateFrom && dateTo) {
            // Ketika 'Dari Tanggal' diubah, perbarui batas minimal 'Sampai Tanggal'
            dateFrom.addEventListener('change', function() {
                dateTo.min = this.value;

                // Jika 'Sampai Tanggal' sebelumnya sudah terpilih dan nilainya menjadi
                // lebih kecil dari 'Dari Tanggal' yang baru, reset ke tanggal yang sama
                if (dateTo.value && dateTo.value < this.value) {
                    dateTo.value = this.value;
                }
            });
        }
    });

    // ── Chart 1: Tren tiket per bulan (line chart) ───────────────────
    (function() {
        const months  = @json($trendMonths->pluck('label'));
        const masuk   = @json($trendMonths->pluck('masuk'));
        const selesai = @json($trendMonths->pluck('selesai'));

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Tiket Masuk',
                        data: masuk,
                        borderColor: '#01458E',
                        backgroundColor: 'rgba(1,69,142,0.08)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#01458E',
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: 'Tiket Selesai',
                        data: selesai,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5,150,105,0.06)',
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#059669',
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4,
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17,24,39,0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: 'Inter', size: 12 },
                        bodyFont: { family: 'Inter', size: 13, weight: 'bold' },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#9CA3AF' },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#F3F4F6' },
                        ticks: {
                            font: { family: 'Inter', size: 11 }, color: '#9CA3AF',
                            stepSize: 1,
                            callback: v => Number.isInteger(v) ? v : '',
                        },
                    },
                },
            },
        });
    })();

    // ── Chart 2: Status tiket (donut) ────────────────────────────────
    (function() {
        const labels = @json(array_keys($tiketPerStatus));
        const data   = @json(array_values($tiketPerStatus));
        const colors = ['#01458E','#0263C8','#38BDF8','#D97706','#059669','#DC2626','#7C3AED'];
        const total  = data.reduce((a, b) => a + b, 0);

        if (total === 0) return;

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 6,
                }],
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17,24,39,0.9)',
                        padding: 10,
                        cornerRadius: 8,
                        bodyFont: { family: 'Inter', size: 13, weight: 'bold' },
                        callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` },
                    },
                },
                animation: { animateScale: true, animateRotate: true },
            },
            plugins: [{
                id: 'centerTotal',
                beforeDraw(chart) {
                    const { ctx, chartArea: { width, height, left, top } } = chart;
                    ctx.save();
                    ctx.font = 'bold 24px Inter';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total, left + width / 2, top + height / 2 - 8);
                    ctx.font = '500 11px Inter';
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('Total', left + width / 2, top + height / 2 + 12);
                    ctx.restore();
                },
            }],
        });
    })();
    </script>

</body>
</html>
