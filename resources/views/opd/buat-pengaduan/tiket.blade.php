<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Tiket — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
        .field-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input:focus {
            border-color: #01458E;
            box-shadow: 0 0 0 3px rgba(1,69,142,.08);
        }
        .field-input::placeholder { color: #9CA3AF; }
        .field-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
    </style>
</head>
<body class="min-h-screen">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="max-w-screen-lg mx-auto px-6 lg:px-8 py-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-7">
        <a href="{{ route('opd.diagnosis.index') }}" class="hover:text-[#01458E] transition-colors">Buat Pengaduan</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium">Formulir Pengaduan</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

        {{-- ── KIRI: ringkasan diagnosis ── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Step indicator --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-5">Progres</p>
                <div class="flex items-center">

                    {{-- Step 1 --}}
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                             style="background:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-400">Diagnosis</span>
                    </div>

                    <div class="flex-1 h-0.5 mx-1" style="background:#01458E;"></div>

                    {{-- Step 2 --}}
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white"
                             style="background:#01458E;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-medium text-gray-400">Solusi</span>
                    </div>

                    <div class="flex-1 h-0.5 mx-1 bg-gray-200 relative">
                        <div class="absolute inset-y-0 left-0 w-full bg-gray-200 rounded"></div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex flex-col items-center gap-1.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold border-2"
                             style="border-color:#01458E; color:#01458E; background:#EEF3F9;">3</div>
                        <span class="text-[10px] font-semibold" style="color:#01458E;">Tiket</span>
                    </div>

                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 px-6 py-5">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-4">Ringkasan Diagnosis</p>
                <div class="space-y-3">

                    @if($kategoriNama)
                    <div class="flex items-start gap-3 p-3 bg-[#EEF3F9] rounded-xl">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                             style="background:#01458E;">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-[#01458E] uppercase tracking-wide mb-0.5">Kategori</p>
                            <p class="text-sm font-semibold text-gray-800">{{ $kategoriNama }}</p>
                        </div>
                    </div>
                    @endif

                    @php
                        $r = $rekomendasi ?? 'admin';
                        $pStyle = match($r) {
                            'eskalasi' => ['bg'=>'#FEE2E2','text'=>'#DC2626','label'=>'Perlu Dieskalasi ke Tim Teknis'],
                            default    => ['bg'=>'#DBEAFE','text'=>'#1D4ED8','label'=>'Dapat Ditangani Admin'],
                        };
                    @endphp
                </div>
            </div>

            {{-- Info box --}}
            <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                <p class="text-xs text-amber-800 leading-relaxed">
                    Tiket Anda akan diverifikasi oleh Admin Helpdesk. Pastikan informasi yang diisi lengkap dan akurat.
                </p>
            </div>

        </div>

        {{-- ── KANAN: formulir ── --}}
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Form header --}}
            <div class="px-7 py-5 border-b border-gray-100"
                 style="background:linear-gradient(135deg,#EEF3F9 0%,#fff 100%);">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#01458E;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Formulir Pengaduan</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Isi detail masalah Anda dengan lengkap dan akurat</p>
                    </div>
                </div>
            </div>

            {{-- Form body --}}
            <form method="POST" action="{{ route('opd.diagnosis.tiket.store') }}" enctype="multipart/form-data"
                x-data="{
                    photos: [],
                    addFiles(files) {
                        for (const file of files) {
                            if (this.photos.length >= 5) { alert('Maksimal 5 foto yang dapat diunggah.'); break; }
                            if (file.size > 5 * 1024 * 1024) { alert('File \'' + file.name + '\' melebihi batas 5 MB, dilewati.'); continue; }
                            this.photos.push({ file, name: file.name, preview: URL.createObjectURL(file) });
                        }
                        this.rebuildInput();
                    },
                    removePhoto(i) {
                        URL.revokeObjectURL(this.photos[i].preview);
                        this.photos.splice(i, 1);
                        this.rebuildInput();
                    },
                    rebuildInput() {
                        const dt = new DataTransfer();
                        this.photos.forEach(p => dt.items.add(p.file));
                        this.$refs.mainInput.files = dt.files;
                    }
                }" class="px-7 py-6">
                @csrf
                <input type="hidden" name="kategori_id"              value="{{ $kategoriId }}">
                <input type="hidden" name="kategori_nama"            value="{{ $kategoriNama }}">
                <input type="hidden" name="kategori_deskripsi"       value="{{ $kategoriDeskripsi ?? '' }}">
                <input type="hidden" name="kb_id"                    value="{{ $kbId ?? '' }}">
                <input type="hidden" name="sop_internal_id"          value="{{ $sopInternalId ?? '' }}">
                <input type="hidden" name="bidang_id"                value="{{ $bidangId ?? '' }}">
                <input type="hidden" name="rekomendasi_penanganan"   value="{{ $rekomendasi ?? '' }}">

                <div class="space-y-5">

                    {{-- Subjek --}}
                    <div>
                        <label class="field-label">
                            Subjek Masalah
                            <span class="text-red-400 ml-0.5">*</span>
                        </label>
                        <input type="text" name="subjek_masalah" required
                               class="field-input"
                               placeholder="Contoh: Internet mati di Ruang 2"
                               value="{{ old('subjek_masalah') }}">
                        @error('subjek_masalah')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kronologi --}}
                    <div>
                        <label class="field-label">
                            Kronologi & Detail Masalah
                            <span class="text-red-400 ml-0.5">*</span>
                        </label>
                        <textarea name="detail_masalah" rows="4" required
                                  class="field-input resize-none"
                                  placeholder="Ceritakan kapan masalah terjadi, apa yang sudah dicoba, dan bagaimana kondisi sekarang...">{{ old('detail_masalah') }}</textarea>
                        @error('detail_masalah')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Spesifikasi --}}
                    <div>
                        <label class="field-label">Spesifikasi Perangkat</label>
                        <input type="text" name="spesifikasi_perangkat"
                               class="field-input"
                               placeholder="Contoh: PC Dell OptiPlex, Windows 11"
                               value="{{ old('spesifikasi_perangkat') }}">
                    </div>

                    {{-- Lokasi --}}
                    <div>
                        <label class="field-label">
                            Lokasi Fisik Perangkat
                            <span class="text-xs font-normal text-gray-400 ml-1">(kosongkan jika masalah website/aplikasi)</span>
                        </label>
                        <textarea name="lokasi" rows="2"
                                  class="field-input resize-none"
                                  placeholder="Contoh: Gedung B, Lantai 2, Ruang Rapat Utama">{{ old('lokasi') }}</textarea>
                    </div>

                    {{-- Upload Multi Foto --}}
                    <div>
                        <label class="field-label">
                            Unggah Foto Bukti
                            <span class="text-xs font-normal text-gray-400 ml-1">(maks. 5 foto, maks. 5 MB per foto)</span>
                        </label>

                        {{-- Input tersembunyi sebagai target DataTransfer --}}
                        <input type="file" name="foto_bukti[]" multiple x-ref="mainInput" class="sr-only" tabindex="-1">

                        {{-- Tombol aksi --}}
                        <div class="flex gap-2 mb-3">
                            {{-- Pilih dari galeri --}}
                            <label :class="photos.length >= 5 ? 'opacity-40 pointer-events-none' : 'hover:border-[#01458E] cursor-pointer'"
                                   class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200 rounded-xl transition-colors shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700">Pilih dari Galeri</span>
                                <input type="file" accept="image/jpeg,image/png,image/jpg" multiple class="sr-only"
                                       @change="addFiles($event.target.files); $event.target.value = ''">
                            </label>

                            {{-- Ambil foto dengan kamera --}}
                            <label :class="photos.length >= 5 ? 'opacity-40 pointer-events-none' : 'hover:border-[#01458E] cursor-pointer'"
                                   class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200 rounded-xl transition-colors shrink-0">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                                </svg>
                                <span class="text-sm font-semibold text-gray-700">Ambil Foto</span>
                                <input type="file" accept="image/jpeg,image/png,image/jpg" capture="environment" class="sr-only"
                                       @change="addFiles($event.target.files); $event.target.value = ''">
                            </label>
                        </div>

                        {{-- Penghitung foto --}}
                        <p x-show="photos.length > 0"
                           class="text-[11px] mb-2"
                           :class="photos.length >= 5 ? 'text-amber-500 font-semibold' : 'text-gray-400'"
                           x-text="photos.length + '/5 foto dipilih' + (photos.length >= 5 ? ' — batas maksimal tercapai' : '')"></p>

                        {{-- Grid preview --}}
                        <template x-if="photos.length > 0">
                            <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                <template x-for="(p, i) in photos" :key="i">
                                    <div class="relative group aspect-square">
                                        <img :src="p.preview" :alt="'Foto ' + (i+1)"
                                             class="w-full h-full object-cover rounded-xl border border-gray-200 shadow-sm">
                                        {{-- Nomor urut --}}
                                        <span class="absolute bottom-1 left-1.5 text-[9px] bg-black/50 text-white px-1.5 py-0.5 rounded-md font-bold"
                                              x-text="i + 1"></span>
                                        {{-- Hapus --}}
                                        <button type="button" @click="removePhoto(i)"
                                                class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow
                                                       opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                {{-- Slot tambah (muncul hanya bila < 5 foto) --}}
                                <template x-if="photos.length < 5">
                                    <label class="aspect-square border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center
                                                  cursor-pointer hover:border-[#01458E] hover:bg-blue-50 transition-colors">
                                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                        <span class="text-[10px] text-gray-400 mt-1">Tambah</span>
                                        <input type="file" accept="image/jpeg,image/png,image/jpg" multiple class="sr-only"
                                               @change="addFiles($event.target.files); $event.target.value = ''">
                                    </label>
                                </template>
                            </div>
                        </template>

                        {{-- Empty state --}}
                        <template x-if="photos.length === 0">
                            <div class="border-2 border-dashed border-gray-200 rounded-xl px-6 py-8 flex flex-col items-center gap-2 bg-gray-50">
                                <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                </svg>
                                <p class="text-sm text-gray-400">Belum ada foto yang dipilih</p>
                                <p class="text-[11px] text-gray-300">JPG, JPEG, PNG — Maks. 5 MB per foto</p>
                            </div>
                        </template>

                        @error('foto_bukti')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                        @error('foto_bukti.*')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                {{-- Submit --}}
                <div class="mt-7 pt-5 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                            class="flex items-center gap-2.5 px-8 py-3 rounded-xl text-white text-sm font-bold
                                   transition hover:-translate-y-0.5 hover:shadow-lg active:scale-95"
                            style="background:#01458E;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                        </svg>
                        Kirim Tiket
                    </button>
                </div>

            </form>
        </div>

    </div>
</main>
</body>
</html>
