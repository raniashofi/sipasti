<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }

        body { background-color: #F4F6FA; }

        /* Fade-up animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fu  { animation: fadeUp 0.45s ease-out forwards; opacity: 0; }
        .fu1 { animation-delay: 0.04s; }
        .fu2 { animation-delay: 0.10s; }
        .fu3 { animation-delay: 0.16s; }
        .fu4 { animation-delay: 0.22s; }
        .fu5 { animation-delay: 0.28s; }
        .fu6 { animation-delay: 0.34s; }

        /* Card base */
        .card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #EAECF0;
            box-shadow: 0 1px 4px rgba(16,24,40,0.04);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .card:hover { box-shadow: 0 6px 24px rgba(1,69,142,0.09); }

        /* Stat card lift */
        .stat-card:hover { transform: translateY(-2px); }

        /* Primary button */
        .btn-blue {
            background-color: #01458E;
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            padding: 9px 18px;
            transition: background-color 0.18s, box-shadow 0.18s, transform 0.18s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-blue:hover {
            background-color: #013a78;
            box-shadow: 0 6px 18px rgba(1,69,142,0.25);
            transform: translateY(-1px);
        }

        /* Action card */
        .action-card {
            border-radius: 14px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
        }
        .action-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(1,69,142,0.12); }

        /* Status pill */
        .pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .pill-dot { width: 6px; height: 6px; border-radius: 50%; }

        /* Scrollbar for table */
        .table-wrap { overflow-x: auto; }
        .table-wrap::-webkit-scrollbar { height: 4px; }
        .table-wrap::-webkit-scrollbar-track { background: #f1f1f1; }
        .table-wrap::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

        /* Dot grid bg */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(1,69,142,0.08) 1.2px, transparent 1.2px);
            background-size: 22px 22px;
        }
    </style>
