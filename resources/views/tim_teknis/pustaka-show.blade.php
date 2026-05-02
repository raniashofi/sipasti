<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->nama_artikel_sop }} — Pustaka Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        main { scrollbar-width: thin; scrollbar-color: #D1D5DB transparent; }
        main::-webkit-scrollbar { width: 6px; }
        main::-webkit-scrollbar-track { background: transparent; }
        main::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }

        .article-content { font-size: 0.875rem; line-height: 1.8; color: #374151; }
        .article-content h1,.article-content h2,.article-content h3,
        .article-content h4,.article-content h5,.article-content h6 { font-weight: 700; margin: 1.25rem 0 0.5rem; color: #111827; }
        .article-content h1 { font-size: 1.75rem; }
        .article-content h2 { font-size: 1.4rem; }
        .article-content h3 { font-size: 1.15rem; }
        .article-content p  { margin-bottom: 0.875rem; }
        .article-content strong { font-weight: 700; }
        .article-content em { font-style: italic; }
        .article-content u  { text-decoration: underline; }
        .article-content s  { text-decoration: line-through; }
        .article-content a  { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; }
        .article-content a:hover { color: #003a70; }
        .article-content ul, .article-content ol { padding-left: 1.5rem; margin-bottom: 0.875rem; }
        .article-content ul { list-style-type: disc; }
        .article-content ol { list-style-type: decimal; }
        .article-content ul ul { list-style-type: circle; }
        .article-content li  { margin-bottom: 0.3rem; }
        .article-content blockquote { border-left: 4px solid #01458E; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; background:#EEF3F9; border-radius: 0 0.5rem 0.5rem 0; padding: 0.75rem 1rem; }
        .article-content code { background: #f3f4f6; color: #01458E; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem; font-family: monospace; }
        .article-content pre  { background: #1f2937; color: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1rem 0; font-size: 0.8rem; }
        .article-content pre code { background: transparent; color: inherit; padding: 0; }
        .article-content img, .article-content video { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 0.875rem 0; }
        .article-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .article-content th, .article-content td { border: 1px solid #e5e7eb; padding: 0.625rem 0.75rem; text-align: left; font-size: 0.8rem; }
        .article-content th { background: #F0F4F8; font-weight: 700; color: #374151; }
        .article-content tr:hover td { background: #fafafa; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarTimTeknis')

    @php
        $bidangLabels = [
            'e_government'                      => 'E-Government',
            'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
            'statistik_persandian'              => 'Statistik & Persandian',
        ];
        $bidangLabel = $teknis?->bidang
            ? ($bidangLabels[$teknis->bidang->nama_bidang] ?? $teknis->bidang->nama_bidang)
            : '—';
        $kodeArtikel = 'KB-' . strtoupper(substr($article->id, 0, 8));
    @endphp

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- ── Header ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-6 py-3.5 flex items-center justify-between shrink-0 sticky top-0 z-30">

            {{-- Breadcrumb --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('tim_teknis.pustaka') }}"
                   class="text-gray-400 text-xs hover:text-gray-600 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    Pustaka Teknis
                </a>
                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="font-semibold text-gray-800 text-sm truncate max-w-xs">{{ $article->nama_artikel_sop }}</span>
            </div>

            {{-- Kanan: badge + tombol kembali --}}
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-amber-600 bg-amber-50 border border-amber-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Hanya Baca
                </span>
                <a href="{{ route('tim_teknis.pustaka') }}"
                   class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </header>

        {{-- ── Warning Banner ── --}}
        <div class="bg-red-50 border-b border-red-100 px-4 lg:px-6 py-2.5 flex items-center gap-3 text-sm text-red-600">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span><strong>INTERNAL</strong> — Artikel ini bersifat rahasia dan hanya dapat diakses oleh staf IT bidang <strong>{{ $bidangLabel }}</strong>. Jangan disebarkan ke pihak luar.</span>
        </div>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-3 lg:px-6 py-4 lg:py-6 overflow-y-auto">
            <div class="flex flex-col lg:flex-row gap-5 lg:gap-7 items-start">

                {{-- ── KIRI: Konten Artikel ── --}}
                <div class="flex-1 flex flex-col gap-5 min-w-0">

                    {{-- Header Image --}}
                    @if($article->header_image)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">
                        <img src="{{ asset('storage/' . $article->header_image) }}"
                             alt="Header {{ $article->nama_artikel_sop }}"
                             class="w-full max-h-64 object-cover">
                    </div>
                    @endif

                    {{-- Judul --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 px-5 lg:px-8 py-5 lg:py-7">
                        <h1 class="text-2xl font-bold text-gray-900 leading-snug">{{ $article->nama_artikel_sop }}</h1>
                        @if($article->deskripsi_singkat)
                        <p class="text-sm text-gray-500 mt-3 leading-relaxed border-t border-gray-100 pt-3">
                            {{ $article->deskripsi_singkat }}
                        </p>
                        @endif
                    </div>

                    {{-- Isi Konten --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 px-5 lg:px-8 py-5 lg:py-7">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-5">Isi Artikel</p>
                        @if($article->isi_konten)
                            <div class="article-content">{!! $article->isi_konten !!}</div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <svg class="w-10 h-10 text-gray-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                                <p class="text-sm text-gray-400">Konten artikel belum tersedia.</p>
                            </div>
                        @endif
                    </div>

                </div>

                {{-- ── KANAN: Sidebar Info ── --}}
                <div class="w-full lg:w-72 lg:shrink-0 space-y-4">

                    {{-- Tags --}}
                    @if($article->tags->count())
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Tag</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($article->tags as $tag)
                            <span class="inline-flex items-center text-xs px-3 py-1.5 rounded-full bg-blue-50 text-[#01458E] font-medium">
                                {{ $tag->nama_tag }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Lampiran File --}}
                    @if($article->lampiran_file)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Lampiran File</p>
                        <a href="{{ asset('storage/' . $article->lampiran_file) }}"
                           target="_blank"
                           class="flex items-center gap-3 p-3 bg-[#F0F4F8] rounded-xl border border-gray-200 hover:border-[#01458E] hover:bg-[#EEF3F9] transition-colors group">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0" style="background:#01458E;">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-gray-700 truncate group-hover:text-[#01458E] transition-colors">
                                    {{ basename($article->lampiran_file) }}
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Klik untuk unduh</p>
                            </div>
                        </a>
                    </div>
                    @endif

                    {{-- Informasi Artikel --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Informasi Artikel</p>
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">ID Artikel</span>
                                <span class="font-semibold text-gray-700">{{ $kodeArtikel }}</span>
                            </div>
                            @if($article->rating)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400">Rating</span>
                                <span class="font-semibold text-gray-700 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    {{ number_format($article->rating, 1) }}/5
                                </span>
                            </div>
                            @endif
                            <div class="border-t border-gray-100 pt-3 space-y-2">
                                <div>
                                    <p class="text-gray-400 mb-0.5">Dibuat</p>
                                    <p class="font-semibold text-gray-700">
                                        {{ $article->created_at?->format('d M Y, H:i') ?? '—' }} WIB
                                    </p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-0.5">Terakhir Diperbarui</p>
                                    <p class="font-semibold text-gray-700">
                                        {{ $article->updated_at?->format('d M Y, H:i') ?? '—' }} WIB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                {{-- end sidebar --}}

            </div>
        </main>

    </div>

</body>
</html>
