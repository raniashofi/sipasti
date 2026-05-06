<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Tugas — Tim Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Scrollbar styling untuk drawer */
        .drawer-scroll::-webkit-scrollbar { width: 4px; }
        .drawer-scroll::-webkit-scrollbar-track { background: transparent; }
        .drawer-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    @include('layouts.sidebarTimTeknis')

    {{-- Buka scope Alpine x-data --}}
    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col" x-data="riwayatPage()" x-cloak>

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Riwayat Tugas</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar seluruh tiket yang telah Anda selesaikan atau kembalikan</p>
            </div>
            {{-- <div class="inline-flex self-start sm:self-auto items-center gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold text-gray-600 bg-gray-100">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $countAll }} tiket selesai
            </div> --}}
        </header>

        <main class="flex-1 px-4 lg:px-6 py-4 lg:py-6 flex flex-col overflow-hidden w-full">

            {{-- ── Filter & Search ── --}}
            <div class="pb-2">
                <form method="GET" action="{{ route('tim_teknis.riwayat') }}" id="filterFormRiwayat"
                      class="bg-white rounded-2xl border border-gray-100 px-4 sm:px-5 py-4 mb-3 sm:mb-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                    <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3 sm:gap-2 items-center">
                        <input type="hidden" name="peran" value="{{ request('peran') }}">
                        <div class="w-full sm:flex-1 sm:min-w-[200px] relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari ID tiket atau subjek masalah..."
                                   oninput="clearTimeout(window._stRiwayat); window._stRiwayat = setTimeout(() => document.getElementById('filterFormRiwayat').submit(), 500)"
                                   class="w-full pl-9 pr-3 py-2.5 sm:py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                        <a href="{{ route('tim_teknis.riwayat', request()->only('peran')) }}"
                           class="flex justify-center sm:justify-start items-center gap-1.5 px-4 py-2.5 sm:py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 w-full sm:w-auto transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- ── Data Table ── --}}
            <div class="flex-1 overflow-auto pb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">

                    {{-- Sub-tabs Peran --}}
                    <div class="flex items-center px-4 sm:px-5 pt-3 sm:pt-4 pb-0 border-b border-gray-100 overflow-x-auto hide-scrollbar">
                        <div class="flex items-center gap-0 shrink-0 min-w-max">
                            @php $activePeran = request('peran', ''); @endphp
                            <a href="{{ route('tim_teknis.riwayat', request()->only('search')) }}"
                               class="px-4 sm:px-5 pb-3 sm:pb-4 text-[13px] sm:text-sm transition-colors {{ $activePeran === '' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                Semua <span class="ml-1 sm:ml-1.5 text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold border border-blue-100">{{ $countAll }}</span>
                            </a>
                            <a href="{{ route('tim_teknis.riwayat', [...request()->only('search'), 'peran' => 'teknisi_utama']) }}"
                               class="px-4 sm:px-5 pb-3 sm:pb-4 text-[13px] sm:text-sm transition-colors {{ $activePeran === 'teknisi_utama' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                Teknisi Utama <span class="ml-1 sm:ml-1.5 text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold border border-blue-100">{{ $countUtama }}</span>
                            </a>
                            <a href="{{ route('tim_teknis.riwayat', [...request()->only('search'), 'peran' => 'teknisi_pendamping']) }}"
                               class="px-4 sm:px-5 pb-3 sm:pb-4 text-[13px] sm:text-sm transition-colors {{ $activePeran === 'teknisi_pendamping' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                Pendamping <span class="ml-1 sm:ml-1.5 text-[10px] sm:text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold border border-blue-100">{{ $countPendamping }}</span>
                            </a>
                        </div>
                    </div>

                    {{-- Data View (Desktop Table & Mobile List) --}}
                    <div class="overflow-x-auto flex-1 w-full">
                        {{-- Mobile card list --}}
                        <div class="sm:hidden divide-y divide-gray-100">
                            @forelse($riwayats as $row)
                            @php
                                $tiket = $row->tiket;
                                $statusAkhir = $tiket?->latestStatus?->status_tiket ?? '';
                                $kategoriNama = $tiket?->kategori?->nama_kategori ?? $tiket?->kb?->kategori?->nama_kategori ?? '—';
                                $hasilConfig = match($statusAkhir) {
                                    'selesai'     => ['label' => 'Berhasil Diperbaiki', 'bg' => '#f0fdf4', 'color' => '#16a34a', 'border' => '#bbf7d0'],
                                    'rusak_berat' => ['label' => 'Rusak Berat',         'bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca'],
                                    default       => ['label' => 'Dikembalikan',        'bg' => '#fffbeb', 'color' => '#d97706', 'border' => '#fde68a'],
                                };
                                $rowJson = json_encode([
                                    'id' => $tiket?->id, 'subjek_masalah' => $tiket?->subjek_masalah,
                                    'detail_masalah' => $tiket?->detail_masalah, 'opd_nama' => $tiket?->opd?->nama_opd ?? '—',
                                    'kategori_nama' => $kategoriNama, 'spesifikasi_perangkat' => $tiket?->spesifikasi_perangkat ?? '—',
                                    'lokasi' => $tiket?->lokasi ?? '—', 'foto_bukti' => $tiket?->foto_bukti,
                                    'rekomendasi_penanganan' => $tiket?->rekomendasi_penanganan,
                                    'is_utama' => ($row->peran_teknisi === 'teknisi_utama'),
                                    'peran_label' => ($row->peran_teknisi === 'teknisi_utama') ? 'Teknisi Utama' : 'Pendamping',
                                    'waktu_tgl' => $row->waktu_ditugaskan?->translatedFormat('d M Y') ?? '—',
                                    'waktu_jam' => ($row->waktu_ditugaskan?->format('H:i') ?? '') . ' WIB',
                                    'status_akhir' => $statusAkhir, 'hasil_label' => $hasilConfig['label'],
                                    'hasil_bg' => $hasilConfig['bg'], 'hasil_color' => $hasilConfig['color'],
                                    'analisis_kerusakan' => $tiket?->latestStatus?->catatan,
                                    'spesifikasi_perangkat_rusak' => $tiket?->latestStatus?->spesifikasi_perangkat_rusak,
                                    'rekomendasi' => $tiket?->latestStatus?->rekomendasi,
                                ]);
                            @endphp
                            <div class="px-4 py-4 hover:bg-gray-50/80 cursor-pointer transition-colors"
                                 @click="openDetail({{ $rowJson }})">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <span class="font-mono text-[11px] font-bold text-[#01458E] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                        #{{ Str::upper(substr($tiket?->id ?? '', -8)) }}
                                    </span>
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full shrink-0 border"
                                          style="background:{{ $hasilConfig['bg'] }};color:{{ $hasilConfig['color'] }};border-color:{{ $hasilConfig['border'] }};">
                                        {{ $hasilConfig['label'] }}
                                    </span>
                                </div>
                                <p class="text-[13px] font-semibold text-gray-800 leading-snug mb-1">{{ $tiket?->subjek_masalah ?? '—' }}</p>
                                <div class="flex flex-wrap gap-x-2 gap-y-1 text-[11px] text-gray-500 font-medium">
                                    <span>{{ Str::limit($tiket?->opd?->nama_opd ?? '—', 22) }}</span>
                                    <span>•</span>
                                    <span class="text-gray-400">{{ $row->waktu_ditugaskan?->translatedFormat('d M Y') ?? '—' }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="px-6 py-12 flex flex-col items-center gap-3 text-center text-gray-400">
                                <div class="w-14 h-14 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="font-semibold text-gray-500 text-sm">Belum ada riwayat tugas</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Desktop table --}}
                        <div class="hidden sm:block">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="border-b border-gray-100 bg-gray-50/50">
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">ID Tiket</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Subjek Masalah</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">OPD</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Hasil Akhir</th>
                                        <th class="px-5 py-4"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($riwayats as $row)
                                        @php
                                            $tiket = $row->tiket;
                                            $statusAkhir = $tiket?->latestStatus?->status_tiket ?? '';
                                            $kategoriNama = $tiket?->kategori?->nama_kategori ?? $tiket?->kb?->kategori?->nama_kategori ?? '—';

                                            $hasilConfig = match($statusAkhir) {
                                                'selesai' => ['label' => 'Berhasil Diperbaiki', 'bg' => '#f0fdf4', 'color' => '#16a34a', 'border' => '#bbf7d0'],
                                                'rusak_berat' => ['label' => 'Rusak Berat', 'bg' => '#fef2f2', 'color' => '#dc2626', 'border' => '#fecaca'],
                                                default => ['label' => 'Dikembalikan', 'bg' => '#fffbeb', 'color' => '#d97706', 'border' => '#fde68a'],
                                            };

                                            $rowJson = json_encode([
                                                'id' => $tiket?->id,
                                                'subjek_masalah' => $tiket?->subjek_masalah,
                                                'detail_masalah' => $tiket?->detail_masalah,
                                                'opd_nama' => $tiket?->opd?->nama_opd ?? '—',
                                                'kategori_nama' => $kategoriNama,
                                                'spesifikasi_perangkat' => $tiket?->spesifikasi_perangkat ?? '—',
                                                'lokasi' => $tiket?->lokasi ?? '—',
                                                'foto_bukti' => $tiket?->foto_bukti,
                                                'rekomendasi_penanganan' => $tiket?->rekomendasi_penanganan,
                                                'is_utama' => ($row->peran_teknisi === 'teknisi_utama'),
                                                'peran_label' => ($row->peran_teknisi === 'teknisi_utama') ? 'Teknisi Utama' : 'Pendamping',
                                                'waktu_tgl' => $row->waktu_ditugaskan?->translatedFormat('d M Y') ?? '—',
                                                'waktu_jam' => ($row->waktu_ditugaskan?->format('H:i') ?? '') . ' WIB',
                                                'status_akhir' => $statusAkhir,
                                                'hasil_label' => $hasilConfig['label'],
                                                'hasil_bg' => $hasilConfig['bg'],
                                                'hasil_color' => $hasilConfig['color'],
                                                'analisis_kerusakan' => $tiket?->latestStatus?->catatan,
                                                'spesifikasi_perangkat_rusak' => $tiket?->latestStatus?->spesifikasi_perangkat_rusak,
                                                'rekomendasi' => $tiket?->latestStatus?->rekomendasi,
                                            ]);
                                        @endphp
                                        <tr class="hover:bg-blue-50/50 cursor-pointer transition-colors" @click="openDetail({{ $rowJson }})">
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="font-mono text-xs font-bold text-[#01458E] bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                                    #{{ Str::upper(substr($tiket?->id ?? '', -8)) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 min-w-[250px] max-w-sm">
                                                <p class="font-semibold text-gray-800 line-clamp-1">{{ $tiket?->subjek_masalah ?? '—' }}</p>
                                                <p class="text-xs text-gray-400 line-clamp-1 mt-0.5">{{ Str::limit($tiket?->detail_masalah ?? '', 55) }}</p>
                                            </td>
                                            <td class="px-5 py-4 text-gray-600 font-medium whitespace-nowrap">
                                                {{ Str::limit($tiket?->opd?->nama_opd ?? '—', 30) }}
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="text-[11px] font-medium px-2.5 py-1 rounded-md border border-gray-200 text-gray-600 bg-gray-50">
                                                    {{ Str::limit($kategoriNama, 20) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 whitespace-nowrap">
                                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full border"
                                                      style="background:{{ $hasilConfig['bg'] }};color:{{ $hasilConfig['color'] }};border-color:{{ $hasilConfig['border'] }};">
                                                    {{ $hasilConfig['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                                <span class="text-gray-300 text-base font-bold">›</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-16 text-center">
                                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                                    <div class="w-16 h-16 rounded-full bg-gray-50 border border-gray-100 flex items-center justify-center">
                                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-500 text-sm">Belum ada riwayat tugas</p>
                                                        <p class="text-xs text-gray-400 mt-1">Tugas yang Anda selesaikan akan muncul di sini.</p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination 10 Data --}}
                    @if(method_exists($riwayats, 'links'))
                    <div class="px-5 py-4 border-t border-gray-100 w-full shrink-0">
                        {{ $riwayats->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </main>

        {{-- ── Overlay Drawer ── --}}
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="close()" class="fixed inset-0 z-[100] bg-black/40 backdrop-blur-sm" x-cloak>
        </div>

        {{-- ── Detail Drawer ── --}}
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed right-0 top-0 h-screen bg-white z-[101] flex flex-col w-full sm:w-[450px] shadow-2xl" @click.stop x-cloak>

            {{-- Drawer Header --}}
            <div class="flex items-center justify-between p-4 sm:p-5 border-b border-gray-100 bg-white/95 backdrop-blur shrink-0 z-10 sticky top-0">
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-base font-bold text-gray-900">Detail Riwayat Tugas</p>
                        <template x-if="selected?.is_utama">
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-blue-50 border border-blue-100 text-[#01458E] uppercase tracking-wide">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Teknisi Utama
                            </span>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500" x-text="(selected?.waktu_tgl ?? '') + ' · ' + (selected?.waktu_jam ?? '')"></p>
                </div>
                <button @click="close()" class="p-2 rounded-xl bg-gray-50 text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Drawer Body --}}
            <div class="flex-1 overflow-y-auto drawer-scroll p-4 sm:p-5 space-y-6">

                {{-- Informasi Tiket --}}
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Informasi Tiket</h4>

                    <div class="space-y-2.5">
                        <div class="flex justify-between items-start gap-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">ID Tiket</span>
                            <span class="text-sm font-bold text-[#01458E] font-mono text-right" x-text="'#' + (selected?.id ?? '')"></span>
                        </div>

                        {{-- Rekomendasi Penanganan --}}
                        {{-- <div class="flex justify-between items-start gap-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Rekomendasi</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider text-right border"
                                :style="rekomendasiBadge(selected?.rekomendasi_penanganan)">
                                <span x-text="rekomendasiLabel(selected?.rekomendasi_penanganan)"></span>
                            </span>
                        </div> --}}

                        {{-- Status Perbaikan --}}
                        <div class="flex justify-between items-start gap-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Status Perbaikan</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold border"
                                :style="`background-color: ${selected?.hasil_bg}; color: ${selected?.hasil_color}; border-color: ${selected?.hasil_color}40;`">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" x-show="selected?.status_akhir === 'selesai'"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" x-show="selected?.status_akhir === 'rusak_berat'"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" x-show="selected?.status_akhir !== 'selesai' && selected?.status_akhir !== 'rusak_berat'"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                <span x-text="selected?.hasil_label"></span>
                            </span>
                        </div>

                        <div class="flex justify-between items-start gap-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">OPD</span>
                            <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="selected?.opd_nama ?? '—'"></span>
                        </div>

                        <div class="flex justify-between items-start gap-4">
                            <span class="text-xs text-gray-500 whitespace-nowrap mt-0.5">Ditugaskan Pada</span>
                            <span class="text-xs font-semibold text-gray-900 text-right leading-snug" x-text="(selected?.waktu_tgl ?? '—') + ', ' + (selected?.waktu_jam ?? '')"></span>
                        </div>
                    </div>
                </div>

                {{-- Detail Masalah --}}
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Detail Masalah</h4>
                    <p class="text-xs font-semibold text-gray-900 mb-3 leading-relaxed" x-text="selected?.subjek_masalah"></p>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 sm:p-4 mb-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1.5">Kronologi Masalah</div>
                        <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-wrap break-words" x-text="selected?.detail_masalah || '—'"></p>
                    </div>

                    <div class="bg-orange-50 border border-orange-200 rounded-xl p-3 sm:p-4">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-orange-700 mb-1.5">Spesifikasi Awal</div>
                        <p class="text-xs text-orange-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selected?.spesifikasi_perangkat || '—'"></p>
                    </div>
                </div>

                {{-- Hasil Penanganan (Tampil jika rusak berat) --}}
                <template x-if="selected?.status_akhir === 'rusak_berat'">
                    <div>
                        <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Hasil Penanganan</h4>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4 mb-3 border-l-4 border-l-red-500">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-red-800 mb-1.5">Analisis Kerusakan</div>
                            <p class="text-xs text-red-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selected?.analisis_kerusakan || '—'"></p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-3 sm:p-4 border-l-4 border-l-green-500">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-green-800 mb-1.5">Rekomendasi / Solusi</div>
                            <p class="text-xs text-green-900 leading-relaxed whitespace-pre-wrap break-words" x-text="selected?.rekomendasi || '—'"></p>
                        </div>
                    </div>
                </template>

                {{-- Foto Bukti --}}
                <div>
                    <h4 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-3 pb-2 border-b border-gray-100">Foto Bukti Lampiran</h4>
                    <template x-if="selected?.foto_bukti?.length > 0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5">
                            <template x-for="(foto, fi) in selected.foto_bukti" :key="fi">
                                <div class="rounded-xl overflow-hidden border border-gray-200 cursor-pointer relative aspect-square group"
                                     @click="activeFoto = foto; showFoto = true">
                                    <img :src="'/storage/' + foto" :alt="'Foto ' + (fi+1)"
                                         class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-white text-[10px] font-bold">Perbesar</span>
                                    </div>
                                    <span class="absolute bottom-1.5 left-1.5 text-[9px] bg-black/60 text-white px-1.5 py-0.5 rounded font-bold" x-text="fi+1"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!selected?.foto_bukti?.length">
                        <div class="h-20 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center bg-gray-50/50">
                            <span class="text-xs text-gray-400 font-medium">Tidak ada lampiran foto</span>
                        </div>
                    </template>
                </div>

                {{-- Ruang kosong untuk scroll aman --}}
                <div class="h-4"></div>
            </div>

            {{-- Drawer Footer --}}
            <div class="shrink-0 p-4 sm:p-5 border-t border-gray-100 bg-white">
                <div class="text-center p-3 sm:p-4 bg-gray-50 border border-gray-200 rounded-xl">
                    <p class="text-[13px] text-gray-800 font-bold mb-0.5">Status: Selesai</p>
                    <p class="text-[11px] text-gray-500 font-medium">Tiket penugasan ini sudah masuk ke dalam arsip riwayat Anda.</p>
                </div>
            </div>
        </div>

        {{-- Modal Perbesar Foto --}}
        <div x-show="showFoto"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showFoto = false; activeFoto = null"
             class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-4 sm:p-6" x-cloak>
            <img :src="activeFoto ? '/storage/' + activeFoto : ''" class="max-w-full max-h-full rounded-xl shadow-2xl object-contain" @click.stop>
            <button class="absolute top-4 right-4 sm:top-6 sm:right-6 text-white/50 hover:text-white p-2 focus:outline-none" @click="showFoto = false; activeFoto = null">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

    </div>

    <script>
    function riwayatPage() {
        return {
            selected: null,
            showFoto: false,
            activeFoto: null,

            openDetail(row) {
                this.selected = row;
            },
            close() {
                this.selected = null;
                this.showFoto = false;
            },
            rekomendasiLabel(p) {
                return { eskalasi: '⚠ ESKALASI', admin: '✓ ADMIN' }[p] ?? '—';
            },
            rekomendasiBadge(p) {
                const map = {
                    eskalasi: 'background:#fef2f2;color:#dc2626;border-color:#fecaca;',
                    admin:    'background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;',
                };
                return map[p] ?? 'background:#f3f4f6;color:#6b7280;border-color:#e5e7eb;';
            }
        };
    }
    </script>
</body>
</html>
