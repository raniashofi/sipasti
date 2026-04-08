<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pustaka Pengetahuan — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    @php
        $bidangLabel = $bidangLabel ?? [];
        $isInternal  = $visibility === 'internal';
    @endphp

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="ml-64 min-h-screen flex flex-col" x-data="{ showFilter: false, showHapus: false, hapusId: null }">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 px-8 py-0 flex items-center justify-between sticky top-0 z-30">

            {{-- Tabs OPD / Internal --}}
            <div class="flex items-center gap-0">
                <a href="{{ route('super_admin.pustaka.opd') }}"
                   class="px-6 py-4 text-sm font-{{ !$isInternal ? 'semibold border-b-2 border-[#01458E] text-[#01458E]' : 'medium border-b-2 border-transparent text-gray-400 hover:text-gray-600' }} transition-colors">
                    Publik (OPD)
                </a>
                <a href="{{ route('super_admin.pustaka.internal') }}"
                   class="px-6 py-4 text-sm font-{{ $isInternal ? 'semibold border-b-2 border-[#01458E] text-[#01458E]' : 'medium border-b-2 border-transparent text-gray-400 hover:text-gray-600' }} transition-colors">
                    Internal (Rahasia)
                </a>
            </div>

            {{-- Search + Filter + Tambah --}}
            <div class="flex items-center gap-3 py-3">
                <form method="GET" action="{{ $isInternal ? route('super_admin.pustaka.internal') : route('super_admin.pustaka.opd') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <input type="text" name="search" value="{{ $search }}"
                               placeholder="Cari nama artikel..."
                               class="pl-4 pr-10 py-2 text-sm border border-gray-200 rounded-full bg-gray-50
                                      focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]
                                      w-60 transition-all">
                        <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Filter Dropdown --}}
                    <div class="relative">
                        <button type="button" @click="showFilter = !showFilter"
                                class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                style="background-color:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                            </svg>
                            Filter
                            <svg class="w-3.5 h-3.5 transition-transform" :class="showFilter ? 'rotate-180' : ''"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="showFilter" @click.outside="showFilter = false"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-lg border border-gray-100 p-4 z-40" style="display:none;">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Filter</p>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Status Publikasi</label>
                                    <select name="status"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                                        <option value="">Semua Status</option>
                                        <option value="draft"     {{ $statusFilter === 'draft'     ? 'selected' : '' }}>Draft</option>
                                        <option value="published" {{ $statusFilter === 'published' ? 'selected' : '' }}>Published</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                                    <select name="kategori_id"
                                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                                        <option value="">Semua Kategori</option>
                                        @foreach($kategoris as $k)
                                        <option value="{{ $k->id }}" {{ $kategoriFilter === $k->id ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex gap-2 pt-1">
                                    <button type="submit"
                                            class="flex-1 py-2 rounded-lg text-xs font-semibold text-white hover:opacity-90 transition-opacity"
                                            style="background-color:#01458E;">Terapkan</button>
                                    <a href="{{ $isInternal ? route('super_admin.pustaka.internal') : route('super_admin.pustaka.opd') }}"
                                       class="flex-1 py-2 rounded-lg text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors text-center">Reset</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <a href="{{ route('super_admin.pustaka.create', ['visibility' => $visibility]) }}"
                   class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                   style="background-color:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Artikel
                </a>
            </div>
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-8 py-7">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden">

                {{-- Table header --}}
                <div class="flex items-center justify-between px-7 pt-5 pb-4 border-b border-gray-100">
                    <p class="text-base font-bold text-gray-900">
                        Manajemen Knowledge Base
                        <span class="ml-2 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                            {{ $articles->count() }} artikel
                        </span>
                    </p>
                    @if($search || $statusFilter || $kategoriFilter)
                    <a href="{{ $isInternal ? route('super_admin.pustaka.internal') : route('super_admin.pustaka.opd') }}"
                       class="text-xs text-gray-400 hover:text-red-500 transition-colors">Hapus filter</a>
                    @endif
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Judul Artikel</th>
                            @if($isInternal)
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Bidang</th>
                            @endif
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Kategori</th>
                            <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Status Publikasi</th>
                            <th class="px-7 py-3.5 text-right text-xs font-bold text-gray-700">Aksi</th>
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
                            @if($isInternal)
                            <td class="px-4 py-4 text-sm text-gray-600">
                                {{ $bidangLabel[$article->kategori?->bidang?->nama_bidang ?? ''] ?? '—' }}
                            </td>
                            @endif
                            <td class="px-4 py-4">
                                @if($article->kategori)
                                <span class="text-xs px-3 py-1 rounded-full border border-gray-200 text-gray-600">
                                    {{ $article->kategori->nama_kategori }}
                                </span>
                                @else
                                <span class="text-gray-300 text-sm">—</span>
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
                            <td class="px-7 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('super_admin.pustaka.edit', $article->id) }}"
                                       class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                       style="background-color:#01458E;">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $isInternal ? 6 : 5 }}" class="px-7 py-12 text-center text-sm text-gray-400">
                                @if($search || $statusFilter || $kategoriFilter)
                                    Tidak ada artikel yang cocok dengan filter.
                                @else
                                    Belum ada artikel. Klik <strong>Tambah Artikel</strong> untuk membuat artikel pertama.
                                @endif
                            </td>
                        </tr>
                        @endforelse

                        @if($articles->count() > 0 && $articles->count() < 7)
                            @for($pad = $articles->count(); $pad < 7; $pad++)
                            <tr class="border-b border-gray-50">
                                <td colspan="{{ $isInternal ? 6 : 5 }}" class="px-7 py-5">
                                    <div class="h-4 rounded-full bg-[#EEF3F9]"></div>
                                </td>
                            </tr>
                            @endfor
                        @endif
                    </tbody>
                </table>
            </div>
        </main>

        {{-- Modal Konfirmasi Hapus --}}
        <div x-show="showHapus"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showHapus = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8 text-center" @click.stop>
                <button @click="showHapus = false"
                        class="absolute top-5 left-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                        style="background-color:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-xl font-bold text-gray-900 mt-2 mb-3">Hapus Artikel</h3>
                <p class="text-sm text-gray-500 mb-8">Apakah kamu yakin ingin menghapus artikel ini? Tindakan ini tidak dapat dibatalkan.</p>
                <form method="POST" :action="`{{ url('super-admin/pustaka') }}/${hapusId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="submit" class="px-8 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Hapus</button>
                        <button type="button" @click="showHapus = false"
                                class="px-8 py-2.5 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                                style="background-color:#DC2626;">Tidak</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
