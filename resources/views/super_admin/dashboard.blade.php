<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    {{-- Main content offset by sidebar --}}
    <div class="ml-64 min-h-screen flex flex-col">

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Dashboard</h1>
                <p class="text-xs text-gray-400 mt-0.5">Selamat datang kembali, Super Admin</p>
            </div>
        </header>

        <main class="flex-1 px-8 py-7 space-y-7">

            {{-- ── Stat Cards ── --}}
            @php
            $cards = [
                [
                    'label' => 'Total OPD',
                    'value' => $stats['total_opd'],
                    'color' => '#01458E',
                    'bg'    => '#EEF3F9',
                    'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                ],
                [
                    'label' => 'Pengguna Internal',
                    'value' => $stats['total_internal'],
                    'color' => '#0263C8',
                    'bg'    => '#EBF3FF',
                    'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                ],
                [
                    'label' => 'Total Tiket',
                    'value' => $stats['total_tiket'],
                    'color' => '#D97706',
                    'bg'    => '#FEF3C7',
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                ],
                [
                    'label' => 'Knowledge Base',
                    'value' => $stats['total_kb'],
                    'color' => '#059669',
                    'bg'    => '#D1FAE5',
                    'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                ],
            ];
            @endphp

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-5">
                @foreach($cards as $card)
                <div class="bg-white rounded-2xl p-5 flex items-center gap-4 shadow-sm border border-gray-50">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                         style="background-color:{{ $card['bg'] }};">
                        <svg class="w-6 h-6" style="color:{{ $card['color'] }};"
                             fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $card['label'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ── Chart + Activity ── --}}
            <div class="grid grid-cols-1 xl:grid-cols-5 gap-5">

                {{-- Donut chart (2 cols) --}}
                <div class="xl:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-gray-50">
                    <h2 class="text-sm font-bold text-gray-800 mb-1">Distribusi Status Tiket</h2>
                    <p class="text-xs text-gray-400 mb-5">Sebaran tiket berdasarkan status saat ini</p>
                    <div class="flex items-center justify-center">
                        <canvas id="tiketChart" width="200" height="200"></canvas>
                    </div>
                    @php
                    $chartColors = ['#01458E','#0263C8','#38BDF8','#D97706','#EF4444','#059669'];
                    $ci = 0;
                    @endphp
                    <div class="mt-5 space-y-2">
                        @foreach($tiketPerStatus as $label => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                      style="background-color:{{ $chartColors[$ci] }};"></span>
                                <span class="text-xs text-gray-600">{{ $label }}</span>
                            </div>
                            <span class="text-xs font-semibold text-gray-800">{{ $count }}</span>
                        </div>
                        @php $ci++; @endphp
                        @endforeach
                    </div>
                </div>

                {{-- Recent Activity (3 cols) --}}
                <div class="xl:col-span-3 bg-white rounded-2xl p-6 shadow-sm border border-gray-50">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <h2 class="text-sm font-bold text-gray-800">Log Aktivitas Terbaru</h2>
                            <p class="text-xs text-gray-400 mt-0.5">8 aktivitas terakhir di sistem</p>
                        </div>
                        <a href="{{ route('super_admin.audit') }}"
                           class="text-xs font-semibold hover:underline" style="color:#01458E;">
                            Lihat Semua →
                        </a>
                    </div>

                    @if($recentActivity->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-gray-300">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm">Belum ada aktivitas tercatat</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($recentActivity as $log)
                        @php
                        $badgeMap = [
                            'login'    => ['bg'=>'bg-blue-50',   'text'=>'text-blue-600',  'label'=>'Login'],
                            'logout'   => ['bg'=>'bg-gray-100',  'text'=>'text-gray-500',  'label'=>'Logout'],
                            'create'   => ['bg'=>'bg-green-50',  'text'=>'text-green-600', 'label'=>'Buat'],
                            'update'   => ['bg'=>'bg-yellow-50', 'text'=>'text-yellow-600','label'=>'Ubah'],
                            'delete'   => ['bg'=>'bg-red-50',    'text'=>'text-red-600',   'label'=>'Hapus'],
                            'escalate' => ['bg'=>'bg-orange-50', 'text'=>'text-orange-600','label'=>'Eskalasi'],
                            'approve'  => ['bg'=>'bg-teal-50',   'text'=>'text-teal-600',  'label'=>'Setujui'],
                            'reject'   => ['bg'=>'bg-red-50',    'text'=>'text-red-500',   'label'=>'Tolak'],
                        ];
                        $badge = $badgeMap[$log->jenis_aktivitas] ?? ['bg'=>'bg-gray-100','text'=>'text-gray-500','label'=>$log->jenis_aktivitas];
                        @endphp
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                                 style="background-color:#EEF3F9;">
                                <svg class="w-4 h-4" style="color:#01458E;" fill="none" stroke="currentColor"
                                     stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-800 truncate">
                                        {{ $log->user?->name ?? 'Sistem' }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-wide">
                                        {{ $log->role_pelaku }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $log->detail_tindakan ?? '-' }}</p>
                            </div>
                            <span class="text-[10px] text-gray-300 shrink-0 mt-0.5">
                                {{ $log->waktu_eksekusi?->diffForHumans() ?? '-' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── Quick Access ── --}}
            <div>
                <h2 class="text-sm font-bold text-gray-700 mb-4">Akses Cepat</h2>
                @php
                $quickLinks = [
                    [
                        'label' => 'Manajemen Pengguna',
                        'desc'  => 'Kelola akun OPD & pengguna internal',
                        'route' => 'super_admin.pengguna.opd',
                        'color' => '#01458E',
                        'bg'    => '#EEF3F9',
                        'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label' => 'Konfigurasi Sistem',
                        'desc'  => 'Kategori gejala & alur diagnosis',
                        'route' => 'super_admin.konfigurasi.kategori',
                        'color' => '#0263C8',
                        'bg'    => '#EBF3FF',
                        'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                    ],
                    [
                        'label' => 'Pustaka Pengetahuan',
                        'desc'  => 'Kelola konten knowledge base',
                        'route' => 'super_admin.pustaka.opd',
                        'color' => '#059669',
                        'bg'    => '#D1FAE5',
                        'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                    ],
                    [
                        'label' => 'Keamanan & Audit',
                        'desc'  => 'Log aktivitas & keamanan sistem',
                        'route' => 'super_admin.audit',
                        'color' => '#7C3AED',
                        'bg'    => '#EDE9FE',
                        'icon'  => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    ],
                ];
                @endphp

                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                    @foreach($quickLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="bg-white rounded-2xl p-5 shadow-sm border border-gray-50 hover:-translate-y-0.5
                              hover:shadow-md transition-all group">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-4"
                             style="background-color:{{ $link['bg'] }};">
                            <svg class="w-5 h-5" style="color:{{ $link['color'] }};"
                                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-800 group-hover:text-[#01458E] transition-colors">
                            {{ $link['label'] }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1 leading-relaxed">{{ $link['desc'] }}</p>
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
        const colors = ['#01458E','#0263C8','#38BDF8','#D97706','#EF4444','#059669'];
        const total  = data.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('tiketChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: total > 0 ? data : [1],
                    backgroundColor: total > 0 ? colors : ['#E5E7EB'],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} tiket`
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw(chart) {
                    const { ctx, chartArea: { width, height, left, top } } = chart;
                    ctx.save();
                    ctx.font = 'bold 24px Inter';
                    ctx.fillStyle = '#111827';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(total, left + width / 2, top + height / 2 - 8);
                    ctx.font = '11px Inter';
                    ctx.fillStyle = '#9CA3AF';
                    ctx.fillText('Total Tiket', left + width / 2, top + height / 2 + 14);
                    ctx.restore();
                }
            }]
        });
    })();
    </script>

</body>
</html>
