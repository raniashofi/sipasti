<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Diagnosis — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
    </style>
</head>
<body class="min-h-screen">

    <div class="sticky top-0 z-30 shadow-sm">
        @include('layouts.topBarOpd')
    </div>

    <main class="max-w-screen-lg mx-auto px-6 lg:px-8 py-10"
          x-data="{
              selected: null,
              urlYa:    '{{ addslashes($urlYa ?? '') }}',
              urlTidak: '{{ addslashes($urlTidak ?? '') }}',
              lanjut() {
                  if (!this.selected) return;
                  window.location.href = this.selected === 'ya' ? this.urlYa : this.urlTidak;
              }
          }">

        {{-- Page header --}}
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 mb-1">
                Kategori: {{ $kategoriNama }}
            </h1>
            @if($node->hint_konteks)
            <p class="text-sm text-gray-400">{{ $node->hint_konteks }}</p>
            @endif
        </div>

        {{-- Question card --}}
        <div class="bg-[#E8EEF5] rounded-2xl p-8 mb-6">

            {{-- Question label + text --}}
            <div class="mb-6 pb-5 border-b border-[#D1DBE8]">
                <p class="text-xs font-semibold text-gray-400 mb-1">Question {{ $qNum }}</p>
                <p class="text-base font-semibold text-gray-900">
                    {{ $node->teks_pertanyaan }}
                </p>
            </div>

            {{-- Answer options --}}
            @php
                // Parse custom labels from hint_konteks if JSON
                $labels = ['ya' => 'Ya', 'tidak' => 'Tidak'];
                if ($node->hint_konteks) {
                    $parsed = json_decode($node->hint_konteks, true);
                    if (is_array($parsed)) {
                        $labels['ya']    = $parsed['ya']    ?? 'Ya';
                        $labels['tidak'] = $parsed['tidak'] ?? 'Tidak';
                    }
                }
            @endphp

            <div class="space-y-3">

                {{-- Ya --}}
                @if($urlYa)
                <button type="button"
                        @click="selected = 'ya'"
                        :class="selected === 'ya'
                            ? 'border-[#01458E] border-l-4 shadow-md'
                            : 'border-[#D1DBE8] hover:border-[#01458E]/40'"
                        class="w-full flex items-center gap-4 bg-white rounded-2xl px-5 py-4 border-2 transition-all cursor-pointer text-left">
                    <div :class="selected === 'ya' ? 'bg-[#01458E]' : 'bg-gray-200'"
                         class="w-4 h-4 rounded-full shrink-0 transition-colors"></div>
                    <span class="text-sm font-semibold text-gray-800">{{ $labels['ya'] }}</span>
                </button>
                @endif

                {{-- Tidak --}}
                @if($urlTidak)
                <button type="button"
                        @click="selected = 'tidak'"
                        :class="selected === 'tidak'
                            ? 'border-[#01458E] border-l-4 shadow-md'
                            : 'border-[#D1DBE8] hover:border-[#01458E]/40'"
                        class="w-full flex items-center gap-4 bg-white rounded-2xl px-5 py-4 border-2 transition-all cursor-pointer text-left">
                    <div :class="selected === 'tidak' ? 'bg-[#01458E]' : 'bg-gray-200'"
                         class="w-4 h-4 rounded-full shrink-0 transition-colors"></div>
                    <span class="text-sm font-semibold text-gray-800">{{ $labels['tidak'] }}</span>
                </button>
                @endif

                @if(!$urlYa && !$urlTidak)
                <p class="text-sm text-gray-400 text-center py-4">
                    Tidak ada jawaban tersedia untuk pertanyaan ini.
                </p>
                @endif
            </div>

        </div>

        {{-- Navigation buttons --}}
        <div class="flex items-center justify-center gap-3">

            {{-- Kembali (only show if not first question) --}}
            @if($qNum > 1)
            <button type="button"
                    onclick="history.back()"
                    class="flex items-center gap-2 px-6 py-3 rounded-full border-2 border-gray-300 text-sm font-semibold text-gray-600 hover:border-gray-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
                Kembali
            </button>
            @endif

            {{-- Lanjut --}}
            <button type="button"
                    @click="lanjut()"
                    :disabled="!selected || (!urlYa && !urlTidak)"
                    :class="selected
                        ? 'bg-[#01458E] hover:bg-[#013a78] shadow-md cursor-pointer'
                        : 'bg-gray-300 cursor-not-allowed'"
                    class="flex items-center gap-2 px-8 py-3 rounded-full text-sm font-semibold text-white transition-all">
                Lanjut
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                </svg>
            </button>
        </div>

    </main>
</body>
</html>
