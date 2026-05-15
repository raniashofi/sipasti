<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php $namaDisplay = ucwords(str_replace('_', ' ', $bidang->nama_bidang)); @endphp
    <title>{{ $namaDisplay }} — Pustaka Internal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        * { font-family: 'Inter', sans-serif; }

        /* Preview Content Styling (Mewarisi style dari editor) */
        #previewContent { font-size: 0.875rem; line-height: 1.8; }
        #previewContent h1, #previewContent h2, #previewContent h3 { font-weight: 700; margin: 1rem 0 0.5rem; }
        #previewContent h1 { font-size: 1.75rem; }
        #previewContent h2 { font-size: 1.5rem; }
        #previewContent p { margin-bottom: 0.75rem; }
        #previewContent strong { font-weight: 700; }
        #previewContent em { font-style: italic; }
        #previewContent a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; }
        #previewContent ul { padding-left: 1.5rem; margin-bottom: 0.75rem; list-style-type: disc; }
        #previewContent ol { padding-left: 1.5rem; margin-bottom: 0.75rem; list-style-type: decimal; }
        #previewContent blockquote { border-left: 4px solid #01458E; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
        #previewContent img, #previewContent video { max-width: 100%; height: auto; border-radius: 0.375rem; margin: 0.75rem 0; }

        .modal-scroll { scrollbar-width: thin; scrollbar-color: #E5E7EB transparent; }
        .modal-scroll::-webkit-scrollbar { width: 4px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    {{-- Data Repository untuk Modal Pratinjau --}}
    <script>
        window.articleDataRepo = {
            @foreach($articles as $a)
            "{{ $a->id }}": {
                title: @json($a->nama_artikel_sop ?? 'Untitled'),
                status: @json($a->status_publikasi),
                visibility: @json($a->visibilitas_akses),
                headerImage: @json($a->header_image ? asset('storage/' . $a->header_image) : null),
                desc: @json($a->deskripsi_singkat),
                content: @json($a->isi_konten),
                lampiran: @json($a->lampiran_file ? basename($a->lampiran_file) : null),
                tags: @json($a->tags->pluck('nama_tag')->toArray())
            },
            @endforeach
        };
    </script>

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col"
         x-data="{
             showHapus: false,
             hapusId: null,
             previewModalOpen: false,
             preview: {},
             openPreview(id) {
                 this.preview = window.articleDataRepo[id] || {};
                 this.previewModalOpen = true;
                 // Delay render sedikit agar DOM modal siap menerima inject HTML
                 setTimeout(() => {
                     document.getElementById('previewContent').innerHTML = this.preview.content || '<p class=\'text-gray-400 italic\'>Tidak ada konten.</p>';
                 }, 50);
             }
         }">

        {{-- ── Top Bar Wrapper ── --}}
        <div class="sticky top-0 z-30 shrink-0">
            <header class="bg-white border-b border-gray-100 pl-14 pr-4 sm:px-8 py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto overflow-x-auto pb-1 sm:pb-0" style="scrollbar-width:none;">
                    <a href="{{ route('super_admin.pustaka.internal') }}"
                       class="w-8 h-8 rounded-lg bg-[#F0F4F8] flex items-center justify-center text-gray-500 hover:bg-[#E5EBF3] transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>

                    <div class="flex items-center gap-2 text-sm whitespace-nowrap">
                        <a href="{{ route('super_admin.pustaka.internal') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                            Pustaka Internal
                        </a>
                        <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="font-semibold text-gray-800">{{ $namaDisplay }}</span>
                        <span class="ml-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 border border-amber-200">RAHASIA</span>
                    </div>
                </div>

                {{-- Desktop Tambah SOP --}}
                <div class="hidden sm:block shrink-0">
                    <a href="{{ route('super_admin.pustaka.create', ['visibility' => 'internal', 'bidang_id' => $bidang->id]) }}"
                       class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 w-full sm:w-auto shadow-sm"
                       style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah SOP
                    </a>
                </div>
            </header>

            {{-- Mobile Tambah SOP --}}
            <div class="sm:hidden bg-white border-b border-gray-100 px-4 py-3">
                <a href="{{ route('super_admin.pustaka.create', ['visibility' => 'internal', 'bidang_id' => $bidang->id]) }}"
                   class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 w-full shadow-sm"
                   style="background-color:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah SOP
                </a>
            </div>
        </div>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-4 sm:px-8 py-7 flex flex-col min-w-0">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- Filter & Search --}}
            <form method="GET" action="{{ route('super_admin.pustaka.internal.bidang', $bidang->id) }}" id="filterForm"
                  class="bg-white rounded-2xl border border-gray-100 px-5 py-4 mb-5 shadow-sm">

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>

                <div class="flex flex-wrap gap-2 items-center">

                    @php
                        $statusOptions  = ['' => 'Semua Status', 'draft' => 'Draft', 'published' => 'Published'];
                        $statusSelected = $statusFilter ?: '';
                        $statusLabel    = $statusOptions[$statusSelected] ?? 'Semua Status';
                    @endphp
                    <input type="hidden" name="status" id="statusInput" value="{{ $statusSelected }}">
                    <div class="relative w-full sm:w-auto"
                         x-data="{
                            open: false,
                            selected: '{{ $statusSelected }}',
                            label: '{{ addslashes($statusLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('statusInput').value = val;
                                this.open = false;
                                document.getElementById('filterForm').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button"
                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 min-w-[150px] w-full sm:w-auto hover:border-blue-200 transition-colors"
                                :class="{ 'border-blue-500 bg-blue-50': open }" @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="label">{{ $statusLabel }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
                             x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                            @foreach($statusOptions as $val => $lbl)
                            <div class="flex items-center gap-2 px-3.5 py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors {{ $statusSelected == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity" :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                {{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex-1 min-w-[250px] w-full sm:w-auto relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul SOP atau tag..."
                               oninput="clearTimeout(window._st); window._st = setTimeout(() => document.getElementById('filterForm').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <a href="{{ route('super_admin.pustaka.internal.bidang', $bidang->id) }}"
                       class="flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors w-full sm:w-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- Article Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-1 flex flex-col">

                <div class="flex items-center justify-between px-7 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <p class="text-base font-bold text-gray-900">{{ $namaDisplay }} — SOP</p>
                    </div>
                    <p class="text-xs font-medium text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-200 shadow-sm">
                        Total: {{ $articles->count() }} SOP
                    </p>
                </div>

                {{-- Mobile card list --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse($articles as $i => $article)
                    <div class="px-4 py-5 hover:bg-blue-50/30 transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <p class="text-sm font-bold text-gray-900 leading-snug">{{ $article->nama_artikel_sop }}</p>
                            @if($article->status_publikasi === 'published')
                            <span class="shrink-0 inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-green-200 text-green-600 bg-green-50 uppercase tracking-wide">
                                Published
                            </span>
                            @else
                            <span class="shrink-0 inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-yellow-200 text-yellow-600 bg-yellow-50 uppercase tracking-wide">
                                Draft
                            </span>
                            @endif
                        </div>

                        @if($article->tags->count())
                        <div class="flex flex-wrap gap-1.5 mb-4">
                            @foreach($article->tags->take(3) as $tag)
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-[#EEF3F9] text-[#01458E]">{{ $tag->nama_tag }}</span>
                            @endforeach
                            @if($article->tags->count() > 3)
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-500">+{{ $article->tags->count() - 3 }}</span>
                            @endif
                        </div>
                        @endif

                        <div class="flex flex-col gap-3">
                            <div class="text-[11px] text-gray-500 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $article->created_at->format('d M Y, H:i') }}
                            </div>

                            {{-- Mobile Actions --}}
                            <div class="flex gap-2">
                                <button type="button" @click="openPreview('{{ $article->id }}')"
                                        class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-gray-600 border border-gray-200 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat
                                </button>
                                <a href="{{ route('super_admin.pustaka.edit', $article->id) }}"
                                   class="flex-1 flex justify-center items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white hover:opacity-90 transition-opacity shadow-sm"
                                   style="background-color:#01458E;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <button type="button" @click="showHapus = true; hapusId = '{{ $article->id }}'"
                                        class="flex items-center justify-center w-9 h-9 rounded-xl text-red-500 border border-red-200 bg-red-50 hover:bg-red-100 transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-400">
                        @if($search || $statusFilter)
                            Tidak ada SOP yang cocok dengan pencarian/filter.
                        @else
                            Belum ada SOP di bidang ini.
                        @endif
                    </div>
                    @endforelse
                </div>

                {{-- Desktop Table --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-14">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul SOP</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-36">Status</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Tgl Dibuat</th>
                                <th class="px-7 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-72">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($articles as $i => $article)
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-7 py-4 text-sm text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-4 py-4">
                                    <p class="text-sm font-bold text-gray-900 group-hover:text-[#01458E] transition-colors">{{ $article->nama_artikel_sop }}</p>
                                    @if($article->tags->count())
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach($article->tags->take(3) as $tag)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-[#EEF3F9] text-[#01458E]">{{ $tag->nama_tag }}</span>
                                        @endforeach
                                        @if($article->tags->count() > 3)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-500">+{{ $article->tags->count() - 3 }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($article->status_publikasi === 'published')
                                    <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border border-green-200 text-green-700 bg-green-50 uppercase tracking-wider">
                                        Published
                                    </span>
                                    @else
                                    <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-1 rounded-full border border-yellow-200 text-yellow-700 bg-yellow-50 uppercase tracking-wider">
                                        Draft
                                    </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-700 font-medium">{{ $article->created_at->format('d M Y') }}</div>
                                    <div class="text-[11px] text-gray-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $article->created_at->format('H:i') }} WIB
                                    </div>
                                </td>
                                <td class="px-7 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- Tombol Pratinjau --}}
                                        <button type="button" @click="openPreview('{{ $article->id }}')"
                                                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Pratinjau
                                        </button>

                                        {{-- Tombol Edit --}}
                                        <a href="{{ route('super_admin.pustaka.edit', $article->id) }}"
                                           class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white hover:opacity-90 transition-opacity shadow-sm"
                                           style="background-color:#01458E;">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </a>

                                        {{-- Tombol Hapus --}}
                                        <button type="button" @click="showHapus = true; hapusId = '{{ $article->id }}'"
                                                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 hover:border-red-200 transition-all shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-7 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500 font-medium">
                                            @if($search || $statusFilter)
                                                Tidak ada SOP yang cocok dengan filter pencarian.
                                            @else
                                                Belum ada SOP Internal. Klik <strong class="text-[#01458E]">Tambah SOP</strong> untuk memulai.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                            {{-- Skeleton/Padder biar tabel gak terlalu kopong jika data sedikit --}}
                            @if($articles->count() > 0 && $articles->count() < 5)
                                @for($pad = $articles->count(); $pad < 5; $pad++)
                                <tr class="bg-transparent pointer-events-none">
                                    <td colspan="5" class="px-7 py-6 border-b border-gray-50/50"></td>
                                </tr>
                                @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        {{-- ── PREVIEW MODAL ── --}}
        <div x-show="previewModalOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="previewModalOpen = false"
             class="fixed inset-0 bg-black/45 backdrop-filter backdrop-blur-sm flex items-center justify-center z-50 p-4"
             x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-gray-100 shrink-0 bg-white">
                    <h2 class="text-lg font-bold text-gray-900">Pratinjau SOP</h2>
                    <button @click="previewModalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Content --}}
                <div class="flex-1 overflow-y-auto px-6 sm:px-8 py-6 bg-white modal-scroll">

                    {{-- Header Image --}}
                    <div x-show="preview.headerImage" class="mb-6">
                        <img :src="preview.headerImage" class="w-full h-40 sm:h-56 object-cover rounded-2xl border border-gray-100 shadow-sm">
                    </div>

                    {{-- Title Area --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border uppercase tracking-wider"
                                  :class="preview.status === 'published' ? 'border-green-200 text-green-700 bg-green-50' : 'border-yellow-200 text-yellow-700 bg-yellow-50'"
                                  x-text="preview.status === 'published' ? 'Published' : 'Draft'">
                            </span>
                            <span class="inline-flex items-center text-[10px] font-bold px-2.5 py-0.5 rounded-full border uppercase tracking-wider border-red-200 text-red-700 bg-red-50">
                                Internal
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-tight" x-text="preview.title"></h1>
                    </div>

                    {{-- Deskripsi Singkat --}}
                    <div x-show="preview.desc" class="mb-6 p-4 sm:p-5 bg-[#F0F4F8] border border-blue-100 rounded-2xl">
                        <p class="text-[#01458E] text-sm leading-relaxed" x-text="preview.desc"></p>
                    </div>

                    {{-- Tags --}}
                    <div x-show="preview.tags && preview.tags.length > 0" class="mb-6">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in preview.tags" :key="tag">
                                <span class="inline-flex items-center text-[11px] font-semibold px-3 py-1 rounded-lg bg-gray-100 text-gray-600" x-text="tag"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Konten Utama (Di-inject via JS) --}}
                    <div id="previewContent" class="prose prose-sm sm:prose-base max-w-none mb-8 text-gray-700"></div>

                    {{-- Lampiran File --}}
                    <div x-show="preview.lampiran" class="border-t border-gray-100 pt-6 mt-auto">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Lampiran File</p>
                        <div class="inline-flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl max-w-full">
                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM3.293 7.293a1 1 0 011.414 0A5 5 0 0013.414 2H12a1 1 0 110-2h4.586A1.5 1.5 0 0118 1.5v4.586a1 1 0 01-2 0V3.414A5 5 0 007.293 11.707a1 1 0 01-1.414-1.414z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-700 truncate" x-text="preview.lampiran"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── MODAL KONFIRMASI HAPUS ── --}}
        <div x-show="showHapus"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
            <div class="absolute inset-0 bg-black/45 backdrop-filter backdrop-blur-sm" @click="showHapus = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 p-8 text-center" @click.stop>
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-xl font-extrabold text-gray-900 mb-2">Hapus SOP?</h3>
                <p class="text-sm text-gray-500 mb-8 leading-relaxed">Tindakan ini akan menghapus data SOP secara permanen dan tidak dapat dibatalkan.</p>
                <form method="POST" :action="`{{ url('super-admin/pustaka') }}/${hapusId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="showHapus = false"
                                class="flex-1 px-4 py-3 rounded-xl text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-3 rounded-xl text-sm font-bold text-white bg-red-500 hover:bg-red-600 shadow-sm shadow-red-500/30 transition-all">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
