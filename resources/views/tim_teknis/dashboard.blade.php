<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Tim Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(15px);
        }
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .pill-dot { width: 6px; height: 6px; border-radius: 50%; }

        .table-wrap { overflow-x: auto; }
        .table-wrap::-webkit-scrollbar { height: 6px; }
        .table-wrap::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }

        .dot-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        /* Mini bar chart */
        .bar-wrap { display: flex; align-items: flex-end; gap: 6px; height: 64px; }
        .bar-col  { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
        .bar      { width: 100%; border-radius: 6px 6px 0 0; background: #0263C8; transition: height 0.4s ease; min-height: 4px; }
        .bar-lbl  { font-size: 9px; color: #94a3b8; font-weight: 600; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#F4F7FB] to-[#E9F0F8] min-h-screen text-gray-800">

    @include('layouts.sidebarTimTeknis')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- Top bar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">Ringkasan aktivitas dan tugas teknis Anda</p>
            </div>
            @if($lastLogin)
            <div class="text-xs text-gray-400 font-medium hidden md:block">
                Login terakhir: <span class="text-gray-600 font-semibold">{{ $lastLogin }}</span>
            </div>
            @endif
        </header>

        <main class="flex-1 px-4 py-4 lg:px-8 lg:py-8 space-y-6 lg:space-y-8">

            {{-- ── Hero Banner ── --}}
            @php
            $bidangLabel = $teknis->bidang?->nama_bidang ?? '—';
            @endphp

            <div class="animate-fade-in-up relative overflow-hidden rounded-3xl text-white shadow-lg"
                 style="background: linear-gradient(135deg, #01458E 0%, #0263C8 60%, #2A93D5 100%);">
                <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full opacity-10 bg-white blur-2xl"></div>
                <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full opacity-10 bg-white blur-xl"></div>
                <div class="dot-grid absolute inset-0 opacity-40"></div>

                <div class="relative z-10 px-5 py-6 lg:px-8 lg:py-9 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <p class="text-blue-100 text-xs font-semibold uppercase tracking-widest mb-2">
                            {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </p>
                        <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-2">
                            Selamat Datang,<br>
                            <span class="text-white font-extrabold">{{ $teknis->nama_lengkap }}</span>
                        </h1>
                        <p class="text-blue-50 text-sm md:text-base max-w-xl">
                            Tim Teknis &mdash; <span class="font-semibold">{{ $bidangLabel }}</span>
                        </p>
                    </div>
                    <div class="shrink-0 flex flex-col gap-3">
                        <a href="{{ route('tim_teknis.antrean') }}"
                           class="inline-flex items-center gap-2 bg-white text-[#01458E] font-bold text-sm px-6 py-3.5 rounded-xl shadow-md transition-all hover:shadow-xl hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Lihat Antrean Tugas
                        </a>
                        @if($stats['aktif'] > 0)
                        <div class="inline-flex items-center gap-2 bg-white/20 text-white text-xs font-semibold px-4 py-2 rounded-xl">
                            <div class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></div>
                            {{ $stats['aktif'] }} tugas sedang aktif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Stat Cards ── --}}
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6 animate-fade-in-up delay-100">
                @php
                $statCards = [
                    [
                        'label'  => 'Tugas Aktif',
                        'value'  => $stats['aktif'],
                        'sub'    => 'Sedang berjalan saat ini',
                        'color'  => '#D97706',
                        'bg'     => '#FEF3C7',
                        'icon'   => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'label'  => 'Selesai Utama',
                        'value'  => $stats['selesai_utama'],
                        'sub'    => 'Sebagai Teknisi Utama',
                        'color'  => '#01458E',
                        'bg'     => '#EEF3F9',
                        'icon'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    ],
                    [
                        'label'  => 'Selesai Pendamping',
                        'value'  => $stats['selesai_pendamping'],
                        'sub'    => 'Sebagai Teknisi Pendamping',
                        'color'  => '#7C3AED',
                        'bg'     => '#EDE9FE',
                        'icon'   => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label'  => 'Total Tugas Selesai',
                        'value'  => $stats['total_selesai'],
                        'sub'    => 'Keseluruhan tugas diselesaikan',
                        'color'  => '#0263C8',
                        'bg'     => '#EBF3FF',
                        'icon'   => 'M5 13l4 4L19 7',
                    ],
                    [
                        'label'  => 'Selesai Tepat Waktu',
                        'value'  => $stats['tepat_waktu'],
                        'sub'    => 'Diselesaikan dalam SLA',
                        'color'  => '#059669',
                        'bg'     => '#D1FAE5',
                        'icon'   => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    ],
                    [
                        'label'  => 'Melebihi SLA',
                        'value'  => $stats['telat'],
                        'sub'    => 'Penyelesaian terlambat',
                        'color'  => $stats['telat'] > 0 ? '#DC2626' : '#9CA3AF',
                        'bg'     => $stats['telat'] > 0 ? '#FEE2E2' : '#F3F4F6',
                        'icon'   => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                    ],
                ];
                @endphp

                @foreach($statCards as $i => $card)
                <div class="bg-white rounded-2xl p-4 sm:p-6 flex items-center gap-3 sm:gap-5 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 relative overflow-hidden group cursor-default">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 transition-transform duration-500 group-hover:scale-125" style="background-color:{{ $card['color'] }};"></div>

                    <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0 relative z-10 transition-transform duration-300 group-hover:scale-110"
                         style="background-color:{{ $card['bg'] }};">
                        <svg class="w-5 h-5 sm:w-7 sm:h-7" style="color:{{ $card['color'] }};"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="relative z-10 flex-1 min-w-0">
                        <p class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($card['value']) }}</p>
                        <p class="text-xs sm:text-sm font-medium text-gray-500 mt-0.5 leading-tight">{{ $card['label'] }}</p>
                        <p class="text-[10px] sm:text-xs text-gray-400 mt-0.5 truncate hidden sm:block">{{ $card['sub'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Main 2-col grid ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 animate-fade-in-up delay-200">

                {{-- LEFT: Tugas Aktif Terbaru --}}
                <div class="lg:col-span-3">

                    {{-- Mobile card list (tampil di mobile, sembunyi di md+) --}}
                    @php
                    $statusMapMobile = [
                        'perbaikan_teknis' => ['label' => 'Perbaikan Teknis', 'pill' => 'bg-amber-50 text-amber-600',    'dot' => 'bg-amber-500'],
                        'dibuka_kembali'   => ['label' => 'Dibuka Kembali',   'pill' => 'bg-orange-50 text-orange-600',  'dot' => 'bg-orange-500'],
                        'verifikasi_admin' => ['label' => 'Verifikasi Admin', 'pill' => 'bg-blue-50 text-blue-600',      'dot' => 'bg-blue-500'],
                        'selesai'          => ['label' => 'Selesai',           'pill' => 'bg-emerald-50 text-emerald-600','dot' => 'bg-emerald-500'],
                        'rusak_berat'      => ['label' => 'Rusak Berat',       'pill' => 'bg-red-50 text-red-600',        'dot' => 'bg-red-500'],
                    ];
                    @endphp
                    <div class="md:hidden bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow overflow-hidden flex flex-col">
                        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Tugas Aktif Terbaru</h2>
                            </div>
                            <a href="{{ route('tim_teknis.antrean') }}"
                               class="text-xs font-semibold text-[#01458E] hover:underline flex items-center gap-1 px-2 py-1 rounded-lg hover:bg-blue-50 transition-colors">
                                Lihat Semua
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                        @forelse($tiketAktif as $tt)
                        @php $tiket = $tt->tiket; @endphp
                        <div class="px-4 py-4 border-b border-gray-100 last:border-0">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200">
                                    #{{ strtoupper(substr($tiket->id, 0, 8)) }}
                                </span>
                                @if($tiket->latestStatus)
                                    @php $stm = $statusMapMobile[$tiket->latestStatus->status_tiket] ?? ['label' => $tiket->latestStatus->status_tiket, 'pill' => 'bg-gray-100 text-gray-600', 'dot' => 'bg-gray-400']; @endphp
                                    <span class="pill {{ $stm['pill'] }} text-[10px]">
                                        <span class="pill-dot {{ $stm['dot'] }}"></span>
                                        {{ $stm['label'] }}
                                    </span>
                                @else
                                    <span class="pill bg-slate-100 text-slate-500 text-[10px]">
                                        <span class="pill-dot bg-slate-400"></span>—
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-1 leading-snug">{{ $tiket->subjek_masalah ?? '-' }}</p>
                            <div class="flex flex-wrap gap-x-2 text-xs text-gray-500 mb-3">
                                <span>{{ $tiket->opd?->nama_opd ?? '—' }}</span>
                                <span class="text-gray-300">•</span>
                                @if($tt->peran_teknisi === 'teknisi_utama')
                                    <span class="text-blue-600 font-semibold">Utama</span>
                                @else
                                    <span class="text-purple-600 font-semibold">Pendamping</span>
                                @endif
                                <span class="text-gray-300">•</span>
                                <span>{{ $tiket->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex gap-2 flex-wrap">
                                <a href="{{ route('tim_teknis.antrean') }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                   style="background-color:#01458E;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Lihat Antrean
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center bg-blue-50/50">
                                    <svg class="w-6 h-6 text-[#01458E] opacity-70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Tidak ada tugas aktif</p>
                                    <p class="text-xs text-gray-500 mt-1">Semua tugas telah diselesaikan.</p>
                                </div>
                            </div>
                        </div>
                        @endforelse
                    </div>

                    {{-- Desktop table (sembunyi di mobile, tampil di md+) --}}
                    <div class="hidden md:block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow overflow-hidden flex flex-col h-full">
                        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Tugas Aktif Terbaru</h2>
                            </div>
                            <a href="{{ route('tim_teknis.antrean') }}"
                               class="text-xs font-semibold text-[#01458E] hover:underline flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                                Lihat Semua
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        {{-- Mobile cards --}}
                        @php
                        $statusMapM = [
                            'perbaikan_teknis' => ['label' => 'Perbaikan Teknis', 'pill' => 'bg-amber-50 text-amber-600'],
                            'dibuka_kembali'   => ['label' => 'Dibuka Kembali',   'pill' => 'bg-orange-50 text-orange-600'],
                            'verifikasi_admin' => ['label' => 'Verifikasi Admin', 'pill' => 'bg-blue-50 text-blue-600'],
                            'selesai'          => ['label' => 'Selesai',           'pill' => 'bg-emerald-50 text-emerald-600'],
                            'rusak_berat'      => ['label' => 'Rusak Berat',       'pill' => 'bg-red-50 text-red-600'],
                        ];
                        @endphp
                        <div class="md:hidden divide-y divide-gray-100">
                            @forelse($tiketAktif as $tt)
                            @php $tiket = $tt->tiket; @endphp
                            <div class="px-4 py-4 hover:bg-blue-50/30 transition-colors">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded border border-gray-200">#{{ strtoupper(substr($tiket->id, 0, 8)) }}</span>
                                    @if($tt->peran_teknisi === 'teknisi_utama')
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 shrink-0">Utama</span>
                                    @else
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 shrink-0">Pendamping</span>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-gray-900 mb-0.5 truncate">{{ $tiket->subjek_masalah ?? '-' }}</p>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs text-gray-400">{{ $tiket->opd?->nama_opd ?? '—' }}</span>
                                    @if($tiket->latestStatus)
                                    @php $stM = $statusMapM[$tiket->latestStatus->status_tiket] ?? ['label' => $tiket->latestStatus->status_tiket, 'pill' => 'bg-gray-100 text-gray-600']; @endphp
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $stM['pill'] }}">{{ $stM['label'] }}</span>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-10 text-center text-sm text-gray-400">Tidak ada tugas aktif.</div>
                            @endforelse
                        </div>

                        <div class="hidden md:block table-wrap flex-1">
                            <table class="w-full whitespace-nowrap">
                                <thead>
                                    <tr class="bg-gray-50/80 border-b border-gray-100">
                                        <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">No. Tiket</th>
                                        <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Judul</th>
                                        <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">OPD</th>
                                        <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Peran</th>
                                        <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                @php
                                $statusMap = [
                                    'perbaikan_teknis' => ['label' => 'Perbaikan Teknis', 'pill' => 'bg-amber-50 text-amber-600',   'dot' => 'bg-amber-500'],
                                    'dibuka_kembali'   => ['label' => 'Dibuka Kembali',   'pill' => 'bg-orange-50 text-orange-600', 'dot' => 'bg-orange-500'],
                                    'verifikasi_admin' => ['label' => 'Verifikasi Admin', 'pill' => 'bg-blue-50 text-blue-600',     'dot' => 'bg-blue-500'],
                                    'selesai'          => ['label' => 'Selesai',           'pill' => 'bg-emerald-50 text-emerald-600','dot'=> 'bg-emerald-500'],
                                    'rusak_berat'      => ['label' => 'Rusak Berat',       'pill' => 'bg-red-50 text-red-600',       'dot' => 'bg-red-500'],
                                ];
                                @endphp
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($tiketAktif as $tt)
                                    @php $tiket = $tt->tiket; @endphp
                                    <tr class="hover:bg-blue-50/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md border border-gray-200">
                                                #{{ strtoupper(substr($tiket->id, 0, 8)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 max-w-[200px]">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $tiket->subjek_masalah ?? '-' }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->created_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs text-gray-600 font-medium">{{ $tiket->opd?->nama_opd ?? '—' }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($tt->peran_teknisi === 'teknisi_utama')
                                            <span class="pill bg-blue-50 text-blue-700 text-[10px]">Utama</span>
                                            @else
                                            <span class="pill bg-purple-50 text-purple-700 text-[10px]">Pendamping</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($tiket->latestStatus)
                                                @php $st = $statusMap[$tiket->latestStatus->status_tiket] ?? ['label' => $tiket->latestStatus->status_tiket, 'pill' => 'bg-gray-100 text-gray-600', 'dot' => 'bg-gray-400']; @endphp
                                                <span class="pill {{ $st['pill'] }}">
                                                    <span class="pill-dot {{ $st['dot'] }}"></span>
                                                    {{ $st['label'] }}
                                                </span>
                                            @else
                                                <span class="pill bg-slate-100 text-slate-500">
                                                    <span class="pill-dot bg-slate-400"></span>—
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center gap-4">
                                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-blue-50/50">
                                                    <svg class="w-8 h-8 text-[#01458E] opacity-70" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-800">Tidak ada tugas aktif</p>
                                                    <p class="text-xs text-gray-500 mt-1">Semua tugas telah diselesaikan.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        </div>
                    </div>

                {{-- RIGHT col --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Chart Selesai 6 Bulan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow p-6">
                        <div class="flex items-center gap-2.5 mb-5">
                            <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Tugas Selesai (6 Bln)</h2>
                        </div>

                        @php
                        $bulanNama = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
                        $chartData = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $d = now()->subMonths($i);
                            $bulan = (int) $d->format('n');
                            $tahun = (int) $d->format('Y');
                            $row   = $distribusiSelesai->first(fn($r) => $r->bulan == $bulan && $r->tahun == $tahun);
                            $chartData[] = ['label' => $bulanNama[$bulan - 1], 'jumlah' => $row ? $row->jumlah : 0];
                        }
                        $maxVal = max(max(array_column($chartData, 'jumlah')), 1);
                        @endphp

                        <div class="bar-wrap">
                            @foreach($chartData as $col)
                            @php $pct = ($col['jumlah'] / $maxVal) * 64; @endphp
                            <div class="bar-col">
                                <span class="text-[9px] font-bold text-gray-500 mb-1">{{ $col['jumlah'] > 0 ? $col['jumlah'] : '' }}</span>
                                <div class="bar" style="height: {{ max($pct, 4) }}px;"></div>
                                <span class="bar-lbl">{{ $col['label'] }}</span>
                            </div>
                            @endforeach
                                 </div>
                        </div>
                    </div>
                </div>

            {{-- ── Quick Access (Akses Cepat) ── --}}
            <div class="animate-fade-in-up delay-200 mt-6">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                    <h2 class="text-base font-bold text-gray-900 uppercase tracking-wide">Akses Cepat</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6">
                    @php
                    $quickLinks = [
                        [
                            'label'   => 'Antrean Tugas',
                            'desc'    => 'Tiket yang ditugaskan kepada Anda',
                            'route'   => 'tim_teknis.antrean',
                            'iconBg'  => 'rgba(245,158,11,0.10)',
                            'iconClr' => '#D97706',
                            'icon'    => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                        ],
                        [
                            'label'   => 'Riwayat Tugas',
                            'desc'    => 'Tugas yang telah diselesaikan',
                            'route'   => 'tim_teknis.riwayat',
                            'iconBg'  => 'rgba(1,69,142,0.08)',
                            'iconClr' => '#01458E',
                            'icon'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        ],
                        [
                            'label'   => 'Pustaka Teknis (SOP)',
                            'desc'    => 'Panduan dan prosedur teknis',
                            'route'   => 'tim_teknis.pustaka',
                            'iconBg'  => 'rgba(16,185,129,0.10)',
                            'iconClr' => '#059669',
                            'icon'    => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253',
                        ],
                    ];
                    @endphp
                    @foreach($quickLinks as $ql)
                    <a href="{{ route($ql['route']) }}"
                       class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-4 hover:shadow-xl hover:-translate-y-1 transition-all group">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0"
                             style="background-color:{{ $ql['iconBg'] }};">
                            <svg class="w-6 h-6" style="color:{{ $ql['iconClr'] }};"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ql['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900 group-hover:text-[#01458E] transition-colors">{{ $ql['label'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $ql['desc'] }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>                  

                </div>
            </div>

        </main>

        {{-- Footer --}}
        <footer class="text-center py-6 border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
            &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
        </footer>

    </div>

</body>
</html>
