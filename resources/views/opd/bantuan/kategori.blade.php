<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kategori->nama_kategori }} — Pusat Bantuan SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="flex-1 max-w-screen-lg w-full mx-auto px-5 md:px-8 py-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-7">
        <a href="{{ route('opd.bantuan') }}" class="hover:text-[#01458E] transition-colors">Pusat Bantuan</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium">{{ $kategori->nama_kategori }}</span>
    </div>

    {{-- Header --}}
    <div class="mb-7">
        <h1 class="text-xl font-bold text-gray-900">
            Cari artikel tentang permasalahan {{ $kategori->nama_kategori }}
        </h1>
        @if($kategori->deskripsi)
        <p class="text-sm text-gray-500 mt-1">{{ $kategori->deskripsi }}</p>
        @endif
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('opd.bantuan.kategori', $kategori->id) }}" class="mb-7">
        <div class="flex gap-3 max-w-xl">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Ketik masalah Anda..."
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl text-sm bg-white border border-gray-200
                              focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]
                              placeholder-gray-300 transition-all">
            </div>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95"
                    style="background:#01458E;">
                Cari
            </button>
        </div>
    </form>

    {{-- Artikel List --}}
    @if($artikels->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-14 text-center">
        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-500">
            @if($search) Tidak ada artikel yang cocok dengan "{{ $search }}"
            @else Belum ada artikel untuk kategori ini
            @endif
        </p>
        @if($search)
        <a href="{{ route('opd.bantuan.kategori', $kategori->id) }}"
           class="inline-block mt-3 text-xs text-[#01458E] hover:underline">Lihat semua artikel</a>
        @endif
    </div>
    @else
    <div class="space-y-2">
        @foreach($artikels as $artikel)
        <a href="{{ route('opd.bantuan.artikel', $artikel->id) }}"
           class="flex items-center justify-between bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4
                  hover:border-[#01458E]/30 hover:shadow-md transition-all group">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 group-hover:text-[#01458E] transition-colors">
                    {{ $artikel->nama_artikel_sop }}
                </p>
                @if($artikel->deskripsi_singkat)
                <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $artikel->deskripsi_singkat }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-4">
                @if($artikel->total_views > 0)
                <span class="text-[11px] text-gray-400 hidden sm:block">{{ number_format($artikel->total_views) }} views</span>
                @endif
                <svg class="w-4 h-4 text-gray-300 group-hover:text-[#01458E] transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </div>
        </a>
        @endforeach
    </div>

    <p class="text-xs text-gray-400 mt-4 text-center">{{ $artikels->count() }} artikel ditemukan</p>
    @endif

    {{-- CTA --}}
    <div class="mt-10 rounded-2xl border border-gray-200 bg-white shadow-sm px-7 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <p class="text-sm font-bold text-gray-800">Butuh Bantuan?</p>
            <p class="text-xs text-gray-500 mt-0.5">Tim support kami siap membantu Anda 24/7</p>
        </div>
        <a href="{{ route('opd.diagnosis.index') }}"
           class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition hover:-translate-y-0.5 hover:shadow-lg active:scale-95"
           style="background:#01458E;">
            Buat Pengaduan
        </a>
    </div>

</main>

<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

</body>
</html>
