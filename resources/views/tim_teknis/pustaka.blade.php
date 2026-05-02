<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pustaka Teknis (SOP) — Tim Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
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
    @endphp

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Pustaka Teknis (SOP)</h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Knowledge Base internal — Bidang
                    <span class="font-semibold text-[#01458E]">{{ $bidangLabel }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold text-red-600 bg-red-50 border border-red-100 shrink-0">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="hidden sm:inline">Internal (Rahasia)</span>
            </div>
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-4 lg:px-8 py-5 lg:py-7 flex flex-col">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- ── Filter Card ── --}}
            <form method="GET" action="{{ route('tim_teknis.pustaka') }}" id="filterFormPustakaTeknis"
                  class="bg-white rounded-2xl border border-gray-100 px-5 py-4 mb-5">

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:items-center">

                    {{-- Kategori dropdown --}}
                    @php
                        $kategoriOptions = ['' => 'Semua Kategori'];
                        foreach($kategoris as $k) {
                            $kategoriOptions[$k->id] = $k->nama_kategori;
                        }
                        $kategoriSelected     = $kategoriFilter ?: '';
                        $kategoriTriggerLabel = $kategoriOptions[$kategoriSelected] ?? 'Semua Kategori';
                    @endphp
                    <input type="hidden" name="kategori_id" id="kategoriInputTeknis" value="{{ $kategoriSelected }}">
                    <div class="relative w-full sm:w-auto"
                         x-data="{
                            open: false,
                            selected: '{{ $kategoriSelected }}',
                            label: '{{ addslashes($kategoriTriggerLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('kategoriInputTeknis').value = val;
                                this.open = false;
                                document.getElementById('filterFormPustakaTeknis').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button"
                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[160px] transition-colors hover:border-blue-200"
                                :class="{ 'border-blue-500 bg-blue-50': open }"
                                @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                <span x-text="label">{{ $kategoriTriggerLabel }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden origin-top"
                             x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             style="display:none;">
                            @foreach($kategoriOptions as $val => $lbl)
                            <div class="flex items-center gap-2 px-3.5 py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors
                                        {{ $kategoriSelected == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                 :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity"
                                      :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                {{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Status dropdown --}}
                    @php
                        $statusOptions      = ['' => 'Semua Status', 'draft' => 'Draft', 'published' => 'Published'];
                        $statusSelected     = $statusFilter ?: '';
                        $statusTriggerLabel = $statusOptions[$statusSelected] ?? 'Semua Status';
                    @endphp
                    <input type="hidden" name="status" id="statusInputTeknis" value="{{ $statusSelected }}">
                    <div class="relative w-full sm:w-auto"
                         x-data="{
                            open: false,
                            selected: '{{ $statusSelected }}',
                            label: '{{ addslashes($statusTriggerLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('statusInputTeknis').value = val;
                                this.open = false;
                                document.getElementById('filterFormPustakaTeknis').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button"
                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[150px] transition-colors hover:border-blue-200"
                                :class="{ 'border-blue-500 bg-blue-50': open }"
                                @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="label">{{ $statusTriggerLabel }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden origin-top"
                             x-show="open"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             style="display:none;">
                            @foreach($statusOptions as $val => $lbl)
                            <div class="flex items-center gap-2 px-3.5 py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors
                                        {{ $statusSelected == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                 :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity"
                                      :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                {{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="w-full sm:flex-1 sm:min-w-0 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari judul artikel atau tag..."
                               oninput="clearTimeout(window._stPustakaTeknis); window._stPustakaTeknis = setTimeout(() => document.getElementById('filterFormPustakaTeknis').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    {{-- Reset --}}
                    <a href="{{ route('tim_teknis.pustaka') }}"
                       class="flex items-center justify-center sm:justify-start gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 w-full sm:w-auto transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- ── Tabel ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden flex-1 flex flex-col">

                <div class="flex items-center justify-between px-7 py-4 border-b border-gray-100">
                    <p class="text-base font-bold text-gray-900">Knowledge Base Internal</p>
                    <p class="text-xs text-gray-400">
                        @if($search || $statusFilter || $kategoriFilter)
                            Menampilkan {{ $articles->count() }} hasil pencarian
                        @else
                            {{ $articles->count() }} artikel
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Judul Artikel</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Status</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Dibuat</th>
                            <th class="px-7 py-3.5 text-center text-xs font-bold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $i => $article)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <td class="px-7 py-4 text-sm text-gray-700">{{ $i + 1 }}</td>

                            <td class="px-4 py-4">
                                <p class="text-sm font-medium text-gray-900">{{ $article->nama_artikel_sop }}</p>
                                @if($article->tags->count())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($article->tags->take(3) as $tag)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-gray-500">{{ $tag->nama_tag }}</span>
                                    @endforeach
                                    @if($article->tags->count() > 3)
                                    <span class="text-[10px] text-gray-400">+{{ $article->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                @if($article->status_publikasi === 'published')
                                <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border border-green-200 text-green-600 bg-green-50">
                                    Published
                                </span>
                                @else
                                <span class="inline-flex items-center text-xs font-semibold px-3 py-1 rounded-full border border-yellow-200 text-yellow-600 bg-yellow-50">
                                    Draft
                                </span>
                                @endif
                            </td>

                            <td class="px-4 py-4 text-xs text-gray-400">
                                {{ $article->created_at?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="px-7 py-4">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('tim_teknis.pustaka.show', $article->id) }}"
                                       class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                       style="background-color:#01458E;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Lihat
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-7 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">
                                        @if($search || $statusFilter || $kategoriFilter)
                                            Tidak ada artikel yang cocok dengan filter.
                                        @else
                                            Belum ada artikel knowledge base untuk bidang ini.
                                        @endif
                                    </p>
                                    @if($search || $statusFilter || $kategoriFilter)
                                    <a href="{{ route('tim_teknis.pustaka') }}" class="text-xs text-[#01458E] hover:underline">Reset filter</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse

                        @if($articles->count() > 0 && $articles->count() < 7)
                            @for($pad = $articles->count(); $pad < 7; $pad++)
                            <tr class="border-b border-gray-50">
                                <td colspan="6" class="px-7 py-5">
                                    <div class="h-4 rounded-full bg-[#EEF3F9]"></div>
                                </td>
                            </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
