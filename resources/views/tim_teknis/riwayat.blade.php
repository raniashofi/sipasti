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
        .drawer-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .drawer-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    @include('layouts.sidebarTimTeknis')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col" x-data="riwayatPage()" x-cloak>

        {{-- ── Top Bar ── --}}
        <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Riwayat Tugas</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar seluruh tiket yang telah Anda selesaikan atau kembalikan</p>
            </div>
            <div class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $countAll }} tiket selesai
            </div>
        </header>

        <main class="flex-1 px-4 lg:px-6 py-4 lg:py-6 space-y-5">

            {{-- ── Filter & Search ── --}}
            <form method="GET" action="{{ route('tim_teknis.riwayat') }}" id="filterFormRiwayat"
                  class="bg-white rounded-2xl border border-gray-100 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>
                <div class="flex flex-wrap gap-2 items-center">
                    <input type="hidden" name="peran" value="{{ request('peran') }}">
                    <div class="flex-1 min-w-0 relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari ID tiket atau subjek masalah..."
                               oninput="clearTimeout(window._stRiwayat); window._stRiwayat = setTimeout(() => document.getElementById('filterFormRiwayat').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>
                    <a href="{{ route('tim_teknis.riwayat', request()->only('peran')) }}"
                       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            {{-- ── Data Table ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                {{-- Sub-tabs Peran --}}
                <div class="flex items-center px-5 pt-4 pb-0 border-b border-gray-100 overflow-x-auto">
                    <div class="flex items-center gap-0 shrink-0">
                        @php $activePeran = request('peran', ''); @endphp
                        <a href="{{ route('tim_teknis.riwayat', request()->only('search')) }}"
                           class="px-5 pb-4 text-sm transition-colors {{ $activePeran === '' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                            Semua <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">{{ $countAll }}</span>
                        </a>
                        <a href="{{ route('tim_teknis.riwayat', [...request()->only('search'), 'peran' => 'teknisi_utama']) }}"
                           class="px-5 pb-4 text-sm transition-colors {{ $activePeran === 'teknisi_utama' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                            Teknisi Utama <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">{{ $countUtama }}</span>
                        </a>
                        <a href="{{ route('tim_teknis.riwayat', [...request()->only('search'), 'peran' => 'teknisi_pendamping']) }}"
                           class="px-5 pb-4 text-sm transition-colors {{ $activePeran === 'teknisi_pendamping' ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold' : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600' }}">
                            Pendamping <span class="ml-1.5 text-[11px] px-1.5 py-0.5 rounded-full bg-blue-50 text-[#01458E] font-semibold">{{ $countPendamping }}</span>
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Tiket</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subjek Masalah</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">OPD</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Hasil Akhir</th>
                                <th class="px-5 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($riwayats as $row)
                                @php
                                    $tiket = $row->tiket;
                                    $statusAkhir = $tiket?->latestStatus?->status_tiket ?? '';
                                    $kategoriNama = $tiket?->kategori?->nama_kategori ?? $tiket?->kb?->kategori?->nama_kategori ?? '—';

                                    // Mapping warna untuk baris tabel
                                    $hasilConfig = match($statusAkhir) {
                                        'selesai' => ['label' => 'Berhasil Diperbaiki', 'bg' => '#f0fdf4', 'color' => '#16a34a'],
                                        'rusak_berat' => ['label' => 'Rusak Berat', 'bg' => '#fef2f2', 'color' => '#dc2626'],
                                        default => ['label' => 'Dikembalikan', 'bg' => '#fffbeb', 'color' => '#d97706'],
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
                                <tr class="hover:bg-gray-50 cursor-pointer transition-colors" @click="openDetail({{ $rowJson }})">
                                    <td class="px-5 py-4">
                                        <span class="font-mono text-xs font-semibold text-[#01458E] bg-blue-50 px-2 py-0.5 rounded">
                                            #{{ Str::upper(substr($tiket?->id ?? '', -8)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 max-w-xs">
                                        <p class="font-semibold text-gray-800 truncate">{{ $tiket?->subjek_masalah ?? '—' }}</p>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($tiket?->detail_masalah ?? '', 45) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-gray-600 font-medium">
                                        {{ Str::limit($tiket?->opd?->nama_opd ?? '—', 22) }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-600 bg-gray-50 whitespace-nowrap">
                                            {{ Str::limit($kategoriNama, 20) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:{{ $hasilConfig['bg'] }};color:{{ $hasilConfig['color'] }};">
                                            {{ $hasilConfig['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="text-gray-300 text-base">›</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-400">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <p class="font-semibold text-gray-500">Belum ada riwayat tugas</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        {{-- ── Overlay Drawer ── --}}
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @click="close()" class="fixed inset-0 z-[100]" style="background:rgba(0,0,0,.32);">
        </div>

        {{-- ── Detail Drawer ── --}}
        <div x-show="selected"
             x-transition:enter="transition ease-out duration-250" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed right-0 top-0 h-screen bg-white z-[101] flex flex-col overflow-hidden w-full sm:w-[440px]"
             style="box-shadow:-4px 0 24px rgba(0,0,0,.12);" @click.stop>

            {{-- Drawer Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #f3f4f6;background:#fff;flex-shrink:0;">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px;">
                        <p style="font-size:14px;font-weight:700;color:#111827;">Detail Riwayat Tugas</p>
                        <template x-if="selected?.is_utama">
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;background:#EEF3F9;color:#01458E;">
                                <svg width="10" height="10" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Teknisi Utama
                            </span>
                        </template>
                    </div>
                    <p style="font-size:11px;color:#9ca3af;" x-text="(selected?.waktu_tgl ?? '') + ' · ' + (selected?.waktu_jam ?? '')"></p>
                </div>
                <button @click="close()" style="width:30px;height:30px;border-radius:8px;background:#f3f4f6;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Drawer Body --}}
            <div class="flex-1 overflow-y-auto drawer-scroll" style="padding:20px;">

                {{-- Informasi Tiket --}}
                <div style="margin-bottom:24px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Informasi Tiket</div>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                        <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">ID Tiket</span>
                        <span style="font-size:12px;font-weight:700;color:#01458E;font-family:'Courier New',monospace;" x-text="'#' + (selected?.id ?? '')"></span>
                    </div>

                    {{-- Rekomendasi Penanganan --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                        <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Rekomendasi</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[4px] text-[11px] font-bold font-mono tracking-wide whitespace-nowrap"
                            :style="rekomendasiBadge(selected?.rekomendasi_penanganan)">
                            <span x-text="rekomendasiLabel(selected?.rekomendasi_penanganan)"></span>
                        </span>
                    </div>

                    {{-- Status Perbaikan (Desain persis "Status ✓ Berhasil" Admin Helpdesk) --}}
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                        <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Status Perbaikan</span>
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold whitespace-nowrap"
                            :style="`background-color: ${selected?.hasil_bg}; color: ${selected?.hasil_color};`">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span x-text="selected?.hasil_label"></span>
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                        <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">OPD</span>
                        <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:240px;" x-text="selected?.opd_nama ?? '—'"></span>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                        <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Ditugaskan</span>
                        <span style="font-size:12px;font-weight:600;color:#111827;" x-text="(selected?.waktu_tgl ?? '—') + ', ' + (selected?.waktu_jam ?? '')"></span>
                    </div>
                </div>

                {{-- Detail Masalah --}}
                <div style="margin-bottom:24px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Detail Masalah</div>
                    <p style="font-size:12px;font-weight:600;color:#111827;margin-bottom:8px;" x-text="selected?.subjek_masalah"></p>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:10px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;opacity:.7;color:#475569;">Kronologi</div>
                        <p style="font-size:12px;color:#334155;line-height:1.6;" x-text="selected?.detail_masalah || '—'"></p>
                    </div>
                    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:12px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;opacity:.7;color:#9a3412;">Spesifikasi Awal</div>
                        <p style="font-size:12px;color:#7c2d12;line-height:1.6;" x-text="selected?.spesifikasi_perangkat || '—'"></p>
                    </div>
                </div>

                {{-- Hasil Penanganan (Tampil jika rusak berat) --}}
                <template x-if="selected?.status_akhir === 'rusak_berat'">
                    <div style="margin-bottom:24px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Hasil Penanganan</div>
                        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;margin-bottom:10px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;color:#991b1b;">Analisis Kerusakan</div>
                            <p style="font-size:12px;color:#7f1d1d;" x-text="selected?.analisis_kerusakan || '—'"></p>
                        </div>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;">
                            <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;color:#14532d;">Rekomendasi</div>
                            <p style="font-size:12px;color:#166534;" x-text="selected?.rekomendasi || '—'"></p>
                        </div>
                    </div>
                </template>

                {{-- Foto Bukti --}}
                <div>
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Foto Bukti</div>
                    <template x-if="selected?.foto_bukti?.length > 0">
                        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:8px;">
                            <template x-for="(foto, fi) in selected.foto_bukti" :key="fi">
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
                    <template x-if="!selected?.foto_bukti?.length">
                        <div style="height:80px;border:1.5px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;background:#f9fafb;">
                            <span style="font-size:12px;color:#9ca3af;">Tidak ada foto lampiran</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Drawer Footer --}}
            <div style="padding:16px 20px;border-top:1px solid #f3f4f6;background:#fff;">
                <div style="text-align:center;padding:12px;background:#F9FAFB;border-radius:10px;border:1.5px dashed #E5E7EB;">
                    <p style="font-size:12px;color:#6B7280;font-weight:600;">Status: Selesai</p>
                    <p style="font-size:11px;color:#9CA3AF;">Tiket ini sudah masuk ke dalam arsip riwayat</p>
                </div>
            </div>
        </div>

        {{-- Modal Perbesar Foto --}}
        <div x-show="showFoto" x-transition @click="showFoto = false; activeFoto = null"
             class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-6">
            <img :src="activeFoto ? '/storage/' + activeFoto : ''" class="max-w-full max-h-full rounded-lg shadow-2xl">
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
                    eskalasi: 'background:#fef2f2;color:#dc2626;',
                    admin:    'background:#eff6ff;color:#1d4ed8;',
                };
                return map[p] ?? 'background:#f3f4f6;color:#6b7280;';
            }
        };
    }
    </script>
</body>
</html>
