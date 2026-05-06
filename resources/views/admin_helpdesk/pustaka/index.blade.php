<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pustaka Solusi — Admin Helpdesk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarAdminHelpdesk')

    @php
        $bidangLabel = $admin?->bidang?->nama_bidang ?? '—';
    @endphp

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Pustaka Solusi</h1>
                <p class="text-xs text-gray-400 mt-0.5">
                    Knowledge Base internal — Bidang
                    <span class="font-semibold text-[#01458E]">{{ $bidangLabel }}</span>
                </p>
            </div>

            {{-- Badge internal --}}
            <div class="inline-flex items-center self-start sm:self-auto gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold text-red-600 bg-red-50 border border-red-100">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Internal (Rahasia)
            </div>
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-4 py-4 lg:px-8 lg:py-7 flex flex-col w-full overflow-hidden">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- ── Filter Card ── --}}
            <form method="GET" action="{{ route('admin_helpdesk.pustaka') }}" id="filterForm"
                  class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-5 shadow-sm">

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-2 sm:items-center">

                    {{-- Status dropdown --}}
                    @php
                        $statusOptions      = ['' => 'Semua Status', 'draft' => 'Draft', 'published' => 'Published'];
                        $statusSelected     = $statusFilter ?: '';
                        $statusTriggerLabel = $statusOptions[$statusSelected] ?? 'Semua Status';
                    @endphp
                    <input type="hidden" name="status" id="statusInput" value="{{ $statusSelected }}">
                    <div class="relative w-full sm:w-auto"
                         x-data="{
                            open: false,
                            selected: '{{ $statusSelected }}',
                            label: '{{ addslashes($statusTriggerLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('statusInput').value = val;
                                this.open = false;
                                document.getElementById('filterForm').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button"
                                class="flex items-center justify-between gap-2 px-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 w-full sm:min-w-[150px] transition-colors hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                :class="{ 'border-blue-500 bg-blue-50': open }"
                                @click="open = !open">
                            <span class="flex items-center gap-1.5 truncate">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="label" class="truncate">{{ $statusTriggerLabel }}</span>
                            </span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 shrink-0" :class="{ 'rotate-180': open }"
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
                            <div class="flex items-center gap-2 px-3.5 py-2.5 sm:py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors
                                        {{ $statusSelected == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                 :class="{ 'text-[#01458E] font-semibold bg-[#EEF3F9]': selected == '{{ $val }}' }"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity"
                                      :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                <span class="truncate">{{ $lbl }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="w-full sm:flex-1 sm:min-w-[200px] relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" id="searchInput" value="{{ $search }}"
                               placeholder="Cari judul artikel atau tag..."
                               oninput="clearTimeout(window._st); window._st = setTimeout(() => document.getElementById('filterForm').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    {{-- Reset --}}
                    <a href="{{ route('admin_helpdesk.pustaka') }}"
                       class="flex justify-center sm:justify-start items-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 w-full sm:w-auto transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- ── Tabel ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 flex-1 flex flex-col overflow-hidden w-full">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-5 sm:px-7 py-4 border-b border-gray-100">
                    <p class="text-base font-bold text-gray-900">Knowledge Base Internal</p>
                    <p class="text-xs text-gray-400 font-medium">
                        @if($search || $statusFilter)
                            Menampilkan {{ count($articles) }} hasil pencarian
                        @else
                            {{ count($articles) }} artikel tersedia
                        @endif
                    </p>
                </div>

                {{-- Mobile card list --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse($articles as $i => $article)
                    <div class="px-4 py-4 hover:bg-gray-50/80 transition-colors">
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <p class="text-sm font-semibold text-gray-900 leading-snug">{{ $article->nama_artikel_sop }}</p>
                            @if($article->status_publikasi === 'published')
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full border border-green-200 text-green-700 bg-green-50 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Published
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full border border-yellow-200 text-yellow-700 bg-yellow-50 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Draft
                            </span>
                            @endif
                        </div>
                        @if($article->tags->count())
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($article->tags->take(3) as $tag)
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-gray-100 text-gray-600">{{ $tag->nama_tag }}</span>
                            @endforeach
                        </div>
                        @endif
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-400">{{ $article->created_at?->format('d M Y') ?? '—' }}</span>
                            <a href="{{ route('admin_helpdesk.pustaka.show', $article->id) }}"
                               class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg text-white"
                               style="background-color:#01458E;">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-12 flex flex-col items-center gap-3 text-center">
                        <div class="w-14 h-14 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-gray-500">Belum ada artikel tersedia.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Desktop table --}}
                <div class="hidden md:block overflow-x-auto flex-1 w-full">
                    <table class="w-full min-w-max text-sm text-left">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-5 sm:px-7 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider w-14">No</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Artikel</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Dibuat</th>
                                <th class="px-5 sm:px-7 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articles as $i => $article)
                            <tr class="border-b border-gray-50 hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 sm:px-7 py-4 text-sm text-gray-500 font-medium whitespace-nowrap">{{ $i + 1 }}</td>

                                <td class="px-4 py-4 min-w-[250px] max-w-sm">
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-2 leading-snug">{{ $article->nama_artikel_sop }}</p>
                                    @if($article->tags->count())
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach($article->tags->take(3) as $tag)
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 border border-gray-200">{{ $tag->nama_tag }}</span>
                                        @endforeach
                                        @if($article->tags->count() > 3)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 text-gray-400">+{{ $article->tags->count() - 3 }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if($article->status_publikasi === 'published')
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border border-green-200 text-green-700 bg-green-50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Published
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full border border-yellow-200 text-yellow-700 bg-yellow-50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> Draft
                                    </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-xs font-medium text-gray-500 whitespace-nowrap">
                                    {{ $article->created_at?->format('d M Y') ?? '—' }}
                                </td>

                                <td class="px-5 sm:px-7 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center">
                                        <a href="{{ route('admin_helpdesk.pustaka.show', $article->id) }}"
                                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white transition-all hover:opacity-90 hover:scale-105 shadow-sm focus:outline-none"
                                           style="background-color:#01458E;">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Lihat Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 sm:px-7 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm font-bold text-gray-500">
                                                @if($search || $statusFilter)
                                                    Tidak ada artikel yang cocok dengan filter.
                                                @else
                                                    Belum ada artikel knowledge base untuk bidang ini.
                                                @endif
                                            </p>
                                            @if($search || $statusFilter)
                                            <a href="{{ route('admin_helpdesk.pustaka') }}" class="text-xs font-semibold text-[#01458E] hover:underline mt-2 inline-block">Reset Filter Pencarian</a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse

                            {{-- Skeleton Loader saat data sedikit agar tabel tidak gepeng (Opsional) --}}
                            @if(count($articles) > 0 && count($articles) < 5)
                                @for($pad = count($articles); $pad < 5; $pad++)
                                <tr class="border-b border-gray-50">
                                    <td colspan="5" class="px-5 sm:px-7 py-5">
                                        <div class="h-5 rounded-md bg-gray-50/50 w-full"></div>
                                    </td>
                                </tr>
                                @endfor
                            @endif
                        </tbody>
                    </table>
                </div>
                </div>

                {{-- Pagination 10 Data --}}
                @if(method_exists($articles, 'links'))
                <div class="px-5 py-4 border-t border-gray-100 w-full shrink-0">
                    {{ $articles->appends(request()->query())->links() }}
                </div>
                @endif
            </div>

        </main>
    </div>

</body>
</html>
