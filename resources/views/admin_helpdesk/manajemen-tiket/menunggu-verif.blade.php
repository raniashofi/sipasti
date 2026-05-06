<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menunggu Verifikasi — Admin Helpdesk</title>
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
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Menunggu Verifikasi</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket masuk yang perlu diverifikasi dan diproses</p>
            </div>
            <a href="{{ route('admin_helpdesk.tiket.menunggu.export') }}"
               class="inline-flex items-center self-start sm:self-auto gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
               style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten utama (tabel + filter) ── --}}
            <div class="flex-1 flex flex-col overflow-hidden w-full">

                {{-- Filter Bar --}}
                <div class="px-4 sm:px-6 pt-5 pb-2">
                    <form method="GET" action="{{ route('admin_helpdesk.tiket.menunggu') }}" id="filterFormMenunggu"
                          class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-3 sm:mb-5 shadow-sm">
                        <input type="hidden" name="tab" id="tabInput" :value="activeTab">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-2 sm:items-center">

                            {{-- Rekomendasi Penanganan Dropdown --}}
                            @php
                                $prioOpts  = ['' => 'Semua Rekomendasi', 'eskalasi' => 'Perlu Dieskalasi', 'admin' => 'Dapat Ditangani Admin'];
                                $prioSel   = request('rekomendasi_penanganan') ?? '';
                                $prioLabel = $prioOpts[$prioSel] ?? 'Semua Rekomendasi';
                            @endphp
                            <input type="hidden" name="rekomendasi_penanganan" id="prioInputMenunggu" value="{{ $prioSel }}">
                            <div class="relative w-full sm:w-auto"
                                 x-data="{
                                    open: false,
                                    selected: '{{ $prioSel }}',
                                    label: '{{ addslashes($prioLabel) }}',
                                    choose(val, lbl) {
                                        this.selected = val; this.label = lbl;
                                        document.getElementById('prioInputMenunggu').value = val;
                                        this.open = false;
                                        document.getElementById('filterFormMenunggu').submit();
                                    }
                                 }"
                                 @click.outside="open = false">
                                <button type="button"
                                        class="flex items-center justify-between gap-2 px-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[200px] transition-colors hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        :class="{ 'border-blue-500 bg-blue-50': open }" @click="open = !open">
                                    <span class="flex items-center gap-1.5 truncate">
                                        <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                                        </svg>
                                        <span x-text="label" class="truncate">{{ $prioLabel }}</span>
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
                                    @foreach($prioOpts as $val => $lbl)
                                    <div class="flex items-center gap-2 px-3.5 py-2.5 sm:py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors {{ $prioSel == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                         :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                         @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity" :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                        <span class="truncate">{{ $lbl }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Search --}}
                            <div class="w-full sm:flex-1 sm:min-w-[200px] relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari ID atau subjek tiket..."
                                       oninput="clearTimeout(window._stMenunggu); window._stMenunggu = setTimeout(() => document.getElementById('filterFormMenunggu').submit(), 500)"
                                       class="w-full pl-9 pr-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>

                            {{-- Reset --}}
                            <a href="{{ route('admin_helpdesk.tiket.menunggu') }}"
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

                {{-- Tab Navigation --}}
                <div class="px-4 sm:px-6 pt-2 pb-0 overflow-x-auto hide-scrollbar">
                    <div class="flex gap-0 border-b border-gray-200 min-w-max">
                        <button type="button" @click="setTab('baru')"
                                class="px-4 sm:px-5 py-3 text-sm font-medium transition-colors -mb-px flex items-center gap-2 border-b-2 whitespace-nowrap"
                                :class="activeTab === 'baru'
                                    ? 'border-[#01458E] text-[#01458E]'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'">
                            Tiket Baru
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold transition-colors"
                                  :class="activeTab === 'baru' ? 'bg-[#01458E] text-white' : 'bg-gray-100 text-gray-600'">
                                {{ count($tiketsVerif) }}
                            </span>
                        </button>
                        <button type="button" @click="setTab('dikembalikan')"
                                class="px-4 sm:px-5 py-3 text-sm font-medium transition-colors -mb-px flex items-center gap-2 border-b-2 whitespace-nowrap"
                                :class="activeTab === 'dikembalikan'
                                    ? 'border-orange-500 text-orange-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'">
                            Dikembalikan Teknisi
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold transition-colors"
                                  :class="activeTab === 'dikembalikan' ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-600'">
                                {{ count($tiketsDikembalikan) }}
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Tabel --}}
                <div class="flex-1 overflow-auto px-4 sm:px-6 py-4">

                    {{-- Tab: Tiket Baru --}}
                    <div x-show="activeTab === 'baru'" class="h-full flex flex-col">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">

                            {{-- Mobile cards --}}
                            <div class="md:hidden divide-y divide-gray-100 overflow-y-auto">
                                @forelse($tiketsVerif as $tiket)
                                @php
                                    $pS = match($tiket->rekomendasi_penanganan) {
                                        'eskalasi' => ['bg'=>'#FEF2F2','text'=>'#DC2626','label'=>'Perlu Dieskalasi'],
                                        default    => ['bg'=>'#EFF6FF','text'=>'#1D4ED8','label'=>'Ditangani Admin'],
                                    };
                                    $tJ = json_encode([
                                        'id'                    => $tiket->id,
                                        'subjek_masalah'        => $tiket->subjek_masalah,
                                        'detail_masalah'        => $tiket->detail_masalah,
                                        'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                        'kategori_nama'         => $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '—'),
                                        'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                        'lokasi'                => $tiket->lokasi ?? '—',
                                        'foto_bukti'            => $tiket->foto_bukti,
                                        'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                        'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                        'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                        'can_terima'            => $tiket->can_terima,
                                        'alasan_kembalikan'     => null,
                                        'sop_judul'             => $tiket->sopInternal?->nama_artikel_sop ?? null,
                                        'sop_konten'            => $tiket->sopInternal?->isi_konten ?? null,
                                    ]);
                                @endphp
                                <div class="px-4 py-4 hover:bg-gray-50/50 transition-colors cursor-pointer"
                                     @click="openDetail({{ $tJ }})">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-[#01458E] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">#{{ Str::upper(substr($tiket->id, -8)) }}</span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border shrink-0" style="background:{{ $pS['bg'] }};color:{{ $pS['text'] }};">{{ $pS['label'] }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800 mb-0.5 line-clamp-1">{{ $tiket->subjek_masalah }}</p>
                                    <p class="text-xs text-gray-400 mb-2 line-clamp-1">{{ $tiket->detail_masalah }}</p>
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-xs text-gray-400 min-w-0">
                                            <span class="line-clamp-1">{{ $tiket->opd?->nama_opd ?? '—' }}</span>
                                            <span class="text-gray-300 text-[11px]">{{ $tiket->created_at?->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0" @click.stop>
                                            <button @click.stop="setTiket({{ $tJ }}); showModal = 'transfer-pilih'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#FEF3C7;color:#D97706;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                            </button>
                                            @if($tiket->can_terima)
                                            <button @click.stop="setTiket({{ $tJ }}); showModal = 'terima'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#D1FAE5;color:#059669;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            @else
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50" style="background:#F3F4F6;color:#9CA3AF;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            @endif
                                            <button @click.stop="setTiket({{ $tJ }}); showModal = 'revisi'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#FEE2E2;color:#DC2626;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-4 py-10 text-center text-sm text-gray-400">Tidak ada tiket baru.</div>
                                @endforelse
                            </div>

                            <div class="hidden md:block overflow-x-auto flex-1">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-gray-50/50">
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">ID Tiket</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Subjek Masalah</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Pengirim (OPD)</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Rekomendasi</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Waktu Masuk</th>
                                            <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($tiketsVerif as $tiket)
                                        @php
                                            $prioritasStyle = match($tiket->rekomendasi_penanganan) {
                                                'eskalasi' => ['bg'=>'#FEF2F2','text'=>'#DC2626','border'=>'#FECACA','label'=>'Perlu Dieskalasi'],
                                                default    => ['bg'=>'#EFF6FF','text'=>'#1D4ED8','border'=>'#BFDBFE','label'=>'Ditangani Admin'],
                                            };
                                            $tiketJson = json_encode([
                                                'id'                    => $tiket->id,
                                                'subjek_masalah'        => $tiket->subjek_masalah,
                                                'detail_masalah'        => $tiket->detail_masalah,
                                                'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                                'kategori_nama'         => $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '—'),
                                                'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                                'lokasi'                => $tiket->lokasi ?? '—',
                                                'foto_bukti'            => $tiket->foto_bukti,
                                                'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                                'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                                'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                                'can_terima'            => $tiket->can_terima,
                                                'alasan_kembalikan'     => null,
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
                                                <span class="text-[11px] font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 bg-gray-50">
                                                    {{ $tiket->kb?->kategori?->nama_kategori ?? ($tiket->kategori?->nama_kategori ?? '—') }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                                      style="background:{{ $prioritasStyle['bg'] }};color:{{ $prioritasStyle['text'] }};border-color:{{ $prioritasStyle['border'] }};">
                                                    {{ $prioritasStyle['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                                <p class="text-xs font-semibold text-gray-700">{{ $tiket->created_at?->translatedFormat('d M Y') }}</p>
                                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->created_at?->format('H:i:s') }} WIB</p>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap" @click.stop>
                                                <div class="flex items-center justify-center gap-2">
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'transfer-pilih'"
                                                            title="Transfer / Eskalasi"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#FEF3C7;color:#D97706;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    @if($tiket->can_terima)
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'terima'"
                                                            title="Terima & Proses"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#D1FAE5;color:#059669;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                    @else
                                                    <div title="Bidang tidak sesuai atau tiket belum memiliki KB"
                                                         class="w-8 h-8 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50"
                                                         style="background:#F3F4F6;color:#9CA3AF;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    @endif
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'revisi'"
                                                            title="Minta Revisi"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#FEE2E2;color:#DC2626;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="px-5 py-16 text-center">
                                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                                    <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-500 text-sm">Tidak ada tiket baru</p>
                                                        <p class="text-xs text-gray-400 mt-1">Semua tiket sudah diproses atau belum ada pengajuan baru.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            @if(method_exists($tiketsVerif, 'links'))
                            <div class="px-5 py-4 border-t border-gray-100 w-full shrink-0">
                                {{ $tiketsVerif->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Tab: Dikembalikan Teknisi --}}
                    <div x-show="activeTab === 'dikembalikan'" class="h-full flex flex-col" style="display:none;">
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">

                            {{-- Mobile cards --}}
                            <div class="md:hidden divide-y divide-gray-100 overflow-y-auto">
                                @forelse($tiketsDikembalikan as $tiket)
                                @php
                                    $pS2 = match($tiket->rekomendasi_penanganan) {
                                        'eskalasi' => ['bg'=>'#FEF2F2','text'=>'#DC2626','label'=>'Perlu Dieskalasi'],
                                        default    => ['bg'=>'#EFF6FF','text'=>'#1D4ED8','label'=>'Ditangani Admin'],
                                    };
                                    $tJ2 = json_encode([
                                        'id'                    => $tiket->id,
                                        'subjek_masalah'        => $tiket->subjek_masalah,
                                        'detail_masalah'        => $tiket->detail_masalah,
                                        'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                        'kategori_nama'         => $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '—'),
                                        'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                        'lokasi'                => $tiket->lokasi ?? '—',
                                        'foto_bukti'            => $tiket->foto_bukti,
                                        'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                        'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                        'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                        'can_terima'            => $tiket->can_terima,
                                        'alasan_kembalikan'     => $tiket->alasan_kembalikan,
                                        'sop_judul'             => $tiket->sopInternal?->nama_artikel_sop ?? null,
                                        'sop_konten'            => $tiket->sopInternal?->isi_konten ?? null,
                                    ]);
                                @endphp
                                <div class="px-4 py-4 hover:bg-orange-50/50 transition-colors cursor-pointer"
                                     @click="openDetail({{ $tJ2 }})">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <span class="font-mono text-xs font-bold text-[#01458E] bg-blue-50 px-2 py-0.5 rounded border border-blue-100">#{{ Str::upper(substr($tiket->id, -8)) }}</span>
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full border shrink-0" style="background:{{ $pS2['bg'] }};color:{{ $pS2['text'] }};">{{ $pS2['label'] }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-800 mb-0.5 line-clamp-1">{{ $tiket->subjek_masalah }}</p>
                                    @if($tiket->alasan_kembalikan)
                                    <p class="text-xs font-semibold mb-1" style="color:#D97706;"><span class="mr-1">↩</span>{{ $tiket->alasan_kembalikan }}</p>
                                    @endif
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="text-xs text-gray-400 min-w-0">
                                            <span class="line-clamp-1">{{ $tiket->opd?->nama_opd ?? '—' }}</span>
                                            <span class="text-gray-300 text-[11px]">{{ $tiket->created_at?->translatedFormat('d M Y') }}</span>
                                        </div>
                                        <div class="flex gap-1.5 shrink-0" @click.stop>
                                            <button @click.stop="setTiket({{ $tJ2 }}); showModal = 'transfer-pilih'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#FEF3C7;color:#D97706;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                            </button>
                                            @if($tiket->can_terima)
                                            <button @click.stop="setTiket({{ $tJ2 }}); showModal = 'terima'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#D1FAE5;color:#059669;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                            @else
                                            <div class="w-8 h-8 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50" style="background:#F3F4F6;color:#9CA3AF;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            @endif
                                            <button @click.stop="setTiket({{ $tJ2 }}); showModal = 'revisi'"
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    style="background:#FEE2E2;color:#DC2626;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="px-4 py-10 text-center text-sm text-gray-400">Tidak ada tiket dikembalikan.</div>
                                @endforelse
                            </div>

                            <div class="hidden md:block overflow-x-auto flex-1">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="border-b border-orange-100 bg-orange-50/50">
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">ID Tiket</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Subjek Masalah</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Pengirim (OPD)</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Rekomendasi</th>
                                            <th class="px-5 py-4 text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Waktu Masuk</th>
                                            <th class="px-5 py-4 text-center text-xs font-semibold text-orange-700 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        @forelse($tiketsDikembalikan as $tiket)
                                        @php
                                            $prioritasStyle = match($tiket->rekomendasi_penanganan) {
                                                'eskalasi' => ['bg'=>'#FEF2F2','text'=>'#DC2626','border'=>'#FECACA','label'=>'Perlu Dieskalasi'],
                                                default    => ['bg'=>'#EFF6FF','text'=>'#1D4ED8','border'=>'#BFDBFE','label'=>'Ditangani Admin'],
                                            };
                                            $tiketJson = json_encode([
                                                'id'                    => $tiket->id,
                                                'subjek_masalah'        => $tiket->subjek_masalah,
                                                'detail_masalah'        => $tiket->detail_masalah,
                                                'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                                'kategori_nama'         => $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '—'),
                                                'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                                'lokasi'                => $tiket->lokasi ?? '—',
                                                'foto_bukti'            => $tiket->foto_bukti,
                                                'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                                'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                                'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                                'can_terima'            => $tiket->can_terima,
                                                'alasan_kembalikan'     => $tiket->alasan_kembalikan,
                                                'sop_judul'             => $tiket->sopInternal?->nama_artikel_sop ?? null,
                                                'sop_konten'            => $tiket->sopInternal?->isi_konten ?? null,
                                            ]);
                                        @endphp
                                        <tr class="hover:bg-orange-50/50 cursor-pointer transition-colors"
                                            @click="openDetail({{ $tiketJson }})">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="font-mono text-xs font-bold text-[#01458E] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                                    #{{ Str::upper(substr($tiket->id, -8)) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 min-w-[250px] max-w-sm">
                                                <p class="font-semibold text-gray-800 line-clamp-1">{{ $tiket->subjek_masalah }}</p>
                                                @if($tiket->alasan_kembalikan)
                                                <p class="text-xs truncate mt-1 font-semibold" style="color:#D97706;">
                                                    <span class="mr-1">↩</span> {{ $tiket->alasan_kembalikan }}
                                                </p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-4 text-gray-600 font-medium whitespace-nowrap">
                                                {{ Str::limit($tiket->opd?->nama_opd ?? '—', 30) }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="text-[11px] font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 bg-gray-50">
                                                    {{ $tiket->kb?->kategori?->nama_kategori ?? ($tiket->kategori?->nama_kategori ?? '—') }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                                      style="background:{{ $prioritasStyle['bg'] }};color:{{ $prioritasStyle['text'] }};border-color:{{ $prioritasStyle['border'] }};">
                                                    {{ $prioritasStyle['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                                <p class="text-xs font-semibold text-gray-700">{{ $tiket->created_at?->translatedFormat('d M Y') }}</p>
                                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->created_at?->format('H:i:s') }} WIB</p>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap" @click.stop>
                                                <div class="flex items-center justify-center gap-2">
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'transfer-pilih'"
                                                            title="Transfer / Eskalasi"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#FEF3C7;color:#D97706;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                                        </svg>
                                                    </button>
                                                    @if($tiket->can_terima)
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'terima'"
                                                            title="Terima & Proses"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#D1FAE5;color:#059669;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </button>
                                                    @else
                                                    <div title="Bidang tidak sesuai atau tiket belum memiliki KB"
                                                         class="w-8 h-8 rounded-lg flex items-center justify-center cursor-not-allowed opacity-50"
                                                         style="background:#F3F4F6;color:#9CA3AF;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                    </div>
                                                    @endif
                                                    <button type="button"
                                                            @click.stop="setTiket({{ $tiketJson }}); showModal = 'revisi'"
                                                            title="Minta Revisi"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                            style="background:#FEE2E2;color:#DC2626;">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="px-5 py-16 text-center">
                                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                                    <div class="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center border border-orange-100">
                                                        <svg class="w-8 h-8 text-orange-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-500 text-sm">Tidak ada tiket dikembalikan</p>
                                                        <p class="text-xs text-gray-400 mt-1">Belum ada tiket yang dikembalikan oleh tim teknis.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            @if(method_exists($tiketsDikembalikan, 'links'))
                            <div class="px-5 py-4 border-t border-gray-100 w-full shrink-0">
                                {{ $tiketsDikembalikan->appends(request()->query())->links() }}
                            </div>
                            @endif
                        </div>
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
                 class="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm"
                 style="display:none;">
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
                 style="display:none;"
                 @click.stop>

                {{-- Drawer Header --}}
                <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur z-10">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Detail Tiket</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="(selectedTiket?.created_at_tgl ?? '') + ' · ' + (selectedTiket?.created_at_jam ?? '')"></p>
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
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold text-right" :style="rekomendasiBadge(selectedTiket?.rekomendasi_penanganan)">
                                    <svg x-show="selectedTiket?.rekomendasi_penanganan === 'eskalasi'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    <svg x-show="selectedTiket?.rekomendasi_penanganan === 'admin'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="rekomendasiLabel(selectedTiket?.rekomendasi_penanganan)"></span>
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
                        </div>
                    </div>

                    {{-- Section: Alasan Dikembalikan --}}
                    <template x-if="selectedTiket?.alasan_kembalikan">
                        <div class="border-l-4 border-orange-500 bg-orange-50 rounded-r-xl p-3 sm:p-4 mt-4">
                            <h4 class="text-[10px] font-bold uppercase tracking-wider text-orange-600 mb-2 pb-1.5 border-b border-orange-200">Alasan Dikembalikan Teknisi</h4>
                            <div class="flex gap-2 items-start">
                                <span class="text-base leading-none mt-0.5 text-orange-600">↩</span>
                                <p class="text-xs text-orange-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.alasan_kembalikan"></p>
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

                    {{-- Section 2: Deskripsi & Spesifikasi --}}
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Detail Masalah</h4>

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

                    {{-- Section 3: Foto Bukti --}}
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

                {{-- Drawer Footer: Tindakan --}}
                <div class="shrink-0 p-4 sm:p-5 border-t border-gray-100 bg-white space-y-3">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Aksi Tindakan</h4>
                    <div class="flex flex-col gap-2.5">
                        <button @click="showModal = 'terima'"
                                :disabled="!selectedTiket?.can_terima"
                                :title="!selectedTiket?.can_terima ? 'Bidang tiket tidak sesuai dengan bidang Anda' : ''"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all focus:outline-none"
                                :class="selectedTiket?.can_terima
                                    ? 'bg-[#01458E] text-white hover:opacity-90 hover:shadow-md'
                                    : 'bg-gray-100 text-gray-400 cursor-not-allowed'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Terima & Proses Tiket
                        </button>
                        <div class="flex gap-2.5">
                            <button @click="showModal = 'transfer-pilih'"
                                    class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                ⇄ Transfer / Eskalasi
                            </button>
                            <button @click="showModal = 'revisi'"
                                    class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 focus:outline-none transition-colors">
                                ↩ Minta Revisi
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Modals ── --}}

            {{-- Overlay --}}
            <div x-show="showModal && showModal !== ''"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = ''"
                 class="fixed inset-0 z-[102] bg-black/40 backdrop-blur-sm"
                 style="display:none;"></div>

            {{-- Modal: Terima & Proses --}}
            <div x-show="showModal === 'terima'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 style="display:none;"
                 @click.self="showModal = ''">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 text-center relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Terima &amp; Proses Tiket</h3>
                    <p class="text-sm font-semibold mb-3 text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-6">Tiket akan masuk ke mode <strong>Panduan Remote (Chat)</strong>. OPD akan mendapat notifikasi.</p>
                    <form x-ref="formTerima" method="POST" action="#" class="flex gap-3"
                          @submit.prevent="submitForm($refs.formTerima, '/admin-helpdesk/tiket/' + selectedTiket.id + '/terima')">
                        @csrf
                        <button type="button" @click="showModal = ''"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 focus:outline-none"
                                style="background:#01458E;">
                            Konfirmasi Terima
                        </button>
                    </form>
                </div>
            </div>

            {{-- Modal: Minta Revisi --}}
            <div x-show="showModal === 'revisi'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 style="display:none;"
                 @click.self="showModal = ''">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Minta Revisi Tiket</h3>
                    <p class="text-sm font-semibold mb-2 text-center text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-xs text-gray-500 mb-5 text-center">OPD akan mendapat notifikasi untuk melengkapi data tiket.</p>
                    <form x-ref="formRevisi" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formRevisi, '/admin-helpdesk/tiket/' + selectedTiket.id + '/revisi')">
                        @csrf
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Alasan Revisi</label>
                            <textarea name="alasan_revisi" rows="4" required
                                      placeholder="Contoh: Foto bukti buram, mohon upload ulang foto yang lebih jelas."
                                      class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = ''"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 focus:outline-none"
                                    style="background:#DC2626;">
                                Kirim Permintaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Pilih Transfer atau Eskalasi --}}
            <div x-show="showModal === 'transfer-pilih'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 style="display:none;"
                 @click.self="showModal = ''">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 text-center relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Transfer/Eskalasi Tiket</h3>
                    <p class="text-sm font-semibold mb-3 text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-6">Transfer/Eskalasi tiket ke?</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center mb-4">
                        <button @click="showModal = 'transfer'"
                                class="w-full sm:flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 focus:outline-none"
                                style="background:#7C3AED;">
                            Admin Helpdesk
                        </button>
                        <button @click="showModal = 'eskalasi'; pendampingIds = []; tekUtamaId = ''"
                                class="w-full sm:flex-1 py-3 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 focus:outline-none"
                                style="background:#D97706;">
                            Tim Teknis
                        </button>
                    </div>
                    <button @click="showModal = ''"
                            class="w-full text-sm font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition-colors focus:outline-none">
                        Batal
                    </button>
                </div>
            </div>

            {{-- Modal: Transfer ke Admin Helpdesk --}}
            <div x-show="showModal === 'transfer'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 style="display:none;"
                 @click.self="showModal = ''">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Transfer Tiket</h3>
                    <p class="text-sm font-semibold mb-2 text-center text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-xs text-gray-500 mb-5 text-center">Transfer tiket ke Admin Helpdesk bidang lain</p>
                    <form x-ref="formTransfer" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formTransfer, '/admin-helpdesk/tiket/' + selectedTiket.id + '/transfer')">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Bidang Tujuan</label>
                            <select name="bidang_id" required
                                    class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Pilih bidang</option>
                                @foreach($bidangs as $bidang)
                                <option value="{{ $bidang->id }}">{{ $bidang->nama_bidang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Instruksi Khusus (opsional)</label>
                            <textarea name="instruksi" rows="3" placeholder="Masukkan instruksi khusus (opsional)..."
                                      class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = 'transfer-pilih'"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 focus:outline-none"
                                    style="background:#7C3AED;">
                                Transfer Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Eskalasi ke Tim Teknis --}}
            <div x-show="showModal === 'eskalasi'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 style="display:none;"
                 @click.self="showModal = ''">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Eskalasi ke Tim Teknis</h3>
                    <p class="text-sm font-semibold mb-2 text-center text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-xs text-gray-500 mb-5 text-center">Tugaskan teknisi untuk perbaikan langsung</p>
                    <form x-ref="formEskalasi" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formEskalasi, '/admin-helpdesk/tiket/' + selectedTiket.id + '/eskalasi')">
                        @csrf
                        {{-- Teknisi Utama --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teknisi Utama <span class="text-red-500">*</span></label>
                            <select name="teknisi_utama_id" required x-model="tekUtamaId"
                                    class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Pilih Teknisi</option>
                                @foreach($teknisis as $tek)
                                <option value="{{ $tek->id }}">
                                    {{ $tek->nama_lengkap }}
                                    @if($tek->bidang) — {{ $tek->bidang->nama_bidang }} @endif
                                    · {{ $tek->tiket_aktif_count }} tiket aktif
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Teknisi Pendamping (multi-select) --}}
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                Teknisi Pendamping
                                <span class="text-gray-400 font-normal">(opsional)</span>
                            </label>
                            <div class="relative" @click.outside="pendampingOpen = false">
                                <button type="button" @click="pendampingOpen = !pendampingOpen"
                                        class="w-full text-left px-3.5 py-3 text-sm border border-gray-200 rounded-xl bg-white flex items-center justify-between hover:border-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-[#01458E]/20"
                                        :class="pendampingOpen ? 'border-[#01458E] ring-2 ring-[#01458E]/20' : ''">
                                    <span x-show="pendampingIds.length === 0" class="text-gray-400">Pilih pendamping (opsional)</span>
                                    <span x-show="pendampingIds.length > 0" class="text-gray-700 font-medium"
                                          x-text="pendampingIds.length + ' teknisi dipilih'"></span>
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform duration-150"
                                         :class="pendampingOpen ? 'rotate-180' : ''"
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>

                                {{-- Dropdown list --}}
                                <div x-show="pendampingOpen"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     class="absolute top-[calc(100%+4px)] left-0 right-0 bg-white border border-gray-200 rounded-xl shadow-lg z-[110] max-h-44 overflow-y-auto"
                                     style="display:none;">
                                    <template x-for="tek in teknisiList.filter(t => String(t.id) !== String(tekUtamaId))" :key="tek.id">
                                        <div @click="togglePendamping(tek.id)"
                                             class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-gray-50 transition-colors"
                                             :class="pendampingIds.includes(tek.id) ? 'bg-blue-50/50 hover:bg-blue-50/80' : ''">
                                            {{-- Checkbox --}}
                                            <div class="w-4 h-4 rounded border-2 flex-shrink-0 flex items-center justify-center transition-colors"
                                                 :class="pendampingIds.includes(tek.id) ? 'bg-[#01458E] border-[#01458E]' : 'border-gray-300'">
                                                <svg x-show="pendampingIds.includes(tek.id)" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            {{-- Nama & bidang --}}
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-800 truncate" x-text="tek.nama"></p>
                                                <p class="text-[11px] text-gray-400 truncate" x-text="tek.bidang ?? '—'"></p>
                                            </div>
                                            {{-- Jumlah tiket aktif --}}
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md flex-shrink-0"
                                                  :class="tek.tiket_aktif === 0
                                                      ? 'bg-green-50 text-green-700'
                                                      : tek.tiket_aktif <= 2
                                                          ? 'bg-amber-50 text-amber-700'
                                                          : 'bg-red-50 text-red-600'"
                                                  x-text="tek.tiket_aktif + ' aktif'"></span>
                                        </div>
                                    </template>
                                    <div x-show="teknisiList.filter(t => String(t.id) !== String(tekUtamaId)).length === 0"
                                         class="px-4 py-4 text-xs text-gray-400 text-center">
                                        Tidak ada teknisi lain tersedia
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden inputs --}}
                            <template x-for="pid in pendampingIds" :key="'hi-' + pid">
                                <input type="hidden" name="teknisi_pendamping_ids[]" :value="pid">
                            </template>

                            {{-- Tags teknisi terpilih --}}
                            <div x-show="pendampingIds.length > 0" class="flex flex-wrap gap-1.5 mt-2.5">
                                <template x-for="pid in pendampingIds" :key="'tag-' + pid">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 border border-blue-100 text-blue-700 text-xs font-medium rounded-lg">
                                        <span x-text="teknisiList.find(t => t.id === pid)?.nama ?? pid"></span>
                                        <button type="button" @click.stop="togglePendamping(pid)"
                                                class="text-blue-400 hover:text-blue-700 focus:outline-none">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Instruksi Khusus (opsional)</label>
                            <textarea name="instruksi" rows="3" placeholder="Masukkan instruksi untuk teknisi..."
                                      class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = 'transfer-pilih'"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 focus:outline-none"
                                    style="background:#D97706;">
                                Eskalasi Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Perbesar Foto --}}
            <div x-show="showFoto"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showFoto = false; activeFoto = null"
                 class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-4 sm:p-6"
                 style="display:none;"
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
                 style="display:none;"
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
                 style="display:none;"
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
        @php
        $teknisiListData = $teknisis->map(fn($t) => [
            'id'          => $t->id,
            'nama'        => $t->nama_lengkap,
            'bidang'      => $t->bidang?->nama_bidang,
            'tiket_aktif' => (int) ($t->tiket_aktif_count ?? 0),
        ]);
        @endphp
        return {
            selectedTiket: null,
            showDrawer: false,
            showModal: '',
            showFoto: false,
            activeFoto: null,
            sopPreviewOpen: false,
            sopPreviewContent: '',
            sopPreviewTitle: '',
            bidangFilter: '',
            tekUtamaId: '',
            pendampingIds: [],
            pendampingOpen: false,
            teknisiList: @json($teknisiListData),
            activeTab: new URLSearchParams(window.location.search).get('tab') || 'baru',

            init() {
                this.$watch('tekUtamaId', (newVal) => {
                    this.pendampingIds = this.pendampingIds.filter(id => id !== newVal);
                });

                // Menutup modal/drawer jika tombol ESC ditekan
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        if (this.showFoto) { this.showFoto = false; }
                        else if (this.sopPreviewOpen) { this.sopPreviewOpen = false; }
                        else if (this.showModal) { this.showModal = ''; }
                        else if (this.showDrawer) { this.showDrawer = false; }
                    }
                });
            },
            togglePendamping(id) {
                if (this.pendampingIds.includes(id)) {
                    this.pendampingIds = this.pendampingIds.filter(i => i !== id);
                } else {
                    this.pendampingIds.push(id);
                }
            },
            setTab(tab) {
                this.activeTab = tab;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', tab);
                history.replaceState({}, '', url);
                const input = document.getElementById('tabInput');
                if (input) input.value = tab;
            },
            openDetail(tiket) {
                this.selectedTiket = tiket;
                this.showDrawer = true;
            },
            setTiket(tiket) {
                this.selectedTiket = tiket;
                this.showDrawer = false;
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
            submitForm(form, url) {
                form.action = url;
                form.submit();
            },
        };
    }
    </script>

</body>
</html>
