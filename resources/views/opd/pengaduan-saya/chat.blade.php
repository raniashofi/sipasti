<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chat — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

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

        /* Scrollbar tipis untuk area pesan */
        .msg-scroll::-webkit-scrollbar { width: 4px; }
        .msg-scroll::-webkit-scrollbar-track { background: transparent; }
        .msg-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
    </style>
</head>
<body class="h-screen flex flex-col overflow-hidden">

@php
    $isAdmin   = $type === 'admin';
    $chatTitle = $isAdmin ? 'Admin Helpdesk' : 'Teknisi';
    $myUserId  = Auth::id();
    $myName    = Auth::user()->opd?->nama_opd ?? 'OPD';
@endphp

{{-- ── Top Bar ── --}}
<div class="shrink-0 shadow-sm border-b border-gray-100 bg-white">
    @include('layouts.topBarOpd')
</div>

{{-- ── Chat Layout ── --}}
<div class="flex-1 overflow-hidden max-w-screen-lg w-full mx-auto px-5 md:px-8 py-6 flex flex-col gap-4">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 shrink-0">
        <a href="{{ route('opd.tiket.index') }}" class="hover:text-[#01458E] transition-colors">Pengaduan Saya</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <a href="{{ route('opd.tiket.show', $tiket->id) }}" class="hover:text-[#01458E] transition-colors">
            Detail Tiket #{{ $tiket->id }}
        </a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium">Chat {{ $chatTitle }}</span>
    </div>

    {{-- Main Grid --}}
    <div class="flex-1 overflow-hidden grid grid-cols-1 lg:grid-cols-5 gap-5">

        {{-- ── LEFT: Detail Pengaduan ── --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-y-auto msg-scroll">
            <div class="px-6 py-4 border-b border-gray-100">
                <p class="text-xs font-bold" style="color:#01458E;">#{{ $tiket->id }}</p>
                <h2 class="text-sm font-bold text-gray-900 mt-0.5">Detail Pengaduan</h2>
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

                @if($tiket->foto_bukti)
                <div>
                    <label class="field-label">Unggah Foto Bukti</label>
                    <img src="{{ Storage::url($tiket->foto_bukti) }}"
                         alt="Foto Bukti"
                         class="w-full max-h-48 object-cover rounded-xl border border-gray-200">
                </div>
                @endif

            </div>
        </div>

        {{-- ── RIGHT: Chat Area ── --}}
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden"
             x-data="{
                messages: {{ json_encode($messages) }},
                newMessage: '',
                selectedFile: null,
                fileName: '',
                previewUrl: null,
                sending: false,
                myId: '{{ $myUserId }}',
                roomId: '{{ $room->id }}',
                type: '{{ $type }}',

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
                    fd.append('type', this.type);
                    if (this.newMessage.trim()) fd.append('konten', this.newMessage.trim());
                    if (this.selectedFile) fd.append('file', this.selectedFile);

                    try {
                        const res = await window.axios.post(
                            '{{ route('opd.tiket.chat.send', $tiket->id) }}',
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
            <div class="px-6 py-4 border-b border-gray-100 shrink-0 flex items-center gap-3">
                <a href="{{ route('opd.tiket.show', $tiket->id) }}"
                   class="text-gray-400 hover:text-[#01458E] transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                    </svg>
                </a>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     style="background:#EEF3F9;">
                    @if($isAdmin)
                    <svg class="w-4.5 h-4.5" style="color:#01458E;width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
                    </svg>
                    @else
                    <svg class="w-4.5 h-4.5" style="color:#01458E;width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                    </svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-900">{{ $chatTitle }}</p>
                    <p class="text-xs text-gray-400">Tiket #{{ $tiket->id }}</p>
                </div>
                <div class="ml-auto flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-xs text-gray-400">Online</span>
                </div>
            </div>

            {{-- Messages Area --}}
            <div x-ref="msgs"
                 class="flex-1 overflow-y-auto msg-scroll px-5 py-4 space-y-3">

                {{-- Empty state --}}
                <template x-if="messages.length === 0">
                    <div class="flex flex-col items-center justify-center h-full py-12 text-center">
                        <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-500">Belum ada pesan</p>
                        <p class="text-xs text-gray-400 mt-1">Mulai percakapan dengan {{ $chatTitle }}</p>
                    </div>
                </template>

                {{-- Message Bubbles --}}
                <template x-for="(msg, idx) in messages" :key="msg.id">
                    <div :class="msg.sender_id === myId ? 'flex justify-end' : 'flex justify-start'">
                        <div class="max-w-[72%]">

                            {{-- Nama pengirim (hanya tampil jika bukan pesan sendiri) --}}
                            <p x-show="msg.sender_id !== myId"
                               class="text-[10px] font-semibold text-gray-500 mb-1 ml-1"
                               x-text="msg.sender_name"></p>

                            {{-- Bubble --}}
                            <div :class="msg.sender_id === myId
                                    ? 'rounded-2xl rounded-br-sm px-4 py-2.5 text-white text-sm leading-relaxed'
                                    : 'rounded-2xl rounded-bl-sm px-4 py-2.5 bg-gray-100 text-gray-800 text-sm leading-relaxed'"
                                 :style="msg.sender_id === myId ? 'background:#01458E;' : ''">

                                {{-- Gambar --}}
                                <template x-if="msg.tipe_konten === 'image' && msg.file_url">
                                    <div>
                                        <img :src="msg.file_url" :alt="msg.sender_name"
                                             class="rounded-xl max-w-full max-h-56 object-cover cursor-pointer"
                                             @click="window.open(msg.file_url, '_blank')">
                                        <p x-show="msg.konten" x-text="msg.konten"
                                           class="mt-2 text-sm leading-relaxed"></p>
                                    </div>
                                </template>

                                {{-- Teks biasa --}}
                                <template x-if="msg.tipe_konten === 'text'">
                                    <p x-text="msg.konten" class="whitespace-pre-wrap break-words"></p>
                                </template>

                            </div>

                            {{-- Timestamp --}}
                            <p :class="msg.sender_id === myId ? 'text-right mr-1' : 'ml-1'"
                               class="text-[10px] text-gray-400 mt-1"
                               x-text="msg.created_at"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Image Preview --}}
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

            {{-- Input Bar --}}
            <div class="px-4 py-3 border-t border-gray-100 shrink-0">
                <div class="flex items-end gap-2 bg-gray-50 border border-gray-200 rounded-2xl px-4 py-2.5">

                    {{-- Textarea --}}
                    <textarea x-model="newMessage"
                              @keydown.enter="handleEnter($event)"
                              placeholder="Tulis pesan..."
                              rows="1"
                              class="flex-1 bg-transparent text-sm text-gray-800 resize-none focus:outline-none placeholder-gray-400 max-h-32"
                              style="line-height:1.5;"></textarea>

                    {{-- Send button --}}
                    <button @click="send()"
                            :disabled="sending || (!newMessage.trim() && !selectedFile)"
                            :class="(sending || (!newMessage.trim() && !selectedFile))
                                ? 'text-gray-300 cursor-not-allowed'
                                : 'hover:scale-110 active:scale-95'"
                            class="transition-transform shrink-0 p-1">
                        <svg x-show="!sending" class="w-5 h-5" fill="none" stroke="currentColor"
                             stroke-width="2.5" viewBox="0 0 24 24"
                             :style="(!newMessage.trim() && !selectedFile) ? 'color:#D1D5DB;' : 'color:#01458E;'">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        <svg x-show="sending" class="w-5 h-5 animate-spin text-[#01458E]"
                             fill="none" viewBox="0 0 24 24" style="display:none;">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>

                    {{-- Image upload --}}
                    <label class="shrink-0 cursor-pointer text-gray-400 hover:text-[#01458E] transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                        <input type="file" x-ref="fileInput"
                               accept="image/jpg,image/jpeg,image/png"
                               class="sr-only"
                               @change="handleFile($event)">
                    </label>

                </div>
                <p class="text-[10px] text-gray-400 text-center mt-1.5">
                    Enter untuk kirim &bull; Shift+Enter untuk baris baru
                </p>
            </div>

        </div>
        {{-- end right panel --}}

    </div>
    {{-- end grid --}}

</div>
{{-- end main --}}

</body>
</html>
