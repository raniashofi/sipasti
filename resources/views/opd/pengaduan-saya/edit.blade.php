<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Revisi Tiket — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }
        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }
        .field-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color .15s;
        }
        .field-input:focus { border-color: #01458E; box-shadow: 0 0 0 3px rgba(1,69,142,.08); }
        .field-textarea { min-height: 90px; resize: vertical; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="flex-1 max-w-2xl w-full mx-auto px-5 py-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-6">
        <a href="{{ route('opd.tiket.index') }}" class="hover:text-[#01458E] transition-colors">Pengaduan Saya</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <a href="{{ route('opd.tiket.show', $tiket->id) }}" class="hover:text-[#01458E] transition-colors">Detail Tiket</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
        <span class="text-gray-600 font-medium">Revisi Tiket</span>
    </div>

    {{-- Alasan Revisi dari Admin --}}
    @php
        $alasanRevisi = $tiket->latestStatus?->catatan;
    @endphp
    @if($alasanRevisi)
    <div class="mb-6 flex gap-3 px-4 py-4 bg-amber-50 border border-amber-200 rounded-2xl">
        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
        <div>
            <p class="text-xs font-bold text-amber-700 mb-0.5">Catatan dari Admin Helpdesk</p>
            <p class="text-sm text-amber-800">{{ $alasanRevisi }}</p>
        </div>
    </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FEF3C7;">
                    <svg class="w-5 h-5" style="color:#D97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-mono text-[#01458E] font-bold">#{{ $tiket->id }}</p>
                    <h1 class="text-base font-bold text-gray-900">Revisi Pengaduan</h1>
                </div>
                <span class="ml-auto text-xs font-bold px-3 py-1 rounded-full" style="background:#FEF3C7;color:#D97706;">Perlu Revisi</span>
            </div>
        </div>

        {{-- Form Body --}}
        <form action="{{ route('opd.tiket.update', $tiket->id) }}" method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Subjek Masalah --}}
            <div>
                <label class="field-label" for="subjek_masalah">Subjek Masalah <span class="text-red-500">*</span></label>
                <input type="text" id="subjek_masalah" name="subjek_masalah"
                       value="{{ old('subjek_masalah', $tiket->subjek_masalah) }}"
                       class="field-input @error('subjek_masalah') border-red-400 @enderror"
                       placeholder="Judul singkat masalah Anda" required>
                @error('subjek_masalah')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Detail Masalah --}}
            <div>
                <label class="field-label" for="detail_masalah">Kronologi & Detail Masalah <span class="text-red-500">*</span></label>
                <textarea id="detail_masalah" name="detail_masalah"
                          class="field-input field-textarea @error('detail_masalah') border-red-400 @enderror"
                          placeholder="Jelaskan masalah secara lengkap..." required>{{ old('detail_masalah', $tiket->detail_masalah) }}</textarea>
                @error('detail_masalah')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Spesifikasi Perangkat --}}
            <div>
                <label class="field-label" for="spesifikasi_perangkat">Spesifikasi Perangkat</label>
                <textarea id="spesifikasi_perangkat" name="spesifikasi_perangkat"
                          class="field-input field-textarea"
                          placeholder="Contoh: PC Dell OptiPlex, RAM 8GB, Windows 10">{{ old('spesifikasi_perangkat', $tiket->spesifikasi_perangkat) }}</textarea>
            </div>

            {{-- Lokasi --}}
            <div>
                <label class="field-label" for="lokasi">Lokasi Fisik Perangkat</label>
                <textarea id="lokasi" name="lokasi"
                          class="field-input"
                          style="min-height:60px;resize:vertical;"
                          placeholder="Contoh: Lantai 2, Ruang Keuangan, Meja 3">{{ old('lokasi', $tiket->lokasi) }}</textarea>
            </div>

            {{-- Foto Bukti --}}
            @php $fotosLama = is_array($tiket->foto_bukti) ? array_values(array_filter($tiket->foto_bukti)) : []; @endphp
            <div x-data="{
                    photos: [],
                    addFiles(files) {
                        for (const file of files) {
                            if (this.photos.length >= 5) { alert('Maksimal 5 foto.'); break; }
                            if (file.size > 5 * 1024 * 1024) { alert('File \'' + file.name + '\' melebihi 5 MB, dilewati.'); continue; }
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
                }">
                <label class="field-label">
                    Foto Bukti
                    <span class="text-xs font-normal text-gray-400 ml-1">(unggah foto baru untuk mengganti semua foto lama — maks. 5 foto, 5 MB per foto)</span>
                </label>

                {{-- Input target DataTransfer --}}
                <input type="file" name="foto_bukti[]" multiple x-ref="mainInput" class="sr-only" tabindex="-1">

                {{-- Foto saat ini --}}
                @if(count($fotosLama) > 0)
                <div class="mb-3">
                    <p class="text-[11px] text-gray-400 mb-1.5">Foto saat ini ({{ count($fotosLama) }} foto):</p>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                        @foreach($fotosLama as $idx => $foto)
                        <div class="relative aspect-square">
                            <img src="{{ Storage::url($foto) }}" alt="Foto {{ $idx+1 }}"
                                 class="w-full h-full object-cover rounded-xl border border-gray-200 shadow-sm">
                            <span class="absolute bottom-1 left-1.5 text-[9px] bg-black/50 text-white px-1.5 py-0.5 rounded-md font-bold">{{ $idx+1 }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Tombol aksi --}}
                <div class="flex gap-2 mb-2">
                    <label :class="photos.length >= 5 ? 'opacity-40 pointer-events-none' : 'hover:border-[#01458E] cursor-pointer'"
                           class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200 rounded-xl transition-colors shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Pilih dari Galeri</span>
                        <input type="file" accept="image/jpeg,image/png,image/jpg" multiple class="sr-only"
                               @change="addFiles($event.target.files); $event.target.value = ''">
                    </label>
                    <label :class="photos.length >= 5 ? 'opacity-40 pointer-events-none' : 'hover:border-[#01458E] cursor-pointer'"
                           class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200 rounded-xl transition-colors shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Ambil Foto</span>
                        <input type="file" accept="image/jpeg,image/png,image/jpg" capture="environment" class="sr-only"
                               @change="addFiles($event.target.files); $event.target.value = ''">
                    </label>
                </div>

                <p x-show="photos.length > 0"
                   class="text-[11px] mb-2"
                   :class="photos.length >= 5 ? 'text-amber-500 font-semibold' : 'text-gray-400'"
                   x-text="photos.length + '/5 foto baru dipilih'"></p>

                <template x-if="photos.length > 0">
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-2">
                        <template x-for="(p, i) in photos" :key="i">
                            <div class="relative group aspect-square">
                                <img :src="p.preview" class="w-full h-full object-cover rounded-xl border border-green-200 shadow-sm">
                                <span class="absolute top-1 left-1.5 text-[9px] bg-green-500/80 text-white px-1.5 py-0.5 rounded-md font-bold">Baru</span>
                                <button type="button" @click="removePhoto(i)"
                                        class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center shadow
                                               opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <template x-if="photos.length < 5">
                            <label class="aspect-square border-2 border-dashed border-gray-200 rounded-xl flex flex-col items-center justify-center cursor-pointer hover:border-[#01458E] hover:bg-blue-50 transition-colors">
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

                @error('foto_bukti')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
                @error('foto_bukti.*')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('opd.tiket.show', $tiket->id) }}"
                   class="flex-1 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 text-center transition-all">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl text-sm font-bold text-white hover:opacity-90 transition-all"
                        style="background:#01458E;">
                    Kirim Ulang Pengaduan
                </button>
            </div>
        </form>
    </div>

</main>

</body>
</html>
