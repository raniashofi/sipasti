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
        $alasanRevisi = $tiket->statusTiket->where('status_tiket', 'perlu_revisi')->last()?->catatan;
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
            <div>
                <label class="field-label">Foto Bukti</label>
                @if($tiket->foto_bukti)
                <div class="mb-3 relative">
                    <img src="{{ Storage::url($tiket->foto_bukti) }}" alt="Foto saat ini"
                         class="w-full max-h-48 object-cover rounded-xl border border-gray-200">
                    <span class="absolute top-2 left-2 text-[10px] font-bold px-2 py-0.5 rounded-md bg-black/40 text-white">Foto saat ini</span>
                </div>
                @endif
                <label class="flex flex-col items-center gap-2 px-4 py-5 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-[#01458E] hover:bg-blue-50 transition-all"
                       x-data="{ name: '' }">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                    </svg>
                    <p class="text-sm text-gray-500" x-text="name || '{{ $tiket->foto_bukti ? 'Ganti foto (opsional)' : 'Upload foto bukti (opsional)' }}'"></p>
                    <p class="text-xs text-gray-400">JPG, JPEG, PNG — Maks. 10MB</p>
                    <input type="file" name="foto_bukti" accept="image/jpg,image/jpeg,image/png"
                           class="sr-only"
                           @change="name = $event.target.files[0]?.name || ''">
                </label>
                @error('foto_bukti')
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
