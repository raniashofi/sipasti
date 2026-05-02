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

    @php $isInternal = $tab === 'internal'; @endphp

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col"
         x-data="{
            {{-- ── Kategori Modals ── --}}
            showCreateKat:  false,
            showEditKat:    false,
            showDeleteKat:  false,
            editKatId:      '',
            editKatNama:    '',
            editKatDesc:    '',
            deleteKatId:    '',
            deleteKatNama:  '',
            openEditKat(id, nama, desc) {
                this.editKatId   = id;
                this.editKatNama = nama;
                this.editKatDesc = desc;
                this.showEditKat = true;
            },
            openDeleteKat(id, nama) {
                this.deleteKatId   = id;
                this.deleteKatNama = nama;
                this.showDeleteKat = true;
            },
            {{-- ── Bidang Modals ── --}}
            showCreateBidang:  false,
            showEditBidang:    false,
            showDeleteBidang:  false,
            editBidangId:      '',
            editBidangNama:    '',
            deleteBidangId:    '',
            deleteBidangNama:  '',
            openEditBidang(id, nama) {
                this.editBidangId   = id;
                this.editBidangNama = nama;
                this.showEditBidang = true;
            },
            openDeleteBidang(id, nama) {
                this.deleteBidangId   = id;
                this.deleteBidangNama = nama;
                this.showDeleteBidang = true;
            },
         }">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">

            {{-- Tabs OPD / Internal --}}
            <div class="flex items-center gap-0">
                <a href="{{ route('super_admin.pustaka.opd') }}"
                   class="px-6 py-4 text-sm border-b-2 transition-colors {{ !$isInternal ? 'font-semibold border-[#01458E] text-[#01458E]' : 'font-medium border-transparent text-gray-400 hover:text-gray-600' }}">
                    Publik (OPD)
                </a>
                <a href="{{ route('super_admin.pustaka.internal') }}"
                   class="px-6 py-4 text-sm border-b-2 transition-colors {{ $isInternal ? 'font-semibold border-[#01458E] text-[#01458E]' : 'font-medium border-transparent text-gray-400 hover:text-gray-600' }}">
                    Internal (Rahasia)
                </a>
            </div>

            {{-- Action Button --}}
            @if(!$isInternal)
            <button @click="showCreateKat = true"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                    style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Kategori
            </button>
            @else
            <button @click="showCreateBidang = true"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                    style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Bidang
            </button>
            @endif
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-8 py-7">

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-sm text-red-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            {{-- ══ TAB OPD: Kategori Cards ══ --}}
            @if(!$isInternal)

            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">Kategori Knowledge Base OPD</h2>
                <p class="text-sm text-gray-500 mt-0.5">Klik kategori untuk melihat dan mengelola artikel di dalamnya.</p>
            </div>

            @if($kategoris->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#EEF3F9] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#01458E]/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Belum ada kategori</p>
                <p class="text-xs text-gray-400 mt-1">Klik <strong>Tambah Kategori</strong> untuk membuat kategori pertama.</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($kategoris as $kat)
                <div class="relative group">
                    {{-- Card link area --}}
                    <a href="{{ route('super_admin.pustaka.opd.kategori', $kat->id) }}"
                       class="block bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md hover:border-blue-100 transition-all duration-200">

                        {{-- Icon --}}
                        <div class="w-10 h-10 rounded-xl bg-[#EEF3F9] flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>

                        {{-- Name --}}
                        <p class="text-sm font-semibold text-gray-900 leading-snug mb-1 pr-10">{{ $kat->nama_kategori }}</p>

                        {{-- Desc --}}
                        @if($kat->deskripsi)
                        <p class="text-xs text-gray-400 line-clamp-2 mb-3">{{ $kat->deskripsi }}</p>
                        @endif

                        {{-- Count --}}
                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-xs text-gray-400">{{ $kat->artikel_count }} artikel</span>
                        </div>
                    </a>

                    {{-- Edit & Delete buttons (hover) --}}
                    <div class="absolute top-3 right-3 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button"
                                @click.stop="openEditKat('{{ $kat->id }}', '{{ addslashes($kat->nama_kategori) }}', '{{ addslashes($kat->deskripsi ?? '') }}')"
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-[#01458E] hover:border-blue-200 transition-colors shadow-sm"
                                title="Edit kategori">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button type="button"
                                @click.stop="openDeleteKat('{{ $kat->id }}', '{{ addslashes($kat->nama_kategori) }}')"
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-500 hover:border-red-200 transition-colors shadow-sm"
                                title="Hapus kategori">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            {{-- ══ TAB INTERNAL: Bidang Cards ══ --}}
            @else

            <div class="mb-5">
                <h2 class="text-lg font-bold text-gray-900">Knowledge Base Internal per Bidang</h2>
                <p class="text-sm text-gray-500 mt-0.5">Klik bidang untuk melihat dan mengelola artikel internal di dalamnya.</p>
            </div>

            @if($bidangs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#EEF3F9] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#01458E]/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700">Belum ada bidang</p>
                <p class="text-xs text-gray-400 mt-1">Klik <strong>Tambah Bidang</strong> untuk membuat bidang pertama.</p>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($bidangs as $bidang)
                @php $namaDisplay = ucwords(str_replace('_', ' ', $bidang->nama_bidang)); @endphp
                <div class="relative group">
                    <a href="{{ route('super_admin.pustaka.internal.bidang', $bidang->id) }}"
                       class="block bg-white rounded-2xl border border-gray-100 p-6 hover:shadow-md hover:border-blue-100 transition-all duration-200">

                        <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>

                        <p class="text-sm font-semibold text-gray-900 leading-snug mb-1 pr-10">{{ $namaDisplay }}</p>

                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-xs text-gray-400">{{ $bidang->artikel_count }} artikel</span>
                        </div>
                    </a>

                    <div class="absolute top-3 right-3 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button type="button"
                                @click.stop="openEditBidang('{{ $bidang->id }}', '{{ addslashes($namaDisplay) }}')"
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-[#01458E] hover:border-blue-200 transition-colors shadow-sm"
                                title="Edit bidang">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button type="button"
                                @click.stop="openDeleteBidang('{{ $bidang->id }}', '{{ addslashes($namaDisplay) }}')"
                                class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-500 hover:border-red-200 transition-colors shadow-sm"
                                title="Hapus bidang">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            @endif
        </main>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- ── MODAL: Tambah Kategori ── --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div x-show="showCreateKat"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showCreateKat = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8" @click.stop>
                <button @click="showCreateKat = false"
                        class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-lg font-bold text-gray-900 mb-6">Tambah Kategori</h3>
                <form method="POST" action="{{ route('super_admin.pustaka.kategori.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kategori" required placeholder="mis. Tutorial, Panduan, Troubleshooting..."
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="deskripsi" rows="3" placeholder="Deskripsi singkat kategori..."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showCreateKat = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                style="background-color:#01458E;">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── MODAL: Edit Kategori ── --}}
        <div x-show="showEditKat"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showEditKat = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8" @click.stop>
                <button @click="showEditKat = false"
                        class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-lg font-bold text-gray-900 mb-6">Edit Kategori</h3>
                <form method="POST" :action="`{{ url('super-admin/pustaka/kategori') }}/${editKatId}`">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_kategori" x-model="editKatNama" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Deskripsi <span class="text-gray-400 font-normal">(opsional)</span></label>
                        <textarea name="deskripsi" x-model="editKatDesc" rows="3"
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showEditKat = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                style="background-color:#01458E;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── MODAL: Hapus Kategori ── --}}
        <div x-show="showDeleteKat"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showDeleteKat = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 p-8 text-center" @click.stop>
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Kategori?</h3>
                <p class="text-sm text-gray-500 mb-1">Kategori <strong x-text="deleteKatNama" class="text-gray-700"></strong> akan dihapus.</p>
                <p class="text-xs text-gray-400 mb-6">Hanya kategori tanpa artikel yang dapat dihapus.</p>
                <form method="POST" :action="`{{ url('super-admin/pustaka/kategori') }}/${deleteKatId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="showDeleteKat = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- ── MODAL: Tambah Bidang ── --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div x-show="showCreateBidang"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showCreateBidang = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8" @click.stop>
                <button @click="showCreateBidang = false"
                        class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-lg font-bold text-gray-900 mb-6">Tambah Bidang</h3>
                <form method="POST" action="{{ route('super_admin.pustaka.bidang.store') }}">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Bidang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_bidang" required placeholder="mis. Infrastruktur TI, E-Government..."
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showCreateBidang = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                style="background-color:#01458E;">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── MODAL: Edit Bidang ── --}}
        <div x-show="showEditBidang"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showEditBidang = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 p-8" @click.stop>
                <button @click="showEditBidang = false"
                        class="absolute top-5 right-5 w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <h3 class="text-lg font-bold text-gray-900 mb-6">Edit Bidang</h3>
                <form method="POST" :action="`{{ url('super-admin/pustaka/bidang') }}/${editBidangId}`">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nama Bidang <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_bidang" x-model="editBidangNama" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="showEditBidang = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                style="background-color:#01458E;">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── MODAL: Hapus Bidang ── --}}
        <div x-show="showDeleteBidang"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
            <div class="absolute inset-0 bg-gray-500/40" @click="showDeleteBidang = false"></div>
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 p-8 text-center" @click.stop>
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Bidang?</h3>
                <p class="text-sm text-gray-500 mb-1">Bidang <strong x-text="deleteBidangNama" class="text-gray-700"></strong> akan dihapus.</p>
                <p class="text-xs text-gray-400 mb-6">Hanya bidang tanpa artikel yang dapat dihapus.</p>
                <form method="POST" :action="`{{ url('super-admin/pustaka/bidang') }}/${deleteBidangId}`">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="showDeleteBidang = false"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 rounded-full text-sm font-semibold text-white bg-red-500 hover:bg-red-600 transition-colors">
                            Ya, Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>
