<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Pengguna OPD — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .modal-scroll::-webkit-scrollbar { width: 4px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
        /* Scrollbar untuk tabel responsif */
        .table-wrap::-webkit-scrollbar { height: 6px; }
        .table-wrap::-webkit-scrollbar-track { background: #f8fafc; border-radius: 8px; }
        .table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    @php
        $openTambah = session('open_tambah') || (old('_modal') === 'tambah' && $errors->any());
        $openEdit   = session('open_edit')   || (old('_modal') === 'edit'   && $errors->any());
        $editOpdId  = session('edit_opd_id', old('_edit_opd_id', ''));

        $editInit = [
            'id'             => $editOpdId,
            'kode_opd'       => old('kode_opd', ''),
            'nama_opd'       => old('nama_opd', ''),
            'email'          => old('email', ''),
            'kdunit'         => old('kdunit', ''),
            'parent_id'      => old('parent_id', ''),
            'is_bagian'      => old('is_bagian', ''),
            'nama_lengkap'   => old('nama_lengkap', ''),
            'bidang_id'      => old('bidang_id', ''),
            'status_teknisi' => old('status_teknisi', ''),
        ];

        // Import ActivityLogController untuk mengambil last login dari activity_log
        use App\Http\Controllers\ActivityLogController;

        // Encode all rows for client-side search & pagination
        $opdRows = $opds->map(fn($o) => [
            'id'             => $o->id,
            'kode_opd'       => $o->kode_opd ?? '',
            'nama_opd'       => $o->nama_opd ?? '',
            'email'          => $o->user?->email ?? '',
            'kdunit'         => $o->kdunit ?? '',
            'parent_id'      => $o->parent_id ?? '',
            'is_bagian'      => $o->is_bagian ?? '',
            'nama_lengkap'   => $o->nama_lengkap ?? '',
            'bidang_id'      => $o->bidang_id ?? '',
            'status_teknisi' => $o->status_teknisi ?? '',
            'last_login'     => $o->user_id ? ActivityLogController::getLastLoginFormatted($o->user_id) : null,
        ])->values();
    @endphp

    <script>
        function opdPage() {
            const allRows = @json($opdRows);
            return {
                search: '',
                currentPage: 1,
                perPage: 10,

                showTambah:      {{ $openTambah ? 'true' : 'false' }},
                showEdit:        {{ $openEdit   ? 'true' : 'false' }},
                showHapus:       false,
                showPass:        false,
                showEditPass:    false,
                hapusId:         null,
                hapusNama:       '',
                tambahIsBagian:  '{{ $openTambah ? old("is_bagian", "") : "" }}',
                tambahParentId:  '{{ $openTambah ? old("parent_id", "") : "" }}',
                edit:            @json($editInit),
                rows:            allRows,

                init() {
                    // Reset pagination ke halaman 1 tiap kali mencari
                    this.$watch('search', () => {
                        this.currentPage = 1;
                    });
                },

                get filtered() {
                    if (!this.search.trim()) return this.rows;
                    const q = this.search.toLowerCase();
                    return this.rows.filter(r =>
                        r.nama_opd.toLowerCase().includes(q) ||
                        r.email.toLowerCase().includes(q) ||
                        r.kode_opd.toLowerCase().includes(q)
                    );
                },

                get paginatedFiltered() {
                    const start = (this.currentPage - 1) * this.perPage;
                    const end = start + this.perPage;
                    return this.filtered.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.filtered.length / this.perPage);
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
                },
                goToPage(page) {
                    this.currentPage = page;
                },

                openEdit(data) {
                    this.edit        = { ...data };
                    this.showEditPass = false;
                    this.showEdit    = true;
                },
                openHapus(id) {
                    const row = this.rows.find(r => r.id === id);
                    this.hapusId   = id;
                    this.hapusNama = row ? row.nama_opd : '';
                    this.showHapus = true;
                }
            };
        }
    </script>

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col" x-data="opdPage()">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 px-4 sm:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-30">
            {{-- Left: Tabs --}}
            <div class="flex items-center gap-0 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0" style="scrollbar-width:none;">
                <a href="{{ route('super_admin.pengguna.opd') }}"
                   class="px-4 sm:px-6 py-2 sm:py-4 text-sm font-semibold border-b-2 transition-colors border-[#01458E] text-[#01458E] whitespace-nowrap">
                    Data OPD
                </a>
                <a href="{{ route('super_admin.pengguna.internal') }}"
                   class="px-4 sm:px-6 py-2 sm:py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors whitespace-nowrap">
                    Data Internal
                </a>
            </div>

            {{-- Right: Export/Tambah --}}
            <button @click="showTambah = true; showPass = false; tambahIsBagian = ''; tambahParentId = ''"
                    class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90 w-full sm:w-auto"
                    style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Akun
            </button>
        </header>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-4 sm:px-8 py-7 flex flex-col min-w-0">

            @if(session('success'))
            <div class="mb-5 flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- ── Filter Card ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Pencarian</p>
                <div class="flex flex-col sm:flex-row flex-wrap gap-2 items-center">
                    {{-- Search --}}
                    <div class="w-full sm:flex-1 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" x-model="search"
                               placeholder="Cari Nama Instansi, Email, atau Kode OPD..."
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    {{-- Reset --}}
                    <button @click="search = ''"
                            x-show="search.trim() !== ''"
                            class="w-full sm:w-auto flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Hapus Filter
                    </button>
                </div>
            </div>

            {{-- ── Data Table ── --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-50 overflow-hidden flex-1 flex flex-col">

                <div class="flex items-center justify-between px-4 sm:px-7 py-4 sm:py-5 border-b border-gray-50">
                    <h2 class="text-sm sm:text-base font-bold text-gray-900">Manajemen Akun OPD</h2>
                    <span class="text-xs text-gray-400" x-text="`${filtered.length} dari ${rows.length} data`"></span>
                </div>

                {{-- Responsif Tabel: table-wrap + min-w --}}
                <div class="overflow-x-auto table-wrap flex-1 min-w-full">
                    <table class="w-full min-w-[800px]">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-4 sm:px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Nama Instansi (OPD)</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Email</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Terakhir Login</th>
                                <th class="px-4 sm:px-7 py-3.5 text-center text-xs font-bold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Gunakan array yang sudah dipaginate --}}
                            <template x-for="(row, index) in paginatedFiltered" :key="row.id">
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    {{-- Penomoran dinamis berdasarkan halaman --}}
                                    <td class="px-4 sm:px-7 py-4 text-sm text-gray-700" x-text="(currentPage - 1) * perPage + index + 1"></td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900" x-text="row.nama_opd || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.email || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <span x-show="row.last_login" x-text="row.last_login"></span>
                                        <span x-show="!row.last_login" class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-4 sm:px-7 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button @click="openEdit(row)"
                                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                                    style="background-color:#D97706;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="openHapus(row.id)"
                                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                                    style="background-color:#DC2626;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            {{-- Empty state saat tidak ada hasil search --}}
                            <tr x-show="filtered.length === 0">
                                <td colspan="5" class="px-4 sm:px-7 py-10 text-center text-sm text-gray-400">
                                    Tidak ada data yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Pagination UI (Hanya tampil jika ada data) --}}
                <div class="px-4 sm:px-7 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3" x-show="filtered.length > 0">
                    <p class="text-xs text-gray-400 text-center sm:text-left">
                        Menampilkan <span x-text="(currentPage - 1) * perPage + 1"></span> -
                        <span x-text="Math.min(currentPage * perPage, filtered.length)"></span> dari
                        <span x-text="filtered.length"></span> entri
                    </p>
                    <div class="flex items-center gap-1 flex-wrap justify-center">
                        <button @click="prevPage()" :disabled="currentPage === 1"
                                class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 text-gray-500">
                            ‹
                        </button>

                        <template x-for="page in totalPages" :key="page">
                            <button @click="goToPage(page)"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold border transition-colors"
                                    :class="currentPage === page ? 'text-white border-transparent bg-[#01458E]' : 'text-gray-600 border-gray-200 hover:bg-gray-50'">
                                <span x-text="page"></span>
                            </button>
                        </template>

                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 text-gray-500">
                            ›
                        </button>
                    </div>
                </div>

            </div>
        </main>

        {{-- ══════════════════════════════════════════
             MODAL: Tambah Data Instansi (OPD) Baru
        ══════════════════════════════════════════ --}}
        <div x-show="showTambah"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="showTambah = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-xl flex flex-col max-h-[90vh] overflow-hidden"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="px-5 sm:px-6 py-4 text-white shrink-0 bg-[#01458E]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">➕ Tambah Akun OPD</p>
                                <p class="text-[10px] sm:text-xs mt-0.5" style="color:#93C5FD;">Isikan data lengkap instansi</p>
                            </div>
                        </div>
                        <button @click="showTambah = false" class="text-blue-300 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="overflow-y-auto modal-scroll px-5 sm:px-6 py-4 flex-1">
                    <form id="form-tambah" method="POST" action="{{ route('super_admin.pengguna.opd.store') }}">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah">

                        @if($openTambah && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
                        </div>
                        @endif

                        {{-- Seksi: Akun --}}
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email"
                                       value="{{ $openTambah ? old('email') : '' }}"
                                       placeholder="Ketik alamat email instansi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input :type="showPass ? 'text' : 'password'" name="password"
                                           placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showPass = !showPass"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        {{-- Seksi: Data Instansi --}}
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Instansi</p>
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode OPD <span class="text-red-400">*</span></label>
                                    <input type="text" name="kode_opd"
                                           value="{{ $openTambah ? old('kode_opd') : '' }}"
                                           placeholder="Contoh: DISDIK"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">KD Unit</label>
                                    <input type="text" name="kdunit"
                                           value="{{ $openTambah ? old('kdunit') : '' }}"
                                           placeholder="Kode unit..."
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Instansi (OPD) <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_opd"
                                       value="{{ $openTambah ? old('nama_opd') : '' }}"
                                       placeholder="Ketik nama singkat instansi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Apakah Bagian? <span class="text-red-400">*</span>
                                    </label>
                                    <select name="is_bagian"
                                            x-model="tambahIsBagian"
                                            @change="if(tambahIsBagian !== 'Y') tambahParentId = ''"
                                            required
                                            :class="!tambahIsBagian ? 'border-red-300' : 'border-gray-200'"
                                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 transition-colors bg-white">
                                        <option value="">— Pilih —</option>
                                        <option value="Y">Ya (Y)</option>
                                        <option value="N">Tidak (N)</option>
                                    </select>
                                    <p x-show="!tambahIsBagian" class="text-[11px] text-red-500 mt-1 font-medium">Wajib dipilih</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1.5"
                                           :class="tambahIsBagian === 'Y' ? 'text-gray-700' : 'text-gray-400'">
                                        OPD Induk (Parent)
                                        <span x-show="tambahIsBagian === 'Y'" class="text-red-400">*</span>
                                    </label>
                                    <select name="parent_id"
                                            x-model="tambahParentId"
                                            :disabled="tambahIsBagian !== 'Y'"
                                            :required="tambahIsBagian === 'Y'"
                                            :class="tambahIsBagian !== 'Y'
                                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-200 opacity-60'
                                                : 'bg-white text-gray-600 border-gray-200 focus:ring-[#01458E]/20 focus:border-[#01458E]'"
                                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors">
                                        <option value="">— Pilih OPD Induk —</option>
                                        @foreach($opdList as $parent)
                                        <option value="{{ $parent->id }}"
                                                {{ ($openTambah && old('parent_id') == $parent->id) ? 'selected' : '' }}>
                                            {{ $parent->nama_opd }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <p x-show="tambahIsBagian !== 'Y'" class="text-[10px] sm:text-[11px] text-gray-400 mt-1">
                                        Pilih "Ya" pada Apakah Bagian? untuk mengaktifkan
                                    </p>
                                    <p x-show="tambahIsBagian === 'Y' && !tambahParentId" class="text-[10px] sm:text-[11px] text-red-500 mt-1 font-medium">
                                        OPD Induk wajib dipilih
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center gap-2 sm:gap-3 px-5 sm:px-6 py-4 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showTambah = false"
                            class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="form-tambah"
                            class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white transition-opacity hover:opacity-90"
                            style="background-color:#16A34A;">
                        Tambah Pengguna
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             MODAL: Edit Data Instansi (OPD)
        ══════════════════════════════════════════ --}}
        <div x-show="showEdit"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="showEdit = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-xl flex flex-col max-h-[90vh] overflow-hidden"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="px-5 sm:px-6 py-4 text-white shrink-0 bg-[#01458E]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">✏️ Edit Akun OPD</p>
                                <p class="text-[10px] sm:text-xs mt-0.5" style="color:#93C5FD;">Perbarui data instansi</p>
                            </div>
                        </div>
                        <button @click="showEdit = false" class="text-blue-300 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="overflow-y-auto modal-scroll px-5 sm:px-6 py-4 flex-1">
                    <form id="form-edit" method="POST" :action="`{{ url('super-admin/pengguna/opd') }}/${edit.id}`">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_modal" value="edit">
                        <input type="hidden" name="_edit_opd_id" :value="edit.id">

                        @if($openEdit && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $err)<p>• {{ $err }}</p>@endforeach
                        </div>
                        @endif

                        {{-- Seksi: Akun --}}
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" x-model="edit.email"
                                       placeholder="Ketik alamat email instansi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <p class="text-[10px] sm:text-[11px] text-gray-400 mb-1.5">Biarkan kosong jika tidak ingin mengubah password.</p>
                                <div class="relative">
                                    <input :type="showEditPass ? 'text' : 'password'" name="password"
                                           placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showEditPass = !showEditPass"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showEditPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg x-show="showEditPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        {{-- Seksi: Data Instansi --}}
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Instansi</p>
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kode OPD <span class="text-red-400">*</span></label>
                                    <input type="text" name="kode_opd" x-model="edit.kode_opd"
                                           placeholder="Contoh: DISDIK"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">KD Unit</label>
                                    <input type="text" name="kdunit" x-model="edit.kdunit"
                                           placeholder="Kode unit..."
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Instansi (OPD) <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_opd" x-model="edit.nama_opd"
                                       placeholder="Ketik nama singkat instansi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Apakah Bagian? <span class="text-red-400">*</span>
                                    </label>
                                    <select name="is_bagian" x-model="edit.is_bagian"
                                            @change="if(edit.is_bagian !== 'Y') edit.parent_id = ''"
                                            required
                                            :class="!edit.is_bagian ? 'border-red-300' : 'border-gray-200'"
                                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 transition-colors bg-white">
                                        <option value="">— Pilih —</option>
                                        <option value="Y">Ya (Y)</option>
                                        <option value="N">Tidak (N)</option>
                                    </select>
                                    <p x-show="!edit.is_bagian" class="text-[11px] text-red-500 mt-1 font-medium">Wajib dipilih</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1.5"
                                           :class="edit.is_bagian === 'Y' ? 'text-gray-700' : 'text-gray-400'">
                                        OPD Induk (Parent)
                                        <span x-show="edit.is_bagian === 'Y'" class="text-red-400">*</span>
                                    </label>
                                    <select name="parent_id" x-model="edit.parent_id"
                                            :disabled="edit.is_bagian !== 'Y'"
                                            :required="edit.is_bagian === 'Y'"
                                            :class="edit.is_bagian !== 'Y'
                                                ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-200 opacity-60'
                                                : 'bg-white text-gray-600 border-gray-200 focus:ring-[#01458E]/20 focus:border-[#01458E]'"
                                            class="w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 transition-colors">
                                        <option value="">— Pilih OPD Induk —</option>
                                        @foreach($opdList as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->nama_opd }}</option>
                                        @endforeach
                                    </select>
                                    <p x-show="edit.is_bagian !== 'Y'" class="text-[10px] sm:text-[11px] text-gray-400 mt-1">
                                        Pilih "Ya" pada Apakah Bagian? untuk mengaktifkan
                                    </p>
                                    <p x-show="edit.is_bagian === 'Y' && !edit.parent_id" class="text-[10px] sm:text-[11px] text-red-500 mt-1 font-medium">
                                        OPD Induk wajib dipilih
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center gap-2 sm:gap-3 px-5 sm:px-6 py-4 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showEdit = false"
                            class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" form="form-edit"
                            class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white transition-opacity hover:opacity-90"
                            style="background-color:#16A34A;">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             MODAL: Hapus Data Instansi (OPD)
        ══════════════════════════════════════════ --}}
        <div x-show="showHapus"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="showHapus = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden mx-4">
                {{-- Header --}}
                <div class="px-5 sm:px-6 py-4 text-white bg-[#DC2626]">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">🗑 Hapus Akun OPD</p>
                                <p class="text-[10px] sm:text-xs mt-0.5" style="color:#FECACA;">Tindakan tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="showHapus = false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Body --}}
                <div class="p-5 sm:p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-2">
                        <p class="text-sm font-semibold text-red-900" x-text="hapusNama"></p>
                        <p class="text-xs text-red-700 mt-1.5">Data yang dihapus tidak dapat dipulihkan.</p>
                    </div>
                </div>
                {{-- Footer --}}
                <form method="POST" :action="`{{ url('super-admin/pengguna/opd') }}/${hapusId}`" class="contents">
                    @csrf
                    @method('DELETE')
                    <div class="px-5 sm:px-6 py-4 border-t border-gray-100 flex gap-2 sm:gap-3">
                        <button type="button" @click="showHapus = false"
                                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white hover:opacity-90 transition-all"
                                style="background:#DC2626;">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- end ml-64 wrapper --}}

</body>
</html>
