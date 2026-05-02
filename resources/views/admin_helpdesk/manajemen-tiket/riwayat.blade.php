<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Tiket — Admin Helpdesk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Perbaikan Style SOP Modal agar responsif dan tidak berantakan */
        #sopPreviewContent { font-size: 0.875rem; line-height: 1.8; color: #1f2937; word-wrap: break-word; overflow-wrap: break-word; }
        #sopPreviewContent h1, #sopPreviewContent h2, #sopPreviewContent h3, #sopPreviewContent h4, #sopPreviewContent h5, #sopPreviewContent h6 { font-weight: 700; margin: 1.25rem 0 0.75rem; color: #111827; line-height: 1.3; }
        #sopPreviewContent h1 { font-size: 1.5rem; } #sopPreviewContent h2 { font-size: 1.25rem; } #sopPreviewContent h3 { font-size: 1.125rem; }
        #sopPreviewContent p { margin-bottom: 1rem; }
        #sopPreviewContent strong { font-weight: 700; } #sopPreviewContent em { font-style: italic; } #sopPreviewContent u { text-decoration: underline; } #sopPreviewContent s { text-decoration: line-through; }
        #sopPreviewContent a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; transition: color 0.2s; } #sopPreviewContent a:hover { color: #003a70; border-color: #003a70; }
        #sopPreviewContent ul, #sopPreviewContent ol { padding-left: 1.5rem; margin-bottom: 1rem; } #sopPreviewContent li { margin-bottom: 0.375rem; }
        #sopPreviewContent ul { list-style-type: disc; } #sopPreviewContent ol { list-style-type: decimal; }
        #sopPreviewContent blockquote { border-left: 4px solid #01458E; padding-left: 1rem; margin: 1rem 0; color: #4b5563; font-style: italic; background: #f9fafb; padding-top: 0.5rem; padding-bottom: 0.5rem; }
        #sopPreviewContent code { background: #f3f4f6; color: #01458E; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem; word-break: break-all; }
        #sopPreviewContent pre { background: #1f2937; color: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1rem 0; font-size: 0.8rem; }
        #sopPreviewContent pre code { background: transparent; color: inherit; padding: 0; word-break: normal; }
        #sopPreviewContent img, #sopPreviewContent video { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        #sopPreviewContent table { width: 100%; border-collapse: collapse; margin: 1rem 0; display: block; overflow-x: auto; }
        #sopPreviewContent th, #sopPreviewContent td { border: 1px solid #e5e7eb; padding: 0.625rem; text-align: left; font-size: 0.8125rem; min-width: 120px; }
        #sopPreviewContent th { background-color: #f9fafb; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    @include('layouts.sidebarAdminHelpdesk')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col" x-data="tiketPage()" x-cloak>

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Riwayat Tiket</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket yang telah selesai ditangani</p>
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten utama (tabel + filter) ── --}}
            <div class="flex-1 flex flex-col overflow-hidden w-full">

                {{-- Filter --}}
                <div class="px-4 sm:px-6 pt-5 pb-2">
                    <form method="GET" action="{{ route('admin_helpdesk.tiket.riwayat') }}" id="filterFormRiwayat"
                          class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-3 sm:mb-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-2 sm:items-center">

                            {{-- Rekomendasi Penanganan Dropdown --}}
                            @php
                                $prioOptsRiwayat = ['' => 'Semua Rekomendasi', 'eskalasi' => 'Perlu Dieskalasi', 'admin' => 'Ditangani Admin'];
                                $prioSelRiwayat  = request('rekomendasi_penanganan') ?? '';
                                $prioLabelRiwayat = $prioOptsRiwayat[$prioSelRiwayat] ?? 'Semua Rekomendasi';
                            @endphp
                            <input type="hidden" name="rekomendasi_penanganan" id="prioInputRiwayat" value="{{ $prioSelRiwayat }}">
                            <div class="relative w-full sm:w-auto"
                                 x-data="{
                                    open: false,
                                    selected: '{{ $prioSelRiwayat }}',
                                    label: '{{ addslashes($prioLabelRiwayat) }}',
                                    choose(val, lbl) {
                                        this.selected = val; this.label = lbl;
                                        document.getElementById('prioInputRiwayat').value = val;
                                        this.open = false;
                                        document.getElementById('filterFormRiwayat').submit();
                                    }
                                 }"
                                 @click.outside="open = false">
                                <button type="button"
                                        class="flex items-center justify-between gap-2 px-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[180px] transition-colors hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{ 'border-blue-500 bg-blue-50': open }" @click="open = !open">
                                    <span class="flex items-center gap-1.5 truncate">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                                        </svg>
                                        <span x-text="label" class="truncate">{{ $prioLabelRiwayat }}</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden origin-top"
                                     x-show="open"
                                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     style="display:none;">
                                    @foreach($prioOptsRiwayat as $val => $lbl)
                                    <div class="flex items-center gap-2 px-3.5 py-2.5 sm:py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors {{ $prioSelRiwayat == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                         :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                         @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity" :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                        <span class="truncate">{{ $lbl }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Kategori Dropdown --}}
                            @php
                                $katOptsRiwayat = ['' => 'Semua Kategori'];
                                foreach($kategori as $kat) { $katOptsRiwayat[$kat->id] = $kat->nama_kategori; }
                                $katSelRiwayat   = request('kategori_id') ?? '';
                                $katLabelRiwayat = $katOptsRiwayat[$katSelRiwayat] ?? 'Semua Kategori';
                            @endphp
                            <input type="hidden" name="kategori_id" id="katInputRiwayat" value="{{ $katSelRiwayat }}">
                            <div class="relative w-full sm:w-auto"
                                 x-data="{
                                    open: false,
                                    selected: '{{ $katSelRiwayat }}',
                                    label: '{{ addslashes($katLabelRiwayat) }}',
                                    choose(val, lbl) {
                                        this.selected = val; this.label = lbl;
                                        document.getElementById('katInputRiwayat').value = val;
                                        this.open = false;
                                        document.getElementById('filterFormRiwayat').submit();
                                    }
                                 }"
                                 @click.outside="open = false">
                                <button type="button"
                                        class="flex items-center justify-between gap-2 px-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[180px] transition-colors hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{ 'border-blue-500 bg-blue-50': open }" @click="open = !open">
                                    <span class="flex items-center gap-1.5 truncate">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <span x-text="label" class="truncate">{{ $katLabelRiwayat }}</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden origin-top"
                                     x-show="open"
                                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     style="display:none;">
                                     <div class="max-h-60 overflow-y-auto">
                                        @foreach($katOptsRiwayat as $val => $lbl)
                                        <div class="flex items-center gap-2 px-3.5 py-2.5 sm:py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors {{ $katSelRiwayat == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                             :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                             @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity" :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                            <span class="truncate">{{ $lbl }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Search --}}
                            <div class="w-full sm:flex-1 sm:min-w-[200px] relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari ID tiket atau subjek..."
                                       oninput="clearTimeout(window._stRiwayat); window._stRiwayat = setTimeout(() => document.getElementById('filterFormRiwayat').submit(), 500)"
                                       class="w-full pl-9 pr-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>

                            {{-- Reset --}}
                            <a href="{{ route('admin_helpdesk.tiket.riwayat') }}"
                               class="flex items-center justify-center w-full sm:w-auto gap-1.5 px-4 py-2.5 sm:py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mx-4 sm:mx-6 mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mx-4 sm:mx-6 mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                {{-- Tabel --}}
                <div class="flex-1 overflow-auto px-4 sm:px-6 pb-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
                        <div class="overflow-x-auto flex-1">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/50">
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">ID Tiket</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Subjek Masalah</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Pengirim (OPD)</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">PIC</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status Akhir</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Selesai</th>
                                        <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($tikets as $tiket)
                                    @php
                                        $statusAkhir  = $tiket->latestStatus?->status_tiket;
                                        $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
                                        $teknisi      = $tiket->teknisiUtama?->timTeknis;
                                        $statusStyle  = match($statusAkhir) {
                                            'selesai'          => ['bg'=>'#D1FAE5','text'=>'#059669','border'=>'#A7F3D0','label'=>'Selesai'],
                                            'rusak_berat'      => ['bg'=>'#FEE2E2','text'=>'#DC2626','border'=>'#FECACA','label'=>'Rusak Berat'],
                                            'dibuka_kembali'   => ['bg'=>'#FEF3C7','text'=>'#D97706','border'=>'#FDE68A','label'=>'Dibuka Kembali'],
                                            'tiket_ditutup'    => ['bg'=>'#DBEAFE','text'=>'#1D4ED8','border'=>'#BFDBFE','label'=>'Tiket Ditutup'],
                                            default            => ['bg'=>'#F3F4F6','text'=>'#6B7280','border'=>'#E5E7EB','label'=>ucfirst(str_replace('_',' ',$statusAkhir ?? '—'))],
                                        };
                                        $tiketJson = json_encode([
                                            'id'                    => $tiket->id,
                                            'subjek_masalah'        => $tiket->subjek_masalah,
                                            'detail_masalah'        => $tiket->detail_masalah,
                                            'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                            'kategori_nama'         => $kategoriNama,
                                            'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                            'lokasi'                => $tiket->lokasi ?? '—',
                                            'foto_bukti'            => $tiket->foto_bukti,
                                            'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                            'teknisi_nama'          => $teknisi?->nama_lengkap ?? '—',
                                            'status_akhir'          => $statusStyle['label'],
                                            'status_akhir_bg'       => $statusStyle['bg'],
                                            'status_akhir_text'     => $statusStyle['text'],
                                            'catatan_status'        => $tiket->latestStatus?->catatan ?? '—',
                                            'rekomendasi'           => $tiket->latestStatus?->rekomendasi ?? '—',
                                            'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                            'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                            'selesai_at'            => $tiket->latestStatus?->created_at?->translatedFormat('d M Y H:i') . ' WIB',
                                            'sop_judul'             => $tiket->sopInternal?->nama_artikel_sop ?? null,
                                            'sop_konten'            => $tiket->sopInternal?->isi_konten ?? null,
                                        ]);
                                    @endphp
                                    <tr class="hover:bg-blue-50/50 cursor-pointer transition-colors"
                                        @click="openDetail({{ $tiketJson }})">
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="font-mono text-xs font-bold text-[#01458E] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                                #{{ Str::upper(substr($tiket->id, -8)) }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 min-w-[250px] max-w-sm">
                                            <p class="font-semibold text-gray-800 line-clamp-1">{{ $tiket->subjek_masalah }}</p>
                                            <p class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ $tiket->detail_masalah }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-gray-600 font-medium whitespace-nowrap">
                                            {{ Str::limit($tiket->opd?->nama_opd ?? '—', 30) }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($teknisi)
                                            <p class="text-sm font-semibold text-gray-800">{{ $teknisi->nama_lengkap }}</p>
                                            <p class="text-[11px] text-gray-500 mt-0.5">Teknisi Utama</p>
                                            @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Admin
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-[11px] font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 bg-gray-50">
                                                {{ $kategoriNama }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                                  style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }};border-color:{{ $statusStyle['border'] }};">
                                                @if($statusAkhir === 'selesai')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @endif
                                                {{ $statusStyle['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                            <p class="text-xs font-semibold text-gray-700">{{ $tiket->latestStatus?->created_at?->translatedFormat('d M Y') ?? '—' }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->latestStatus?->created_at?->format('H:i:s') }} WIB</p>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap" @click.stop>
                                            <div class="flex items-center justify-center">
                                                <button type="button" @click="openDetail({{ $tiketJson }})"
                                                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition-all shadow-sm focus:outline-none"
                                                        style="background:#01458E;">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    Detail
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="px-5 py-16 text-center">
                                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500 text-sm">Belum ada riwayat tiket</p>
                                                    <p class="text-xs text-gray-400 mt-1">Tiket yang sudah diselesaikan akan muncul di sini.</p>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination 10 Data --}}
                        @if(method_exists($tikets, 'links'))
                        <div class="px-5 py-4 border-t border-gray-100 w-full shrink-0">
                            {{ $tikets->appends(request()->query())->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Detail Drawer Overlay ── --}}
            <div x-show="selectedTiket && showDrawer"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeDetail()"
                 class="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm">
            </div>

            {{-- ── Detail Drawer ── --}}
            <div x-show="selectedTiket && showDrawer"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed right-0 top-0 h-screen bg-white z-[101] flex flex-col w-full sm:w-[450px] shadow-2xl"
                 @click.stop>

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur z-10">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Detail Riwayat Tiket</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="'Masuk: ' + (selectedTiket?.created_at_tgl ?? '') + ' · ' + (selectedTiket?.created_at_jam ?? '')"></p>
                    </div>
                    <button @click="closeDetail()" class="p-2 rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Drawer Body --}}
                <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-6" style="scrollbar-width:thin;">

                    {{-- Section 1: Informasi Tiket --}}
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Informasi Tiket</h4>

                        <div class="space-y-2.5">
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">ID Tiket</span>
                                <span class="text-sm font-bold text-[#01458E] font-mono text-right" x-text="'#' + selectedTiket?.id"></span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Rekomendasi</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold text-right border"
                                      :style="rekomendasiBadge(selectedTiket?.rekomendasi_penanganan)">
                                    <svg x-show="selectedTiket?.rekomendasi_penanganan === 'eskalasi'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    <svg x-show="selectedTiket?.rekomendasi_penanganan === 'admin'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="rekomendasiLabel(selectedTiket?.rekomendasi_penanganan)"></span>
                                </span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Status Akhir</span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border"
                                      :style="'background:'+selectedTiket?.status_akhir_bg+';color:'+selectedTiket?.status_akhir_text+';border-color:'+selectedTiket?.status_akhir_text+'30'">
                                    <svg x-show="selectedTiket?.status_akhir === 'Selesai'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="selectedTiket?.status_akhir === 'Rusak Berat'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    <svg x-show="selectedTiket?.status_akhir === 'Dibuka Kembali'" class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-text="selectedTiket?.status_akhir"></span>
                                </span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Pengirim (OPD)</span>
                                <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="selectedTiket?.opd_nama ?? '—'"></span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Kategori</span>
                                <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="selectedTiket?.kategori_nama ?? '—'"></span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Lokasi</span>
                                <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="selectedTiket?.lokasi ?? '—'"></span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">PIC Teknisi</span>
                                <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="selectedTiket?.teknisi_nama === '—' ? 'Admin' : selectedTiket?.teknisi_nama"></span>
                            </div>
                            <div class="flex justify-between items-start gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Selesai Pada</span>
                                <span class="text-xs font-bold text-gray-900 font-mono text-right" x-text="selectedTiket?.selesai_at ?? '—'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Catatan Penanganan --}}
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Catatan Penanganan</h4>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4">
                            <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.catatan_status || '—'"></p>
                        </div>
                    </div>

                    {{-- Section 3: Rekomendasi (Opsional dari Teknisi) --}}
                    <template x-if="selectedTiket?.rekomendasi && selectedTiket?.rekomendasi !== '—'">
                        <div>
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Rekomendasi Teknisi</h4>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4">
                                <p class="text-xs text-blue-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.rekomendasi || '—'"></p>
                            </div>
                        </div>
                    </template>

                    {{-- Section: SOP Internal --}}
                    <template x-if="selectedTiket?.sop_judul">
                        <div>
                            <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-100">
                                <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400">SOP Internal Penanganan</h4>
                                <button @click="openSopPreview()" class="flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-orange-500 text-white hover:bg-orange-600 transition-colors focus:outline-none">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Pratinjau
                                </button>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 sm:p-4 flex gap-3 items-start">
                                <div class="w-8 h-8 rounded-lg bg-orange-500 shrink-0 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-orange-600 mb-0.5">Panduan SOP</p>
                                    <p class="text-xs font-semibold text-orange-900 leading-snug break-words" x-text="selectedTiket?.sop_judul"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Section 4: Detail Masalah Awal --}}
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Detail Masalah Awal</h4>

                        <div class="flex justify-between items-start gap-4 mb-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Subjek</span>
                            <span class="text-xs font-semibold text-gray-900 text-right leading-relaxed" x-text="selectedTiket?.subjek_masalah"></span>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4 mb-3">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kronologi Masalah</div>
                            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.detail_masalah || '—'"></p>
                        </div>

                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 sm:p-4">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-orange-700 mb-1.5">Spesifikasi Perangkat</div>
                            <p class="text-xs text-orange-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.spesifikasi_perangkat || '—'"></p>
                        </div>
                    </div>

                    {{-- Section 5: Foto Bukti --}}
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Foto Bukti Lampiran</h4>
                        <template x-if="selectedTiket?.foto_bukti?.length > 0">
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                                <template x-for="(foto, fi) in selectedTiket.foto_bukti" :key="fi">
                                    <div class="rounded-xl overflow-hidden border border-gray-200 cursor-pointer relative aspect-square group"
                                         @click="activeFoto = foto; showFoto = true">
                                        <img :src="'/storage/' + foto" :alt="'Foto ' + (fi+1)" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white text-[10px] font-bold">Perbesar</span>
                                        </div>
                                        <span class="absolute bottom-1.5 left-1.5 text-[9px] bg-black/60 text-white px-1.5 py-0.5 rounded font-bold" x-text="fi+1"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedTiket?.foto_bukti?.length">
                            <div class="h-24 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50/50">
                                <span class="text-xs text-gray-400 font-medium">Tidak ada lampiran foto</span>
                            </div>
                        </template>
                    </div>

                    <div class="h-4"></div>
                </div>

                {{-- Drawer Footer: Status Akhir --}}
                <div class="shrink-0 p-4 sm:p-5 border-t border-gray-100 bg-white">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Status Penanganan Akhir</h4>
                    <div class="flex items-center gap-3 p-3 rounded-xl border"
                         :style="'background:' + selectedTiket?.status_akhir_bg + '20; border-color:' + selectedTiket?.status_akhir_bg">
                        <svg x-show="selectedTiket?.status_akhir === 'Selesai'" class="w-5 h-5 shrink-0" :style="'color:' + selectedTiket?.status_akhir_text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="selectedTiket?.status_akhir !== 'Selesai'" class="w-5 h-5 shrink-0" :style="'color:' + selectedTiket?.status_akhir_text" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>

                        <div>
                            <p class="text-xs font-bold" :style="'color:' + selectedTiket?.status_akhir_text" x-text="selectedTiket?.status_akhir ?? 'Selesai'"></p>
                            <p class="text-[11px] text-gray-500 mt-0.5" x-text="'Waktu Selesai: ' + (selectedTiket?.selesai_at ?? '—')"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Modals ── --}}

            {{-- Overlay Perbesar Foto --}}
            <div x-show="showFoto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showFoto = false; activeFoto = null"
                 class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-4 sm:p-6"
                 x-cloak>
                <img :src="activeFoto ? '/storage/' + activeFoto : ''"
                     class="max-w-full max-h-full rounded-xl shadow-2xl object-contain" @click.stop>
                <button class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/50 hover:text-white p-2 focus:outline-none" @click="showFoto = false; activeFoto = null">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal: Preview SOP Overlay --}}
            <div x-show="sopPreviewOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sopPreviewOpen = false"
                 class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[110] p-0 sm:p-4"
                 x-cloak></div>

            {{-- Modal: Preview SOP Content --}}
            <div x-show="sopPreviewOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 flex items-end sm:items-center justify-center z-[111] p-0 sm:p-4 pointer-events-none"
                 x-cloak>
                <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-3xl h-[85vh] sm:h-[90vh] max-h-[800px] overflow-hidden flex flex-col transform transition-all pointer-events-auto"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-5 py-4 sm:px-8 sm:py-5 border-b border-gray-100 shrink-0 bg-white">
                        <h2 class="text-base sm:text-lg font-bold text-gray-900">Pratinjau SOP</h2>
                        <button @click="sopPreviewOpen = false" class="p-2 -mr-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded-lg transition-colors focus:outline-none">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Content --}}
                    <div class="flex-1 overflow-y-auto px-5 py-5 sm:px-8 sm:py-8">
                        {{-- Badge + Title --}}
                        <div class="mb-5 sm:mb-6">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-red-500 uppercase tracking-wider mb-2">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Internal</span>
                            </div>
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 leading-tight break-words" x-text="sopPreviewTitle || 'SOP Internal'"></h1>
                        </div>

                        {{-- Meta --}}
                        <div class="mb-6 pb-5 border-b border-gray-100 text-sm">
                            <div class="flex flex-wrap gap-4 sm:gap-8">
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Visibilitas</p>
                                    <div class="flex items-center gap-1.5 font-bold text-gray-900 text-xs sm:text-sm">
                                        <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Internal</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-0.5">Status</p>
                                    <div class="flex items-center gap-1.5 font-bold text-green-600 text-xs sm:text-sm">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Published</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SOP Content Inject --}}
                        <div id="sopPreviewContent" class="w-full"></div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
    function tiketPage() {
        return {
            selectedTiket: null,
            showDrawer: false,
            showModal: '',
            showFoto: false,
            activeFoto: null,
            sopPreviewOpen: false,
            sopPreviewContent: '',
            sopPreviewTitle: '',

            openDetail(t) {
                this.selectedTiket = t;
                this.showDrawer = true;
            },
            closeDetail() {
                this.selectedTiket = null;
                this.showDrawer = false;
                this.showModal = '';
            },
            openSopPreview() {
                if (this.selectedTiket?.sop_judul) {
                    this.sopPreviewTitle = this.selectedTiket.sop_judul;
                    this.sopPreviewContent = this.selectedTiket.sop_konten || '<p class="text-gray-500 italic">Konten SOP tidak tersedia.</p>';
                    this.sopPreviewOpen = true;

                    setTimeout(() => {
                        document.getElementById('sopPreviewContent').innerHTML = this.sopPreviewContent;
                    }, 50);
                }
            },
            rekomendasiLabel(p) {
                return { eskalasi: 'Perlu Dieskalasi', admin: 'Ditangani Admin' }[p] ?? '—';
            },
            rekomendasiBadge(p) {
                if (!p) return 'background:#F3F4F6;color:#9CA3AF;border: 1px solid #E5E7EB;';
                const map = {
                    eskalasi: 'background:#FEF2F2;color:#DC2626;border: 1px solid #FECACA;',
                    admin:    'background:#EFF6FF;color:#1D4ED8;border: 1px solid #BFDBFE;',
                };
                return map[p] ?? 'background:#F3F4F6;color:#9CA3AF;border: 1px solid #E5E7EB;';
            },
        };
    }
    </script>
</body>
</html>
