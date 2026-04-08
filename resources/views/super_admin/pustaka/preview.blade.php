<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $article->nama_artikel_sop }} — Preview</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* Content Preview Styling */
        .preview-content { }
        .preview-content h1 { font-size: 2.5rem; font-weight: 800; margin: 2rem 0 1rem; }
        .preview-content h1:first-child { margin-top: 0; }
        .preview-content h2 { font-size: 1.875rem; font-weight: 700; margin: 2rem 0 1rem; }
        .preview-content h3 { font-size: 1.5rem; font-weight: 700; margin: 1.5rem 0 0.75rem; }
        .preview-content h4 { font-size: 1.25rem; font-weight: 700; margin: 1.25rem 0 0.5rem; }
        .preview-content h5 { font-size: 1.125rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .preview-content h6 { font-size: 1rem; font-weight: 600; margin: 1rem 0 0.5rem; }
        .preview-content p { margin-bottom: 1rem; line-height: 1.8; color: #374151; }
        .preview-content strong { font-weight: 700; color: #111827; }
        .preview-content em { font-style: italic; }
        .preview-content u { text-decoration: underline; }
        .preview-content s { text-decoration: line-through; }
        .preview-content a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; }
        .preview-content a:hover { color: #003a70; }
        .preview-content ul { list-style: disc inside; padding-left: 1.5rem; margin-bottom: 1rem; }
        .preview-content ol { list-style: decimal inside; padding-left: 1.5rem; margin-bottom: 1rem; }
        .preview-content li { margin-bottom: 0.5rem; }
        .preview-content blockquote { border-left: 4px solid #01458E; padding-left: 1.5rem; margin: 1.5rem 0; color: #6b7280; font-style: italic; }
        .preview-content code { background: #f3f4f6; color: #01458E; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-family: 'Courier New', monospace; }
        .preview-content pre { background: #1f2937; color: #f3f4f6; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1rem 0; line-height: 1.5; }
        .preview-content pre code { background: transparent; color: inherit; padding: 0; }
        .preview-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1.5rem 0; }
        .preview-content video { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1.5rem 0; }
        .preview-content iframe { max-width: 100%; border-radius: 0.5rem; margin: 1.5rem 0; }
        .preview-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; }
        .preview-content th, .preview-content td { border: 1px solid #e5e7eb; padding: 0.75rem; text-align: left; }
        .preview-content th { background: #f3f4f6; font-weight: 600; }

        /* Scrollbar */
        html { scrollbar-width: thin; scrollbar-color: #D1D5DB transparent; }
        html::-webkit-scrollbar { width: 8px; }
        html::-webkit-scrollbar-track { background: transparent; }
        html::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
        html::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
    </style>
</head>
<body class="bg-white min-h-screen">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">{{ $article->status_publikasi === 'published' ? '✅ Published' : '📝 Draft' }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $article->nama_artikel_sop }}</h1>
            </div>
            <button onclick="window.close()" class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors font-medium">
                ✕ Tutup
            </button>
        </div>
    </header>

    {{-- Meta Information --}}
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-6 py-4">
            <div class="flex items-center gap-6 text-sm">
                <div>
                    <p class="text-gray-500">Kategori</p>
                    <p class="font-semibold text-gray-900">{{ $article->kategori->nama_kategori ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total Views</p>
                    <p class="font-semibold text-gray-900">{{ number_format($article->total_views) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Rating</p>
                    <p class="font-semibold text-gray-900">⭐ {{ $article->rating ? number_format($article->rating, 1) : '—' }}/5</p>
                </div>
                <div>
                    <p class="text-gray-500">Dibuat</p>
                    <p class="font-semibold text-gray-900">{{ $article->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Header Image --}}
    @if($article->header_image)
    <div class="bg-gray-50 border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-6 py-6">
            <img src="{{ asset('storage/' . $article->header_image) }}"
                 alt="Header"
                 class="w-full h-64 object-cover rounded-lg">
        </div>
    </div>
    @endif

    {{-- Main Content --}}
    <main class="max-w-4xl mx-auto px-6 py-12">

        {{-- Deskripsi Singkat --}}
        @if($article->deskripsi_singkat)
        <div class="mb-8 p-6 bg-blue-50 border border-blue-100 rounded-lg">
            <p class="text-lg text-blue-900">{{ $article->deskripsi_singkat }}</p>
        </div>
        @endif

        {{-- Konten Artikel --}}
        <div class="preview-content mb-12">
            {!! $article->isi_konten !!}
        </div>

        {{-- Tags --}}
        @if($article->tags->count())
        <div class="pt-8 border-t border-gray-200">
            <p class="text-sm font-semibold text-gray-600 mb-3">Tag:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($article->tags as $tag)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                    {{ $tag->nama_tag }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Lampiran --}}
        @if($article->lampiran_file)
        <div class="mt-8 pt-8 border-t border-gray-200">
            <p class="text-sm font-semibold text-gray-600 mb-3">File Lampiran:</p>
            <a href="{{ asset('storage/' . $article->lampiran_file) }}"
               download
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 8l4-4m-4 4l-4-4"/>
                </svg>
                {{ basename($article->lampiran_file) }}
            </a>
        </div>
        @endif

    </main>

    {{-- Footer --}}
    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="max-w-4xl mx-auto px-6 py-8 text-center text-sm text-gray-600">
            <p>Artikel dibuat pada {{ $article->created_at->format('d M Y, H:i') }} WIB</p>
            <p class="text-xs mt-2 text-gray-400">KB-{{ strtoupper(substr($article->id, 0, 8)) }}</p>
        </div>
    </footer>

</body>
</html>
