<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview: {{ $article->nama_artikel_sop }} — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <a href="javascript:history.back()"
                   class="w-8 h-8 rounded-lg bg-[#F0F4F8] flex items-center justify-center text-gray-500 hover:bg-[#E5EBF3] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-500">Pustaka</span>
                    <span class="text-gray-300">/</span>
                    <span class="font-semibold text-gray-800">Preview Artikel</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8 max-w-4xl mx-auto w-full">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                @if($article->header_image)
                    <img src="{{ asset('storage/' . $article->header_image) }}"
                         alt="Header"
                         class="w-full h-64 object-cover">
                @endif

                <div class="p-6 lg:p-10">

                    <div class="flex flex-wrap items-center gap-2 mb-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $article->status_publikasi === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $article->status_publikasi === 'published' ? 'Published' : 'Draft' }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            {{ $article->visibilitas_akses === 'opd' ? 'OPD' : 'Internal' }}
                        </span>
                        @if($article->kategoriArtikel)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ $article->kategoriArtikel->nama_kategori }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 mb-3">{{ $article->nama_artikel_sop }}</h1>

                    @if($article->deskripsi_singkat)
                        <p class="text-gray-500 text-sm mb-6 leading-relaxed">{{ $article->deskripsi_singkat }}</p>
                    @endif

                    @if($article->isi_konten)
                        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
                            {!! $article->isi_konten !!}
                        </div>
                    @else
                        <p class="text-gray-400 italic">Konten artikel belum tersedia.</p>
                    @endif

                    @if($article->lampiran_file)
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <p class="text-sm font-medium text-gray-700 mb-2">Lampiran</p>
                            <a href="{{ asset('storage/' . $article->lampiran_file) }}"
                               target="_blank"
                               class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828L18 9.828A4 4 0 1012.172 4L5.586 10.586a6 6 0 108.485 8.485L20 13"/>
                                </svg>
                                Unduh Lampiran
                            </a>
                        </div>
                    @endif

                </div>
            </div>

        </main>
    </div>

</body>
</html>
