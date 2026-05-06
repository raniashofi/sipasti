<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->nama_artikel_sop }} — Pustaka Solusi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Scrollbar styling */
        main { scrollbar-width: thin; scrollbar-color: #D1D5DB transparent; }
        main::-webkit-scrollbar { width: 6px; }
        main::-webkit-scrollbar-track { background: transparent; }
        main::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }

        /* ── CSS Konten SOP ── */
        .article-content { font-size: 0.875rem; line-height: 1.8; color: #1f2937; word-wrap: break-word; overflow-wrap: break-word; }
        .article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6 { font-weight: 700; margin: 1.5rem 0 0.75rem; color: #111827; line-height: 1.3; }
        .article-content h1 { font-size: 1.75rem; }
        .article-content h2 { font-size: 1.5rem; }
        .article-content h3 { font-size: 1.25rem; }
        .article-content p  { margin-bottom: 1rem; }
        .article-content strong { font-weight: 700; color: #111827; }
        .article-content ul, .article-content ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .article-content ul { list-style-type: disc; }
        .article-content ol { list-style-type: decimal; }
        .article-content li  { margin-bottom: 0.375rem; }
        .article-content blockquote { border-left: 4px solid #01458E; padding-left: 1.25rem; margin: 1.25rem 0; color: #4b5563; font-style: italic; background:#f9fafb; border-radius: 0 0.5rem 0.5rem 0; padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .article-content code { background: #f3f4f6; color: #01458E; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; word-break: break-all; }
        .article-content pre  { background: #1f2937; color: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1.25rem 0; font-size: 0.8rem; }
        .article-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1.25rem 0; }
        .article-content table { width: 100%; border-collapse: collapse; margin: 1.25rem 0; display: block; overflow-x: auto; }
        @media (min-width: 768px) { .article-content table { display: table; } }
        .article-content th, .article-content td { border: 1px solid #e5e7eb; padding: 0.75rem 1rem; text-align: left; font-size: 0.8125rem; min-width: 120px; }
        .article-content th { background: #f9fafb; font-weight: 700; color: #374151; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarAdminHelpdesk')

    @php
        $bidangLabel = $admin?->bidang?->nama_bidang ?? '—';
        $kodeArtikel = 'KB-' . strtoupper(substr($article->id, 0, 8));
    @endphp

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- ── Header ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-6 py-4 flex items-center justify-between shrink-0 sticky top-0 z-30">

            {{-- Breadcrumb (Kiri) --}}
            <div class="flex items-center gap-1.5 sm:gap-2 text-sm overflow-hidden mr-4">
                <a href="{{ route('admin_helpdesk.pustaka') }}"
                   class="text-gray-400 text-[11px] sm:text-xs hover:text-[#01458E] transition-colors flex items-center gap-1 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span class="hidden sm:inline">Pustaka Solusi</span>
                </a>
                <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="font-semibold text-gray-800 text-xs sm:text-sm truncate max-w-[120px] sm:max-w-md">{{ $article->nama_artikel_sop }}</span>
            </div>

            {{-- Badge (Kanan Pojok) --}}
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[10px] sm:text-xs font-bold text-amber-700 bg-amber-50 border border-amber-100 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Hanya Baca
                </span>
            </div>
        </header>

        {{-- ── Warning Banner ── --}}
        <div class="bg-red-50 border-b border-red-100 px-4 sm:px-6 py-2.5 flex items-start sm:items-center gap-3 text-[11px] sm:text-xs text-red-700">
            <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span><strong>INTERNAL</strong> — Artikel ini bersifat rahasia untuk bidang <strong>{{ $bidangLabel }}</strong>. Jangan disebarkan ke pihak luar.</span>
        </div>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-4 sm:px-6 py-6 overflow-y-auto">
            <div class="flex flex-col lg:flex-row gap-6 items-start max-w-7xl mx-auto">

                {{-- KIRI: Konten --}}
                <div class="w-full lg:flex-1 flex flex-col gap-5 min-w-0">
                    @if($article->header_image)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <img src="{{ asset('storage/' . $article->header_image) }}" class="w-full max-h-64 object-cover">
                    </div>
                    @endif

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 sm:px-8 py-6 sm:py-7">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 leading-tight break-words">{{ $article->nama_artikel_sop }}</h1>
                        @if($article->deskripsi_singkat)
                        <p class="text-xs sm:text-sm text-gray-500 mt-4 leading-relaxed border-t border-gray-100 pt-4 italic">
                            "{{ $article->deskripsi_singkat }}"
                        </p>
                        @endif
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-5 sm:px-8 py-6 sm:py-7">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-5">Konten Dokumentasi</p>
                        @if($article->isi_konten)
                            <div class="article-content">{!! $article->isi_konten !!}</div>
                        @else
                            <div class="text-center py-10">
                                <p class="text-sm text-gray-400">Konten tidak tersedia.</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- KANAN: Sidebar --}}
                <div class="w-full lg:w-80 shrink-0 flex flex-col gap-4">
                    @if($article->tags->count())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Tag</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($article->tags as $tag)
                            <span class="text-[11px] px-2.5 py-1 rounded-lg bg-blue-50 text-[#01458E] font-bold border border-blue-100">{{ $tag->nama_tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($article->lampiran_file)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Unduhan</p>
                        <a href="{{ asset('storage/' . $article->lampiran_file) }}" target="_blank"
                           class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200 hover:border-[#01458E] transition-all group">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-[#01458E] text-white shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-gray-800 truncate">{{ basename($article->lampiran_file) }}</p>
                                <p class="text-[10px] text-gray-500 font-medium">Klik untuk unduh</p>
                            </div>
                        </a>
                    </div>
                    @endif

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Informasi</p>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center"><span class="text-gray-500">ID</span><span class="font-bold text-[#01458E]">{{ $kodeArtikel }}</span></div>
                            <div class="border-t border-gray-100 pt-3">
                                <p class="text-[10px] text-gray-400 mb-1 uppercase font-bold">Dibuat</p>
                                <p class="font-semibold text-gray-800">{{ $article->created_at?->format('d M Y, H:i') }} WIB</p>
                            </div>
                            <div class="pt-1">
                                <p class="text-[10px] text-gray-400 mb-1 uppercase font-bold">Update</p>
                                <p class="font-semibold text-gray-800">{{ $article->updated_at?->format('d M Y, H:i') }} WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
