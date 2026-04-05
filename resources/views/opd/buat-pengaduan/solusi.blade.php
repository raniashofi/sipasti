<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solusi — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        /* Render isi_konten as readable article */
        .prose-konten h1, .prose-konten h2, .prose-konten h3, .prose-konten h4 {
            font-weight: 700;
            color: #111827;
            margin-top: 1.25rem;
            margin-bottom: 0.4rem;
        }
        .prose-konten h1 { font-size: 1.15rem; }
        .prose-konten h2 { font-size: 1.05rem; }
        .prose-konten h3 { font-size: 0.95rem; }
        .prose-konten p  { font-size: 0.875rem; color: #4B5563; line-height: 1.7; margin-bottom: 0.5rem; }
        .prose-konten ul, .prose-konten ol { padding-left: 1.25rem; margin-bottom: 0.75rem; }
        .prose-konten li { font-size: 0.875rem; color: #4B5563; line-height: 1.7; }
        .prose-konten strong { font-weight: 600; color: #111827; }
        .prose-konten hr { border-color: #E5E7EB; margin: 1rem 0; }
    </style>
</head>
<body class="min-h-screen">

    <div class="sticky top-0 z-30 shadow-sm">
        @include('layouts.topBarOpd')
    </div>

    <main class="max-w-screen-lg mx-auto px-6 lg:px-8 py-10 space-y-5">

        {{-- Solution article card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">

            {{-- Header --}}
            <div class="flex items-start gap-4 mb-5 pb-5 border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color:rgba(1,69,142,0.09);">
                    <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-[#01458E] uppercase tracking-wide mb-0.5">
                        Solusi Terdeteksi
                    </p>
                    <h2 class="text-xl font-bold" style="color:#01458E;">
                        {{ $node->judul_solusi ?? $kb?->nama_artikel_sop ?? 'Solusi Ditemukan' }}
                    </h2>
                    @if($node->penjelasan_solusi)
                    <p class="text-sm text-[#4A7BB0] mt-1 leading-relaxed">
                        {{ $node->penjelasan_solusi }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- KB article content --}}
            @if($kb?->isi_konten)
            <div class="prose-konten">
                {!! nl2br(e($kb->isi_konten)) !!}
            </div>
            @else
            <p class="text-sm text-gray-400">Tidak ada konten artikel tersedia.</p>
            @endif
        </div>

        <hr class="border-[#D1DBE8]">

        {{-- Resolution prompt --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-8 py-6
                    flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5">
            <div>
                <p class="text-sm font-semibold text-gray-800">
                    Silakan coba solusi di atas.
                </p>
                <p class="text-sm text-gray-400 mt-0.5">Apakah masalah Anda sudah teratasi?</p>
            </div>
            <div class="flex gap-3 shrink-0">

                {{-- Berhasil --}}
                <a href="{{ route('opd.dashboard') }}"
                   class="flex flex-col items-center justify-center px-6 py-3 rounded-xl text-white text-center transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background-color:#16A34A; min-width:160px;">
                    <span class="text-sm font-bold tracking-wide">YA, BERHASIL</span>
                    <span class="text-[11px] font-medium opacity-80 mt-0.5">Masalah selesai, kembali ke Beranda</span>
                </a>

                {{-- Masih bermasalah → form tiket --}}
                @php
                    $tiketUrl = route('opd.diagnosis.tiket') . '?' . http_build_query([
                        'kategori_id'   => $kategoriId,
                        'kategori_nama' => $kategoriNama,
                        'diagnosa'      => ($node->judul_solusi ?? $kb?->nama_artikel_sop ?? 'Tidak diketahui'),
                    ]);
                @endphp
                <a href="{{ $tiketUrl }}"
                   class="flex flex-col items-center justify-center px-6 py-3 rounded-xl text-white text-center transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background-color:#DC2626; min-width:160px;">
                    <span class="text-sm font-bold tracking-wide">TIDAK, MASIH BERMASALAH</span>
                    <span class="text-[11px] font-medium opacity-80 mt-0.5">Lanjut buat Tiket Pengaduan</span>
                </a>
            </div>
        </div>

    </main>
</body>
</html>
