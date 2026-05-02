<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Antrean Tugas — Tim Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    @include('layouts.sidebarTimTeknis')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col" x-data="antreanPage()" x-cloak>

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Antrean Tugas</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket yang ditugaskan kepada Anda untuk perbaikan teknis</p>
            </div>
            <div class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-amber-700 bg-amber-50">
                <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                {{ $tikets->count() }} tiket aktif
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten Utama ── --}}
            <div class="flex-1 flex flex-col overflow-hidden">

                {{-- Filter --}}
                <div class="px-6 pt-5 pb-2">
                <form method="GET" action="{{ route('tim_teknis.antrean') }}" id="filterFormAntrean"
                      class="bg-white rounded-2xl border border-gray-100 px-5 py-4 mb-5">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                    <div class="flex flex-wrap gap-2 items-center">
                        <div class="flex-1 min-w-0 relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Cari tiket..."
                                   oninput="clearTimeout(window._stAntrean); window._stAntrean = setTimeout(() => document.getElementById('filterFormAntrean').submit(), 500)"
                                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                        </div>
                        <a href="{{ route('tim_teknis.antrean', request()->only('search')) }}"
                           class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset
                        </a>
                    </div>
                </form>
                </div>

                {{-- Flash --}}
                @if(session('success'))
                <div class="mx-6 mt-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('error') }}
                </div>
                @endif

                {{-- Tabel --}}
                <div class="flex-1 overflow-auto px-6 py-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                        {{-- Sub-tabs Peran --}}
                        <div class="flex flex-wrap items-center justify-between px-5 pt-4 pb-0 border-b border-gray-100 gap-x-2">
                            <div class="flex items-center gap-0 overflow-x-auto">
                                @php $activePeran = request('peran', ''); @endphp
                                <a href="{{ route('tim_teknis.antrean', request()->only('search')) }}"
                                   class="px-5 pb-4 text-sm transition-colors
                                          {{ $activePeran === ''
                                              ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                              : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                    Semua
                                    <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                        {{ $countAll }}
                                    </span>
                                </a>
                                <a href="{{ route('tim_teknis.antrean', [...request()->only('search'), 'peran' => 'teknisi_utama']) }}"
                                   class="px-5 pb-4 text-sm transition-colors
                                          {{ $activePeran === 'teknisi_utama'
                                              ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                              : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                    Teknisi Utama
                                    <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                        {{ $countUtama }}
                                    </span>
                                </a>
                                <a href="{{ route('tim_teknis.antrean', [...request()->only('search'), 'peran' => 'teknisi_pendamping']) }}"
                                   class="px-5 pb-4 text-sm transition-colors
                                          {{ $activePeran === 'teknisi_pendamping'
                                              ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                              : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                                    Pendamping
                                    <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">
                                        {{ $countPendamping }}
                                    </span>
                                </a>
                            </div>
                            <p class="text-sm font-bold text-gray-900 pb-4 hidden sm:block">
                                {{ $activePeran === 'teknisi_utama' ? 'Tiket sebagai Teknisi Utama' : ($activePeran === 'teknisi_pendamping' ? 'Tiket sebagai Pendamping' : 'Semua Tiket Aktif') }}
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Tiket</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subjek Masalah</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">OPD</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Peran</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pesan</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ditugaskan</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($tikets as $tiket)
                                @php
                                    $isUtama = $tiket->my_peran === 'teknisi_utama';
                                    $kategoriNama = $tiket->kategori?->nama_kategori
                                        ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
                                    $tiketJson = json_encode([
                                        'id'                    => $tiket->id,
                                        'subjek_masalah'        => $tiket->subjek_masalah,
                                        'detail_masalah'        => $tiket->detail_masalah,
                                        'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                        'kategori_nama'         => $kategoriNama,
                                        'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                        'lokasi'                => $tiket->lokasi ?? '—',
                                        'foto_bukti'            => $tiket->foto_bukti,
                                        'rekomendasi_penanganan' => $tiket->rekomendasi_penanganan,
                                        'kb_judul'              => $tiket->kb?->nama_artikel_sop ?? null,
                                        'sop_judul'             => $tiket->sopInternal?->nama_artikel_sop ?? null,
                                        'sop_konten'            => $tiket->sopInternal?->isi_konten ?? null,
                                        'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                        'created_at_jam'        => $tiket->created_at?->format('H:i') . ' WIB',
                                        'unread_count'              => $tiket->unread_count,
                                        'chat_url'                  => route('tim_teknis.tiket.chat', $tiket->id),
                                        'is_utama'                  => $isUtama,
                                        'peran_label'               => $isUtama ? 'Teknisi Utama' : 'Pendamping',
                                        'is_dibuka_kembali'         => $tiket->latestStatus?->status_tiket === 'dibuka_kembali',
                                        'pernah_dibuka_kembali'     => $tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->isNotEmpty(),
                                        'pernah_dibuka_kembali_opd' => $tiket->pernah_dibuka_kembali_opd ?? false,
                                        'alasan_buka_kembali'       => $tiket->alasan_buka_kembali,
                                        'file_bukti_buka_kembali'   => $tiket->file_bukti_buka_kembali,
                                        'catatan_admin'             => $tiket->catatan_admin,
                                    ]);
                                @endphp
                                <tr class="hover:bg-gray-50 cursor-pointer transition-colors"
                                    @click="openDetail({{ $tiketJson }})">
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-xs font-semibold text-[#01458E] bg-blue-50 px-2 py-0.5 rounded">
                                            #{{ Str::upper(substr($tiket->id, -8)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 max-w-xs">
                                        <p class="font-semibold text-gray-800 truncate">{{ $tiket->subjek_masalah }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($tiket->detail_masalah, 50) }}</p>
                                        @if($tiket->pernah_dibuka_kembali_opd ?? false)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-1.5 py-0.5 rounded mt-1" style="background:#FEE2E2;color:#991B1B;">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Dibuka Kembali
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 font-medium text-xs">
                                        {{ Str::limit($tiket->opd?->nama_opd ?? '—', 22) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-600 bg-gray-50 whitespace-nowrap">
                                            {{ Str::limit($kategoriNama, 20) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($isUtama)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full"
                                              style="background:#EEF3F9;color:#01458E;">
                                            <svg width="9" height="9" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Utama
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-full"
                                              style="background:#F3F4F6;color:#6B7280;">
                                            <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                            </svg>
                                            Pendamping
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($tiket->unread_count > 0)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-red-500 px-2.5 py-1 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-white/70 animate-pulse"></span>
                                            {{ $tiket->unread_count }} belum dibaca
                                        </span>
                                        @else
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-400 font-medium">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                            Terbaca
                                        </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 text-xs">
                                        <p class="font-medium">{{ $tiket->created_at?->translatedFormat('d M Y') }}</p>
                                        <p class="text-gray-400">{{ $tiket->created_at?->format('H:i') }} WIB</p>
                                    </td>
                                    <td class="px-5 py-4" @click.stop>
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($isUtama)
                                            {{-- Chat — hanya teknisi utama --}}
                                            <a href="{{ route('tim_teknis.tiket.chat', $tiket->id) }}"
                                               title="Chat dengan OPD"
                                               class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                               style="background:#EEF3F9;color:#01458E;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                                                </svg>
                                            </a>
                                            {{-- Selesai — hanya teknisi utama --}}
                                            <button type="button"
                                                    @click.stop="setTiket({{ $tiketJson }}); showModal = 'konfirmasi'"
                                                    title="Tandai Selesai / Gagal"
                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                                    style="background:#D1FAE5;color:#059669;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                            {{-- Kembalikan — hanya teknisi utama, dan hanya jika belum pernah dibuka kembali --}}
                                            @if(!$tiket->statusTiket->where('status_tiket', 'dibuka_kembali')->isNotEmpty())
                                            <button type="button"
                                                    @click.stop="setTiket({{ $tiketJson }}); showModal = 'kembalikan'"
                                                    title="Kembalikan ke Admin"
                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                                    style="background:#FEF3C7;color:#D97706;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                                                </svg>
                                            </button>
                                            @endif
                                            @else
                                            {{-- Pendamping: lihat chat (hanya lihat) + lihat detail --}}
                                            <a href="{{ route('tim_teknis.tiket.chat', $tiket->id) }}"
                                               @click.stop
                                               title="Lihat Chat (Hanya Lihat)"
                                               class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                               style="background:#F3F4F6;color:#6B7280;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                                                </svg>
                                            </a>
                                            <button type="button"
                                                    @click.stop="openDetail({{ $tiketJson }})"
                                                    title="Lihat Detail"
                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                                    style="background:#F3F4F6;color:#6B7280;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 016 0z"/>
                                                </svg>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-400">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                                                </svg>
                                            </div>
                                            <p class="font-semibold text-gray-500">Tidak ada tiket dalam antrean</p>
                                            <p class="text-sm">Belum ada tiket yang ditugaskan kepada Anda.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Overlay Drawer ── --}}
            <div x-show="selectedTiket && showDrawer"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeDetail()"
                 class="fixed inset-0 z-[100]"
                 style="background:rgba(0,0,0,.32);">
            </div>

            {{-- ── Detail Drawer ── --}}
            <div x-show="selectedTiket && showDrawer"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed right-0 top-0 h-screen bg-white z-[101] flex flex-col overflow-hidden w-full sm:w-[440px]"
                 style="box-shadow:-4px 0 24px rgba(0,0,0,.12);"
                 @click.stop>

                {{-- Drawer Header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #f3f4f6;background:#fff;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                            <p style="font-size:14px;font-weight:700;color:#111827;">Detail Tiket</p>
                            {{-- Badge peran --}}
                            <template x-if="selectedTiket?.is_utama">
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:#EEF3F9;color:#01458E;">
                                    <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Teknisi Utama
                                </span>
                            </template>
                            <template x-if="!selectedTiket?.is_utama">
                                <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:600;padding:2px 8px;border-radius:999px;background:#F3F4F6;color:#6B7280;">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                                    Pendamping
                                </span>
                            </template>
                        </div>
                        <p style="font-size:11px;color:#9ca3af;" x-text="(selectedTiket?.created_at_tgl ?? '') + ' · ' + (selectedTiket?.created_at_jam ?? '')"></p>
                    </div>
                    <button @click="closeDetail()"
                            style="width:30px;height:30px;border-radius:8px;background:#f3f4f6;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Info pendamping notice --}}
                <template x-if="!selectedTiket?.is_utama">
                    <div style="margin:12px 20px 0;padding:10px 12px;background:#FEF9C3;border:1px solid #FDE047;border-radius:10px;display:flex;align-items:flex-start;gap:8px;">
                        <svg width="14" height="14" style="color:#CA8A04;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p style="font-size:11px;color:#92400E;line-height:1.5;">Anda berperan sebagai <strong>teknisi pendamping</strong> pada tiket ini. Anda dapat melihat chat sebagai referensi, namun penyelesaian dan pengembalian tiket hanya dapat dilakukan oleh teknisi utama.</p>
                    </div>
                </template>

                {{-- Drawer Body --}}
                <div class="flex-1 overflow-y-auto" style="padding:20px;scrollbar-width:thin;">

                    {{-- Info Tiket --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Informasi Tiket</div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">ID Tiket</span>
                            <span style="font-size:12px;font-weight:700;color:#01458E;font-family:'Courier New',monospace;" x-text="'#' + selectedTiket?.id"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Peran Saya</span>
                            <span style="font-size:12px;font-weight:700;" :style="selectedTiket?.is_utama ? 'color:#01458E' : 'color:#6B7280'" x-text="selectedTiket?.peran_label ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Rekomendasi</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold"
                                  :style="rekomendasiBadge(selectedTiket?.rekomendasi_penanganan)">
                                <svg x-show="selectedTiket?.rekomendasi_penanganan === 'eskalasi'" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                <svg x-show="selectedTiket?.rekomendasi_penanganan === 'admin'" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                <span x-text="rekomendasiLabel(selectedTiket?.rekomendasi_penanganan)"></span>
                            </span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">OPD</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;" x-text="selectedTiket?.opd_nama ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Kategori</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;" x-text="selectedTiket?.kategori_nama ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Artikel KB</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;" x-text="selectedTiket?.kb_judul ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Lokasi</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;" x-text="selectedTiket?.lokasi || '—'"></span>
                        </div>
                    </div>

                    {{-- Section: SOP Internal --}}
                    <template x-if="selectedTiket?.sop_judul">
                        <div style="margin-bottom:20px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;">
                                <span>SOP Internal Penanganan</span>
                                <button @click="openSopPreview()"
                                        style="font-size:11px;font-weight:600;padding:4px 10px;border-radius:6px;background:#F97316;color:#fff;border:none;cursor:pointer;display:flex;gap:4px;align-items:center;transition:opacity .2s;"
                                        onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Pratinjau
                                </button>
                            </div>
                            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px;display:flex;gap:10px;align-items:flex-start;">
                                <div style="flex-shrink:0;width:32px;height:32px;border-radius:8px;background:#f97316;display:flex;align-items:center;justify-content:center;">
                                    <svg width="16" height="16" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#f97316;margin-bottom:3px;">Panduan SOP</p>
                                    <p style="font-size:12px;font-weight:600;color:#7c2d12;line-height:1.5;word-break:break-word;" x-text="selectedTiket?.sop_judul"></p>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Detail Masalah --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Detail Masalah</div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Subjek</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;line-height:1.5;" x-text="selectedTiket?.subjek_masalah"></span>
                        </div>
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:6px;opacity:.7;color:#475569;">Kronologi Masalah</div>
                            <p style="font-size:12px;color:#334155;line-height:1.65;word-break:break-word;white-space:pre-wrap;" x-text="selectedTiket?.detail_masalah || '—'"></p>
                        </div>
                        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:6px;opacity:.7;color:#9a3412;">Spesifikasi Perangkat</div>
                            <p style="font-size:12px;color:#7c2d12;line-height:1.65;word-break:break-word;white-space:pre-wrap;" x-text="selectedTiket?.spesifikasi_perangkat || '—'"></p>
                        </div>
                    </div>

                    {{-- Foto Bukti --}}
                    <div style="margin-bottom:8px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Foto Bukti</div>
                        <template x-if="selectedTiket?.foto_bukti?.length > 0">
                            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">
                                <template x-for="(foto, fi) in selectedTiket.foto_bukti" :key="fi">
                                    <div style="border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;cursor:pointer;position:relative;aspect-ratio:1;"
                                         @click="activeFoto = foto; showFoto = true">
                                        <img :src="'/storage/' + foto" :alt="'Foto ' + (fi+1)"
                                             style="width:100%;height:100%;object-fit:cover;display:block;">
                                        <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;"
                                             onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                                            <span style="color:#fff;font-size:11px;font-weight:600;">Perbesar</span>
                                        </div>
                                        <span style="position:absolute;bottom:4px;left:6px;font-size:9px;background:rgba(0,0,0,.5);color:#fff;padding:1px 5px;border-radius:4px;font-weight:700;" x-text="fi+1"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedTiket?.foto_bukti?.length">
                            <div style="height:80px;border-radius:10px;border:1.5px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;background:#f9fafb;">
                                <span style="font-size:12px;color:#9ca3af;font-weight:500;">Tidak ada lampiran foto</span>
                            </div>
                        </template>
                    </div>

                    {{-- Catatan dari Admin Helpdesk --}}
                    <template x-if="selectedTiket?.catatan_admin">
                        <div style="margin-bottom:16px;border-left:3px solid #01458E;padding:12px 12px 12px 14px;background:#EEF3F9;border-radius:0 10px 10px 0;">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #bfdbfe;">
                                <svg width="13" height="13" style="color:#01458E;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#01458E;">Catatan dari Admin Helpdesk</span>
                            </div>
                            <p style="font-size:12px;color:#1e3a5f;line-height:1.65;word-break:break-word;white-space:pre-wrap;" x-text="selectedTiket?.catatan_admin"></p>
                        </div>
                    </template>

                    {{-- Laporan OPD Membuka Kembali Tiket --}}
                    <template x-if="selectedTiket?.pernah_dibuka_kembali_opd">
                        <div style="margin-bottom:16px;border-left:3px solid #EF4444;padding:12px 12px 12px 14px;background:#FEF2F2;border-radius:0 10px 10px 0;">
                            <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #fecaca;">
                                <svg width="13" height="13" style="color:#DC2626;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#DC2626;">Laporan OPD — Buka Kembali Tiket</span>
                            </div>
                            <div style="margin-bottom:8px;">
                                <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:6px;color:#7f1d1d;">Catatan / Alasan OPD</div>
                                <p style="font-size:12px;color:#7f1d1d;line-height:1.65;word-break:break-word;white-space:pre-wrap;"
                                   x-text="selectedTiket?.alasan_buka_kembali || '—'"></p>
                            </div>
                            <template x-if="selectedTiket?.file_bukti_buka_kembali">
                                <div>
                                    <p style="font-size:10px;font-weight:700;color:#DC2626;margin-bottom:6px;text-transform:uppercase;">Bukti Foto dari OPD</p>
                                    <img :src="'/storage/' + selectedTiket.file_bukti_buka_kembali"
                                         alt="Bukti Buka Kembali"
                                         style="width:100%;max-height:160px;object-fit:cover;border-radius:8px;border:1px solid #fca5a5;cursor:pointer;"
                                         @click="activeFoto = selectedTiket.file_bukti_buka_kembali; showFoto = true">
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Drawer Footer: Tindakan --}}
                <div style="flex-shrink:0;padding:16px 20px;border-top:1px solid #f3f4f6;background:#fff;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;">Aksi Tindakan</div>

                    {{-- Teknisi Utama: semua aksi --}}
                    <template x-if="selectedTiket?.is_utama">
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <a :href="selectedTiket?.chat_url"
                               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:10px;font-size:13px;font-weight:700;background:#EEF3F9;color:#01458E;text-decoration:none;border:1px solid #dbeafe;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                                </svg>
                                Chat dengan OPD
                            </a>
                            <div style="display:flex;gap:8px;">
                                <button @click="showModal = 'konfirmasi'"
                                        style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:700;background:#D1FAE5;border:1px solid #6ee7b7;color:#065f46;cursor:pointer;">
                                    ✓ Selesai / Gagal
                                </button>
                                <template x-if="!selectedTiket?.pernah_dibuka_kembali">
                                    <button @click="showModal = 'kembalikan'"
                                            style="flex:1;padding:9px;border-radius:10px;font-size:12px;font-weight:600;background:#FEF3C7;border:1px solid #fcd34d;color:#92400e;cursor:pointer;">
                                        ↩ Kembalikan
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Teknisi Pendamping: lihat chat + info hanya lihat --}}
                    <template x-if="!selectedTiket?.is_utama">
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            <a :href="selectedTiket?.chat_url"
                               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:10px;border-radius:10px;font-size:13px;font-weight:700;background:#F3F4F6;color:#374151;text-decoration:none;border:1px solid #E5E7EB;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                                </svg>
                                Lihat Chat (Hanya Lihat)
                            </a>
                            <div style="text-align:center;padding:12px;background:#F9FAFB;border-radius:10px;border:1.5px dashed #E5E7EB;">
                                <svg width="20" height="20" style="color:#9CA3AF;margin:0 auto 4px;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p style="font-size:12px;color:#6B7280;font-weight:600;">Mode Hanya Lihat</p>
                                <p style="font-size:11px;color:#9CA3AF;margin-top:2px;">Penyelesaian &amp; pengembalian tiket dikelola oleh teknisi utama</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ── Modals ── --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = ''"
                 class="fixed inset-0 bg-black/40 z-[102]"></div>

            {{-- Modal: Konfirmasi Selesai/Gagal --}}
            <div x-show="showModal === 'konfirmasi'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 text-center relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Tiket Selesai?</h3>
                    <p class="text-sm font-semibold mb-4" style="color:#01458E;" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-6">Pilih hasil perbaikan tiket ini.</p>
                    <div class="flex gap-3">
                        <button @click="showModal = 'gagal'"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                style="background:#DC2626;">
                            Tiket Gagal
                        </button>
                        <button @click="showModal = 'selesai'"
                                class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                style="background:#059669;">
                            Tiket Selesai
                        </button>
                    </div>
                    <button @click="showModal = ''"
                            class="mt-3 w-full py-2 rounded-xl text-sm font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 transition-all">
                        Batal
                    </button>
                </div>
            </div>

            {{-- Modal: Tiket Berhasil (Selesai) --}}
            <div x-show="showModal === 'selesai'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 relative" @click.stop>
                    <button @click="showModal = 'konfirmasi'" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#D1FAE5;">
                        <svg class="w-7 h-7" style="color:#059669;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Tiket Berhasil Diperbaiki</h3>
                    <p class="text-sm font-semibold mb-1 text-center" style="color:#01458E;" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-4 text-center">Tiket telah sukses dan berhasil diperbaiki</p>
                    <form x-ref="formSelesai" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formSelesai, '/tim-teknis/tiket/' + selectedTiket.id + '/selesai')">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Catatan Tambahan untuk OPD</label>
                            <textarea name="catatan" rows="3"
                                      placeholder="Masukkan catatan (opsional)..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = 'konfirmasi'"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                    style="background:#059669;">
                                Selesai
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Tiket Gagal (Rusak Berat) --}}
            <div x-show="showModal === 'gagal'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 relative" @click.stop>
                    <button @click="showModal = 'konfirmasi'" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEE2E2;">
                        <svg class="w-7 h-7" style="color:#DC2626;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Tiket Gagal Diperbaiki</h3>
                    <p class="text-sm font-semibold mb-1 text-center" style="color:#01458E;" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-4 text-center">Tiket tidak dapat diperbaiki karena aset rusak total</p>
                    <form x-ref="formGagal" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formGagal, '/tim-teknis/tiket/' + selectedTiket.id + '/gagal')">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Analisis Kerusakan <span class="text-red-500">*</span></label>
                            <textarea name="analisis_kerusakan" rows="2" required
                                      placeholder="Masukkan catatan..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Spesifikasi Perangkat Rusak</label>
                            <textarea name="spesifikasi_perangkat_rusak" rows="2"
                                      placeholder="Masukkan catatan..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Rekomendasi Tindak Lanjut <span class="text-red-500">*</span></label>
                            <textarea name="rekomendasi" rows="2" required
                                      placeholder="Masukkan catatan..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = 'konfirmasi'"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                    style="background:#DC2626;">
                                Kirim Rekomendasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Kembalikan ke Admin --}}
            <div x-show="showModal === 'kembalikan'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4" style="background:#FEF3C7;">
                        <svg class="w-7 h-7" style="color:#D97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Kembalikan Tiket</h3>
                    <p class="text-sm font-semibold mb-1 text-center" style="color:#01458E;" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-4 text-center">Tiket akan dikembalikan ke Admin Helpdesk</p>
                    <form x-ref="formKembalikan" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formKembalikan, '/tim-teknis/tiket/' + selectedTiket.id + '/kembalikan')">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Alasan pengembalian tiket <span class="text-red-500">*</span></label>
                            <textarea name="alasan_kembalikan" rows="3" required
                                      placeholder="Masukkan alasan..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = ''"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                                    style="background:#D97706;">
                                Kembalikan Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Perbesar Foto --}}
            <div x-show="showFoto"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 @click="showFoto = false; activeFoto = null"
                 class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-6">
                <img :src="activeFoto ? '/storage/' + activeFoto : ''"
                     class="max-w-full max-h-full rounded-xl shadow-2xl object-contain" @click.stop>
            </div>

            {{-- Modal: Preview SOP --}}
            <style>
                #sopPreviewContent { font-size: 0.875rem; line-height: 1.8; color: #111827; }
                #sopPreviewContent h1, #sopPreviewContent h2, #sopPreviewContent h3, #sopPreviewContent h4, #sopPreviewContent h5, #sopPreviewContent h6 { font-weight: 700; margin: 1rem 0 0.5rem; color: #111827; }
                #sopPreviewContent h1 { font-size: 1.75rem; } #sopPreviewContent h2 { font-size: 1.5rem; } #sopPreviewContent h3 { font-size: 1.25rem; }
                #sopPreviewContent p { margin-bottom: 0.75rem; }
                #sopPreviewContent strong { font-weight: 700; } #sopPreviewContent em { font-style: italic; } #sopPreviewContent u { text-decoration: underline; } #sopPreviewContent s { text-decoration: line-through; }
                #sopPreviewContent a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; } #sopPreviewContent a:hover { color: #003a70; }
                #sopPreviewContent ul, #sopPreviewContent ol { padding-left: 1.5rem; margin-bottom: 0.75rem; } #sopPreviewContent li { margin-bottom: 0.25rem; }
                #sopPreviewContent blockquote { border-left: 4px solid #01458E; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
                #sopPreviewContent code { background: #f3f4f6; color: #01458E; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem; }
                #sopPreviewContent pre { background: #1f2937; color: #f3f4f6; padding: 0.75rem; border-radius: 0.375rem; overflow-x: auto; margin: 0.75rem 0; font-size: 0.75rem; }
                #sopPreviewContent pre code { background: transparent; color: inherit; padding: 0; }
                #sopPreviewContent img, #sopPreviewContent video { max-width: 100%; height: auto; border-radius: 0.375rem; margin: 0.75rem 0; }
                #sopPreviewContent table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; }
                #sopPreviewContent th, #sopPreviewContent td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; font-size: 0.75rem; }
                #sopPreviewContent ul { list-style-type: disc; } #sopPreviewContent ol { list-style-type: decimal; }
                #sopPreviewContent ul ul { list-style-type: circle; } #sopPreviewContent ul ul ul { list-style-type: square; }
            </style>
            {{-- Modal: Preview SOP Overlay --}}
            <div x-show="sopPreviewOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sopPreviewOpen = false"
                 class="fixed inset-0 bg-black/45 backdrop-filter backdrop-blur-sm z-[110]"
                 x-cloak></div>

            {{-- Modal: Preview SOP Content --}}
            <div x-show="sopPreviewOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 flex items-center justify-center z-[111] pointer-events-none"
                 x-cloak>
                <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-4 h-[90vh] overflow-hidden flex flex-col pointer-events-auto"
                     @click.stop>

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-5 lg:px-8 py-4 border-b border-gray-200 shrink-0">
                        <h2 class="text-lg font-bold text-gray-900">Pratinjau SOP</h2>
                        <button @click="sopPreviewOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Content --}}
                    <div class="flex-1 overflow-y-auto px-4 lg:px-8 py-5 lg:py-6">

                        {{-- Badge + Title --}}
                        <div class="mb-6">
                            <div class="flex items-center gap-1 text-xs text-red-500 mb-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Internal</span>
                            </div>
                            <h1 class="text-3xl font-bold text-gray-900" x-text="sopPreviewTitle || 'SOP Internal'"></h1>
                        </div>

                        {{-- Meta --}}
                        <div class="mb-6 pb-6 border-b border-gray-200 text-sm">
                            <div class="flex gap-6">
                                <div>
                                    <p class="text-gray-500">Visibilitas</p>
                                    <div class="flex items-center gap-1 font-semibold text-gray-900">
                                        <svg class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Internal</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-gray-500">Status</p>
                                    <div class="flex items-center gap-1 font-semibold text-green-600">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Published</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SOP Content --}}
                        <div id="sopPreviewContent" class="prose prose-sm max-w-none mb-6"></div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
    function antreanPage() {
        return {
            selectedTiket: null,
            showDrawer: false,
            showModal: '',
            showFoto: false,
            activeFoto: null,
            sopPreviewOpen: false,
            sopPreviewContent: '',
            sopPreviewTitle: '',

            openDetail(tiket) {
                this.selectedTiket = tiket;
                this.showDrawer = true;
            },
            setTiket(tiket) {
                this.selectedTiket = tiket;
                this.showDrawer = false;
            },
            closeDetail() {
                this.selectedTiket = null;
                this.showDrawer = false;
                this.showModal = '';
            },
            rekomendasiLabel(p) {
                return { eskalasi: 'Perlu Dieskalasi ke Tim Teknis', admin: 'Dapat Ditangani Admin' }[p] ?? '—';
            },
            rekomendasiBadge(p) {
                const map = {
                    eskalasi: 'background:#FEE2E2;color:#DC2626;',
                    admin:    'background:#DBEAFE;color:#1D4ED8;',
                };
                return map[p] ?? map.admin;
            },
            openSopPreview() {
                if (this.selectedTiket?.sop_judul) {
                    this.sopPreviewTitle = this.selectedTiket.sop_judul;
                    this.sopPreviewContent = this.selectedTiket.sop_konten || '';
                    this.sopPreviewOpen = true;
                    setTimeout(() => {
                        document.getElementById('sopPreviewContent').innerHTML = this.sopPreviewContent;
                    }, 50);
                }
            },
            submitForm(form, url) {
                form.action = url;
                form.submit();
            },
        };
    }
    </script>

</body>
</html>
