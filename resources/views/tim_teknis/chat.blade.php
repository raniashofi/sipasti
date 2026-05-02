<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Perbaikan Teknis — Tim Teknis</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        .field-box {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 9px 13px;
            font-size: 12px;
            color: #374151;
            background: #F9FAFB;
            min-height: 36px;
            line-height: 1.5;
        }
        .field-box-area { min-height: 64px; }
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #6B7280;
            margin-bottom: 4px;
        }

        .msg-scroll::-webkit-scrollbar { width: 4px; }
        .msg-scroll::-webkit-scrollbar-track { background: transparent; }
        .msg-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
    </style>
</head>
<body class="h-screen overflow-hidden bg-gray-50 text-gray-800">

@php
    $myUserId     = Auth::id();
    $myName       = $teknis?->nama_lengkap ?? 'Tim Teknis';
    $opdNama      = $tiket->opd?->nama_opd ?? 'OPD';
    $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
    $hasAdminRoom = $adminRoom !== null;
@endphp

{{-- ── Sidebar ── --}}
@include('layouts.sidebarTimTeknis')

{{-- ── Main Wrapper ── --}}
<div class="ml-0 lg:ml-64 h-screen flex flex-col overflow-hidden">

    {{-- ── Header ── --}}
    <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center gap-4 shrink-0 shadow-sm">

        {{-- Back button --}}
        <a href="{{ route('tim_teknis.antrean') }}"
           class="p-2 rounded-xl text-gray-400 hover:text-[#01458E] hover:bg-blue-50 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
        </a>

        {{-- Icon --}}
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#EEF3F9;">
            <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
            </svg>
        </div>

        {{-- Title --}}
        <div class="flex-1 min-w-0">
            <h1 class="text-base font-bold text-gray-900 truncate">Chat dengan {{ $opdNama }}</h1>
            <p class="text-xs text-gray-400 mt-0.5">Perbaikan Teknis · Tiket #{{ $tiket->id }}</p>
        </div>

        {{-- Status badge --}}
        @if($canSend)
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold text-blue-700 bg-blue-50 shrink-0">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            Sesi Aktif
        </div>
        @else
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-500 bg-gray-100 shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Hanya Lihat
        </div>
        @endif

    </header>

    {{-- ── Content Area ── --}}
    <div class="flex-1 overflow-hidden px-3 lg:px-6 py-3 lg:py-5 flex flex-col gap-3 lg:gap-4">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-gray-400 shrink-0">
            <a href="{{ route('tim_teknis.antrean') }}" class="hover:text-[#01458E] transition-colors">Antrean Tugas</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="text-gray-600 font-medium">Chat #{{ $tiket->id }} — {{ $opdNama }}</span>
        </div>

        {{-- ── Main Grid ── --}}
        <div class="flex-1 overflow-hidden flex flex-col gap-3" x-data="{ showDetail: false }">

            {{-- Mobile: toggle detail tiket --}}
            <button @click="showDetail = !showDetail"
                    class="lg:hidden w-full flex items-center justify-between px-4 py-2.5 bg-white rounded-xl border border-gray-100 text-xs font-semibold text-gray-600 shrink-0">
                <span x-text="showDetail ? 'Sembunyikan Detail Tiket' : 'Lihat Detail Tiket'">Lihat Detail Tiket</span>
                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': showDetail }"
                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div class="flex-1 overflow-hidden grid grid-cols-1 lg:grid-cols-5 gap-5">

            {{-- ── LEFT: Detail Tiket ── --}}
            <div :class="showDetail ? '' : 'hidden lg:block'"
                 class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-y-auto msg-scroll">
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-xs font-bold" style="color:#01458E;">#{{ $tiket->id }}</p>
                    <h2 class="text-sm font-bold text-gray-900 mt-0.5">Detail Tiket Perbaikan</h2>
                </div>
                <div class="px-6 py-5 space-y-3.5">

                    <div>
                        <label class="field-label">Subjek Masalah</label>
                        <div class="field-box">{{ $tiket->subjek_masalah ?? '—' }}</div>
                    </div>

                    <div>
                        <label class="field-label">Kronologi & Detail Masalah</label>
                        <div class="field-box field-box-area">{{ $tiket->detail_masalah ?? '—' }}</div>
                    </div>

                    <div>
                        <label class="field-label">OPD Pengirim</label>
                        <div class="field-box">{{ $opdNama }}</div>
                    </div>

                    <div>
                        <label class="field-label">Kategori</label>
                        <div class="field-box">{{ $kategoriNama }}</div>
                    </div>

                    @if($tiket->spesifikasi_perangkat)
                    <div>
                        <label class="field-label">Spesifikasi Perangkat</label>
                        <div class="field-box">{{ $tiket->spesifikasi_perangkat }}</div>
                    </div>
                    @endif

                    @if($tiket->lokasi)
                    <div>
                        <label class="field-label">Lokasi Fisik Perangkat</label>
                        <div class="field-box field-box-area">{{ $tiket->lokasi }}</div>
                    </div>
                    @endif

                    @php $fotosTtc = is_array($tiket->foto_bukti) ? array_values(array_filter($tiket->foto_bukti)) : []; @endphp
                    @if(count($fotosTtc) > 0)
                    <div>
                        <label class="field-label">Foto Bukti</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($fotosTtc as $idx => $foto)
                            <div class="relative aspect-square">
                                <img src="{{ Storage::url($foto) }}" alt="Foto Bukti {{ $idx+1 }}"
                                     class="w-full h-full object-cover rounded-xl border border-gray-200">
                                <span class="absolute bottom-1 left-1.5 text-[9px] bg-black/50 text-white px-1.5 py-0.5 rounded-md font-bold">{{ $idx+1 }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($bukaKembaliStatus)
                    <div style="border-left:3px solid #EF4444;background:#FEF2F2;border-radius:0 10px 10px 0;padding:12px 12px 12px 14px;">
                        <p style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#DC2626;margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid #fecaca;">
                            Alasan OPD Membuka Kembali
                        </p>
                        <p style="font-size:12px;color:#7f1d1d;line-height:1.65;word-break:break-word;white-space:pre-wrap;">{{ $bukaKembaliStatus->catatan ?? '—' }}</p>
                        @if($bukaKembaliStatus->file_bukti)
                        <div style="margin-top:10px;">
                            <p style="font-size:10px;font-weight:700;color:#DC2626;margin-bottom:6px;text-transform:uppercase;">Bukti Foto</p>
                            <a href="{{ Storage::url($bukaKembaliStatus->file_bukti) }}" target="_blank">
                                <img src="{{ Storage::url($bukaKembaliStatus->file_bukti) }}"
                                     alt="Bukti Buka Kembali"
                                     style="width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid #fca5a5;">
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif

                </div>
            </div>

            {{-- ── RIGHT: Chat Area ── --}}
            <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden"
                 x-data="{
                    {{-- Tab state --}}
                    activeTab: 'teknis',
                    canSend: {{ $canSend ? 'true' : 'false' }},

                    {{-- Teknis room --}}
                    messages: {{ json_encode($messages) }},
                    newMessage: '',
                    selectedFile: null,
                    fileName: '',
                    previewUrl: null,
                    sending: false,
                    myId: '{{ $myUserId }}',
                    roomId: '{{ $room->id }}',

                    {{-- Admin room (riwayat, hanya lihat) --}}
                    adminMessages: {{ json_encode($adminMessages) }},
                    adminRoomId: '{{ $adminRoom?->id ?? '' }}',

                    init() {
                        this.$nextTick(() => this.scrollBottom());

                        window.Echo.private('chat.' + this.roomId)
                            .listen('.NewChatMessage', (e) => {
                                this.messages.push(e);
                                if (this.activeTab === 'teknis') {
                                    this.$nextTick(() => this.scrollBottom());
                                }
                            });

                        if (this.adminRoomId) {
                            window.Echo.private('chat.' + this.adminRoomId)
                                .listen('.NewChatMessage', (e) => {
                                    this.adminMessages.push(e);
                                    if (this.activeTab === 'admin') {
                                        this.$nextTick(() => this.scrollAdminBottom());
                                    }
                                });
                        }
                    },

                    switchTab(tab) {
                        this.activeTab = tab;
                        this.$nextTick(() => {
                            if (tab === 'teknis') this.scrollBottom();
                            else this.scrollAdminBottom();
                        });
                    },

                    scrollBottom() {
                        const el = this.$refs.msgs;
                        if (el) el.scrollTop = el.scrollHeight;
                    },

                    scrollAdminBottom() {
                        const el = this.$refs.adminMsgs;
                        if (el) el.scrollTop = el.scrollHeight;
                    },

                    handleFile(event) {
                        const file = event.target.files[0];
                        if (!file) return;
                        this.selectedFile = file;
                        this.fileName = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.previewUrl = e.target.result; };
                        reader.readAsDataURL(file);
                    },

                    clearFile() {
                        this.selectedFile = null;
                        this.fileName = '';
                        this.previewUrl = null;
                        this.$refs.fileInput.value = '';
                    },

                    async send() {
                        if (this.sending) return;
                        if (!this.newMessage.trim() && !this.selectedFile) return;

                        this.sending = true;
                        const fd = new FormData();
                        fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
                        if (this.newMessage.trim()) fd.append('konten', this.newMessage.trim());
                        if (this.selectedFile) fd.append('file', this.selectedFile);

                        try {
                            const res = await window.axios.post(
                                '{{ route('tim_teknis.tiket.chat.send', $tiket->id) }}',
                                fd
                            );
                            this.messages.push(res.data);
                            this.newMessage = '';
                            this.clearFile();
                            this.$nextTick(() => this.scrollBottom());
                        } catch(err) {
                            console.error(err);
                        } finally {
                            this.sending = false;
                        }
                    },

                    handleEnter(e) {
                        if (!e.shiftKey) {
                            e.preventDefault();
                            this.send();
                        }
                    }
                 }">

                {{-- ── Tab Bar ── --}}
                <div class="px-5 pt-4 pb-0 border-b border-gray-100 shrink-0 flex items-center gap-0">
                    {{-- Tab: Chat Perbaikan Teknis --}}
                    <button @click="switchTab('teknis')"
                            :class="activeTab === 'teknis'
                                ? 'border-b-2 border-[#01458E] text-[#01458E] font-semibold'
                                : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex items-center gap-1.5 px-4 pb-3 text-xs transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                        </svg>
                        Chat Perbaikan Teknis
                        @if(!$canSend)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold" style="background:#F3F4F6;color:#9CA3AF;">Hanya Lihat</span>
                        @endif
                    </button>

                    {{-- Tab: Riwayat Chat Admin (hanya tampil jika admin room ada) --}}
                    @if($hasAdminRoom)
                    <button @click="switchTab('admin')"
                            :class="activeTab === 'admin'
                                ? 'border-b-2 border-amber-500 text-amber-600 font-semibold'
                                : 'border-b-2 border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex items-center gap-1.5 px-4 pb-3 text-xs transition-colors shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Riwayat Chat Admin
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold" style="background:#FEF3C7;color:#92400E;">Hanya Lihat</span>
                    </button>
                    @endif
                </div>

                {{-- ══════════════════════════════════════ --}}
                {{-- TAB 1: Chat Perbaikan Teknis           --}}
                {{-- ══════════════════════════════════════ --}}
                <template x-if="activeTab === 'teknis'">
                    <div class="flex flex-col flex-1 overflow-hidden">

                        {{-- Banner hanya-lihat untuk pendamping --}}
                        @if(!$canSend)
                        <div class="mx-4 mt-3 px-3 py-2.5 rounded-xl flex items-center gap-2.5 shrink-0"
                             style="background:#F0F9FF;border:1px solid #BAE6FD;">
                            <svg class="w-4 h-4 shrink-0" style="color:#0284C7;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <p class="text-xs" style="color:#0C4A6E;">
                                Anda berperan sebagai <strong>teknisi pendamping</strong> — dapat melihat percakapan ini, namun tidak dapat mengirim pesan.
                            </p>
                        </div>
                        @endif

                        {{-- Messages Area --}}
                        <div x-ref="msgs"
                             class="flex-1 overflow-y-auto msg-scroll px-5 py-4 space-y-3">

                            <template x-if="messages.length === 0">
                                <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada pesan</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        @if($canSend)
                                        Mulai komunikasi perbaikan dengan {{ $opdNama }}
                                        @else
                                        Belum ada percakapan perbaikan teknis untuk tiket ini
                                        @endif
                                    </p>
                                </div>
                            </template>

                            <template x-for="(msg, idx) in messages" :key="msg.id">
                                <div :class="msg.sender_id === myId ? 'flex justify-end' : 'flex justify-start'">
                                    <div class="max-w-[72%]">
                                        <p x-show="msg.sender_id !== myId"
                                           class="text-[10px] font-semibold text-gray-500 mb-1 ml-1"
                                           x-text="msg.sender_name"></p>
                                        <div :class="msg.sender_id === myId
                                                ? 'rounded-2xl rounded-br-sm px-4 py-2.5 text-white text-sm leading-relaxed'
                                                : 'rounded-2xl rounded-bl-sm px-4 py-2.5 bg-gray-100 text-gray-800 text-sm leading-relaxed'"
                                             :style="msg.sender_id === myId ? 'background:#01458E;' : ''">
                                            <template x-if="msg.tipe_konten === 'image' && msg.file_url">
                                                <div>
                                                    <img :src="msg.file_url" :alt="msg.sender_name"
                                                         class="rounded-xl max-w-full max-h-56 object-cover cursor-pointer"
                                                         @click="window.open(msg.file_url, '_blank')">
                                                    <p x-show="msg.konten" x-text="msg.konten"
                                                       class="mt-2 text-sm leading-relaxed"></p>
                                                </div>
                                            </template>
                                            <template x-if="msg.tipe_konten === 'text'">
                                                <p x-text="msg.konten" class="whitespace-pre-wrap break-words"></p>
                                            </template>
                                        </div>
                                        <p :class="msg.sender_id === myId ? 'text-right mr-1' : 'ml-1'"
                                           class="text-[10px] text-gray-400 mt-1"
                                           x-text="msg.created_at"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Image Preview (hanya untuk teknisi utama) --}}
                        @if($canSend)
                        <div x-show="previewUrl" x-transition class="px-4 pt-3 shrink-0">
                            <div class="relative inline-block">
                                <img :src="previewUrl" class="h-20 w-20 object-cover rounded-xl border border-gray-200">
                                <button @click="clearFile()"
                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full
                                               flex items-center justify-center text-[10px] font-bold hover:bg-red-600">
                                    ✕
                                </button>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1" x-text="fileName"></p>
                        </div>

                        {{-- Input Bar (hanya teknisi utama) --}}
                        <div class="px-4 py-3 border-t border-gray-100 bg-white shrink-0">
                            <div class="flex items-end gap-2 border border-gray-200 rounded-2xl px-3 py-2 bg-white
                                        focus-within:border-[#01458E] focus-within:ring-2 focus-within:ring-[#01458E]/10 transition-all duration-150">
                                <label class="shrink-0 self-end mb-0.5 cursor-pointer p-1.5 rounded-xl text-gray-400
                                              hover:text-[#01458E] hover:bg-blue-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                    </svg>
                                    <input type="file" x-ref="fileInput"
                                           accept="image/jpg,image/jpeg,image/png"
                                           class="sr-only"
                                           @change="handleFile($event)">
                                </label>
                                <textarea x-model="newMessage"
                                          @keydown.enter="handleEnter($event)"
                                          placeholder="Tulis komunikasi atau instruksi perbaikan..."
                                          rows="1"
                                          class="flex-1 bg-transparent text-sm text-gray-800 resize-none border-0 outline-none ring-0 shadow-none
                                                 focus:outline-none focus:ring-0 focus:border-0 focus:shadow-none
                                                 placeholder-gray-400 max-h-32 py-1.5"
                                          style="line-height:1.5;"></textarea>
                                <button @click="send()"
                                        :disabled="sending || (!newMessage.trim() && !selectedFile)"
                                        class="shrink-0 self-end mb-0.5 w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-150"
                                        :class="(sending || (!newMessage.trim() && !selectedFile))
                                            ? 'cursor-not-allowed'
                                            : 'hover:opacity-90 active:scale-95'"
                                        :style="(!newMessage.trim() && !selectedFile)
                                            ? 'background:#E5E7EB;'
                                            : 'background:#01458E;'">
                                    <svg x-show="!sending" class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                         stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                                    </svg>
                                    <svg x-show="sending" class="w-4 h-4 text-white animate-spin"
                                         fill="none" viewBox="0 0 24 24" style="display:none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="text-[10px] text-gray-400 text-center mt-1.5">
                                Enter untuk kirim &bull; Shift+Enter untuk baris baru
                            </p>
                        </div>
                        @endif

                    </div>
                </template>

                {{-- ══════════════════════════════════════ --}}
                {{-- TAB 2: Riwayat Chat Admin             --}}
                {{-- ══════════════════════════════════════ --}}
                @if($hasAdminRoom)
                <template x-if="activeTab === 'admin'">
                    <div class="flex flex-col flex-1 overflow-hidden">

                        {{-- Banner riwayat --}}
                        <div class="mx-4 mt-3 px-3 py-2.5 rounded-xl flex items-center gap-2.5 shrink-0"
                             style="background:#FFFBEB;border:1px solid #FDE68A;">
                            <svg class="w-4 h-4 shrink-0" style="color:#D97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs" style="color:#92400E;">
                                Riwayat percakapan antara <strong>OPD</strong> dan <strong>Admin Helpdesk</strong> sebelum tiket ini dieskalasi ke Tim Teknis. Hanya dapat dilihat.
                            </p>
                        </div>

                        {{-- Messages Area --}}
                        <div x-ref="adminMsgs"
                             class="flex-1 overflow-y-auto msg-scroll px-5 py-4 space-y-3">

                            <template x-if="adminMessages.length === 0">
                                <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-gray-500">Belum ada riwayat pesan</p>
                                    <p class="text-xs text-gray-400 mt-1">Tidak ada percakapan di fase panduan remote</p>
                                </div>
                            </template>

                            <template x-for="(msg, idx) in adminMessages" :key="msg.id">
                                <div :class="msg.sender_id === myId ? 'flex justify-end' : 'flex justify-start'">
                                    <div class="max-w-[72%]">
                                        <p class="text-[10px] font-semibold text-gray-500 mb-1 ml-1"
                                           x-text="msg.sender_name"></p>
                                        <div class="rounded-2xl rounded-bl-sm px-4 py-2.5 text-sm leading-relaxed"
                                             :style="msg.sender_id === myId
                                                ? 'background:#F3F4F6;color:#374151;border-radius:1rem 1rem 0.25rem 1rem;'
                                                : 'background:#F3F4F6;color:#374151;border-radius:1rem 1rem 1rem 0.25rem;'">
                                            <template x-if="msg.tipe_konten === 'image' && msg.file_url">
                                                <div>
                                                    <img :src="msg.file_url" :alt="msg.sender_name"
                                                         class="rounded-xl max-w-full max-h-56 object-cover cursor-pointer"
                                                         @click="window.open(msg.file_url, '_blank')">
                                                    <p x-show="msg.konten" x-text="msg.konten"
                                                       class="mt-2 text-sm leading-relaxed"></p>
                                                </div>
                                            </template>
                                            <template x-if="msg.tipe_konten === 'text'">
                                                <p x-text="msg.konten" class="whitespace-pre-wrap break-words"></p>
                                            </template>
                                        </div>
                                        <p class="ml-1 text-[10px] text-gray-400 mt-1"
                                           x-text="msg.created_at"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer: info hanya lihat --}}
                        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 shrink-0">
                            <div class="flex items-center justify-center gap-2 py-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                                <p class="text-[11px] text-gray-400 font-medium">Riwayat ini hanya dapat dilihat, tidak dapat dibalas</p>
                            </div>
                        </div>

                    </div>
                </template>
                @endif

            </div>
            {{-- end right panel --}}

            </div>
            {{-- end inner grid --}}
        </div>
        {{-- end grid wrapper --}}

    </div>
    {{-- end content --}}

</div>
{{-- end main wrapper --}}

</body>
</html>
