<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Tiket — Admin Helpdesk</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    @include('layouts.sidebarAdminHelpdesk')

    <div class="ml-64 min-h-screen flex flex-col" x-data="tiketPage()" x-cloak>

        {{-- Top bar --}}
        <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Riwayat Tiket</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket yang telah selesai ditangani</p>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-green-700 bg-green-50">
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Tiket selesai ditangani
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten utama (tabel + filter) ── --}}
            <div class="flex-1 flex flex-col overflow-hidden">

                {{-- Filter --}}
                <form method="GET" action="{{ route('admin_helpdesk.tiket.riwayat') }}"
                      class="px-6 py-4 bg-white border-b border-gray-100 flex items-center gap-3 flex-wrap">
                    <div class="relative flex-1 min-w-48">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari tiket..."
                               class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E]">
                    </div>
                    <select name="opd_id" class="text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white">
                        <option value="">Semua OPD</option>
                        @foreach($opds as $opd)
                        <option value="{{ $opd->id }}" {{ request('opd_id') == $opd->id ? 'selected' : '' }}>{{ $opd->nama_opd }}</option>
                        @endforeach
                    </select>
                    <select name="prioritas" class="text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white">
                        <option value="">Semua Prioritas</option>
                        <option value="tinggi"  {{ request('prioritas') == 'tinggi'  ? 'selected' : '' }}>Tinggi</option>
                        <option value="sedang"  {{ request('prioritas') == 'sedang'  ? 'selected' : '' }}>Sedang</option>
                        <option value="rendah"  {{ request('prioritas') == 'rendah'  ? 'selected' : '' }}>Rendah</option>
                    </select>
                    <select name="kategori_id" class="text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] text-gray-600 bg-white">
                        <option value="">Semua Kategori</option>
                        @foreach($kategori as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all hover:opacity-90"
                            style="background:#01458E;">
                        Terapkan
                    </button>
                    <a href="{{ route('admin_helpdesk.tiket.riwayat') }}"
                       class="text-sm font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl transition-all">
                        Reset
                    </a>
                </form>

                {{-- Flash Messages --}}
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
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Tiket</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subjek Masalah</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pengirim (OPD)</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">PIC</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status Akhir</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Selesai</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($tikets as $tiket)
                                @php
                                    $statusAkhir  = $tiket->latestStatus?->status_tiket;
                                    $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
                                    $teknisi      = $tiket->teknisiUtama?->timTeknis;
                                    $statusStyle  = match($statusAkhir) {
                                        'selesai'          => ['bg'=>'#D1FAE5','text'=>'#059669','label'=>'Selesai'],
                                        'rusak_berat'      => ['bg'=>'#FEE2E2','text'=>'#DC2626','label'=>'Rusak Berat'],
                                        'dibuka_kembali'   => ['bg'=>'#FEF3C7','text'=>'#D97706','label'=>'Dibuka Kembali'],
                                        default            => ['bg'=>'#F3F4F6','text'=>'#6B7280','label'=>ucfirst($statusAkhir ?? '—')],
                                    };
                                    $tiketJson = json_encode([
                                        'id'                    => $tiket->id,
                                        'subjek_masalah'        => $tiket->subjek_masalah,
                                        'detail_masalah'        => $tiket->detail_masalah,
                                        'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                        'kategori_nama'         => $kategoriNama,
                                        'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                        'lokasi'                => $tiket->lokasi ?? '—',
                                        'foto_bukti'            => $tiket->foto_bukti,
                                        'prioritas'             => $tiket->prioritas,
                                        'teknisi_nama'          => $teknisi?->nama_lengkap ?? '—',
                                        'status_akhir'          => $statusStyle['label'],
                                        'status_akhir_bg'       => $statusStyle['bg'],
                                        'status_akhir_text'     => $statusStyle['text'],
                                        'catatan_status'        => $tiket->latestStatus?->catatan ?? '—',
                                        'rekomendasi'           => $tiket->latestStatus?->rekomendasi ?? '—',
                                        'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                        'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                        'selesai_at'            => $tiket->latestStatus?->created_at?->translatedFormat('d M Y H:i') . ' WIB',
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
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($tiket->detail_masalah, 55) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600 font-medium">{{ Str::limit($tiket->opd?->nama_opd ?? '—', 25) }}</td>
                                    <td class="px-5 py-4">
                                        @if($teknisi)
                                        <p class="text-sm font-semibold text-gray-800">{{ $teknisi->nama_lengkap }}</p>
                                        @else
                                        <span class="text-xs text-gray-400">Admin</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-600 bg-gray-50">{{ $kategoriNama }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                                              style="background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['text'] }};">
                                            {{ $statusStyle['label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 text-xs">
                                        <p class="font-medium">{{ $tiket->latestStatus?->created_at?->translatedFormat('d M Y') ?? '—' }}</p>
                                        <p class="text-gray-400">{{ $tiket->latestStatus?->created_at?->format('H:i:s') }} WIB</p>
                                    </td>
                                    <td class="px-5 py-4" @click.stop>
                                        <div class="flex items-center justify-center">
                                            <button type="button" @click="openDetail({{ $tiketJson }})"
                                                    class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition-all"
                                                    style="background:#01458E;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-400">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <p class="font-semibold text-gray-500">Belum ada riwayat tiket</p>
                                            <p class="text-sm">Tiket yang sudah diselesaikan akan muncul di sini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ── Detail Drawer Overlay ── --}}
            <div x-show="selectedTiket"
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
            <div x-show="selectedTiket"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed right-0 top-0 h-screen bg-white z-[101] flex flex-col overflow-hidden"
                 style="width:430px;box-shadow:-4px 0 24px rgba(0,0,0,.12);"
                 @click.stop>

                {{-- Drawer Header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;background:#fff;z-index:1;">
                    <div>
                        <p style="font-size:14px;font-weight:700;color:#111827;">Detail Riwayat Tiket</p>
                        <p style="font-size:11px;color:#9ca3af;margin-top:2px;" x-text="'Masuk: ' + (selectedTiket?.created_at_tgl ?? '') + ' · ' + (selectedTiket?.created_at_jam ?? '')"></p>
                    </div>
                    <button @click="closeDetail()"
                            style="width:30px;height:30px;border-radius:8px;background:#f3f4f6;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;transition:background .15s;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Drawer Body --}}
                <div class="flex-1 overflow-y-auto" style="padding:20px;scrollbar-width:thin;">

                    {{-- Section 1: Informasi Tiket --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Informasi Tiket</div>

                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">ID Tiket</span>
                            <span style="font-size:12px;font-weight:700;color:#01458E;font-family:'Courier New',monospace;text-align:right;" x-text="'#' + selectedTiket?.id"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Prioritas</span>
                            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;text-transform:uppercase;"
                                  :style="prioritasBadge(selectedTiket?.prioritas)"
                                  x-text="prioritasLabel(selectedTiket?.prioritas)"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Status Akhir</span>
                            <span style="font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;"
                                  :style="'background:'+selectedTiket?.status_akhir_bg+';color:'+selectedTiket?.status_akhir_text"
                                  x-text="selectedTiket?.status_akhir"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Pengirim (OPD)</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:230px;" x-text="selectedTiket?.opd_nama ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Kategori</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:230px;" x-text="selectedTiket?.kategori_nama ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Lokasi</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:230px;" x-text="selectedTiket?.lokasi ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">PIC</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:230px;" x-text="selectedTiket?.teknisi_nama ?? '—'"></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Selesai</span>
                            <span style="font-size:11px;font-weight:600;color:#111827;font-family:'Courier New',monospace;text-align:right;" x-text="selectedTiket?.selesai_at ?? '—'"></span>
                        </div>
                    </div>

                    {{-- Section 2: Catatan Penanganan --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Catatan Penanganan</div>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;">
                            <p style="font-size:12px;color:#374151;line-height:1.65;" x-text="selectedTiket?.catatan_status || '—'"></p>
                        </div>
                    </div>

                    {{-- Section 3: Rekomendasi --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Rekomendasi</div>
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;">
                            <p style="font-size:12px;color:#374151;line-height:1.65;" x-text="selectedTiket?.rekomendasi || '—'"></p>
                        </div>
                    </div>

                    {{-- Section 4: Detail Masalah --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Detail Masalah</div>

                        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:12px;">
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Subjek</span>
                            <span style="font-size:12px;font-weight:600;color:#111827;text-align:right;max-width:230px;line-height:1.5;" x-text="selectedTiket?.subjek_masalah"></span>
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

                    {{-- Section 5: Foto Bukti --}}
                    <div style="margin-bottom:8px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Foto Bukti Lampiran</div>
                        <template x-if="selectedTiket?.foto_bukti">
                            <div style="border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;cursor:pointer;position:relative;"
                                 @click="showFoto = true">
                                <img :src="'/storage/' + selectedTiket.foto_bukti" alt="Foto Bukti"
                                     style="width:100%;height:160px;object-fit:cover;display:block;">
                                <div style="position:absolute;inset:0;background:rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;"
                                     onmouseenter="this.style.opacity='1'" onmouseleave="this.style.opacity='0'">
                                    <span style="color:#fff;font-size:12px;font-weight:600;">Klik untuk perbesar</span>
                                </div>
                            </div>
                        </template>
                        <template x-if="!selectedTiket?.foto_bukti">
                            <div style="height:80px;border-radius:10px;border:1.5px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;background:#f9fafb;">
                                <span style="font-size:12px;color:#9ca3af;font-weight:500;">Tidak ada lampiran foto</span>
                            </div>
                        </template>
                    </div>

                </div>

                {{-- Drawer Footer --}}
                <div style="flex-shrink:0;padding:16px 20px;border-top:1px solid #f3f4f6;background:#fff;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;">Status Akhir</div>
                    <div style="display:flex;align-items:center;gap:8px;padding:12px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;">
                        <svg style="width:16px;height:16px;color:#059669;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p style="font-size:12px;font-weight:700;color:#059669;" x-text="selectedTiket?.status_akhir ?? 'Selesai'"></p>
                            <p style="font-size:11px;color:#9ca3af;" x-text="'Diselesaikan: ' + (selectedTiket?.selesai_at ?? '—')"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Modals ── --}}

            {{-- Overlay --}}
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="showModal = ''"
                 class="fixed inset-0 bg-black/40 z-[102]"></div>

            {{-- Modal: Perbesar Foto --}}
            <div x-show="showFoto"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 @click="showFoto = false"
                 class="fixed inset-0 z-[105] bg-black/90 flex items-center justify-center p-6">
                <img :src="selectedTiket?.foto_bukti ? '/storage/' + selectedTiket.foto_bukti : ''"
                     class="max-w-full max-h-full rounded-xl shadow-2xl object-contain" @click.stop>
            </div>

        </main>
    </div>

    <script>
    function tiketPage() {
        return {
            selectedTiket: null,
            showModal: '',
            showFoto: false,

            openDetail(t) {
                this.selectedTiket = t;
            },
            closeDetail() {
                this.selectedTiket = null;
                this.showModal = '';
            },
            prioritasLabel(p) {
                return { tinggi: 'Tinggi', sedang: 'Sedang', rendah: 'Rendah' }[p] ?? '—';
            },
            prioritasBadge(p) {
                const map = {
                    tinggi: 'background:#FEE2E2;color:#DC2626;',
                    sedang: 'background:#FEF3C7;color:#D97706;',
                    rendah: 'background:#D1FAE5;color:#059669;',
                };
                return map[p] ?? map.sedang;
            },
        };
    }
    </script>
</body>
</html>
