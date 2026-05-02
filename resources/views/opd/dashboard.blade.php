<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        body { background-color: #F4F6FA; }

        /* Fade-up animation */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fu  { animation: fadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .fu1 { animation-delay: 0.04s; }
        .fu2 { animation-delay: 0.10s; }
        .fu3 { animation-delay: 0.16s; }
        .fu4 { animation-delay: 0.22s; }
        .fu5 { animation-delay: 0.28s; }

        /* Card base */
        .card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #EAECF0;
            box-shadow: 0 2px 6px rgba(16,24,40,0.02);
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }
        .card:hover { box-shadow: 0 10px 30px rgba(1,69,142,0.06); }

        /* Stat card lift */
        .stat-card:hover { transform: translateY(-3px); }

        /* Primary button */
        .btn-blue {
            background-color: #01458E;
            color: #fff;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
            padding: 10px 20px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-blue:hover {
            background-color: #013a78;
            box-shadow: 0 6px 16px rgba(1,69,142,0.25);
            transform: translateY(-1px);
        }

        /* Status pill */
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

        /* Scrollbar for table */
        .table-wrap { overflow-x: auto; }
        .table-wrap::-webkit-scrollbar { height: 6px; }
        .table-wrap::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
        .table-wrap::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Dot grid bg */
        .dot-grid {
            background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    {{-- Navbar --}}
    <div class="sticky top-0 z-30 shadow-sm border-b border-gray-100">
        @include('layouts.topBarOpd')
    </div>

    <main class="flex-1 max-w-screen-xl w-full mx-auto px-5 md:px-8 py-8 space-y-7">

        {{-- ── Hero Banner ── --}}
        <div class="fu fu1 relative overflow-hidden rounded-3xl text-white shadow-lg"
             style="background: linear-gradient(135deg, #01458E 0%, #0263C8 60%, #2A93D5 100%);">

            {{-- decorative circles --}}
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full opacity-10 bg-white blur-2xl"></div>
            <div class="absolute -left-10 -bottom-10 w-40 h-40 rounded-full opacity-10 bg-white blur-xl"></div>
            <div class="dot-grid absolute inset-0 opacity-40"></div>

            <div class="relative z-10 px-8 py-9 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-blue-100 text-xs font-semibold uppercase tracking-widest mb-2">
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </p>
                    <h1 class="text-3xl md:text-4xl font-bold leading-tight mb-2">
                        Selamat Datang,<br>
                        <span class="text-white font-extrabold">{{ $opd?->nama_opd ?? 'OPD' }}</span>
                    </h1>
                    <p class="text-blue-50 text-sm md:text-base max-w-xl">
                        Kelola, pantau, dan selesaikan seluruh pengaduan layanan TIK Anda dengan mudah melalui dasbor ini.
                    </p>
                </div>
                <div class="shrink-0 mt-2 md:mt-0">
                    <a href="{{ route('opd.diagnosis.index') }}"
                       class="inline-flex items-center gap-2 bg-white text-[#01458E] font-bold text-sm px-6 py-3.5 rounded-xl shadow-md transition-all hover:shadow-xl hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Buat Pengaduan Baru
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
            @php
            $statCards = [
                ['label' => 'Total Pengaduan',   'value' => $stats['total'],   'badge' => 'Semua',   'badgeCls'=> 'bg-slate-100 text-slate-500',   'iconBg' => 'rgba(1,69,142,0.08)', 'iconClr' => '#01458E', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ['label' => 'Sedang Diproses',   'value' => $stats['aktif'],   'badge' => 'Aktif',   'badgeCls'=> 'bg-amber-50 text-amber-600',    'iconBg' => 'rgba(245,158,11,0.12)', 'iconClr' => '#D97706', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Perlu Revisi',      'value' => $stats['revisi'],  'badge' => 'Revisi',  'badgeCls'=> 'bg-red-50 text-red-500',        'iconBg' => 'rgba(239,68,68,0.10)',  'iconClr' => '#DC2626', 'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
                ['label' => 'Pengaduan Selesai', 'value' => $stats['selesai'], 'badge' => 'Selesai', 'badgeCls'=> 'bg-emerald-50 text-emerald-600','iconBg' => 'rgba(16,185,129,0.12)','iconClr' => '#059669', 'icon' => 'M5 13l4 4L19 7'],
            ];
            @endphp

            @foreach($statCards as $i => $s)
            <div class="fu fu{{ $i+2 }} card stat-card p-6 flex flex-col justify-between">
                <div class="flex items-start justify-between mb-5">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background-color: {{ $s['iconBg'] }};">
                        <svg class="w-5 h-5" style="color:{{ $s['iconClr'] }};" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="pill {{ $s['badgeCls'] }} text-[10px]">{{ $s['badge'] }}</span>
                </div>
                <div>
                    <p class="text-3xl font-extrabold text-gray-900 leading-none mb-1.5">{{ $s['value'] }}</p>
                    <p class="text-xs text-gray-500 font-medium">{{ $s['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- ── Main 2-col grid ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT col (Table) --}}
            <div class="lg:col-span-2">
                <div class="fu fu4 card overflow-hidden flex flex-col h-full">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Pengaduan Terbaru</h2>
                        </div>
                        <a href="{{ route('opd.tiket.index') }}" class="text-xs font-semibold text-[#01458E] hover:underline flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                            Lihat Semua
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="table-wrap flex-1">
                        <table class="w-full whitespace-nowrap">
                            <thead>
                                <tr class="bg-gray-50/80 border-b border-gray-100">
                                    <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">No. Tiket</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Judul Pengaduan</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3.5"></th>
                                </tr>
                            </thead>
                            @php
                            $statusMap = [
                                'verifikasi_admin' => ['label'=>'Verifikasi Admin', 'pill'=>'bg-blue-50 text-blue-600',   'dot'=>'bg-blue-500'],
                                'perlu_revisi'     => ['label'=>'Perlu Revisi',     'pill'=>'bg-red-50 text-red-600',     'dot'=>'bg-red-500'],
                                'panduan_remote'   => ['label'=>'Panduan Remote',   'pill'=>'bg-purple-50 text-purple-600','dot'=>'bg-purple-500'],
                                'perbaikan_teknis' => ['label'=>'Perbaikan Teknis', 'pill'=>'bg-amber-50 text-amber-600', 'dot'=>'bg-amber-500'],
                                'rusak_berat'      => ['label'=>'Rusak Berat',      'pill'=>'bg-orange-50 text-orange-600','dot'=>'bg-orange-500'],
                                'selesai'          => ['label'=>'Selesai',          'pill'=>'bg-emerald-50 text-emerald-600','dot'=>'bg-emerald-500'],
                            ];
                            @endphp
                            <tbody class="divide-y divide-gray-100">
                                @forelse($tiketTerbaru as $tiket)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <span class="font-mono text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md border border-gray-200">
                                            #{{ strtoupper(substr($tiket->id, 0, 8)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 max-w-[220px]">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $tiket->subjek_masalah ?? '-' }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->created_at->diffForHumans() }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-gray-600 font-medium">{{ $tiket->kategori?->nama_kategori ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($tiket->latestStatus)
                                            @php $st = $statusMap[$tiket->latestStatus->status_tiket] ?? ['label'=>$tiket->latestStatus->status_tiket,'pill'=>'bg-gray-100 text-gray-600','dot'=>'bg-gray-400']; @endphp
                                            <span class="pill {{ $st['pill'] }}">
                                                <span class="pill-dot {{ $st['dot'] }}"></span>
                                                {{ $st['label'] }}
                                            </span>
                                        @else
                                            <span class="pill bg-slate-100 text-slate-600">
                                                <span class="pill-dot bg-slate-400"></span> Menunggu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="#" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-400 hover:text-[#01458E] hover:bg-blue-50 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </a>
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
                                                <p class="text-sm font-bold text-gray-800">Belum ada pengaduan</p>
                                                <p class="text-xs text-gray-500 mt-1">Laporan pengaduan Anda akan muncul di sini.</p>
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

            {{-- RIGHT col (Alur & Tips) --}}
            <div class="space-y-6">

                {{-- Alur Pengaduan --}}
                <div class="fu fu5 card p-6 bg-white">
                    <div class="flex items-center gap-2.5 mb-6">
                        <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Alur Pengaduan</h2>
                    </div>

                    @php
                    $steps = [
                        ['no'=>'1','label'=>'Diagnosis Mandiri',  'desc'=>'Jawab pertanyaan sistem untuk mendeteksi akar masalah.', 'clr'=>'#8B5CF6','bg'=>'rgba(139,92,246,0.10)'],
                        ['no'=>'2','label'=>'Panduan Solusi',     'desc'=>'Terapkan perbaikan awal berdasarkan instruksi sistem.', 'clr'=>'#14B8A6','bg'=>'rgba(20,184,166,0.10)'],
                        ['no'=>'3','label'=>'Pengajuan Tiket',    'desc'=>'Kirim tiket jika masalah tidak teratasi mandiri.', 'clr'=>'#01458E','bg'=>'rgba(1,69,142,0.10)'],
                        ['no'=>'4','label'=>'Verifikasi Admin',   'desc'=>'Admin Helpdesk memvalidasi tiket Anda.', 'clr'=>'#F59E0B','bg'=>'rgba(245,158,11,0.10)'],
                        ['no'=>'5','label'=>'Penanganan Teknisi', 'desc'=>'Tim teknis melakukan perbaikan di lapangan.', 'clr'=>'#3B82F6','bg'=>'rgba(59,130,246,0.10)'],
                        ['no'=>'6','label'=>'Konfirmasi Selesai', 'desc'=>'Anda mengonfirmasi layanan kembali normal.', 'clr'=>'#10B981','bg'=>'rgba(16,185,129,0.10)'],
                    ];
                    @endphp

                    <div class="space-y-0">
                        @foreach($steps as $s)
                        <div class="flex items-start gap-4 group">
                            <div class="flex flex-col items-center shrink-0 mt-0.5">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold transition-transform group-hover:scale-110"
                                     style="background-color:{{ $s['bg'] }}; color:{{ $s['clr'] }};">
                                    {{ $s['no'] }}
                                </div>
                                @if(!$loop->last)
                                <div class="w-px flex-1 my-1.5 bg-gray-200" style="min-height:24px;"></div>
                                @endif
                            </div>
                            <div class="{{ $loop->last ? '' : 'pb-5' }}">
                                <p class="text-[13px] font-bold text-gray-800 leading-none">{{ $s['label'] }}</p>
                                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $s['desc'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tips Pengaduan --}}
                <div class="fu fu5 card p-6 bg-white">
                    <div class="flex items-center gap-2.5 mb-5">
                        <div class="w-1.5 h-4 rounded-full bg-[#01458E]"></div>
                        <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Tips Pengaduan</h2>
                    </div>
                    <ul class="space-y-3.5">
                        @php
                        $tips = [
                            'Coba diagnosis mandiri sebelum membuat tiket untuk masalah yang umum.',
                            'Isi formulir dengan detail lengkap agar tiket diproses lebih cepat.',
                            'Sertakan foto bukti kondisi perangkat yang bermasalah.',
                            'Segera konfirmasi tiket selesai setelah masalah teratasi.',
                        ];
                        @endphp
                        @foreach($tips as $tip)
                        <li class="flex items-start gap-3">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 text-[#01458E]"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/>
                            </svg>
                            <p class="text-xs text-gray-500 leading-relaxed font-medium">{{ $tip }}</p>
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
        &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
    </footer>

</body>
</html>
