<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panduan Remote — Admin Helpdesk</title>
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
                <h1 class="text-lg font-bold text-gray-900">Panduan Remote</h1>
                <p class="text-xs text-gray-400 mt-0.5">Daftar tiket yang sedang dalam sesi panduan remote (chat)</p>
            </div>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-blue-700 bg-blue-50">
                <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                Chat aktif dengan OPD
            </div>
        </header>

        <main class="flex-1 flex overflow-hidden">

            {{-- ── Konten utama (tabel + filter) ── --}}
            <div class="flex-1 flex flex-col overflow-hidden">

                {{-- Filter --}}
                <form method="GET" action="{{ route('admin_helpdesk.tiket.panduan') }}"
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
                    <button type="submit"
                            class="text-sm font-semibold text-white px-5 py-2.5 rounded-xl transition-all hover:opacity-90"
                            style="background:#01458E;">
                        Terapkan
                    </button>
                    <a href="{{ route('admin_helpdesk.tiket.panduan') }}"
                       class="text-sm font-semibold text-gray-500 bg-gray-100 hover:bg-gray-200 px-5 py-2.5 rounded-xl transition-all">
                        Reset
                    </a>
                </form>

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
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">ID Tiket</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Subjek Masalah</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Pengirim (OPD)</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Kategori</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status Chat</th>
                                    <th class="px-5 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Waktu Masuk</th>
                                    <th class="px-5 py-3.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($tikets as $tiket)
                                @php
                                    $hasChat      = $tiket->chatRooms->isNotEmpty();
                                    $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
                                    $tiketJson    = json_encode([
                                        'id'                    => $tiket->id,
                                        'subjek_masalah'        => $tiket->subjek_masalah,
                                        'detail_masalah'        => $tiket->detail_masalah,
                                        'opd_nama'              => $tiket->opd?->nama_opd ?? '—',
                                        'kategori_nama'         => $kategoriNama,
                                        'spesifikasi_perangkat' => $tiket->spesifikasi_perangkat ?? '—',
                                        'lokasi'                => $tiket->lokasi ?? '—',
                                        'foto_bukti'            => $tiket->foto_bukti,
                                        'prioritas'             => $tiket->prioritas,
                                        'created_at_tgl'        => $tiket->created_at?->translatedFormat('d M Y'),
                                        'created_at_jam'        => $tiket->created_at?->format('H:i:s') . ' WIB',
                                        'has_chat'              => $hasChat,
                                        'catatan_status'        => $tiket->latestStatus?->catatan ?? '—',
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
                                        <span class="text-xs px-2.5 py-1 rounded-lg border border-gray-200 text-gray-600 bg-gray-50">{{ $kategoriNama }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($hasChat)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full" style="background:#DBEAFE;color:#1D4ED8;">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>Aktif
                                        </span>
                                        @else
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background:#F3F4F6;color:#6B7280;">Belum Ada Chat</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-gray-500 text-xs">
                                        <p class="font-medium">{{ $tiket->created_at?->translatedFormat('d M Y') }}</p>
                                        <p class="text-gray-400">{{ $tiket->created_at?->format('H:i:s') }} WIB</p>
                                    </td>
                                    <td class="px-5 py-4" @click.stop>
                                        <div class="flex items-center justify-center gap-1.5">
                                            @if($hasChat)
                                            <a href="{{ route('admin_helpdesk.tiket.chat', $tiket->id) }}" title="Lihat Chat"
                                               class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg text-white hover:opacity-90"
                                               style="background:#1D4ED8;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                Chat
                                            </a>
                                            @else
                                            <button type="button" @click="openDetail({{ $tiketJson }})"
                                                    class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg text-white hover:opacity-90"
                                                    style="background:#01458E;">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                Mulai Panduan
                                            </button>
                                            @endif
                                            <button type="button" @click.stop="openDetail({{ $tiketJson }}); showModal='eskalasi'"
                                                    title="Eskalasi ke Tim Teknis"
                                                    class="w-8 h-8 rounded-full flex items-center justify-center transition-all hover:scale-110"
                                                    style="background:#FEF3C7;color:#D97706;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3 text-gray-400">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                            </div>
                                            <p class="font-semibold text-gray-500">Tidak ada tiket dalam Panduan Remote</p>
                                            <p class="text-sm">Tiket akan muncul setelah Admin Helpdesk menerima pengajuan.</p>
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
                        <p style="font-size:14px;font-weight:700;color:#111827;">Detail Tiket</p>
                        <p style="font-size:11px;color:#9ca3af;margin-top:2px;" x-text="(selectedTiket?.created_at_tgl ?? '') + ' · ' + (selectedTiket?.created_at_jam ?? '')"></p>
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
                            <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">Status Chat</span>
                            <span :style="selectedTiket?.has_chat
                                      ? 'font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:#DBEAFE;color:#1D4ED8;'
                                      : 'font-size:10px;font-weight:700;padding:2px 8px;border-radius:4px;background:#F3F4F6;color:#6B7280;'"
                                  x-text="selectedTiket?.has_chat ? 'Aktif' : 'Belum Ada Chat'"></span>
                        </div>
                    </div>

                    {{-- Section 2: Catatan Status --}}
                    <div style="margin-bottom:20px;">
                        <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">Catatan Status</div>
                        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;">
                            <p style="font-size:12px;color:#374151;line-height:1.65;" x-text="selectedTiket?.catatan_status || '—'"></p>
                        </div>
                    </div>

                    {{-- Section 3: Detail Masalah --}}
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

                    {{-- Section 4: Foto Bukti --}}
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
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:12px;">Aksi Tindakan</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <template x-if="selectedTiket">
                            <a :href="'/admin-helpdesk/tiket/' + selectedTiket.id + '/chat'"
                               class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90 hover:-translate-y-0.5 hover:shadow-md active:scale-95"
                               style="background:#1D4ED8;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Buka Ruang Chat
                            </a>
                        </template>
                        <button @click="showModal = 'eskalasi'"
                                style="width:100%;padding:9px;border-radius:10px;font-size:12px;font-weight:600;background:#fff;border:1px solid #e5e7eb;color:#374151;cursor:pointer;">
                            ⇄ Eskalasi ke Tim Teknis
                        </button>
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

            {{-- Modal: Eskalasi ke Tim Teknis --}}
            <div x-show="showModal === 'eskalasi'"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="fixed inset-0 z-[103] flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 relative" @click.stop>
                    <button @click="showModal = ''" class="absolute top-4 right-4 w-7 h-7 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 text-center">Eskalasi ke Tim Teknis</h3>
                    <p class="text-sm font-semibold mb-1 text-center" style="color:#01458E;" x-text="'#' + selectedTiket?.id + ' — ' + selectedTiket?.subjek_masalah"></p>
                    <p class="text-sm text-gray-500 mb-5 text-center">Tugaskan teknisi untuk perbaikan langsung</p>
                    <form x-ref="formEskalasi" method="POST" action="#"
                          @submit.prevent="submitForm($refs.formEskalasi, '/admin-helpdesk/tiket/' + selectedTiket.id + '/eskalasi')">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teknisi Utama <span class="text-red-500">*</span></label>
                            <select name="teknisi_utama_id" required
                                    class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Pilih Teknisi</option>
                                @foreach($teknisis as $tek)
                                @php $bl = ['e_government'=>'E-Government','infrastruktur_teknologi_informasi'=>'Infrastruktur TI','statistik_persandian'=>'Statistik & Persandian']; @endphp
                                <option value="{{ $tek->id }}">
                                    {{ $tek->nama_lengkap }}
                                    @if($tek->bidang) — {{ $bl[$tek->bidang->nama_bidang] ?? $tek->bidang->nama_bidang }} @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Teknisi Pendamping (opsional)</label>
                            <select name="teknisi_pendamping_id"
                                    class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white">
                                <option value="">Tidak ada</option>
                                @foreach($teknisis as $tek)
                                <option value="{{ $tek->id }}">
                                    {{ $tek->nama_lengkap }}
                                    @if($tek->bidang) — {{ $bl[$tek->bidang->nama_bidang] ?? $tek->bidang->nama_bidang }} @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">Instruksi Khusus (opsional)</label>
                            <textarea name="instruksi" rows="3" placeholder="Masukkan instruksi untuk teknisi..."
                                      class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] resize-none"></textarea>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="showModal = ''"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-all">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                                    style="background:#D97706;">
                                Eskalasi Tiket
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
            submitForm(form, url) {
                form.action = url;
                form.submit();
            },
        };
    }
    </script>
</body>
</html>
