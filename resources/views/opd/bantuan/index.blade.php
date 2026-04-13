<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pusat Bantuan — SiPasti</title>
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

    {{-- ── Hero / Search ── --}}
    <div class="rounded-2xl px-8 py-10 text-center mb-8"
         style="background:linear-gradient(135deg,#01458E 0%,#0369a1 100%);">
        <h1 class="text-2xl font-bold text-white mb-1">Cari solusi terbaik untuk segala permasalahan IT Anda</h1>
        <p class="text-sm text-blue-200 mb-6">Temukan panduan, tutorial, dan solusi dari tim IT Diskominfo</p>

        <form method="GET" action="{{ route('opd.bantuan') }}" class="flex gap-3 max-w-xl mx-auto">
            <div class="flex-1 relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                     stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Ketik masalah Anda..."
                       class="w-full pl-11 pr-4 py-3 rounded-xl text-sm bg-white border-0 focus:outline-none focus:ring-2 focus:ring-white/40 text-gray-800 placeholder-gray-400">
            </div>
            <button type="submit"
                    class="px-6 py-3 rounded-xl text-sm font-bold text-white transition hover:opacity-90 active:scale-95"
                    style="background:#0284c7;">
                Cari
            </button>
        </form>
    </div>

    {{-- ── Hasil Pencarian ── --}}
    @if($search)
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-gray-800">
                Hasil pencarian untuk <span class="text-[#01458E]">"{{ $search }}"</span>
            </h2>
            <a href="{{ route('opd.bantuan') }}" class="text-xs text-gray-400 hover:text-gray-600 transition-colors">Hapus pencarian</a>
        </div>

        @if($hasilCari->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-12 text-center">
            <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-500">Tidak ada artikel yang cocok</p>
            <p class="text-xs text-gray-400 mt-1">Coba kata kunci lain atau <a href="{{ route('opd.diagnosis.index') }}" class="text-[#01458E] hover:underline">buat pengaduan</a></p>
        </div>
        @else
        <div class="space-y-2">
            @foreach($hasilCari as $artikel)
            <a href="{{ route('opd.bantuan.artikel', $artikel->id) }}"
               class="flex items-center justify-between bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4
                      hover:border-[#01458E]/30 hover:shadow-md transition-all group">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 group-hover:text-[#01458E] transition-colors truncate">
                        {{ $artikel->nama_artikel_sop }}
                    </p>
                    @if($artikel->deskripsi_singkat)
                    <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $artikel->deskripsi_singkat }}</p>
                    @endif
                    @if($artikel->kategori)
                    <span class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-full"
                          style="background:#EEF3F9;color:#01458E;">{{ $artikel->kategori->nama_kategori }}</span>
                    @endif
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-[#01458E] shrink-0 ml-4 transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    @if(!$search)

    {{-- ── Kategori ── --}}
    <div class="mb-8">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4 text-center">atau pilih kategori untuk menemukan bantuan dengan cepat</p>
        @php
            $iconMap = [
                'Jaringan & Konektivitas' => ['icon' => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.038 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.038-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253M3 12c0 .778.099 1.533.284 2.253', 'bg' => '#EEF3F9', 'color' => '#01458E'],
                'Perangkat Keras'         => ['icon' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 15V5.25m19.5 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 7.409A2.25 2.25 0 012.25 5.493V5.25', 'bg' => '#FFF7ED', 'color' => '#EA580C'],
                'Perangkat Lunak'         => ['icon' => 'M6.75 7.5l3 2.25-3 2.25m4.5 0h3m-9 8.25h13.5A2.25 2.25 0 0021 18V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v12a2.25 2.25 0 002.25 2.25z', 'bg' => '#F0FDF4', 'color' => '#16A34A'],
                'Keamanan Siber'          => ['icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', 'bg' => '#FFF1F2', 'color' => '#DC2626'],
                'Layanan TIK'             => ['icon' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z', 'bg' => '#F5F3FF', 'color' => '#7C3AED'],
            ];
            $defaultIcon = ['icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z', 'bg' => '#EEF3F9', 'color' => '#01458E'];
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @forelse($kategoris as $kat)
            @php $ic = $iconMap[$kat->nama_kategori] ?? $defaultIcon; @endphp
            <a href="{{ route('opd.bantuan.kategori', $kat->id) }}"
               class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-5
                      hover:border-[#01458E]/30 hover:shadow-md transition-all group text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3"
                     style="background:{{ $ic['bg'] }};">
                    <svg class="w-6 h-6" fill="none" stroke="{{ $ic['color'] }}" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ic['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-800 group-hover:text-[#01458E] transition-colors leading-tight">
                    {{ $kat->nama_kategori }}
                </p>
                @if($kat->deskripsi)
                <p class="text-[11px] text-gray-400 mt-1 leading-tight line-clamp-2">{{ $kat->deskripsi }}</p>
                @endif
                <p class="text-[11px] font-semibold mt-2" style="color:#01458E;">
                    {{ $kat->artikel_count }} artikel
                </p>
            </a>
            @empty
            <p class="col-span-4 text-sm text-gray-400 text-center py-6">Belum ada kategori tersedia.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Artikel Paling Sering Dibaca ── --}}
    @if($topArtikel->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-base font-bold text-gray-800 mb-4">Artikel Paling Sering Dibaca</h2>
        <div class="space-y-2">
            @foreach($topArtikel as $artikel)
            <a href="{{ route('opd.bantuan.artikel', $artikel->id) }}"
               class="flex items-center justify-between bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4
                      hover:border-[#01458E]/30 hover:shadow-md transition-all group">
                <span class="text-sm font-semibold text-gray-700 group-hover:text-[#01458E] transition-colors">
                    {{ $artikel->nama_artikel_sop }}
                </span>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-[#01458E] shrink-0 ml-4 transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── FAQ ── --}}
    <div class="mb-8" x-data="{ open: null }">
        <h2 class="text-base font-bold text-gray-800 mb-4">Pertanyaan Umum (FAQ)</h2>
        <div class="space-y-2">
            @php
            $faqs = [
                ['q' => 'Berapa lama waktu respon jika saya membuat tiket pengaduan?',         'a' => 'Untuk jam kerja (08.00 - 16.00), Admin Helpdesk akan merespon tiket Anda maksimal dalam 15 menit.'],
                ['q' => 'Apakah saya bisa membatalkan tiket yang sudah dikirim?',               'a' => 'Tiket yang sudah dikirim tidak dapat dibatalkan secara langsung. Hubungi Admin Helpdesk melalui chat jika ada perubahan.'],
                ['q' => 'Apa yang harus disiapkan sebelum teknisi datang?',                    'a' => 'Pastikan perangkat bermasalah dalam kondisi menyala, lokasi mudah diakses, dan terdapat staf yang mendampingi teknisi selama pemeriksaan.'],
                ['q' => 'Bagaimana cara mengetahui status pengaduan saya?',                    'a' => 'Buka menu "Pengaduan Saya" untuk melihat status terkini tiket Anda secara real-time.'],
                ['q' => 'Apakah bisa mengajukan pengaduan di luar jam kerja?',                 'a' => 'Bisa. Tiket akan diproses pada hari kerja berikutnya. Untuk darurat, hubungi kontak emergency yang tertera di profil Diskominfo.'],
            ];
            @endphp

            @foreach($faqs as $i => $faq)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <button @click="open = open === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-5 py-4 text-left">
                    <span class="text-sm font-semibold text-gray-800">Q: {{ $faq['q'] }}</span>
                    <svg class="w-4 h-4 text-gray-400 shrink-0 ml-4 transition-transform duration-200"
                         :class="open === {{ $i }} ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="px-5 pb-4 text-sm text-gray-500 leading-relaxed border-t border-gray-50"
                     style="display:none;">
                    A: {{ $faq['a'] }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── CTA Buat Pengaduan ── --}}
    <div class="rounded-2xl px-8 py-7 flex flex-col sm:flex-row items-center justify-between gap-4"
         style="background:linear-gradient(135deg,#01458E 0%,#0369a1 100%);">
        <div>
            <p class="text-base font-bold text-white">Tidak menemukan solusi yang Anda cari?</p>
            <p class="text-sm text-blue-200 mt-0.5">Buat tiket pengaduan dan tim kami akan membantu Anda segera.</p>
        </div>
        <a href="{{ route('opd.diagnosis.index') }}"
           class="shrink-0 px-6 py-3 rounded-xl text-sm font-bold text-[#01458E] bg-white
                  transition hover:shadow-lg hover:-translate-y-0.5 active:scale-95">
            Buat Pengaduan
        </a>
    </div>

    @endif {{-- end if !search --}}

</main>

<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

</body>
</html>
