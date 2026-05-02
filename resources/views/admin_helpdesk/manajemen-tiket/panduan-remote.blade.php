<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Remote — Admin Helpdesk</title>
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
                <h1 class="text-lg font-bold text-gray-900">Panduan Remote</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket yang sedang dalam sesi panduan remote (chat)</p>
            </div>
            <div class="inline-flex items-center self-start sm:self-auto gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold text-blue-700 bg-blue-50">
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse shrink-0"></div>
                Chat aktif dengan OPD
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten utama (tabel + filter) ── --}}
            <div class="flex-1 flex flex-col overflow-hidden w-full">

                {{-- Filter --}}
                <div class="px-4 sm:px-6 pt-5 pb-2">
                    <form method="GET" action="{{ route('admin_helpdesk.tiket.panduan') }}" id="filterFormPanduan"
                          class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-3 sm:mb-5 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-2 sm:items-center">

                            {{-- Search --}}
                            <div class="w-full sm:flex-1 sm:min-w-[200px] relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                                </svg>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari ID tiket atau subjek..."
                                       oninput="clearTimeout(window._stPanduan); window._stPanduan = setTimeout(() => document.getElementById('filterFormPanduan').submit(), 500)"
                                       class="w-full pl-9 pr-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                            </div>

                            {{-- Reset --}}
                            <a href="{{ route('admin_helpdesk.tiket.panduan') }}"
                               class="flex items-center justify-center w-full sm:w-auto gap-1.5 px-4 py-2.5 sm:py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                {{-- Flash --}}
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
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Status Chat</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Waktu Masuk</th>
                                        <th class="px-5 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($tikets as $tiket)
                                    @php
                                        $hasChat      = $tiket->chatRooms->isNotEmpty();
                                        $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
                                        $bidangId     = $tiket->bidang_id;
                                        $isDibukaKembaliOpd = str_starts_with($tiket->latestStatus?->catatan ?? '', '[Dibuka Kembali oleh OPD]');
                                        $tiketJson    = json_encode([
                                            'id'                    => $tiket->id,
                                            'subjek_masalah'        => $tiket->subjek_masalah,
                                            'detail_masalah'        => $tiket->detail_masalah,
                                            'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                            'kategori_nama'         => $kategoriNama,
                                            'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                            'lokasi'                => $tiket->lokasi ?? '—',
                                            'foto_bukti'            => $tiket->foto_bukti,
                                            'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                            'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                            'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                            'has_chat'              => $hasChat,
                                            'catatan_status'            => $tiket->latestStatus?->catatan ?? '—',
                                            'is_dibuka_kembali_opd'     => $isDibukaKembaliOpd,
                                            'alasan_buka_kembali'       => $isDibukaKembaliOpd ? substr($tiket->latestStatus->catatan, strlen('[Dibuka Kembali oleh OPD] ')) : null,
                                            'file_bukti_buka_kembali'   => $tiket->latestStatus?->file_bukti,
                                            'bidang_id'             => $bidangId,
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
                                            @if($isDibukaKembaliOpd)
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-md mt-1.5 border border-amber-200" style="background:#FEF3C7;color:#92400E;">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                Dibuka Kembali
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-gray-600 font-medium whitespace-nowrap">
                                            {{ Str::limit($tiket->opd?->nama_opd ?? '—', 30) }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="text-[11px] font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 bg-gray-50">
                                                {{ $kategoriNama }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            @if($hasChat)
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border border-blue-200" style="background:#EFF6FF;color:#1D4ED8;">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse shrink-0"></span>Aktif
                                            </span>
                                            @else
                                            <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full border border-gray-200" style="background:#F9FAFB;color:#6B7280;">
                                                Belum Ada Chat
                                            </span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-4 text-gray-500 whitespace-nowrap">
                                            <p class="text-xs font-semibold text-gray-700">{{ $tiket->created_at?->translatedFormat('d M Y') }}</p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $tiket->created_at?->format('H:i:s') }} WIB</p>
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap" @click.stop>
                                            <div class="flex items-center justify-center gap-2">
                                                @if($hasChat)
                                                <a href="{{ route('admin_helpdesk.tiket.chat', $tiket->id) }}" title="Lihat Chat"
                                                   class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-lg text-white hover:opacity-90 shadow-sm transition-opacity"
                                                   style="background:#1D4ED8;">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                    Chat
                                                </a>
                                                @else
                                                <a href="{{ route('admin_helpdesk.tiket.chat', $tiket->id) }}" title="Mulai Chat Panduan Remote"
                                                   class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-all hover:scale-110 shadow-sm"
                                                   style="background:#01458E;color:white;">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                </a>
                                                @endif
                                                <button type="button" @click.stop="setTiket({{ $tiketJson }}); showModal='selesai'"
                                                        title="Selesaikan Tiket"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                        style="background:#D1FAE5;color:#065F46;">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                                <button type="button" @click.stop="setTiket({{ $tiketJson }}); showModal='eskalasi'"
                                                        title="Eskalasi ke Tim Teknis"
                                                        class="w-8 h-8 rounded-lg flex items-center justify-center transition-all hover:scale-110"
                                                        style="background:#FEF3C7;color:#D97706;">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-16 text-center">
                                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100">
                                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-500 text-sm">Tidak ada tiket dalam Panduan Remote</p>
                                                    <p class="text-xs text-gray-400 mt-1">Tiket akan muncul setelah Anda menerima pengajuan masuk.</p>
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
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold text-right border"
                                      :style="rekomendasiBadge(selectedTiket?.rekomendasi_penanganan)">
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
                            <div class="flex justify-between items-center gap-4">
                                <span class="text-xs text-gray-500 whitespace-nowrap">Status Chat</span>
                                <span :class="selectedTiket?.has_chat
                                          ? 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200'
                                          : 'text-[10px] font-bold px-2.5 py-1 rounded-full bg-gray-50 text-gray-600 border border-gray-200'"
                                      x-text="selectedTiket?.has_chat ? 'Aktif' : 'Belum Ada Chat'"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Laporan Buka Kembali --}}
                    <template x-if="selectedTiket?.is_dibuka_kembali_opd">
                        <div class="border-l-4 border-red-500 bg-red-50 rounded-r-xl p-3 sm:p-4">
                            <div class="flex items-center gap-2 mb-3 pb-2 border-b border-red-200">
                                <svg class="w-4 h-4 text-red-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-red-700">Laporan OPD — Buka Kembali Tiket</span>
                            </div>
                            <div class="mb-3">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-red-800 mb-1.5">Catatan / Alasan OPD</div>
                                <div class="bg-white/60 border border-red-200 rounded-lg p-3">
                                    <p class="text-xs text-red-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.alasan_buka_kembali || '—'"></p>
                                </div>
                            </div>
                            <template x-if="selectedTiket?.file_bukti_buka_kembali">
                                <div>
                                    <div class="text-[10px] font-bold uppercase tracking-wider text-red-800 mb-2">Bukti Foto dari OPD</div>
                                    <img :src="'/storage/' + selectedTiket.file_bukti_buka_kembali"
                                         alt="Bukti Buka Kembali"
                                         class="w-full max-h-40 object-cover rounded-xl border border-red-200 cursor-pointer hover:opacity-90 transition-opacity"
                                         @click="activeFoto = selectedTiket.file_bukti_buka_kembali; showFoto = true">
                                    <p class="text-[9px] text-red-600 mt-1.5 text-center font-medium">Klik untuk perbesar</p>
                                </div>
                            </template>
                            <template x-if="!selectedTiket?.file_bukti_buka_kembali">
                                <div class="h-12 rounded-lg border border-dashed border-red-300 flex items-center justify-center bg-white/50 mt-1">
                                    <span class="text-[11px] text-red-400 font-medium">Tidak ada bukti foto dilampirkan</span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Section 2: Catatan Status --}}
                    <template x-if="!selectedTiket?.is_dibuka_kembali_opd">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Catatan Status</div>
                            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 sm:p-4">
                                <p class="text-xs text-gray-800 leading-relaxed whitespace-pre-wrap break-words" x-text="selectedTiket?.catatan_status || '—'"></p>
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

                    {{-- Section 3: Detail Masalah --}}
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

                    {{-- Section 4: Foto Bukti --}}
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

                {{-- Drawer Footer --}}
                <div class="shrink-0 p-4 sm:p-5 border-t border-gray-100 bg-white space-y-3">
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Aksi Tindakan</h4>
                    <div class="flex flex-col gap-2.5">
                        <template x-if="selectedTiket">
                            <a :href="'/admin-helpdesk/tiket/' + selectedTiket.id + '/chat'"
                               class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 hover:shadow-md"
                               style="background:#1D4ED8;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Buka Ruang Chat
                            </a>
                        </template>
                        <div class="flex gap-2.5">
                            <button @click="showModal = 'selesai'"
                                    class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 focus:outline-none transition-colors">
                                ✓ Selesaikan Tiket
                            </button>
                            <button @click="showModal = 'eskalasi'"
                                    class="flex-1 py-2.5 rounded-xl text-xs font-bold bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                ⇄ Eskalasi ke Tim Teknis
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Modals ── --}}

            {{-- Overlay --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = ''"
                 class="fixed inset-0 z-[102] bg-black/40 backdrop-blur-sm"
                 x-cloak></div>

            {{-- Modal: Eskalasi ke Tim Teknis --}}
            <div x-show="showModal === 'eskalasi'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 x-cloak>
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
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teknisi Utama <span class="text-red-500">*</span></label>
                            <select name="teknisi_utama_id" required x-model="tekUtamaId"
                                    class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Pilih Teknisi</option>
                                @foreach($teknisis as $tek)
                                <option value="{{ $tek->id }}" x-show="!selectedTiket?.bidang_id || '{{ $tek->bidang_id }}' === selectedTiket?.bidang_id">
                                    {{ $tek->nama_lengkap }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teknisi Pendamping <span class="text-gray-400 font-normal">(opsional)</span></label>
                            <select name="teknisi_pendamping_id"
                                    class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Tidak ada</option>
                                @foreach($teknisis as $tek)
                                <option value="{{ $tek->id }}"
                                        x-show="(!selectedTiket?.bidang_id || '{{ $tek->bidang_id }}' === selectedTiket?.bidang_id) && tekUtamaId !== '{{ $tek->id }}'"
                                        :disabled="tekUtamaId === '{{ $tek->id }}'">
                                    {{ $tek->nama_lengkap }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Instruksi Khusus (opsional)</label>
                            <textarea name="instruksi" rows="3" placeholder="Masukkan instruksi untuk teknisi..."
                                      class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = ''"
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

            {{-- Modal: Selesaikan Tiket --}}
            <div x-show="showModal === 'selesai'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4 sm:p-0"
                 x-cloak>
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 sm:p-8 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-8 h-8 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-400 hover:text-gray-600 flex items-center justify-center transition-colors focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="flex flex-col items-center mb-5">
                        <div class="w-14 h-14 rounded-full flex items-center justify-center mb-4 bg-green-100 border border-green-200">
                            <svg class="w-7 h-7" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Selesaikan Tiket</h3>
                        <p class="text-sm font-semibold mb-2 text-center text-[#01458E] break-words" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                        <p class="text-xs text-gray-500 text-center">Tandai tiket ini sudah berhasil diselesaikan melalui panduan remote</p>
                    </div>
                    <form x-ref="formSelesai" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formSelesai, '/admin-helpdesk/tiket/' + selectedTiket.id + '/selesai')">
                        @csrf
                        <div class="mb-6">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Catatan Penyelesaian (opsional)</label>
                            <textarea name="catatan" rows="3" placeholder="Deskripsikan solusi yang diberikan..."
                                      class="w-full px-3.5 py-3 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-200 focus:border-green-500 resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = ''"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors focus:outline-none">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 focus:outline-none"
                                    style="background:#059669;">
                                Selesaikan
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
            tekUtamaId: '',

            openDetail(t) {
                this.selectedTiket = t;
                this.showDrawer = true;
            },
            setTiket(t) {
                this.selectedTiket = t;
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
