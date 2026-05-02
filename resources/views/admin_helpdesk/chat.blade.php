<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat Panduan Remote — Admin Helpdesk</title>
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
            word-wrap: break-word;
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
    $myName       = $admin?->nama_lengkap ?? 'Admin Helpdesk';
    $opdNama      = $tiket->opd?->nama_opd ?? 'OPD';
    $kategoriNama = $tiket->kategori?->nama_kategori ?? $tiket->kb?->kategori?->nama_kategori ?? '—';
@endphp

{{-- ── Sidebar ── --}}
@include('layouts.sidebarAdminHelpdesk')

{{-- ── Main Wrapper ── --}}
<div class="ml-0 lg:ml-64 h-screen flex flex-col overflow-hidden">

    {{-- ── Header ── --}}
    <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-6 py-3 sm:py-4 flex items-center justify-between shrink-0 shadow-sm z-30">

        <div class="flex items-center gap-2 sm:gap-4 w-full min-w-0">
            {{-- Back button --}}
            <a href="{{ route('admin_helpdesk.tiket.panduan') }}"
               class="p-1.5 sm:p-2 rounded-xl text-gray-400 hover:text-[#01458E] hover:bg-blue-50 transition-colors shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>

            {{-- Icon --}}
            <div class="hidden sm:flex w-9 h-9 rounded-xl items-center justify-center shrink-0" style="background:#EEF3F9;">
                <svg class="w-5 h-5" style="color:#01458E;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                </svg>
            </div>

            {{-- Title --}}
            <div class="flex-1 min-w-0">
                <h1 class="text-sm sm:text-base font-bold text-gray-900 truncate">Chat dengan {{ Str::limit($opdNama, 25) }}</h1>
                <p class="text-[10px] sm:text-xs text-gray-400 mt-0.5">Panduan Remote · Tiket #{{ $tiket->id }}</p>
            </div>
        </div>

        {{-- Status badge --}}
        <div class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg sm:rounded-xl text-[10px] sm:text-xs font-semibold text-blue-700 bg-blue-50 shrink-0 border border-blue-100 ml-2">
            <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-blue-500 animate-pulse"></span>
            <span class="hidden sm:inline">Sesi Aktif</span>
            <span class="sm:hidden">Aktif</span>
        </div>
    </header>

    {{-- ── Content Area ── --}}
    <div class="flex-1 overflow-hidden px-3 sm:px-6 py-3 sm:py-5 flex flex-col gap-3 sm:gap-4 w-full">

        {{-- Breadcrumb --}}
        <div class="hidden sm:flex items-center gap-2 text-xs text-gray-400 shrink-0">
            <a href="{{ route('admin_helpdesk.tiket.panduan') }}" class="hover:text-[#01458E] transition-colors">Panduan Remote</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="text-gray-600 font-medium truncate max-w-sm">Chat #{{ $tiket->id }} — {{ $opdNama }}</span>
        </div>

        {{-- ── Main Container (Flex Col on Mobile, Flex Row on Desktop) ── --}}
        <div class="flex-1 overflow-hidden flex flex-col lg:flex-row gap-3 sm:gap-5 w-full mx-auto">

            {{-- ── LEFT: Detail Tiket (Collapsible on Mobile) ── --}}
            <div class="w-full lg:w-[320px] xl:w-[380px] shrink-0 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden"
                 x-data="{ openDetail: false }">

                {{-- Toggle Button for Mobile --}}
                <div class="lg:hidden px-4 py-3 bg-gray-50/50 flex justify-between items-center cursor-pointer border-b border-gray-100"
                     @click="openDetail = !openDetail">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-blue-50 text-[#01458E]">#{{ Str::upper(substr($tiket->id, -8)) }}</span>
                        <span class="text-xs font-bold text-gray-800">Detail Masalah Tiket</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                         :class="openDetail ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </div>

                {{-- Header for Desktop --}}
                <div class="hidden lg:block px-6 py-4 border-b border-gray-100 bg-gray-50/30">
                    <p class="text-[11px] font-bold text-[#01458E] font-mono">#{{ $tiket->id }}</p>
                    <h2 class="text-sm font-bold text-gray-900 mt-1">Detail Tiket Panduan</h2>
                </div>

                {{-- Detail Content (Scrollable) --}}
                <div class="px-4 sm:px-6 py-4 sm:py-5 space-y-4 overflow-y-auto msg-scroll flex-1"
                     :class="openDetail ? 'block' : 'hidden lg:block'">

                    <div>
                        <label class="field-label">Subjek Masalah</label>
                        <div class="field-box font-medium">{{ $tiket->subjek_masalah ?? '—' }}</div>
                    </div>

                    <div>
                        <label class="field-label">Kronologi & Detail Masalah</label>
                        <div class="field-box field-box-area whitespace-pre-wrap">{{ $tiket->detail_masalah ?? '—' }}</div>
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
                        <div class="field-box whitespace-pre-wrap">{{ $tiket->spesifikasi_perangkat }}</div>
                    </div>
                    @endif

                    @if($tiket->lokasi)
                    <div>
                        <label class="field-label">Lokasi Fisik Perangkat</label>
                        <div class="field-box field-box-area">{{ $tiket->lokasi }}</div>
                    </div>
                    @endif

                    @php $fotosAhc = is_array($tiket->foto_bukti) ? array_values(array_filter($tiket->foto_bukti)) : []; @endphp
                    @if(count($fotosAhc) > 0)
                    <div>
                        <label class="field-label">Foto Bukti</label>
                        <div class="grid grid-cols-2 gap-2.5 mt-2">
                            @foreach($fotosAhc as $idx => $foto)
                            <div class="relative aspect-square cursor-pointer group" @click="window.open('{{ Storage::url($foto) }}', '_blank')">
                                <img src="{{ Storage::url($foto) }}" alt="Foto Bukti {{ $idx+1 }}"
                                     class="w-full h-full object-cover rounded-xl border border-gray-200 group-hover:opacity-90 transition-opacity">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                    <span class="text-white text-[10px] font-bold">Lihat</span>
                                </div>
                                <span class="absolute bottom-1.5 left-1.5 text-[9px] bg-black/60 text-white px-1.5 py-0.5 rounded font-bold">{{ $idx+1 }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ── RIGHT: Chat Area ── --}}
            <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden min-w-0 relative"
                 x-data="{
                    messages: {{ json_encode($messages) }},
                    newMessage: '',
                    selectedFile: null,
                    fileName: '',
                    previewUrl: null,
                    sending: false,
                    myId: '{{ $myUserId }}',
                    roomId: '{{ $room->id }}',

                    init() {
                        this.$nextTick(() => this.scrollBottom());

                        window.Echo.private('chat.' + this.roomId)
                            .listen('.NewChatMessage', (e) => {
                                this.messages.push(e);
                                this.$nextTick(() => this.scrollBottom());
                            });
                    },

                    scrollBottom() {
                        const el = this.$refs.msgs;
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
                                '{{ route('admin_helpdesk.tiket.chat.send', $tiket->id) }}',
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

                {{-- Chat Header --}}
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 shrink-0 flex items-center justify-between bg-gray-50/50 z-10">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0 border border-blue-100" style="background:#EEF3F9;">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" style="color:#01458E;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm sm:text-base font-bold text-gray-900 truncate">{{ $opdNama }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                <p class="text-[10px] sm:text-xs text-green-600 font-medium">Online</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div x-ref="msgs" class="flex-1 overflow-y-auto msg-scroll px-3 sm:px-5 py-4 space-y-4 bg-white relative">

                    {{-- Empty state --}}
                    <template x-if="messages.length === 0">
                        <div class="flex flex-col items-center justify-center h-full py-10 text-center px-4">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3 sm:mb-4">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-gray-700">Belum ada obrolan</p>
                            <p class="text-xs text-gray-400 mt-1 max-w-[250px] leading-relaxed">Mulai sapa atau berikan instruksi panduan remote kepada OPD di bawah ini.</p>
                        </div>
                    </template>

                    {{-- Message Bubbles --}}
                    <template x-for="(msg, idx) in messages" :key="msg.id">
                        <div :class="msg.sender_id === myId ? 'flex justify-end' : 'flex justify-start'">
                            <div class="max-w-[85%] sm:max-w-[75%] lg:max-w-[70%]">

                                {{-- Sender name (hanya jika bukan pesan sendiri) --}}
                                <p x-show="msg.sender_id !== myId"
                                   class="text-[10px] font-bold text-gray-400 mb-1 ml-1"
                                   x-text="msg.sender_name"></p>

                                {{-- Bubble --}}
                                <div :class="msg.sender_id === myId
                                        ? 'rounded-2xl rounded-br-sm px-3 sm:px-4 py-2 sm:py-2.5 text-white shadow-sm'
                                        : 'rounded-2xl rounded-bl-sm px-3 sm:px-4 py-2 sm:py-2.5 bg-gray-100 text-gray-800 border border-gray-200/60 shadow-sm'"
                                     :style="msg.sender_id === myId ? 'background:#01458E;' : ''">

                                    {{-- Gambar Lampiran --}}
                                    <template x-if="msg.tipe_konten === 'image' && msg.file_url">
                                        <div class="mb-1.5">
                                            <img :src="msg.file_url" :alt="msg.sender_name"
                                                 class="rounded-xl w-full max-h-48 sm:max-h-60 object-cover cursor-pointer hover:opacity-95 transition-opacity bg-black/5"
                                                 @click="window.open(msg.file_url, '_blank')">
                                        </div>
                                    </template>

                                    {{-- Teks Konten --}}
                                    <template x-if="msg.konten">
                                        <p x-text="msg.konten" class="text-[13px] sm:text-sm leading-relaxed whitespace-pre-wrap break-words" :class="msg.tipe_konten === 'image' ? 'mt-2' : ''"></p>
                                    </template>
                                </div>

                                {{-- Timestamp --}}
                                <div :class="msg.sender_id === myId ? 'text-right mr-1' : 'ml-1'" class="mt-1 flex items-center gap-1" :class="msg.sender_id === myId ? 'justify-end' : 'justify-start'">
                                    <p class="text-[9px] sm:text-[10px] text-gray-400 font-medium" x-text="msg.created_at"></p>
                                    <template x-if="msg.sender_id === myId">
                                        <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Image Preview Area --}}
                <div x-show="previewUrl" x-transition.opacity class="px-4 sm:px-5 pt-3 pb-1 shrink-0 bg-gray-50 border-t border-gray-100">
                    <div class="relative inline-block group">
                        <img :src="previewUrl" class="h-16 w-16 sm:h-20 sm:w-20 object-cover rounded-xl border-2 border-white shadow-md">
                        <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center"></div>
                        <button @click="clearFile()"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full
                                       flex items-center justify-center text-[10px] font-bold shadow-md hover:bg-red-600 transition-transform hover:scale-110 z-10">
                            ✕
                        </button>
                    </div>
                    <p class="text-[10px] text-gray-500 mt-1.5 font-medium truncate max-w-[200px]" x-text="fileName"></p>
                </div>

                {{-- Input Bar --}}
                <div class="px-3 sm:px-5 py-3 sm:py-4 bg-white shrink-0 z-20" style="box-shadow: 0 -4px 10px rgba(0,0,0,0.02);">
                    <div class="flex items-end gap-2 border border-gray-200 rounded-2xl px-2 sm:px-3 py-1.5 sm:py-2 bg-gray-50
                                focus-within:border-[#01458E] focus-within:ring-4 focus-within:ring-[#01458E]/10 focus-within:bg-white transition-all duration-200">

                        {{-- Attach image (Kiri) --}}
                        <label class="shrink-0 self-end mb-1 sm:mb-1.5 cursor-pointer p-1.5 sm:p-2 rounded-xl text-gray-400 hover:text-[#01458E] hover:bg-blue-50 transition-colors">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                            <input type="file" x-ref="fileInput" accept="image/jpg,image/jpeg,image/png" class="sr-only" @change="handleFile($event)">
                        </label>

                        {{-- Textarea (Tengah) --}}
                        <textarea x-model="newMessage"
                                  @keydown.enter="handleEnter($event)"
                                  placeholder="Tulis pesan..."
                                  rows="1"
                                  class="flex-1 bg-transparent text-[13px] sm:text-sm text-gray-800 resize-none border-0 outline-none ring-0 shadow-none
                                         focus:outline-none focus:ring-0 focus:border-0 focus:shadow-none
                                         placeholder-gray-400 max-h-24 sm:max-h-32 py-2 sm:py-2.5 px-1"
                                  style="line-height:1.5;"></textarea>

                        {{-- Send button (Kanan) --}}
                        <button @click="send()"
                                :disabled="sending || (!newMessage.trim() && !selectedFile)"
                                class="shrink-0 self-end mb-1 sm:mb-1.5 w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center transition-all duration-200"
                                :class="(sending || (!newMessage.trim() && !selectedFile))
                                    ? 'cursor-not-allowed bg-gray-200 text-gray-400'
                                    : 'bg-[#01458E] text-white hover:opacity-90 hover:scale-105 active:scale-95 shadow-md'">
                            <svg x-show="!sending" class="w-4 h-4 sm:w-4.5 sm:h-4.5 translate-x-[1px] translate-y-[1px]" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/>
                            </svg>
                            <svg x-show="sending" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" style="display:none;">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="hidden sm:block text-[10px] text-gray-400 text-center mt-2 font-medium">
                        Tekan <kbd class="px-1 py-0.5 rounded bg-gray-100 border border-gray-200">Enter</kbd> untuk kirim, <kbd class="px-1 py-0.5 rounded bg-gray-100 border border-gray-200">Shift + Enter</kbd> untuk baris baru.
                    </p>
                </div>
            </div>
            {{-- end right panel --}}

        </div>
        {{-- end grid --}}

    </div>
    {{-- end content --}}

</div>
{{-- end main wrapper --}}

</body>
</html>
