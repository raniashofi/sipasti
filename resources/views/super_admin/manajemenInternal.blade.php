<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manajemen Pengguna Internal — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        .modal-scroll::-webkit-scrollbar { width: 4px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    @php
        $bidangLabel = [
            'e_government'                     => 'E-Government',
            'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
            'statistik_persandian'             => 'Statistik & Persandian',
        ];

        // Tim Teknis modal state
        $openTambahTT = session('open_tambah_tt') || (old('_modal') === 'tambah_tt' && $errors->any());
        $openEditTT   = session('open_edit_tt')   || (old('_modal') === 'edit_tt'   && $errors->any());
        $editTTId     = session('edit_tt_id', old('_edit_tt_id', ''));

        $editTTInit = [
            'id'             => $editTTId,
            'nama_lengkap'   => old('nama_lengkap', ''),
            'email'          => old('email', ''),
            'bidang_id'      => old('bidang_id', ''),
            'status_teknisi' => old('status_teknisi', ''),
        ];

        // Admin Helpdesk modal state
        $openTambahAH = session('open_tambah_ah') || (old('_modal') === 'tambah_ah' && $errors->any());
        $openEditAH   = session('open_edit_ah')   || (old('_modal') === 'edit_ah'   && $errors->any());
        $editAHId     = session('edit_ah_id', old('_edit_ah_id', ''));

        $editAHInit = [
            'id'           => $editAHId,
            'nama_lengkap' => old('nama_lengkap', ''),
            'email'        => old('email', ''),
            'bidang_id'    => old('bidang_id', ''),
        ];

        // Pimpinan modal state
        $openTambahPimpinan = session('open_tambah_pimpinan') || (old('_modal') === 'tambah_pimpinan' && $errors->any());
        $openEditPimpinan   = session('open_edit_pimpinan')   || (old('_modal') === 'edit_pimpinan'   && $errors->any());
        $editPimpinanId     = session('edit_pimpinan_id', old('_edit_pimpinan_id', ''));

        $editPimpinanInit = [
            'id'           => $editPimpinanId,
            'nama_lengkap' => old('nama_lengkap', ''),
            'email'        => old('email', ''),
        ];

        // Active sub-tab
        $activeTab = $tab ?? 'tim_teknis';
        if ($openTambahAH || $openEditAH) $activeTab = 'admin_helpdesk';
        if ($openTambahPimpinan || $openEditPimpinan) $activeTab = 'pimpinan';
        if ($openTambahTT || $openEditTT) $activeTab = 'tim_teknis';

        // Import ActivityLogController untuk mengambil last login dari activity_log
        use App\Http\Controllers\ActivityLogController;
    @endphp

    <script>
        function internalPage() {
            @php
                $ttRows = $timTeknis->map(fn($tt) => [
                    'id'             => $tt->id,
                    'nama_lengkap'   => $tt->nama_lengkap ?? '',
                    'email'          => $tt->user?->email ?? '',
                    'bidang_id'      => $tt->bidang_id ?? '',
                    'bidang_label'   => $bidangLabel[$tt->bidang?->nama_bidang ?? ''] ?? '',
                    'status_teknisi' => $tt->status_teknisi ?? '',
                    'last_login'     => $tt->user_id ? ActivityLogController::getLastLoginFormatted($tt->user_id) : null,
                ])->values();

                $ahRows = $adminHelpdesk->map(fn($ah) => [
                    'id'           => $ah->id,
                    'nama_lengkap' => $ah->nama_lengkap ?? '',
                    'email'        => $ah->user?->email ?? '',
                    'bidang_id'    => $ah->bidang_id ?? '',
                    'bidang_label' => $bidangLabel[$ah->bidang?->nama_bidang ?? ''] ?? '',
                    'last_login'   => $ah->user_id ? ActivityLogController::getLastLoginFormatted($ah->user_id) : null,
                ])->values();

                $pimpinanRows = $pimpinanList->map(fn($p) => [
                    'id'           => $p->id,
                    'nama_lengkap' => $p->nama_lengkap ?? '',
                    'email'        => $p->user?->email ?? '',
                    'last_login'   => $p->user_id ? ActivityLogController::getLastLoginFormatted($p->user_id) : null,
                ])->values();
            @endphp
            const allTT      = @json($ttRows);
            const allAH      = @json($ahRows);
            const allPimpinan = @json($pimpinanRows);

            return {
                tab:    '{{ $activeTab }}',
                search: '',

                get filteredTT() {
                    if (!this.search.trim()) return allTT;
                    const q = this.search.toLowerCase();
                    return allTT.filter(r => r.nama_lengkap.toLowerCase().includes(q) || r.email.toLowerCase().includes(q));
                },
                get filteredAH() {
                    if (!this.search.trim()) return allAH;
                    const q = this.search.toLowerCase();
                    return allAH.filter(r => r.nama_lengkap.toLowerCase().includes(q) || r.email.toLowerCase().includes(q));
                },
                get filteredPimpinan() {
                    if (!this.search.trim()) return allPimpinan;
                    const q = this.search.toLowerCase();
                    return allPimpinan.filter(r => r.nama_lengkap.toLowerCase().includes(q) || r.email.toLowerCase().includes(q));
                },

                // Tim Teknis
                showTambahTT: {{ $openTambahTT ? 'true' : 'false' }},
                showEditTT:   {{ $openEditTT   ? 'true' : 'false' }},
                showHapusTT:  false,
                showPassTT:   false,
                showEditPassTT: false,
                hapusTTId: null,
                editTT: @json($editTTInit),

                openEditTT(data) {
                    this.editTT = { ...data };
                    this.showEditPassTT = false;
                    this.showEditTT = true;
                },
                openHapusTT(id) {
                    this.hapusTTId = id;
                    this.showHapusTT = true;
                },

                // Admin Helpdesk
                showTambahAH: {{ $openTambahAH ? 'true' : 'false' }},
                showEditAH:   {{ $openEditAH   ? 'true' : 'false' }},
                showHapusAH:  false,
                showPassAH:   false,
                showEditPassAH: false,
                hapusAHId: null,
                editAH: @json($editAHInit),

                openEditAH(data) {
                    this.editAH = { ...data };
                    this.showEditPassAH = false;
                    this.showEditAH = true;
                },
                openHapusAH(id) {
                    this.hapusAHId = id;
                    this.showHapusAH = true;
                },

                // Pimpinan
                showTambahPimpinan: {{ $openTambahPimpinan ? 'true' : 'false' }},
                showEditPimpinan:   {{ $openEditPimpinan   ? 'true' : 'false' }},
                showHapusPimpinan:  false,
                showPassPimpinan:   false,
                showEditPassPimpinan: false,
                hapusPimpinanId: null,
                editPimpinan: @json($editPimpinanInit),

                openEditPimpinan(data) {
                    this.editPimpinan = { ...data };
                    this.showEditPassPimpinan = false;
                    this.showEditPimpinan = true;
                },
                openHapusPimpinan(id) {
                    this.hapusPimpinanId = id;
                    this.showHapusPimpinan = true;
                },
            };
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <div class="ml-64 min-h-screen flex flex-col" x-data="internalPage()">

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 px-8 py-0 flex items-center justify-between sticky top-0 z-30">

            {{-- Tabs OPD / Internal --}}
            <div class="flex items-center gap-0">
                <a href="{{ route('super_admin.pengguna.opd') }}"
                   class="px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-400 hover:text-gray-600 transition-colors">
                    Data OPD
                </a>
                <a href="{{ route('super_admin.pengguna.internal') }}"
                   class="px-6 py-4 text-sm font-semibold border-b-2 border-[#01458E] text-[#01458E] transition-colors">
                    Data Internal
                </a>
            </div>

            {{-- Search + Tambah --}}
            <div class="flex items-center gap-4 py-3">
                <div class="relative">
                    <input type="text" x-model="search"
                           placeholder="Cari Nama..."
                           class="pl-4 pr-10 py-2 text-sm border border-gray-200 rounded-full bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]
                                  w-56 transition-all">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                    </span>
                </div>

                <button x-show="tab === 'tim_teknis'"
                        @click="showTambahTT = true; showPassTT = false"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                        style="background-color:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Tim Teknis
                </button>

                <button x-show="tab === 'admin_helpdesk'"
                        @click="showTambahAH = true; showPassAH = false"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                        style="background-color:#01458E; display:none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Admin Helpdesk
                </button>

                <button x-show="tab === 'pimpinan'"
                        @click="showTambahPimpinan = true; showPassPimpinan = false"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white transition-opacity hover:opacity-90"
                        style="background-color:#01458E; display:none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Pimpinan
                </button>
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

                {{-- Sub-tabs: Tim Teknis / Admin Helpdesk --}}
                <div class="flex items-center justify-between px-7 pt-5 pb-0 border-b border-gray-100">
                    <div class="flex items-center gap-0">
                        <button @click="tab = 'tim_teknis'"
                                :class="tab === 'tim_teknis'
                                    ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                    : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                                class="px-5 pb-4 text-sm transition-colors">
                            Tim Teknis
                            <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                {{ $timTeknis->count() }}
                            </span>
                        </button>
                        <button @click="tab = 'admin_helpdesk'"
                                :class="tab === 'admin_helpdesk'
                                    ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                    : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                                class="px-5 pb-4 text-sm transition-colors">
                            Admin Helpdesk
                            <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                {{ $adminHelpdesk->count() }}
                            </span>
                        </button>
                        <button @click="tab = 'pimpinan'"
                                :class="tab === 'pimpinan'
                                    ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                    : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                                class="px-5 pb-4 text-sm transition-colors">
                            Pimpinan
                            <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                {{ $pimpinanList->count() }}
                            </span>
                        </button>
                    </div>
                    <p x-show="tab === 'tim_teknis'" class="text-base font-bold text-gray-900 pb-4">Manajemen Akun Tim Teknis</p>
                    <p x-show="tab === 'admin_helpdesk'" class="text-base font-bold text-gray-900 pb-4" style="display:none;">Manajemen Akun Admin Helpdesk</p>
                    <p x-show="tab === 'pimpinan'" class="text-base font-bold text-gray-900 pb-4" style="display:none;">Manajemen Akun Pimpinan</p>
                </div>

                {{-- ── TABEL TIM TEKNIS ── --}}
                <div x-show="tab === 'tim_teknis'">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Nama Lengkap</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Email</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Bidang</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Status</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Terakhir Login</th>
                                <th class="px-7 py-3.5 text-right text-xs font-bold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in filteredTT" :key="row.id">
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-7 py-4 text-sm text-gray-700" x-text="index + 1"></td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900" x-text="row.nama_lengkap || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.email || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.bidang_label || '-'"></td>
                                    <td class="px-4 py-4">
                                        <span x-show="row.status_teknisi === 'online'"
                                              class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-green-50 text-green-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Online
                                        </span>
                                        <span x-show="row.status_teknisi === 'offline'"
                                              class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500" style="display:none;">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Offline
                                        </span>
                                        <span x-show="!row.status_teknisi" class="text-gray-300 text-sm" style="display:none;">—</span>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <span x-show="row.last_login" x-text="row.last_login"></span>
                                        <span x-show="!row.last_login" class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-7 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditTT(row)"
                                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                                    style="background-color:#D97706;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="openHapusTT(row.id)"
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
                            <tr x-show="filteredTT.length === 0">
                                <td colspan="7" class="px-7 py-10 text-center text-sm text-gray-400">Tidak ada data yang cocok.</td>
                            </tr>
                            <template x-if="filteredTT.length > 0 && filteredTT.length < 7">
                                <template x-for="i in (7 - filteredTT.length)" :key="'skt-' + i">
                                    <tr class="border-b border-gray-50"><td colspan="7" class="px-7 py-5"><div class="h-4 rounded-full bg-[#EEF3F9]"></div></td></tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- ── TABEL ADMIN HELPDESK ── --}}
                <div x-show="tab === 'admin_helpdesk'" style="display:none;">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Nama Lengkap</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Email</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Bidang</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Terakhir Login</th>
                                <th class="px-7 py-3.5 text-right text-xs font-bold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in filteredAH" :key="row.id">
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-7 py-4 text-sm text-gray-700" x-text="index + 1"></td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900" x-text="row.nama_lengkap || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.email || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.bidang_label || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <span x-show="row.last_login" x-text="row.last_login"></span>
                                        <span x-show="!row.last_login" class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-7 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditAH(row)"
                                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                                    style="background-color:#D97706;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="openHapusAH(row.id)"
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
                            <tr x-show="filteredAH.length === 0">
                                <td colspan="6" class="px-7 py-10 text-center text-sm text-gray-400">Tidak ada data yang cocok.</td>
                            </tr>
                            <template x-if="filteredAH.length > 0 && filteredAH.length < 7">
                                <template x-for="i in (7 - filteredAH.length)" :key="'ska-' + i">
                                    <tr class="border-b border-gray-50"><td colspan="6" class="px-7 py-5"><div class="h-4 rounded-full bg-[#EEF3F9]"></div></td></tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- ── TABEL PIMPINAN ── --}}
                <div x-show="tab === 'pimpinan'" style="display:none;">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-7 py-3.5 text-left text-xs font-bold text-gray-700 w-14">No</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Nama Lengkap</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Email</th>
                                <th class="px-4 py-3.5 text-left text-xs font-bold text-gray-700">Terakhir Login</th>
                                <th class="px-7 py-3.5 text-right text-xs font-bold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in filteredPimpinan" :key="row.id">
                                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                    <td class="px-7 py-4 text-sm text-gray-700" x-text="index + 1"></td>
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900" x-text="row.nama_lengkap || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-600" x-text="row.email || '-'"></td>
                                    <td class="px-4 py-4 text-sm text-gray-500">
                                        <span x-show="row.last_login" x-text="row.last_login"></span>
                                        <span x-show="!row.last_login" class="text-gray-300">—</span>
                                    </td>
                                    <td class="px-7 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditPimpinan(row)"
                                                    class="flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                                                    style="background-color:#D97706;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <button @click="openHapusPimpinan(row.id)"
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
                            <tr x-show="filteredPimpinan.length === 0">
                                <td colspan="5" class="px-7 py-10 text-center text-sm text-gray-400">Tidak ada data yang cocok.</td>
                            </tr>
                            <template x-if="filteredPimpinan.length > 0 && filteredPimpinan.length < 7">
                                <template x-for="i in (7 - filteredPimpinan.length)" :key="'skp-' + i">
                                    <tr class="border-b border-gray-50"><td colspan="5" class="px-7 py-5"><div class="h-4 rounded-full bg-[#EEF3F9]"></div></td></tr>
                                </template>
                            </template>
                        </tbody>
                    </table>
                </div>

            </div>
        </main>

        {{-- ═══════════════════════════════════════════════════════
             MODALS TIM TEKNIS
        ═══════════════════════════════════════════════════════ --}}

        {{-- Tambah Tim Teknis --}}
        <div x-show="showTambahTT"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showTambahTT = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showTambahTT = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Tambah Tim Teknis Baru</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-tambah-tt" method="POST" action="{{ route('super_admin.pengguna.internal.tt.store') }}">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah_tt">

                        @if($openTambahTT && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" value="{{ $openTambahTT ? old('email') : '' }}"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input :type="showPassTT ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showPassTT = !showPassTT"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPassTT" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassTT" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Tim Teknis</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" value="{{ $openTambahTT ? old('nama_lengkap') : '' }}"
                                       placeholder="Nama lengkap teknisi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bidang</label>
                                <select name="bidang_id"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Bidang —</option>
                                    @foreach($bidangs as $b)
                                    <option value="{{ $b->id }}" {{ ($openTambahTT && old('bidang_id') === $b->id) ? 'selected' : '' }}>
                                        {{ $bidangLabel[$b->nama_bidang] ?? $b->nama_bidang }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Teknisi</label>
                                <select name="status_teknisi"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Status —</option>
                                    <option value="online"  {{ ($openTambahTT && old('status_teknisi') === 'online')  ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ ($openTambahTT && old('status_teknisi') === 'offline') ? 'selected' : '' }}>Offline</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showTambahTT = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-tambah-tt"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Tambah Pengguna</button>
                </div>
            </div>
        </div>

        {{-- Edit Tim Teknis --}}
        <div x-show="showEditTT"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showEditTT = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showEditTT = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Edit Data Tim Teknis</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-edit-tt" method="POST" :action="`{{ url('super-admin/pengguna/internal/tim-teknis') }}/${editTT.id}`">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_modal" value="edit_tt">
                        <input type="hidden" name="_edit_tt_id" :value="editTT.id">

                        @if($openEditTT && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" x-model="editTT.email"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <p class="text-[11px] text-gray-400 mb-1.5">Biarkan kosong jika tidak ingin mengubah.</p>
                                <div class="relative">
                                    <input :type="showEditPassTT ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showEditPassTT = !showEditPassTT"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showEditPassTT" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showEditPassTT" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Tim Teknis</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" x-model="editTT.nama_lengkap"
                                       placeholder="Nama lengkap teknisi..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bidang</label>
                                <select name="bidang_id" x-model="editTT.bidang_id"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Bidang —</option>
                                    @foreach($bidangs as $b)
                                    <option value="{{ $b->id }}">{{ $bidangLabel[$b->nama_bidang] ?? $b->nama_bidang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Teknisi</label>
                                <select name="status_teknisi" x-model="editTT.status_teknisi"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Status —</option>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showEditTT = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-edit-tt"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Simpan Perubahan</button>
                </div>
            </div>
        </div>

        {{-- Hapus Tim Teknis --}}
        <div x-show="showHapusTT"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showHapusTT = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 text-white rounded-t-3xl" style="background:#DC2626;border-radius:1.5rem 1.5rem 0 0;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">🗑 Hapus Data Tim Teknis</p>
                                <p class="text-xs mt-0.5" style="color:#FECACA;">Tindakan tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="showHapusTT = false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Body --}}
                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-4">
                        <p class="text-sm font-semibold text-red-900" x-text="hapusTTNama"></p>
                        <p class="text-xs text-red-700 mt-1.5">Data yang dihapus tidak dapat dipulihkan.</p>
                    </div>
                </div>
                {{-- Footer --}}
                <form method="POST" :action="`{{ url('super-admin/pengguna/internal/tim-teknis') }}/${hapusTTId}`" class="contents">
                    @csrf
                    @method('DELETE')
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        <button type="button" @click="showHapusTT = false"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                                style="background:#DC2626;">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
                        <button type="button" @click="showHapusTT = false"
                                class="px-8 py-2.5 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                                style="background-color:#DC2626;">Tidak</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             MODALS ADMIN HELPDESK
        ═══════════════════════════════════════════════════════ --}}

        {{-- Tambah Admin Helpdesk --}}
        <div x-show="showTambahAH"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showTambahAH = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showTambahAH = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Tambah Admin Helpdesk Baru</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-tambah-ah" method="POST" action="{{ route('super_admin.pengguna.internal.ah.store') }}">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah_ah">

                        @if($openTambahAH && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" value="{{ $openTambahAH ? old('email') : '' }}"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input :type="showPassAH ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showPassAH = !showPassAH"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPassAH" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassAH" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Admin Helpdesk</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" value="{{ $openTambahAH ? old('nama_lengkap') : '' }}"
                                       placeholder="Nama lengkap admin helpdesk..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bidang</label>
                                <select name="bidang_id"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Bidang —</option>
                                    @foreach($bidangs as $b)
                                    <option value="{{ $b->id }}" {{ ($openTambahAH && old('bidang_id') === $b->id) ? 'selected' : '' }}>
                                        {{ $bidangLabel[$b->nama_bidang] ?? $b->nama_bidang }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showTambahAH = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-tambah-ah"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Tambah Pengguna</button>
                </div>
            </div>
        </div>

        {{-- Edit Admin Helpdesk --}}
        <div x-show="showEditAH"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showEditAH = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showEditAH = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Edit Data Admin Helpdesk</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-edit-ah" method="POST" :action="`{{ url('super-admin/pengguna/internal/admin-helpdesk') }}/${editAH.id}`">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_modal" value="edit_ah">
                        <input type="hidden" name="_edit_ah_id" :value="editAH.id">

                        @if($openEditAH && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" x-model="editAH.email"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <p class="text-[11px] text-gray-400 mb-1.5">Biarkan kosong jika tidak ingin mengubah.</p>
                                <div class="relative">
                                    <input :type="showEditPassAH ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showEditPassAH = !showEditPassAH"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showEditPassAH" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showEditPassAH" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Admin Helpdesk</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" x-model="editAH.nama_lengkap"
                                       placeholder="Nama lengkap admin helpdesk..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Bidang</label>
                                <select name="bidang_id" x-model="editAH.bidang_id"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white transition-colors">
                                    <option value="">— Pilih Bidang —</option>
                                    @foreach($bidangs as $b)
                                    <option value="{{ $b->id }}">{{ $bidangLabel[$b->nama_bidang] ?? $b->nama_bidang }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showEditAH = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-edit-ah"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Simpan Perubahan</button>
                </div>
            </div>
        </div>

        {{-- Hapus Admin Helpdesk --}}
        <div x-show="showHapusAH"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showHapusAH = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 text-white rounded-t-3xl" style="background:#DC2626;border-radius:1.5rem 1.5rem 0 0;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">🗑 Hapus Data Admin Helpdesk</p>
                                <p class="text-xs mt-0.5" style="color:#FECACA;">Tindakan tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="showHapusAH = false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Body --}}
                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-4">
                        <p class="text-sm font-semibold text-red-900" x-text="hapusAHNama"></p>
                        <p class="text-xs text-red-700 mt-1.5">Data yang dihapus tidak dapat dipulihkan.</p>
                    </div>
                </div>
                {{-- Footer --}}
                <form method="POST" :action="`{{ url('super-admin/pengguna/internal/admin-helpdesk') }}/${hapusAHId}`" class="contents">
                    @csrf
                    @method('DELETE')
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        <button type="button" @click="showHapusAH = false"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                                style="background:#DC2626;">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════
             MODALS PIMPINAN
        ═══════════════════════════════════════════════════════ --}}

        {{-- Tambah Pimpinan --}}
        <div x-show="showTambahPimpinan"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showTambahPimpinan = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showTambahPimpinan = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Tambah Pimpinan Baru</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-tambah-pimpinan" method="POST" action="{{ route('super_admin.pengguna.internal.pimpinan.store') }}">
                        @csrf
                        <input type="hidden" name="_modal" value="tambah_pimpinan">

                        @if($openTambahPimpinan && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" value="{{ $openTambahPimpinan ? old('email') : '' }}"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password <span class="text-red-400">*</span></label>
                                <div class="relative">
                                    <input :type="showPassPimpinan ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showPassPimpinan = !showPassPimpinan"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showPassPimpinan" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showPassPimpinan" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Pimpinan</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" value="{{ $openTambahPimpinan ? old('nama_lengkap') : '' }}"
                                       placeholder="Nama lengkap pimpinan..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showTambahPimpinan = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-tambah-pimpinan"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Tambah Pengguna</button>
                </div>
            </div>
        </div>

        {{-- Edit Pimpinan --}}
        <div x-show="showEditPimpinan"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showEditPimpinan = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[90vh]" @click.stop>

                <div class="relative flex items-center justify-center px-8 pt-7 pb-5 shrink-0">
                    <button @click="showEditPimpinan = false"
                            class="absolute left-5 top-5 w-9 h-9 rounded-full flex items-center justify-center text-white hover:opacity-80"
                            style="background-color:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <h3 class="text-xl font-bold text-gray-900">Edit Data Pimpinan</h3>
                </div>

                <div class="overflow-y-auto modal-scroll px-8 pb-2">
                    <form id="form-edit-pimpinan" method="POST" :action="`{{ url('super-admin/pengguna/internal/pimpinan') }}/${editPimpinan.id}`">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_modal" value="edit_pimpinan">
                        <input type="hidden" name="_edit_pimpinan_id" :value="editPimpinan.id">

                        @if($openEditPimpinan && $errors->any())
                        <div class="mb-4 text-xs text-red-600 bg-red-50 rounded-xl px-4 py-3 space-y-1">
                            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                        </div>
                        @endif

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Akun</p>
                        <div class="space-y-3 mb-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="email" x-model="editPimpinan.email"
                                       placeholder="Ketik email..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                                <p class="text-[11px] text-gray-400 mb-1.5">Biarkan kosong jika tidak ingin mengubah.</p>
                                <div class="relative">
                                    <input :type="showEditPassPimpinan ? 'text' : 'password'" name="password" placeholder="••••••"
                                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm pr-12 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                                    <button type="button" @click="showEditPassPimpinan = !showEditPassPimpinan"
                                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                        <svg x-show="!showEditPassPimpinan" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="showEditPassPimpinan" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 mb-5"></div>

                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Data Pimpinan</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-400">*</span></label>
                                <input type="text" name="nama_lengkap" x-model="editPimpinan.nama_lengkap"
                                       placeholder="Nama lengkap pimpinan..."
                                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 transition-colors">
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3 px-8 py-5 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showEditPimpinan = false"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-gray-600 bg-gray-200 hover:bg-gray-300 transition-colors">Batal</button>
                    <button type="submit" form="form-edit-pimpinan"
                            class="flex-1 py-3 rounded-full text-sm font-semibold text-white hover:opacity-90 transition-opacity"
                            style="background-color:#16A34A;">Simpan Perubahan</button>
                </div>
            </div>
        </div>

        {{-- Hapus Pimpinan --}}
        <div x-show="showHapusPimpinan"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"  x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click.self="showHapusPimpinan = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">
            <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.stop>
                {{-- Header --}}
                <div class="px-6 py-4 text-white rounded-t-3xl" style="background:#DC2626;border-radius:1.5rem 1.5rem 0 0;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">🗑 Hapus Data Pimpinan</p>
                                <p class="text-xs mt-0.5" style="color:#FECACA;">Tindakan tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="showHapusPimpinan = false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
                {{-- Body --}}
                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-4">
                        <p class="text-sm font-semibold text-red-900" x-text="hapusPimpinanNama"></p>
                        <p class="text-xs text-red-700 mt-1.5">Data yang dihapus tidak dapat dipulihkan.</p>
                    </div>
                </div>
                {{-- Footer --}}
                <form method="POST" :action="`{{ url('super-admin/pengguna/internal/pimpinan') }}/${hapusPimpinanId}`" class="contents">
                    @csrf
                    @method('DELETE')
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        <button type="button" @click="showHapusPimpinan = false"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                                style="background:#DC2626;">
                            Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>{{-- end ml-64 --}}

</body>
</html>
