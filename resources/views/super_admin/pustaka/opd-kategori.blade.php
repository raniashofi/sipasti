<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $kategori->nama_kategori }} — Pustaka OPD</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col"
         x-data="{ showHapus: false, hapusId: null }">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                {{-- Back Button --}}
                <a href="{{ route('super_admin.pustaka.opd') }}"
                   class="w-8 h-8 rounded-lg bg-[#F0F4F8] flex items-center justify-center text-gray-500 hover:bg-[#E5EBF3] transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>

                {{-- Breadcrumb --}}
                <div class="flex items-center gap-2 text-sm">
                    <a href="{{ route('super_admin.pustaka.opd') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                        Pustaka OPD
                    </a>
                    <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                    <span class="font-semibold text-gray-800">{{ $kategori->nama_kategori }}</span>
                </div>
            </div>

            {{-- Tambah Artikel --}}
            <a href="{{ route('super_admin.pustaka.create', ['visibility' => 'opd', 'kategori_id' => $kategori->id]) }}"
               class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
               style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Artikel
            </a>
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-8 py-7 flex flex-col">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- Filter & Search --}}
            <form method="GET" action="{{ route('super_admin.pustaka.opd.kategori', $kategori->id) }}" id="filterForm"
                  class="bg-white rounded-2xl border border-gray-100 px-5 py-4 mb-5">

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>

                <div class="flex flex-wrap gap-2 items-center">

                    {{-- Status Dropdown --}}
                    @php
                        $statusOptions    = ['' => 'Semua Status', 'draft' => 'Draft', 'published' => 'Published'];
                        $statusSelected   = $statusFilter ?: '';
                        $statusLabel      = $statusOptions[$statusSelected] ?? 'Semua Status';
                    @endphp
                    <input type="hidden" name="status" id="statusInput" value="{{ $statusSelected }}">
                    <div class="relative"
                         x-data="{
                            open: false,
                            selected: '{{ $statusSelected }}',
                            label: '{{ addslashes($statusLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('statusInput').value = val;
                                this.open = false;
                                document.getElementById('filterForm').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button"
                                class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-[#F0F4F8] text-sm text-gray-700 min-w-[150px] hover:border-blue-200 transition-colors"
                                :class="{ 'border-blue-500 bg-blue-50': open }" @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="label">{{ $statusLabel }}</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute top-[calc(100%+6px)] left-0 min-w-full bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden"
                             x-show="open"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             style="display:none;">
                            @foreach($statusOptions as $val => $lbl)
                            <div class="flex items-center gap-2 px-3.5 py-2 text-sm text-gray-700 cursor-pointer hover:bg-[#F0F4F8] transition-colors {{ $statusSelected == $val ? 'text-[#01458E] font-semibold bg-[#EEF3F9]' : '' }}"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#01458E] shrink-0 transition-opacity" :class="selected == '{{ $val }}' ? 'opacity-100' : 'opacity-0'"></span>
                                {{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="flex-1 min-w-[250px] relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul artikel atau tag..."
                               oninput="clearTimeout(window._st); window._st = setTimeout(() => document.getElementById('filterForm').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    {{-- Reset --}}
                    <a href="{{ route('super_admin.pustaka.opd.kategori', $kategori->id) }}"
                       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- Article Table --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden flex-1 flex flex-col">

                <div class="flex items-center justify-between px-7 py-4 border-b border-gray-100">
                    <div>
                        <p class="text-base font-bold text-gray-900">{{ $kategori->nama_kategori }}</p>
                        @if($kategori->deskripsi)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $kategori->deskripsi }}</p>
                        @endif
                    </div>
                    <p class="text-xs text-gray-400">
                        {{ $articles->count() }} artikel
                    </p>
                </div>

                <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-12">No</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Judul Artikel</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Status</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Dibuat</th>
                            <th class="px-7 py-3.5 text-center text-xs font-bold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $i => $article)
                        <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <td class="px-7 py-4 text-sm text-gray-500">{{ $i + 1 }}</td>
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
                            <td class="px-4 py-4">
                                <div class="text-sm text-gray-600">
                                    <div class="font-medium">{{ $article->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $article->created_at->format('H:i') }}</div>
                                </div>
                            </td>
                            <td class="px-7 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('super_admin.pustaka.edit', $article->id) }}"
                                       class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                       style="background-color:#01458E;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <button @click="showHapus = true; hapusId = '{{ $article->id }}'"
                                            class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-red-500 border border-red-200 hover:bg-red-50 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-7 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-sm text-gray-400">
                                        @if($search || $statusFilter)
                                            Tidak ada artikel yang cocok dengan filter.
                                        @else
                                            Belum ada artikel. Klik <strong class="text-gray-600">Tambah Artikel</strong> untuk memulai.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse

                        @if($articles->count() > 0 && $articles->count() < 7)
                            @for($pad = $articles->count(); $pad < 7; $pad++)
                            <tr class="border-b border-gray-50">
                                <td colspan="4" class="px-7 py-5">
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

        {{-- Modal Konfirmasi Hapus Artikel --}}
        <div x-show="showHapus"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showHapus = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 p-8 text-center" @click.stop>
                <button @click="showHapus = false"
                        class="absolute top-5 left-5 w-8 h-8 rounded-full flex items-center justify-center text-white hover:opacity-80"
                        style="background-color:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-lg font-bold text-gray-900 mt-2 mb-2">Hapus Artikel</h3>
                <p class="text-sm text-gray-500 mb-7">Apakah kamu yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form method="POST" :action="`{{ url('super-admin/pustaka') }}/${hapusId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">
                            Ya, Hapus
                        </button>
                        <button type="button" @click="showHapus = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
