<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengaduan Saya — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fu  { animation: fadeUp 0.4s cubic-bezier(0.16,1,0.3,1) forwards; opacity: 0; }
        .fu1 { animation-delay: 0.04s; }
        .fu2 { animation-delay: 0.10s; }
        .fu3 { animation-delay: 0.16s; }

        .card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #EAECF0;
            box-shadow: 0 2px 6px rgba(16,24,40,0.03);
        }

        /* Status badge outlined style */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 600;
            border: 1.5px solid;
            white-space: nowrap;
        }
        .badge-verifikasi  { color: #D97706; border-color: #FCD34D; background: #FFFBEB; }
        .badge-panduan     { color: #2563EB; border-color: #93C5FD; background: #EFF6FF; }
        .badge-perbaikan   { color: #EA580C; border-color: #FCA5A5; background: #FFF7ED; }
        .badge-selesai     { color: #059669; border-color: #6EE7B7; background: #ECFDF5; }
        .badge-rusak       { color: #DC2626; border-color: #FCA5A5; background: #FEF2F2; }
        .badge-revisi      { color: #B45309; border-color: #FDE68A; background: #FFFBEB; }
        .badge-default     { color: #6B7280; border-color: #D1D5DB; background: #F9FAFB; }

        /* Action buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .btn-chat   { background: #01458E; color: #fff; }
        .btn-chat:hover   { background: #013a78; }
        .btn-detail { background: #D97706; color: #fff; }
        .btn-detail:hover { background: #B45309; }
        .btn-konfirm{ background: #059669; color: #fff; }
        .btn-konfirm:hover{ background: #047857; }

        /* Search input */
        .search-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 9999px;
            padding: 9px 42px 9px 18px;
            font-size: 13px;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .search-input:focus {
            border-color: #01458E;
            box-shadow: 0 0 0 3px rgba(1,69,142,.08);
        }
        .search-input::placeholder { color: #9CA3AF; }

        /* Table scrollbar */
        .table-wrap { overflow-x: auto; }
        .table-wrap::-webkit-scrollbar { height: 5px; }
        .table-wrap::-webkit-scrollbar-track { background: #f8fafc; }
        .table-wrap::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="flex-1 max-w-screen-xl w-full mx-auto px-4 md:px-8 py-6 md:py-8">

    {{-- Flash success --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fu fu1 flex items-start gap-3 mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
        <svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
        <button @click="show = false" class="ml-auto text-emerald-400 hover:text-emerald-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @endif

    {{-- Header --}}
    <div class="fu fu1 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pengaduan Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Daftar seluruh tiket pengaduan yang telah Anda buat</p>
        </div>
        <a href="{{ route('opd.diagnosis.index') }}"
           class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2.5 rounded-xl text-white text-sm font-bold
                  transition hover:-translate-y-0.5 hover:shadow-lg active:scale-95"
           style="background:#01458E;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pengaduan Baru
        </a>
    </div>

    {{-- Filter & Search bar --}}
    <div class="fu fu2 card p-5 mb-5 relative">
        <form method="GET" action="{{ route('opd.tiket.index') }}"
              class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">

            <input type="hidden" name="status" value="{{ request('status') }}">

            {{-- Filter dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                               transition hover:opacity-90 active:scale-95 shrink-0"
                        style="background:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    <svg class="w-3.5 h-3.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     style="display: none;"
                     class="absolute left-0 top-full mt-2 w-52 bg-white border border-gray-100 rounded-xl shadow-lg z-50 py-2">
                    @php
                    $filterOptions = [
                        ''                  => 'Semua Status',
                        'verifikasi_admin'  => 'Verifikasi Admin',
                        'perlu_revisi'      => 'Perlu Revisi',
                        'panduan_remote'    => 'Panduan Remote',
                        'perbaikan_teknis'  => 'Perbaikan Teknis',
                        'rusak_berat'       => 'Rusak Berat',
                        'selesai'           => 'Selesai',
                    ];
                    @endphp
                    @foreach($filterOptions as $val => $label)
                    <a href="{{ route('opd.tiket.index', array_merge(request()->except('status','page'), $val ? ['status' => $val] : [])) }}"
                       class="flex items-center gap-2.5 px-4 py-2.5 text-sm transition-colors
                              {{ request('status') == $val ? 'bg-blue-50 text-[#01458E] font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        @if(request('status') == $val)
                        <svg class="w-3.5 h-3.5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        @else
                        <span class="w-3.5 h-3.5"></span>
                        @endif
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Search --}}
            <div class="flex-1 relative">
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari ID Tiket atau Judul Masalah..."
                       class="search-input pr-10">
                <button type="submit"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#01458E] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </button>
            </div>

            {{-- Clear filter --}}
            @if(request()->hasAny(['status','search']))
            <a href="{{ route('opd.tiket.index') }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-500
                      bg-gray-100 hover:bg-gray-200 transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ── Mobile card list (tampil di bawah md) ── --}}
    <div class="fu fu3 card overflow-hidden md:hidden">

        @forelse($tikets as $tiket)
            @php
            $status = $tiket->latestStatus?->status_tiket ?? '';
            $statusConfig = match($status) {
                'verifikasi_admin'  => ['label' => 'Verifikasi Admin',  'cls' => 'badge-verifikasi'],
                'panduan_remote'    => ['label' => 'Panduan Remote',    'cls' => 'badge-panduan'],
                'perbaikan_teknis'  => ['label' => 'Perbaikan Teknis',  'cls' => 'badge-perbaikan'],
                'selesai',
                'tiket_ditutup'     => ['label' => 'Selesai',           'cls' => 'badge-selesai'],
                'rusak_berat'       => ['label' => 'Rusak Berat',       'cls' => 'badge-rusak'],
                'perlu_revisi'      => ['label' => 'Perlu Revisi',      'cls' => 'badge-revisi'],
                'dibuka_kembali'    => ['label' => 'Dibuka Kembali',    'cls' => 'badge-perbaikan'],
                default             => ['label' => 'Menunggu',          'cls' => 'badge-default'],
            };
            $showChat    = in_array($status, ['panduan_remote', 'perbaikan_teknis']);
            $showKonfirm = in_array($status, ['selesai', 'tiket_ditutup']) && $tiket->penilaian === null;
            @endphp

            <div class="px-4 py-4 border-b border-gray-100 last:border-0">
                {{-- Baris atas: ID + Badge --}}
                <div class="flex items-start justify-between gap-2 mb-2">
                    <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200">
                        #TKT-{{ substr(explode('-', $tiket->id)[1], 0, 5) }}
                    </span>
                    <span class="badge {{ $statusConfig['cls'] }}">{{ $statusConfig['label'] }}</span>
                </div>

                {{-- Subjek --}}
                <p class="text-sm font-semibold text-gray-900 mb-1 leading-snug">{{ $tiket->subjek_masalah ?? '-' }}</p>

                {{-- Meta: kategori + tanggal --}}
                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500 mb-3">
                    <span>{{ $tiket->kategori?->nama_kategori ?? '—' }}</span>
                    <span class="text-gray-300">•</span>
                    <span>{{ $tiket->created_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm') ?? '-' }}</span>
                </div>

                {{-- Tombol aksi --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if($showChat)
                    <a href="{{ route('opd.tiket.chat', $tiket->id) }}" class="btn-action btn-chat">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        Chat
                    </a>
                    @endif

                    @if($showKonfirm)
                    <button type="button" onclick="bukaModalRating('{{ $tiket->id }}')" class="btn-action btn-konfirm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Selesai
                    </button>
                    @endif

                    <a href="{{ route('opd.tiket.show', $tiket->id) }}" class="btn-action btn-detail">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                        Detail
                    </a>
                </div>
            </div>

        @empty
            <div class="px-6 py-16 flex flex-col items-center gap-4 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-blue-50">
                    <svg class="w-7 h-7 text-[#01458E] opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Belum ada pengaduan</p>
                    <p class="text-xs text-gray-500 mt-1">
                        @if(request()->hasAny(['status','search']))
                            Tidak ada tiket yang sesuai dengan filter Anda.
                        @else
                            Tiket pengaduan yang Anda buat akan muncul di sini.
                        @endif
                    </p>
                </div>
                @if(!request()->hasAny(['status','search']))
                <a href="{{ route('opd.diagnosis.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:shadow-lg"
                   style="background:#01458E;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Pengaduan Pertama
                </a>
                @endif
            </div>
        @endforelse

        {{-- Pagination mobile --}}
        @if($tikets->hasPages())
        <div class="px-4 py-4 border-t border-gray-100 flex flex-col items-center gap-3">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-semibold text-gray-700">{{ $tikets->firstItem() }}–{{ $tikets->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $tikets->total() }}</span> tiket
            </p>
            <div class="flex items-center gap-1">
                @if($tikets->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 bg-gray-50 cursor-not-allowed">&lsaquo; Prev</span>
                @else
                <a href="{{ $tikets->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">&lsaquo; Prev</a>
                @endif
                @foreach($tikets->getUrlRange(max(1, $tikets->currentPage()-2), min($tikets->lastPage(), $tikets->currentPage()+2)) as $page => $url)
                    @if($page == $tikets->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background:#01458E;">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">{{ $page }}</a>
                    @endif
                @endforeach
                @if($tikets->hasMorePages())
                <a href="{{ $tikets->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">Next &rsaquo;</a>
                @else
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 bg-gray-50 cursor-not-allowed">Next &rsaquo;</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── Table (tampil mulai md ke atas) ── --}}
    <div class="fu fu3 card overflow-hidden hidden md:block">
        <div class="table-wrap">
            <table class="w-full whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100">
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">ID Tiket</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Subjek Masalah</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                        <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                @forelse($tikets as $tiket)
                    @php
                    $status = $tiket->latestStatus?->status_tiket ?? '';

                    $statusConfig = match($status) {
                        'verifikasi_admin'  => ['label' => 'Verifikasi Admin',  'cls' => 'badge-verifikasi'],
                        'panduan_remote'    => ['label' => 'Panduan Remote',    'cls' => 'badge-panduan'],
                        'perbaikan_teknis'  => ['label' => 'Perbaikan Teknis',  'cls' => 'badge-perbaikan'],
                        'selesai',
                        'tiket_ditutup'     => ['label' => 'Selesai',           'cls' => 'badge-selesai'],
                        'rusak_berat'       => ['label' => 'Rusak Berat',       'cls' => 'badge-rusak'],
                        'perlu_revisi'      => ['label' => 'Perlu Revisi',      'cls' => 'badge-revisi'],
                        'dibuka_kembali'    => ['label' => 'Dibuka Kembali',    'cls' => 'badge-perbaikan'],
                        default             => ['label' => 'Menunggu',          'cls' => 'badge-default'],
                    };

                    $showChat   = in_array($status, ['panduan_remote', 'perbaikan_teknis']);
                    $showKonfirm= in_array($status, ['selesai', 'tiket_ditutup']) && $tiket->penilaian === null;
                    @endphp

                    <tr class="hover:bg-blue-50/20 transition-colors">

                        {{-- ID Tiket --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1.5 rounded-lg border border-gray-200">
                                #TKT-{{ substr(explode('-', $tiket->id)[1], 0, 5) }}
                            </span>
                        </td>

                        {{-- Subjek --}}
                        <td class="px-6 py-4 max-w-[220px]">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $tiket->subjek_masalah ?? '-' }}</p>
                        </td>

                        {{-- Kategori --}}
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">{{ $tiket->kategori?->nama_kategori ?? '—' }}</span>
                        </td>

                        {{-- Tanggal --}}
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-600">
                                {{ $tiket->created_at?->locale('id')->isoFormat('D MMM YYYY, HH:mm') ?? '-' }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            <span class="badge {{ $statusConfig['cls'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2 flex-wrap">

                                @if($showChat)
                                <a href="{{ route('opd.tiket.chat', $tiket->id) }}"
                                   class="btn-action btn-chat">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                    </svg>
                                    Chat
                                </a>
                                @endif

                                @if($showKonfirm)
                                <button type="button"
                                        onclick="bukaModalRating('{{ $tiket->id }}')"
                                        class="btn-action btn-konfirm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Selesai
                                </button>
                                @endif

                                <a href="{{ route('opd.tiket.show', $tiket->id) }}"
                                   class="btn-action btn-detail">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                    </svg>
                                    Detail
                                </a>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-blue-50">
                                    <svg class="w-8 h-8 text-[#01458E] opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800">Belum ada pengaduan</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        @if(request()->hasAny(['status','search']))
                                            Tidak ada tiket yang sesuai dengan filter Anda.
                                        @else
                                            Tiket pengaduan yang Anda buat akan muncul di sini.
                                        @endif
                                    </p>
                                </div>
                                @if(!request()->hasAny(['status','search']))
                                <a href="{{ route('opd.diagnosis.index') }}"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-bold
                                          transition hover:-translate-y-0.5 hover:shadow-lg"
                                   style="background:#01458E;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Buat Pengaduan Pertama
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($tikets->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between gap-4 flex-wrap">
            <p class="text-xs text-gray-500">
                Menampilkan <span class="font-semibold text-gray-700">{{ $tikets->firstItem() }}–{{ $tikets->lastItem() }}</span>
                dari <span class="font-semibold text-gray-700">{{ $tikets->total() }}</span> tiket
            </p>
            <div class="flex items-center gap-1">
                {{-- Prev --}}
                @if($tikets->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 bg-gray-50 cursor-not-allowed">
                    &lsaquo; Prev
                </span>
                @else
                <a href="{{ $tikets->previousPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">
                    &lsaquo; Prev
                </a>
                @endif

                {{-- Page numbers --}}
                @foreach($tikets->getUrlRange(max(1, $tikets->currentPage()-2), min($tikets->lastPage(), $tikets->currentPage()+2)) as $page => $url)
                    @if($page == $tikets->currentPage())
                    <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-white" style="background:#01458E;">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($tikets->hasMorePages())
                <a href="{{ $tikets->nextPageUrl() }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-blue-50 hover:text-[#01458E] transition-colors">
                    Next &rsaquo;
                </a>
                @else
                <span class="px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 bg-gray-50 cursor-not-allowed">
                    Next &rsaquo;
                </span>
                @endif
            </div>
        </div>
        @endif
    </div>

</main>

{{-- Footer --}}
<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

{{-- Modal Rating Tutup Tiket --}}
<div x-data="ratingModal()" x-show="open"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="open = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:none;">

    <div class="relative z-10 bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 flex flex-col overflow-hidden"
         @click.stop
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Header --}}
        <div class="px-6 py-4 text-white shrink-0" style="background:#059669;border-radius:1.5rem 1.5rem 0 0;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Konfirmasi & Penilaian Layanan</p>
                        <p class="text-xs mt-0.5" style="color:rgba(255,255,255,.75);" x-text="'Tiket #' + tiketId"></p>
                    </div>
                </div>
                <button @click="open = false" class="hover:text-white/60 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-5">
            <p class="text-sm text-gray-600 text-center leading-relaxed">
                Terima kasih! Tiket akan ditutup. Seberapa puas Anda dengan
                kecepatan dan hasil perbaikan dari tim kami?
            </p>

            {{-- Bintang --}}
            <div class="flex justify-center gap-2">
                <template x-for="i in 5" :key="i">
                    <button type="button"
                            @click="rating = i"
                            @mouseenter="ratingHover = i"
                            @mouseleave="ratingHover = 0"
                            class="text-4xl transition-transform hover:scale-110 focus:outline-none">
                        <span :class="(ratingHover || rating) >= i ? 'text-yellow-400' : 'text-gray-200'">★</span>
                    </button>
                </template>
            </div>

            <form :action="'/opd/pengaduan-saya/' + tiketId + '/konfirm'" method="POST">
                @csrf
                <input type="hidden" name="penilaian" :value="rating">
                <button type="submit"
                        :disabled="rating === 0"
                        :class="rating === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90 active:scale-95'"
                        class="w-full py-3 rounded-xl text-sm font-bold text-white transition flex items-center justify-center gap-2"
                        style="background:#059669;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                    KIRIM & TUTUP TIKET
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalRating(id) {
    window.dispatchEvent(new CustomEvent('open-rating', { detail: { id } }));
}
document.addEventListener('alpine:init', () => {
    Alpine.data('ratingModal', () => ({
        open: false,
        tiketId: '',
        rating: 0,
        ratingHover: 0,
        init() {
            window.addEventListener('open-rating', (e) => {
                this.tiketId = e.detail.id;
                this.rating = 0;
                this.ratingHover = 0;
                this.open = true;
            });
        }
    }));
});
</script>

</body>
</html>
