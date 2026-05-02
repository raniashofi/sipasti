<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        /* Animasi masuk halaman */
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
    </style>
</head>
<body class="bg-gradient-to-br from-[#F4F7FB] to-[#E9F0F8] min-h-screen text-gray-800 text-opacity-90">

    @include('layouts.sidebarSuperAdmin')

    {{-- Main content offset by sidebar --}}
    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- Top bar with Glassmorphism --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm transition-all">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
                <p class="text-sm text-gray-400 mt-0.5">Selamat datang kembali, Super Admin</p>
            </div>
            </header>

        <main class="flex-1 px-8 py-8 space-y-8">

            {{-- ── Stat Cards ── --}}
            @php
            $cards = [
                [
                    'label' => 'Total OPD',
                    'value' => $stats['total_opd'] ?? 0,
                    'color' => '#01458E',
                    'bg'    => '#EEF3F9',
                    'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                ],
                [
                    'label' => 'Pengguna Internal',
                    'value' => $stats['total_internal'] ?? 0,
                    'color' => '#0263C8',
                    'bg'    => '#EBF3FF',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                ],
                [
                    'label' => 'Total Tiket',
                    'value' => $stats['total_tiket'] ?? 0,
                    'color' => '#D97706',
                    'bg'    => '#FEF3C7',
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                ],
                [
                    'label' => 'Knowledge Base',
                    'value' => $stats['total_kb'] ?? 0,
                    'color' => '#059669',
                    'bg'    => '#D1FAE5',
                    'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                ],
            ];
            @endphp

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-6 animate-fade-in-up">
                @foreach($cards as $card)
                <div class="bg-white rounded-2xl p-6 flex items-center gap-5 shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 relative overflow-hidden group cursor-default">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 transition-transform duration-500 group-hover:scale-125" style="background-color:{{ $card['color'] }};"></div>

                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 relative z-10 transition-transform duration-300 group-hover:scale-110"
                         style="background-color:{{ $card['bg'] }};">
                        <svg class="w-7 h-7" style="color:{{ $card['color'] }};"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <p class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($card['value']) }}</p>
                        <p class="text-sm font-medium text-gray-500 mt-1">{{ $card['label'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Metrik Kepuasan & KB ── --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 animate-fade-in-up delay-100">

                {{-- Rata-rata Penilaian Tiket --}}
                <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#FEF3C7;">
                        <svg class="w-6 h-6" style="color:#D97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Kepuasan Tiket</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            @php $avg = $stats['avg_penilaian'] ?? 0; @endphp
                            <span class="text-2xl font-extrabold text-gray-900">{{ $avg > 0 ? number_format($avg, 1) : '—' }}</span>
                            @if($avg > 0)
                            <span class="text-sm text-gray-400">/ 5</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-0.5 mt-1">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3.5 h-3.5 {{ $i <= round($avg) ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            @endfor
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">{{ $stats['tiket_selesai'] }} tiket telah selesai</p>
                    </div>
                </div>

                {{-- Tiket Dibuka Kembali --}}
                @php $dibukakembali = $tiketPerStatus['Dibuka Kembali'] ?? 0; @endphp
                <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#FEE2E2;">
                        <svg class="w-6 h-6" style="color:#DC2626;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Dibuka Kembali</p>
                        <p class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($dibukakembali) }}</p>
                        <p class="text-[11px] text-gray-400 mt-1">Tiket yang dibuka ulang OPD</p>
                    </div>
                    @if($dibukakembali > 0)
                    <span class="shrink-0 text-[11px] font-bold px-2.5 py-1 rounded-full" style="background:#FEE2E2;color:#DC2626;">Perlu Perhatian</span>
                    @endif
                </div>

                {{-- Knowledge Base Terbit --}}
                <div class="bg-white rounded-2xl px-6 py-5 shadow-sm border border-gray-100 flex items-center gap-5 hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" style="background:#D1FAE5;">
                        <svg class="w-6 h-6" style="color:#059669;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">KB Terbit</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-extrabold text-gray-900">{{ number_format($stats['kb_published']) }}</span>
                            <span class="text-sm text-gray-400">/ {{ number_format($stats['total_kb']) }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">{{ number_format($stats['total_kb_ratings']) }} penilaian diterima</p>
                    </div>
                </div>

            </div>

            {{-- ── Chart + Activity ── --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 animate-fade-in-up delay-200">

                {{-- Donut chart (2 cols) --}}
                <div id="card-distribusi" class="xl:col-span-2 bg-white rounded-2xl p-7 shadow-sm border border-gray-100 flex flex-col self-start hover:shadow-md transition-shadow">
                    <h2 class="text-base font-bold text-gray-800">Distribusi Status Tiket</h2>
                    <p class="text-sm text-gray-400 mb-6 mt-1">Sebaran tiket berdasarkan status saat ini</p>

                    <div class="flex-1 flex flex-col justify-center">
                        <div class="flex items-center justify-center relative">
                            <canvas id="tiketChart" width="220" height="220" class="max-h-[220px]"></canvas>
                        </div>

                        @php
                        $chartColors = ['#01458E','#0263C8','#38BDF8','#D97706','#EF4444','#059669','#DC2626'];
                        $ci = 0;
                        @endphp
                        <div class="mt-8 space-y-3 px-2">
                            @foreach($tiketPerStatus as $label => $count)
                            <div class="flex items-center justify-between group">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full shrink-0 shadow-sm transition-transform group-hover:scale-125"
                                          style="background-color:{{ $chartColors[$ci] }};"></span>
                                    <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900 transition-colors">{{ $label }}</span>
                                </div>
                                <span class="text-sm font-bold text-gray-800 bg-gray-50 px-2.5 py-0.5 rounded-md">{{ $count }}</span>
                            </div>
                            @php $ci++; @endphp
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Recent Activity (3 cols) with Timeline UI --}}
                <div id="card-activity" class="xl:col-span-3 bg-white rounded-2xl p-7 shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-col self-start overflow-hidden">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-50 shrink-0">
                        <div>
                            <h2 class="text-base font-bold text-gray-800">Log Aktivitas Terbaru</h2>
                            <p class="text-sm text-gray-400 mt-1">Sistem mencatat aktivitas admin dan pengguna</p>
                        </div>
                        <a href="{{ route('super_admin.audit') }}"
                           class="text-sm font-semibold hover:underline flex items-center gap-1 transition-colors" style="color:#01458E;">
                            Lihat Semua
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>

                    @if($recentActivity->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <div class="bg-gray-50 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-gray-500">Belum ada aktivitas tercatat</p>
                    </div>
                    @else
                    <div class="overflow-y-auto flex-1 pr-1" style="scrollbar-width:thin;scrollbar-color:#E5E7EB transparent;">
                    <div class="relative border-l-2 border-gray-100 ml-4 space-y-6 mt-2">
                        @foreach($recentActivity as $log)
                        @php
                        $badgeMap = [
                            'login'    => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'label'=>'Login', 'dot'=>'bg-blue-500'],
                            'logout'   => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'label'=>'Logout', 'dot'=>'bg-gray-400'],
                            'create'   => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'label'=>'Buat', 'dot'=>'bg-green-500'],
                            'update'   => ['bg'=>'bg-yellow-100', 'text'=>'text-yellow-700', 'label'=>'Ubah', 'dot'=>'bg-yellow-500'],
                            'delete'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Hapus', 'dot'=>'bg-red-500'],
                            'escalate' => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'label'=>'Eskalasi', 'dot'=>'bg-orange-500'],
                            'approve'  => ['bg'=>'bg-teal-100',   'text'=>'text-teal-700',   'label'=>'Setujui', 'dot'=>'bg-teal-500'],
                            'reject'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Tolak', 'dot'=>'bg-red-500'],
                        ];
                        $badge = $badgeMap[$log->jenis_aktivitas] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-600','label'=>$log->jenis_aktivitas, 'dot'=>'bg-gray-400'];
                        @endphp

                        <div class="flex items-start gap-4 relative">
                            <div class="absolute -left-[25px] mt-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $badge['dot'] }} shadow-sm"></div>

                            <div class="flex-1 bg-gray-50/50 hover:bg-gray-50 rounded-xl p-3.5 border border-gray-100 transition-colors">
                                <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-gray-900">
                                            {{ $log->user?->name ?? 'Sistem' }}
                                        </span>
                                        <span class="text-[11px] px-2.5 py-0.5 rounded-md font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                            {{ $badge['label'] }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $log->waktu_eksekusi?->diffForHumans() ?? '-' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-white border border-gray-200 text-gray-500 rounded uppercase tracking-wide">
                                        {{ $log->role_pelaku }}
                                    </span>
                                    <p class="text-sm text-gray-600 truncate">{{ $log->detail_tindakan ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Quick Access ── --}}
            <div class="animate-fade-in-up delay-200" style="animation-delay:300ms;">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-gray-800">Akses Cepat</h2>
                </div>

                @php
                $quickLinks = [
                    [
                        'label' => 'Manajemen Pengguna',
                        'desc'  => 'Kelola akun OPD & internal',
                        'route' => 'super_admin.pengguna.opd',
                        'color' => '#01458E',
                        'bg'    => '#EEF3F9',
                        'border'=> 'hover:border-[#01458E]/30',
                        'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label' => 'Konfigurasi Sistem',
                        'desc'  => 'Gejala & alur diagnosis',
                        'route' => 'super_admin.konfigurasi.konfigurasiSistem',
                        'color' => '#0263C8',
                        'bg'    => '#EBF3FF',
                        'border'=> 'hover:border-[#0263C8]/30',
                        'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label' => 'Pustaka Pengetahuan',
                        'desc'  => 'Kelola konten basis data',
                        'route' => 'super_admin.pustaka.opd',
                        'color' => '#059669',
                        'bg'    => '#D1FAE5',
                        'border'=> 'hover:border-[#059669]/30',
                        'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    ],
                    [
                        'label' => 'Keamanan & Audit',
                        'desc'  => 'Log aktivitas & keamanan',
                        'route' => 'super_admin.audit',
                        'color' => '#7C3AED',
                        'bg'    => '#EDE9FE',
                        'border'=> 'hover:border-[#7C3AED]/30',
                        'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    ],
                ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    @foreach($quickLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:-translate-y-1 {{ $link['border'] }} hover:shadow-lg transition-all duration-300 group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                             style="background-color:{{ $link['bg'] }};">
                            <svg class="w-6 h-6" style="color:{{ $link['color'] }};"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800 group-hover:text-[{{ $link['color'] }}] transition-colors">
                                {{ $link['label'] }}
                            </p>
                            <p class="text-sm text-gray-500 mt-1 leading-relaxed">{{ $link['desc'] }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

        </main>
    </div>

    <script>
    (function () {
        const labels = @json(array_keys($tiketPerStatus));
        const data   = @json(array_values($tiketPerStatus));
        const colors = ['#01458E','#0263C8','#38BDF8','#D97706','#EF4444','#059669','#DC2626'];
        const total  = data.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('tiketChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: total > 0 ? data : [1],
                    backgroundColor: total > 0 ? colors : ['#F3F4F6'],
                    borderWidth: 2, // Tambahan border putih di chart
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                cutout: '75%', // Diperbesar sedikit agar lebih tipis & elegan
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: 'Inter', size: 13 },
                        bodyFont: { family: 'Inter', size: 14, weight: 'bold' },
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} tiket`
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const { ctx, chartArea: { width, height, left, top } } = chart;
                    ctx.save();
                    ctx.font = 'bold 30px Inter'; // Font angka di-bold dan dibesarkan
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total, left + width / 2, top + height / 2 - 10);
                    ctx.font = '500 13px Inter'; // Font label sedikit dipertebal
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('Total Tiket', left + width / 2, top + height / 2 + 18);
                    ctx.restore();
                }
            }]
        });
    })();

    // Samakan tinggi card Log Aktivitas dengan card Distribusi Status Tiket
    (function syncCardHeight() {
        const donutCard    = document.getElementById('card-distribusi');
        const activityCard = document.getElementById('card-activity');
        if (!donutCard || !activityCard) return;

        function sync() {
            const h = donutCard.offsetHeight;
            activityCard.style.maxHeight = h + 'px';
            activityCard.style.height    = h + 'px';
        }

        // Jalankan setelah chart selesai render
        requestAnimationFrame(() => requestAnimationFrame(sync));
        window.addEventListener('resize', sync);
    })();
    </script>

</body>
</html>
