<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Tiket — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        .form-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            color: #111827;
            background: #fff;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .form-input:focus {
            border-color: #01458E;
            box-shadow: 0 0 0 3px rgba(1,69,142,0.10);
        }
        .form-input::placeholder { color: #9CA3AF; }

        .form-label {
            display: block;
            font-size: 13px;
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
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

            {{-- LEFT: Detail Diagnosis (2 cols) --}}
            <div class="lg:col-span-2">
                <h2 class="text-xl font-bold mb-5" style="color:#01458E;">Detail Diagnosis</h2>

                {{-- Step indicator --}}
                <div class="flex items-center gap-2 mb-7">
                    {{-- Step 1 --}}
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white"
                             style="background-color:#01458E;">1</div>
                        <span class="text-[10px] font-medium text-gray-500">Diagnosis</span>
                    </div>
                    <div class="flex-1 border-t-2 border-dashed mb-4" style="border-color:#01458E;"></div>
                    {{-- Step 2 --}}
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white"
                             style="background-color:#01458E;">2</div>
                        <span class="text-[10px] font-medium text-gray-500">Solusi</span>
                    </div>
                    <div class="flex-1 border-t-2 border-dashed mb-4 border-gray-300"></div>
                    {{-- Step 3 (active) --}}
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold border-2"
                             style="border-color:#01458E; color:#01458E; background:#fff;">3</div>
                        <span class="text-[10px] font-semibold" style="color:#01458E;">Buat Tiket</span>
                    </div>
                </div>

                {{-- Diagnosis summary --}}
                <div class="space-y-2">
                    @if($kategoriNama)
                    <div class="flex items-center gap-3 bg-[#EEF3F9] rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 shrink-0" style="color:#01458E;" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold">Kategori:</span> {{ $kategoriNama }}
                        </p>
                    </div>
                    @endif

                    @if($diagnosa)
                    <div class="flex items-center gap-3 bg-[#EEF3F9] rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 shrink-0" style="color:#01458E;" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                        <p class="text-sm text-gray-700">
                            <span class="font-semibold">Diagnosa:</span> {{ $diagnosa }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- RIGHT: Formulir Pengaduan (3 cols) --}}
            <div class="lg:col-span-3 bg-[#EEF3F9] rounded-2xl p-7">

                <h2 class="text-xl font-bold mb-1" style="color:#01458E;">Formulir Pengaduan</h2>
                <p class="text-sm text-gray-500 mb-6 leading-relaxed">
                    Silahkan lengkapi formulir berikut untuk mengajukan pengaduan layanan IT.
                    Pastikan data yang Anda isi detail dan akurat untuk mempercepat proses verifikasi oleh Admin Helpdesk.
                </p>

                <form method="POST" action="#" enctype="multipart/form-data"
                      x-data="{ fileName: '' }">
                    @csrf

                    {{-- Hidden fields dari diagnosis --}}
                    <input type="hidden" name="kategori_id"   value="{{ $kategoriId }}">
                    <input type="hidden" name="kategori_nama" value="{{ $kategoriNama }}">
                    <input type="hidden" name="diagnosa"      value="{{ $diagnosa }}">

                    <div class="space-y-5">

                        {{-- Subjek Masalah --}}
                        <div>
                            <label class="form-label">Subjek Masalah</label>
                            <input type="text" name="subjek_masalah"
                                   class="form-input"
                                   placeholder="Contoh: Website E-Office Error"
                                   value="{{ old('subjek_masalah') }}"
                                   required>
                        </div>

                        {{-- Kronologi & Detail --}}
                        <div>
                            <label class="form-label">Kronologi & Detail Masalah</label>
                            <textarea name="detail_masalah" rows="4"
                                      class="form-input resize-none"
                                      placeholder="Contoh: Saat membuka website E-Office pukul 09.00 WIB, muncul pesan error '500 Internal Server Error'. Saya sudah mencoba refresh halaman dan ganti browser tapi tetap tidak bisa dibuka."
                                      required>{{ old('detail_masalah') }}</textarea>
                        </div>

                        {{-- Spesifikasi Perangkat --}}
                        <div>
                            <label class="form-label">Spesifikasi Perangkat</label>
                            <input type="text" name="spesifikasi_perangkat"
                                   class="form-input"
                                   placeholder="Contoh: PC Dell"
                                   value="{{ old('spesifikasi_perangkat') }}">
                        </div>

                        {{-- Lokasi Fisik --}}
                        <div>
                            <label class="form-label">
                                Lokasi Fisik Perangkat
                                <span class="font-normal text-gray-400">(kosongkan jika masalah website/aplikasi)</span>
                            </label>
                            <textarea name="lokasi" rows="2"
                                      class="form-input resize-none"
                                      placeholder="Contoh: Gedung B Balaikota, Lantai 2, Ruang Rapat Utama (Dekat Jendela).">{{ old('lokasi') }}</textarea>
                        </div>

                        {{-- Upload Foto Bukti --}}
                        <div>
                            <label class="form-label">Unggah Foto Bukti</label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 bg-white border-2 border-gray-200 hover:border-[#01458E]
                                              rounded-xl px-4 py-2.5 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                         stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-700">Upload</span>
                                    <input type="file" name="foto_bukti" accept="image/jpeg,image/png"
                                           class="hidden"
                                           @change="fileName = $event.target.files[0]?.name ?? ''">
                                </label>
                                <div>
                                    <p x-show="!fileName" class="text-sm font-medium" style="color:#01458E;">Choose Images</p>
                                    <p x-show="fileName"  class="text-sm font-medium text-gray-700" x-text="fileName"></p>
                                    <p class="text-[11px] text-gray-400 mt-0.5">JPG, JPEG, dan PNG. Max 5 MB</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Submit --}}
                    <div class="mt-7 flex justify-end">
                        <button type="submit"
                                class="flex items-center gap-2 px-8 py-3 rounded-xl text-white text-sm font-bold
                                       transition hover:-translate-y-0.5 hover:shadow-lg"
                                style="background-color:#01458E;">
                            KIRIM TIKET
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </main>
</body>
</html>
