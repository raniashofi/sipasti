<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solusi — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        /* ── Artikel prose — cermin dari previewContent di pustaka ── */
        .prose-artikel { font-size: 0.875rem; line-height: 1.8; color: #374151; }
        .prose-artikel h1,.prose-artikel h2,.prose-artikel h3,
        .prose-artikel h4,.prose-artikel h5,.prose-artikel h6 { font-weight: 700; margin: 1.25rem 0 .5rem; color: #111827; }
        .prose-artikel h1 { font-size: 1.35rem; }
        .prose-artikel h2 { font-size: 1.15rem; }
        .prose-artikel h3 { font-size: 1rem; }
        .prose-artikel h4 { font-size: .9rem; }
        .prose-artikel p { margin-bottom: .75rem; }
        .prose-artikel strong { font-weight: 700; color: #111827; }
        .prose-artikel em { font-style: italic; }
        .prose-artikel u  { text-decoration: underline; }
        .prose-artikel s  { text-decoration: line-through; }
        .prose-artikel ul,.prose-artikel ol { padding-left: 1.5rem; margin-bottom: .75rem; }
        .prose-artikel ul { list-style-type: disc; }
        .prose-artikel ol { list-style-type: decimal; }
        .prose-artikel ul ul { list-style-type: circle; }
        .prose-artikel li  { margin-bottom: .25rem; }
        .prose-artikel blockquote {
            border-left: 4px solid #01458E;
            padding-left: 1rem; margin: 1rem 0;
            color: #6b7280; font-style: italic;
        }
        .prose-artikel a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; }
        .prose-artikel a:hover { color: #003a70; }
        .prose-artikel code {
            background: #f3f4f6; color: #01458E;
            padding: .1rem .35rem; border-radius: .25rem; font-size: .8rem;
        }
        .prose-artikel pre {
            background: #1f2937; color: #f3f4f6;
            padding: .75rem; border-radius: .5rem;
            overflow-x: auto; margin: .75rem 0; font-size: .75rem;
        }
        .prose-artikel pre code { background: transparent; color: inherit; padding: 0; }
        .prose-artikel img,.prose-artikel video {
            max-width: 100%; height: auto;
            border-radius: .5rem; margin: .75rem 0;
        }
        .prose-artikel table { width: 100%; border-collapse: collapse; margin: .75rem 0; }
        .prose-artikel th,.prose-artikel td {
            border: 1px solid #e5e7eb; padding: .5rem;
            text-align: left; font-size: .75rem;
        }
        .prose-artikel th { background: #f9fafb; font-weight: 600; }
        .prose-artikel hr { border-color: #e5e7eb; margin: 1.25rem 0; }
    </style>
</head>
<body class="min-h-screen">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 space-y-5 sm:space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400">
        <a href="{{ route('opd.diagnosis.index') }}" class="hover:text-[#01458E] transition-colors">Buat Pengaduan</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium">Solusi Terdeteksi</span>
    </div>

    {{-- ── Artikel solusi ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="px-4 py-5 sm:px-7 sm:py-6 border-b border-gray-100"
             style="background-color: #ffffff;">
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-xl flex items-center justify-center shrink-0"
                     style="background:#01458E;">
                    <svg class="w-8 h-8 sm:w-12 sm:h-12 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-bold text-[#01458E] uppercase tracking-widest mb-1">Solusi Terdeteksi</p>
                    <h1 class="text-base sm:text-xl font-bold text-gray-900 leading-snug">
                        {{ $node->judul_solusi ?? $kb?->nama_artikel_sop ?? 'Solusi Ditemukan' }}
                    </h1>
                    @if($node->penjelasan_solusi)
                    <p class="text-xs sm:text-sm text-gray-500 mt-1.5 leading-relaxed">{{ $node->penjelasan_solusi }}</p>
                    @elseif($kb?->deskripsi_singkat)
                    <p class="text-xs sm:text-sm text-gray-500 mt-1.5 leading-relaxed">{{ $kb->deskripsi_singkat }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta
        <div class="px-7 py-4 border-b border-gray-100 flex items-center gap-6 text-sm">
            <div>
                <p class="text-[11px] text-gray-400 mb-0.5">Kategori</p>
                <p class="font-semibold text-gray-800">{{ $kategoriNama ?: '—' }}</p>
            </div>
            @if($diagnosa)
            <div>
                <p class="text-[11px] text-gray-400 mb-0.5">Diagnosa</p>
                <p class="font-semibold text-gray-800 text-xs">{{ $diagnosa }}</p>
            </div>
            @endif
        </div> --}}

        {{-- Deskripsi singkat highlight --}}
        @if($kb?->deskripsi_singkat && $node->penjelasan_solusi)
        <div class="mx-4 sm:mx-7 mt-4 sm:mt-5 p-3 sm:p-4 bg-blue-50 border border-blue-100 rounded-xl">
            <p class="text-xs sm:text-sm text-blue-900 leading-relaxed">{{ $kb->deskripsi_singkat }}</p>
        </div>
        @endif

        {{-- Konten artikel --}}
        <div class="px-4 py-5 sm:px-7 sm:py-7">
            @if($kb?->isi_konten)
                <div class="prose-artikel">{!! $kb->isi_konten !!}</div>
            @elseif($node->penjelasan_solusi)
                <div class="prose-artikel"><p>{{ $node->penjelasan_solusi }}</p></div>
            @else
                <div class="flex items-center gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <svg class="w-5 h-5 text-yellow-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    <p class="text-sm text-yellow-800">Belum ada konten artikel untuk solusi ini.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- ── CTA resolusi ── --}}
    @php
        $tiketUrl = route('opd.diagnosis.tiket') . '?' . http_build_query([
            'kategori_id'            => $kategoriId,
            'kategori_nama'          => $kategoriNama,
            'kategori_deskripsi'     => $kategoriDeskripsi ?? '',
            'kb_id'                  => $node->kb_id ?? '',
            'sop_internal_id'        => $node->sop_internal_id ?? '',
            'bidang_id'              => $bidangId ?? '',
            'rekomendasi_penanganan' => $node->rekomendasi_penanganan ?? 'admin',
        ]);
    @endphp

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-4 py-5 sm:px-8 sm:py-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between sm:gap-5">
        <div>
            <p class="text-base sm:text-lg font-bold text-gray-800">Silakan coba solusi di atas.</p>
            <p class="text-sm text-gray-800 mt-0.5">Apakah masalah Anda sudah teratasi?</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:shrink-0">
            {{-- Berhasil --}}
            <a href="{{ route('opd.dashboard') }}"
               class="flex flex-col items-center justify-center px-5 py-4 sm:px-8 sm:py-5 rounded-xl text-white text-center
                      transition hover:-translate-y-0.5 hover:shadow-md w-full sm:w-auto"
               style="background-color:#16A34A; min-width:0;">
                <span class="text-sm font-bold tracking-wide">YA, BERHASIL</span>
                <span class="text-[11px] font-medium opacity-85 mt-0.5">Masalah selesai, kembali ke Beranda</span>
            </a>

            {{-- Masih bermasalah --}}
            <a href="{{ $tiketUrl }}"
               class="flex flex-col items-center justify-center px-5 py-4 sm:px-8 sm:py-5 rounded-xl text-white text-center
                      transition hover:-translate-y-0.5 hover:shadow-md w-full sm:w-auto"
               style="background-color:#DC2626; min-width:0;">
                <span class="text-sm font-bold tracking-wide">TIDAK, MASIH BERMASALAH</span>
                <span class="text-[11px] font-medium opacity-85 mt-0.5">Lanjut buat Tiket Pengaduan</span>
            </a>
        </div>
    </div>

</main>
</body>
</html>