</head>
<body class="min-h-screen">

    {{-- Navbar --}}
    <div class="sticky top-0 z-30 shadow-sm">
        @include('layouts.topBarOpd')
    </div>

    <div class="max-w-screen-xl mx-auto px-6 lg:px-8 py-7 space-y-6">

        {{-- ── Hero Banner ── --}}
        <div class="fu fu1 relative overflow-hidden rounded-2xl text-white"
             style="background: linear-gradient(120deg, #01458E 0%, #0263C8 60%, #1d84c8 100%);">

            {{-- decorative circles --}}
            <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full opacity-10"
                 style="background: rgba(255,255,255,0.3);"></div>
            <div class="absolute -right-6 bottom-0 w-40 h-40 rounded-full opacity-10"
                 style="background: rgba(255,255,255,0.2);"></div>
            <div class="dot-grid absolute inset-0 opacity-30"></div>

            <div class="relative z-10 px-8 py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
                <div>
                    <p class="text-white/60 text-xs font-medium mb-1 tracking-wide">
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                    <h1 class="text-2xl font-bold leading-snug mb-1">
                        Selamat Datang,<br>
                        <span class="text-white/90 font-extrabold">{{ $opd->nama_opd }}</span>
                    </h1>
                    <p class="text-white/60 text-sm">
                        Kelola dan pantau seluruh pengaduan layanan TIK Anda di sini.
                    </p>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('opd.diagnosis.index') }}"
                       class="inline-flex items-center gap-2 bg-white font-semibold text-sm px-5 py-3 rounded-xl shadow-md transition hover:shadow-lg hover:-translate-y-0.5"
                       style="color: #01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Pengaduan
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            @php
            $statCards = [
                [
                    'label'   => 'Total Pengaduan',
                    'value'   => $stats['total'],
                    'badge'   => 'Semua',
                    'badgeCls'=> 'bg-slate-100 text-slate-500',
                    'iconBg'  => 'rgba(1,69,142,0.09)',
                    'iconClr' => '#01458E',
                    'icon'    => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                ],
                [
                    'label'   => 'Sedang Diproses',
                    'value'   => $stats['aktif'],
                    'badge'   => 'Aktif',
                    'badgeCls'=> 'bg-amber-50 text-amber-600',
                    'iconBg'  => 'rgba(245,158,11,0.10)',
                    'iconClr' => '#F59E0B',
                    'icon'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label'   => 'Perlu Revisi',
                    'value'   => $stats['revisi'],
                    'badge'   => 'Revisi',
                    'badgeCls'=> 'bg-red-50 text-red-500',
                    'iconBg'  => 'rgba(239,68,68,0.09)',
                    'iconClr' => '#EF4444',
                    'icon'    => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                ],
                [
                    'label'   => 'Pengaduan Selesai',
                    'value'   => $stats['selesai'],
                    'badge'   => 'Selesai',
                    'badgeCls'=> 'bg-emerald-50 text-emerald-600',
                    'iconBg'  => 'rgba(16,185,129,0.10)',
                    'iconClr' => '#10B981',
                    'icon'    => 'M5 13l4 4L19 7',
                ],
            ];
            @endphp

            @foreach($statCards as $i => $s)
            <div class="fu fu{{ $i+2 }} card stat-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                         style="background-color: {{ $s['iconBg'] }};">
                        <svg class="w-[18px] h-[18px]" style="color:{{ $s['iconClr'] }};"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="pill {{ $s['badgeCls'] }} text-[10px]">{{ $s['badge'] }}</span>
                </div>
                <p class="text-[32px] font-extrabold text-gray-900 leading-none mb-1">{{ $s['value'] }}</p>
                <p class="text-xs text-gray-400 font-medium">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- ── Main 2-col grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- LEFT col --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Quick Actions --}}
                <div class="fu fu3 card p-5">
                    <p class="text-[13px] font-semibold text-gray-700 mb-4">Aksi Cepat</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                        {{-- Buat Pengaduan --}}
                        <a href="{{ route('opd.diagnosis.index') }}" class="action-card"
                           style="background: linear-gradient(135deg,#01458E,#0263C8);">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                     stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-white">Buat Pengaduan</p>
                                <p class="text-[11px] text-white/60 mt-0.5">Laporkan insiden baru</p>
                            </div>
                        </a>

                        {{-- Diagnosis Mandiri --}}
                        <a href="{{ route('opd.bantuan') }}" class="action-card bg-white border border-gray-200">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 style="background-color: rgba(1,69,142,0.08);">
                                <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Diagnosis Mandiri</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Coba selesaikan sendiri</p>
                            </div>
                        </a>

                        {{-- Pusat Bantuan --}}
                        <a href="{{ route('opd.bantuan') }}" class="action-card bg-white border border-gray-200">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                 style="background-color: rgba(1,69,142,0.08);">
                                <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Pusat Bantuan</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Panduan & Knowledge Base</p>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Recent Tickets --}}
                <div class="fu fu4 card overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-4 rounded-full" style="background-color:#01458E;"></div>
                            <p class="text-[13px] font-semibold text-gray-800">Pengaduan Terbaru</p>
                        </div>
                        <a href="{{ route('opd.tiket.index') }}"
                           class="text-[12px] font-semibold hover:underline flex items-center gap-1"
                           style="color:#01458E;">
                            Lihat Semua
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="table-wrap">
                        <table class="w-full">
                            <thead>
                                <tr style="background-color:#F8FAFC;">
                                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">No. Tiket</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Judul Pengaduan</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Kategori</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            @php
                            $statusMap = [
                                'verifikasi_admin' => ['label'=>'Verifikasi Admin', 'pill'=>'bg-blue-50 text-blue-600',   'dot'=>'bg-blue-400'],
                                'perlu_revisi'     => ['label'=>'Perlu Revisi',     'pill'=>'bg-red-50 text-red-500',     'dot'=>'bg-red-400'],
                                'panduan_remote'   => ['label'=>'Panduan Remote',   'pill'=>'bg-purple-50 text-purple-600','dot'=>'bg-purple-400'],
                                'perbaikan_teknis' => ['label'=>'Perbaikan Teknis', 'pill'=>'bg-amber-50 text-amber-600', 'dot'=>'bg-amber-400'],
                                'rusak_berat'      => ['label'=>'Rusak Berat',      'pill'=>'bg-orange-50 text-orange-600','dot'=>'bg-orange-400'],
                                'selesai'          => ['label'=>'Selesai',          'pill'=>'bg-emerald-50 text-emerald-600','dot'=>'bg-emerald-400'],
                            ];
                            @endphp
                            <tbody class="divide-y divide-gray-50">
                                @forelse($tiketTerbaru as $tiket)
                                <tr class="hover:bg-gray-50/70 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-[11px] text-gray-400 bg-gray-100 px-2 py-0.5 rounded">
                                            #{{ strtoupper(substr($tiket->id, 0, 8)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 max-w-[200px]">
                                        <p class="text-[13px] font-medium text-gray-900 truncate">
                                            {{ $tiket->subjek_masalah ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-[12px] text-gray-500">{{ $tiket->lokasi ?? '-' }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-[12px] text-gray-400">—</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($tiket->latestStatus)
                                            @php $st = $statusMap[$tiket->latestStatus->status_tiket] ?? ['label'=>$tiket->latestStatus->status_tiket,'pill'=>'bg-gray-100 text-gray-500','dot'=>'bg-gray-400']; @endphp
                                            <span class="pill {{ $st['pill'] }}">
                                                <span class="pill-dot {{ $st['dot'] }}"></span>
                                                {{ $st['label'] }}
                                            </span>
                                        @else
                                            <span class="pill bg-slate-100 text-slate-500">
                                                <span class="pill-dot bg-slate-400"></span> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="#" class="text-[12px] font-semibold hover:underline" style="color:#01458E;">
                                            Detail →
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center"
                                                 style="background-color:rgba(1,69,142,0.07);">
                                                <svg class="w-6 h-6" style="color:#01458E;" fill="none"
                                                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold text-gray-700">Belum ada pengaduan</p>
                                            <p class="text-xs text-gray-400 max-w-xs">
                                                Buat pengaduan baru untuk melaporkan gangguan layanan TIK Anda.
                                            </p>
                                            <a href="{{ route('opd.diagnosis.index') }}" class="btn-blue mt-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Buat Pengaduan
                                            </a>
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
            <div class="space-y-5">

                {{-- Alur Pengaduan --}}
                <div class="fu fu3 card p-5">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-1.5 h-4 rounded-full" style="background-color:#01458E;"></div>
                        <p class="text-[13px] font-semibold text-gray-800">Alur Pengaduan</p>
                    </div>

                    @php
                    $steps = [
                        ['no'=>'1','label'=>'Pengaduan Dikirim',  'desc'=>'Tiket berhasil dibuat & dikirim.',       'clr'=>'#01458E','bg'=>'rgba(1,69,142,0.10)'],
                        ['no'=>'2','label'=>'Verifikasi Admin',   'desc'=>'Admin helpdesk meninjau tiket Anda.',   'clr'=>'#F59E0B','bg'=>'rgba(245,158,11,0.10)'],
                        ['no'=>'3','label'=>'Penanganan Teknisi', 'desc'=>'Tim teknis melakukan perbaikan.',        'clr'=>'#3B82F6','bg'=>'rgba(59,130,246,0.10)'],
                        ['no'=>'4','label'=>'Konfirmasi Selesai', 'desc'=>'Anda mengonfirmasi masalah teratasi.',  'clr'=>'#10B981','bg'=>'rgba(16,185,129,0.10)'],
                    ];
                    @endphp

                    <div class="space-y-4">
                        @foreach($steps as $s)
                        <div class="flex items-start gap-3">
                            {{-- number badge + connecting line --}}
                            <div class="flex flex-col items-center shrink-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold"
                                     style="background-color:{{ $s['bg'] }}; color:{{ $s['clr'] }};">
                                    {{ $s['no'] }}
                                </div>
                                @if(!$loop->last)
                                <div class="w-px flex-1 mt-1 mb-1" style="min-height:20px; background-color:#E5E7EB;"></div>
                                @endif
                            </div>
                            <div class="{{ $loop->last ? '' : 'pb-4' }}">
                                <p class="text-[12px] font-semibold text-gray-800">{{ $s['label'] }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5 leading-relaxed">{{ $s['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tips --}}
                <div class="fu fu4 card p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-1.5 h-4 rounded-full" style="background-color:#01458E;"></div>
                        <p class="text-[13px] font-semibold text-gray-800">Tips Pengaduan</p>
                    </div>
                    <ul class="space-y-3">
                        @php
                        $tips = [
                            'Coba diagnosis mandiri sebelum membuat tiket untuk masalah yang umum.',
                            'Isi formulir dengan detail lengkap agar tiket diproses lebih cepat.',
                            'Sertakan foto bukti kondisi perangkat yang bermasalah.',
                            'Segera konfirmasi tiket selesai setelah masalah teratasi.',
                        ];
                        @endphp
                        @foreach($tips as $tip)
                        <li class="flex items-start gap-2.5">
                            <svg class="w-3.5 h-3.5 mt-0.5 shrink-0" style="color:#01458E;"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                            </svg>
                            <p class="text-[12px] text-gray-500 leading-relaxed">{{ $tip }}</p>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="text-center py-5 mt-4 border-t border-gray-200/60 text-gray-400 text-[11px]">
        &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
    </footer>

</body>
</html>
