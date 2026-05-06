<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnosis Mandiri — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
    </style>
</head>
<body class="min-h-screen">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="max-w-screen-lg mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
      x-data="{
          selected: null,
          urlYa:    '{{ addslashes($urlYa ?? '') }}',
          urlTidak: '{{ addslashes($urlTidak ?? '') }}',
          lanjut() {
              if (!this.selected) return;
              window.location.href = this.selected === 'ya' ? this.urlYa : this.urlTidak;
          }
      }">

{{-- Page header — di luar container biru --}}
    <div class="mb-6 sm:mb-8">
        {{-- Tombol Kembali ke Index --}}
        <a href="{{ route('opd.diagnosis.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-500 hover:text-[#01458E] transition-colors mb-3 sm:mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Pilihan Kategori
        </a>

        <h1 class="text-lg sm:text-xl font-bold text-gray-900 mb-1">Kategori: {{ $kategoriNama }}</h1>
        @if(!empty($kategoriDeskripsi))
        <p class="text-sm text-gray-400 leading-relaxed">{{ $kategoriDeskripsi }}</p>
        @endif
    </div>

    {{-- Kontainer biru muda — area pertanyaan --}}
    <div class="px-4 py-8 sm:px-12 sm:pt-14 sm:pb-10 md:px-24 md:pt-20 md:pb-12" style="background-color:#E8EEF5;">

        @php
            $labels = ['ya' => 'Ya', 'tidak' => 'Tidak'];
            if ($node->hint_konteks) {
                $parsed = json_decode($node->hint_konteks, true);
                if (is_array($parsed)) {
                    $labels['ya']    = $parsed['ya']    ?? 'Ya';
                    $labels['tidak'] = $parsed['tidak'] ?? 'Tidak';
                }
            }
        @endphp

        {{-- Question label + text --}}
        <div class="mb-5 pb-5 sm:mb-6 sm:pb-6 border-b border-[#C8D5E8]">
            <p class="text-xs sm:text-sm text-gray-400 font-normal mb-1">Question {{ $qNum }}</p>
            <p class="text-sm sm:text-base font-bold text-gray-900 leading-snug">
                {{ $node->teks_pertanyaan ?? 'Pertanyaan tidak tersedia.' }}
            </p>
        </div>

        {{-- Answer options --}}
        <div class="space-y-3 max-w-3xl mx-auto">

            @if($urlYa)
            <button type="button"
                    @click="selected = 'ya'"
                    :class="selected === 'ya'
                        ? 'border-[#01458E] border-l-[5px] shadow-md'
                        : 'border-transparent'"
                    class="w-full text-left bg-white rounded-2xl px-6 py-5 border-2 transition-all duration-150 cursor-pointer hover:shadow-sm">
                <span class="text-sm font-semibold text-gray-800">{{ $labels['ya'] }}</span>
            </button>
            @endif

            @if($urlTidak)
            <button type="button"
                    @click="selected = 'tidak'"
                    :class="selected === 'tidak'
                        ? 'border-[#01458E] border-l-[5px] shadow-md'
                        : 'border-transparent'"
                    class="w-full text-left bg-white rounded-2xl px-6 py-5 border-2 transition-all duration-150 cursor-pointer hover:shadow-sm">
                <span class="text-sm font-semibold text-gray-800">{{ $labels['tidak'] }}</span>
            </button>
            @endif

            @if(!$urlYa && !$urlTidak)
            <p class="text-sm text-gray-400 text-center py-6">
                Tidak ada pilihan jawaban untuk pertanyaan ini.
            </p>
            @endif

        </div>

        {{-- Navigation buttons — di dalam container, di bawah --}}
        <div class="flex items-center justify-center gap-3 mt-10 sm:mt-16 md:mt-24">

            @if($qNum > 1)
            <button type="button"
                    onclick="history.back()"
                    class="flex items-center gap-2 px-5 py-2.5 sm:px-7 sm:py-3 rounded-full border-2 border-[#01458E]
                           text-xs sm:text-sm font-semibold text-[#01458E] bg-white hover:bg-[#EEF3F9] transition-colors">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Kembali
            </button>
            @endif

            <button type="button"
                    @click="lanjut()"
                    :disabled="!selected"
                    :class="selected
                        ? 'bg-[#01458E] hover:bg-[#013a78] shadow cursor-pointer'
                        : 'bg-[#B0C4D8] cursor-not-allowed'"
                    class="flex items-center gap-2 px-6 py-2.5 sm:px-8 sm:py-3 rounded-full text-xs sm:text-sm font-semibold text-white transition-all">
                Lanjut
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </button>

        </div>

    </div>

</main>
</body>
</html>
