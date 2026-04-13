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
                x-data="{ fileName: '', imagePreview: null }" class="px-7 py-6">
                @csrf
                <input type="hidden" name="kategori_id"        value="{{ $kategoriId }}">
                <input type="hidden" name="kategori_nama"      value="{{ $kategoriNama }}">
                <input type="hidden" name="kategori_deskripsi" value="{{ $kategoriDeskripsi ?? '' }}">
                <input type="hidden" name="kb_id"              value="{{ $kbId ?? '' }}">

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

                    {{-- Upload --}}
                    {{-- Upload --}}
                    <div>
                        <label class="field-label">Unggah Foto Bukti</label>
                        <div class="flex flex-col gap-4">

                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-gray-200
                                             hover:border-[#01458E] rounded-xl cursor-pointer transition-colors shrink-0">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>

                                    {{-- Teks berubah otomatis berdasarkan fileName --}}
                                    <span class="text-sm font-semibold text-gray-700" x-text="fileName ? 'Ganti File' : 'Pilih File'"></span>

                                    <input type="file" name="foto_bukti" accept="image/jpeg,image/png,image/jpg"
                                           class="hidden"
                                           @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    fileName = file.name;
                                                    imagePreview = URL.createObjectURL(file);
                                                } else {
                                                    fileName = '';
                                                    imagePreview = null;
                                                }
                                           ">
                                </label>
                                <div>
                                    <p x-show="!fileName" class="text-sm font-medium text-[#01458E]">Belum ada file dipilih</p>
                                    <p x-show="fileName"  class="text-sm font-medium text-gray-700 max-w-[180px] truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">JPG, JPEG, PNG — Maks. 10 MB</p>
                                </div>
                            </div>

                            {{-- Preview Gambar --}}
                            <div x-show="imagePreview" class="relative mt-2" style="display: none;">
                                <p class="text-xs text-gray-500 mb-2 font-medium">Pratinjau Gambar:</p>
                                <div class="relative inline-block">
                                    <img :src="imagePreview" alt="Preview Bukti" class="h-32 w-auto object-cover rounded-xl border-2 border-gray-100 shadow-sm">

                                    {{-- Tombol hapus preview (Opsional, tapi bikin UI makin keren) --}}
                                    <button type="button" @click="fileName = ''; imagePreview = null; $refs.fileInput.value = ''"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 shadow-md hover:bg-red-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
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
