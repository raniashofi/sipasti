<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Admin Helpdesk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
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
<body class="bg-gradient-to-br from-[#F4F7FB] to-[#E9F0F8] min-h-screen text-gray-800">

    @include('layouts.sidebarAdminHelpdesk')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- Top bar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div>
                <h1 class="text-xl font-extrabold text-gray-900 tracking-tight">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Selamat datang, {{ $adminProfile?->nama_lengkap ?? Auth::user()->name }}
                </p>
            </div>
            <div class="hidden sm:block text-right">
                <p class="text-xs text-gray-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">{{ now()->format('H:i') }} WIB</p>
            </div>
        </header>

        <main class="flex-1 px-4 py-4 lg:px-8 lg:py-8 space-y-6 lg:space-y-8">

            {{-- ── Stat Cards ── --}}
            @php
            $cards = [
                [
                    'label' => 'Menunggu Verifikasi',
                    'value' => $stats['menunggu_verif'],
                    'desc'  => 'Tiket baru masuk, perlu tindakan',
                    'color' => '#D97706',
                    'bg'    => '#FEF3C7',
                    'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                    'route' => 'admin_helpdesk.tiket.menunggu',
                    'badge' => $stats['menunggu_verif'] > 0 ? 'Perlu Aksi' : null,
                    'badge_color' => '#D97706',
                    'badge_bg'    => '#FEF3C7',
                ],
                [
                    'label' => 'Panduan Remote',
                    'value' => $stats['panduan_remote'],
                    'desc'  => 'Tiket sedang ditangani via chat',
                    'color' => '#0263C8',
                    'bg'    => '#EBF3FF',
                    'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'route' => 'admin_helpdesk.tiket.panduan_remote',
                    'badge' => null,
                ],
                [
                    'label' => 'Eskalasi ke Teknisi',
                    'value' => $stats['eskalasi'],
                    'desc'  => 'Tiket yang diteruskan tim teknis',
                    'color' => '#7C3AED',
                    'bg'    => '#EDE9FE',
                    'icon'  => 'M13 10V3L4 14h7v7l9-11h-7z',
                    'route' => 'admin_helpdesk.tiket.distribusi',
                    'badge' => null,
                ],
                [
                    'label' => 'Tiket Selesai',
                    'value' => $stats['selesai'],
                    'desc'  => 'Tiket berhasil diselesaikan',
                    'color' => '#059669',
                    'bg'    => '#D1FAE5',
                    'icon'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'route' => 'admin_helpdesk.tiket.riwayat',
                    'badge' => null,
                ],
            ];
            @endphp

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-6 animate-fade-in-up">
                @foreach($cards as $card)
                <div class="bg-white rounded-2xl p-4 sm:p-6 flex items-center gap-3 sm:gap-5 shadow-sm border border-gray-100
                            hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 relative overflow-hidden group cursor-default">
                    <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-20 transition-transform duration-500 group-hover:scale-125"
                         style="background-color:{{ $card['color'] }};"></div>

                    <div class="w-10 h-10 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center shrink-0 relative z-10 transition-transform duration-300 group-hover:scale-110"
                         style="background-color:{{ $card['bg'] }};">
                        <svg class="w-5 h-5 sm:w-7 sm:h-7" style="color:{{ $card['color'] }};"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>

                    <div class="relative z-10 flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-1">
                            <p class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($card['value']) }}</p>
                            @if(!empty($card['badge']))
                            <span class="text-[9px] sm:text-[10px] font-bold px-1.5 sm:px-2 py-0.5 rounded-full shrink-0 mt-1 whitespace-nowrap"
                                  style="background-color:{{ $card['badge_bg'] }};color:{{ $card['badge_color'] }};">
                                {{ $card['badge'] }}
                            </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-gray-700 mt-0.5 leading-tight">{{ $card['label'] }}</p>
                        <p class="text-[10px] sm:text-xs text-gray-400 mt-0.5 truncate hidden sm:block">{{ $card['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Chart + Activity ── --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 animate-fade-in-up delay-100">

                {{-- Donut chart (2 cols) --}}
                <div id="card-distribusi" class="xl:col-span-2 bg-white rounded-2xl p-5 sm:p-7 shadow-sm border border-gray-100 flex flex-col self-start hover:shadow-md transition-shadow">
                    <h2 class="text-base font-bold text-gray-800">Distribusi Status Tiket</h2>
                    <p class="text-sm text-gray-400 mb-6 mt-1">Sebaran tiket yang sedang ditangani</p>

                    <div class="flex items-center justify-center relative">
                        <canvas id="tiketChart" width="200" height="200" class="max-h-[200px]"></canvas>
                    </div>

                    @php
                    $chartColors = ['#D97706','#EF4444','#0263C8','#7C3AED','#DC2626','#059669'];
                    $ci = 0;
                    @endphp
                    <div class="mt-6 space-y-2.5 px-1">
                        @foreach($tiketPerStatus as $label => $count)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 transition-transform group-hover:scale-125"
                                      style="background-color:{{ $chartColors[$ci] }};"></span>
                                <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">{{ $label }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-800 bg-gray-50 px-2.5 py-0.5 rounded-md">{{ $count }}</span>
                        </div>
                        @php $ci++; @endphp
                        @endforeach
                    </div>
                </div>

                {{-- Recent Activity (3 cols) --}}
                <div id="card-activity" class="xl:col-span-3 bg-white rounded-2xl p-5 sm:p-7 shadow-sm border border-gray-100 hover:shadow-md transition-shadow flex flex-col self-start overflow-hidden">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-50 shrink-0">
                        <div>
                            <h2 class="text-base font-bold text-gray-800">Log Aktivitas Terbaru</h2>
                            <p class="text-sm text-gray-400 mt-1">Riwayat tindakan Anda di sistem</p>
                        </div>
                        <a href="{{ Route::has('admin_helpdesk.log') ? route('admin_helpdesk.log') : '#' }}"
                           class="text-sm font-semibold hover:underline flex items-center gap-1 transition-colors" style="color:#01458E;">
                            Lihat Semua
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
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
                        <div class="relative border-l-2 border-gray-100 ml-4 space-y-5 mt-2">
                            @foreach($recentActivity as $log)
                            @php
                            $badgeMap = [
                                'login'    => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'label'=>'Login',    'dot'=>'bg-blue-500'],
                                'logout'   => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'label'=>'Logout',   'dot'=>'bg-gray-400'],
                                'create'   => ['bg'=>'bg-green-100',  'text'=>'text-green-700',  'label'=>'Buat',     'dot'=>'bg-green-500'],
                                'update'   => ['bg'=>'bg-yellow-100', 'text'=>'text-yellow-700', 'label'=>'Ubah',     'dot'=>'bg-yellow-500'],
                                'delete'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Hapus',    'dot'=>'bg-red-500'],
                                'approve'  => ['bg'=>'bg-teal-100',   'text'=>'text-teal-700',   'label'=>'Terima',   'dot'=>'bg-teal-500'],
                                'reject'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Revisi',   'dot'=>'bg-red-500'],
                                'escalate' => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'label'=>'Eskalasi', 'dot'=>'bg-purple-500'],
                                'transfer' => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'label'=>'Transfer', 'dot'=>'bg-orange-500'],
                            ];
                            $badge = $badgeMap[$log->jenis_aktivitas] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-600','label'=>$log->jenis_aktivitas,'dot'=>'bg-gray-400'];
                            @endphp
                            <div class="flex items-start gap-4 relative">
                                <div class="absolute -left-[25px] mt-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $badge['dot'] }} shadow-sm"></div>
                                <div class="flex-1 bg-gray-50/50 hover:bg-gray-50 rounded-xl p-3.5 border border-gray-100 transition-colors">
                                    <div class="flex items-center justify-between mb-1.5 flex-wrap gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-900">{{ $log->user?->name ?? 'Sistem' }}</span>
                                            <span class="text-[11px] px-2.5 py-0.5 rounded-md font-bold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                {{ $badge['label'] }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-400 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $log->waktu_eksekusi?->diffForHumans() ?? '-' }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 break-words">{{ $log->detail_tindakan ?? '-' }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Quick Access ── --}}
            <div class="animate-fade-in-up delay-200">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-base font-bold text-gray-800">Akses Cepat</h2>
                </div>

                @php
                $quickLinks = [
                    [
                        'label' => 'Menunggu Verif',
                        'desc'  => 'Tinjau & verifikasi tiket masuk',
                        'route' => 'admin_helpdesk.tiket.menunggu',
                        'color' => '#D97706',
                        'bg'    => '#FEF3C7',
                        'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                        'count' => $stats['menunggu_verif'],
                    ],
                    [
                        'label' => 'Panduan Remote',
                        'desc'  => 'Pantau chat & perbaikan remote',
                        'route' => 'admin_helpdesk.tiket.panduan_remote',
                        'color' => '#0263C8',
                        'bg'    => '#EBF3FF',
                        'icon'  => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                        'count' => $stats['panduan_remote'],
                    ],
                    [
                        'label' => 'Distribusi & Eskalasi',
                        'desc'  => 'Transfer & eskalasi ke tim teknis',
                        'route' => 'admin_helpdesk.tiket.distribusi',
                        'color' => '#7C3AED',
                        'bg'    => '#EDE9FE',
                        'icon'  => 'M13 10V3L4 14h7v7l9-11h-7z',
                        'count' => $stats['eskalasi'],
                    ],
                    [
                        'label' => 'Pustaka Solusi',
                        'desc'  => 'Referensi knowledge base OPD',
                        'route' => 'admin_helpdesk.pustaka',
                        'color' => '#059669',
                        'bg'    => '#D1FAE5',
                        'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253',
                        'count' => $stats['total_kb'],
                    ],
                ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                    @foreach($quickLinks as $link)
                    <a href="{{ Route::has($link['route']) ? route($link['route']) : '#' }}"
                       class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-gray-100 hover:-translate-y-1 hover:shadow-lg transition-all duration-300 group flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                             style="background-color:{{ $link['bg'] }};">
                            <svg class="w-6 h-6" style="color:{{ $link['color'] }};"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-bold text-gray-800">{{ $link['label'] }}</p>
                                @if($link['count'] > 0)
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full shrink-0"
                                      style="background-color:{{ $link['bg'] }};color:{{ $link['color'] }};">
                                    {{ $link['count'] }}
                                </span>
                                @endif
                            </div>
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
        const colors = ['#D97706','#EF4444','#0263C8','#7C3AED','#DC2626','#059669'];
        const total  = data.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('tiketChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: total > 0 ? data : [1],
                    backgroundColor: total > 0 ? colors : ['#F3F4F6'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 8,
                }]
            },
            options: {
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} tiket`
                        }
                    }
                },
                animation: { animateScale: true, animateRotate: true }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const { ctx, chartArea: { width, height, left, top } } = chart;
                    ctx.save();
                    ctx.font = 'bold 28px Inter';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total, left + width / 2, top + height / 2 - 10);
                    ctx.font = '500 12px Inter';
                    ctx.fillStyle = '#6B7280';
                    ctx.fillText('Total Tiket', left + width / 2, top + height / 2 + 16);
                    ctx.restore();
                }
            }]
        });
    })();

    // Perbaikan: Sync height hanya aktif di layar Desktop (xl) ke atas
    (function syncCardHeight() {
        const donutCard    = document.getElementById('card-distribusi');
        const activityCard = document.getElementById('card-activity');
        if (!donutCard || !activityCard) return;

        function sync() {
            if (window.innerWidth >= 1280) { // breakpoint xl di Tailwind
                const h = donutCard.offsetHeight;
                activityCard.style.maxHeight = h + 'px';
                activityCard.style.height    = h + 'px';
            } else {
                // Di layar kecil, beri max-height agar tetap bisa di-scroll tapi tidak memakan seluruh layar
                activityCard.style.maxHeight = '450px';
                activityCard.style.height    = 'auto';
            }
        }
        requestAnimationFrame(() => requestAnimationFrame(sync));
        window.addEventListener('resize', sync);
    })();
    </script>

</body>
</html>
