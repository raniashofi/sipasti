<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $article ? 'Edit Artikel' : 'Tambah Artikel' }} — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }

        * { font-family: 'Inter', sans-serif; }
        textarea { resize: vertical; }

        /* Scrollbar styling */
        main { scrollbar-width: thin; scrollbar-color: #D1D5DB transparent; }
        main::-webkit-scrollbar { width: 6px; }
        main::-webkit-scrollbar-track { background: transparent; }
        main::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
        main::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }

        .modal-scroll { scrollbar-width: thin; scrollbar-color: #E5E7EB transparent; }
        .modal-scroll::-webkit-scrollbar { width: 4px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }
        .file-input { display: none; }
        .upload-zone { transition: all 0.15s ease; }
        .upload-zone:hover { border-color: #01458E; background-color: rgba(1, 69, 142, 0.02); }

        /* Quill Editor Styling */
        .ql-container { border: 1px solid #e5e7eb; font-size: 0.875rem; font-family: 'Inter', sans-serif; }
        .ql-toolbar { border: 1px solid #e5e7eb; border-radius: 0.75rem 0.75rem 0 0; background-color: #ffffff; }
        .ql-toolbar.ql-snow { border-bottom: 1px solid #e5e7eb; }
        .ql-toolbar.ql-snow .ql-formats button { color: #6b7280; }
        .ql-toolbar.ql-snow .ql-formats button:hover { color: #01458E; }
        .ql-toolbar.ql-snow .ql-formats button.ql-active { color: #01458E; }
        .ql-snow.ql-toolbar button:hover, .ql-snow.ql-toolbar button.ql-active { color: #01458E; }
        .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke { stroke: #01458E; }

        /* Quill Toolbar Tooltip */
        .ql-toolbar button[data-tooltip]::after {
            content: attr(data-tooltip);
            position: absolute;
            background: #1f2937;
            color: #ffffff;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            top: calc(100% + 8px);
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
            z-index: 1000;
        }
        .ql-toolbar button[data-tooltip]::before {
            content: '';
            position: absolute;
            background: #1f2937;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            top: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%) rotate(45deg);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.15s;
            z-index: 1000;
        }
        .ql-toolbar button[data-tooltip]:hover::after,
        .ql-toolbar button[data-tooltip]:hover::before { opacity: 1; }

        .ql-editor { min-height: 400px; line-height: 1.8; }
        .ql-editor.ql-blank::before { color: #d1d5db; font-style: normal; }
        .quill-wrapper { border-radius: 0.75rem; overflow: hidden; border: 1px solid #e5e7eb; background-color: #ffffff; }

        /* Preview Content Styling */
        #previewContent { font-size: 0.875rem; line-height: 1.8; }
        #previewContent h1, #previewContent h2, #previewContent h3, #previewContent h4, #previewContent h5, #previewContent h6 { font-weight: 700; margin: 1rem 0 0.5rem; }
        #previewContent h1 { font-size: 1.75rem; }
        #previewContent h2 { font-size: 1.5rem; }
        #previewContent h3 { font-size: 1.25rem; }
        #previewContent p { margin-bottom: 0.75rem; }
        #previewContent strong { font-weight: 700; }
        #previewContent em { font-style: italic; }
        #previewContent u { text-decoration: underline; }
        #previewContent s { text-decoration: line-through; }
        #previewContent a { color: #01458E; text-decoration: none; border-bottom: 1px solid #01458E; }
        #previewContent a:hover { color: #003a70; }
        #previewContent ul, #previewContent ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
        #previewContent li { margin-bottom: 0.25rem; }
        #previewContent blockquote { border-left: 4px solid #01458E; padding-left: 1rem; margin: 1rem 0; color: #6b7280; font-style: italic; }
        #previewContent code { background: #f3f4f6; color: #01458E; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.8rem; }
        #previewContent pre { background: #1f2937; color: #f3f4f6; padding: 0.75rem; border-radius: 0.375rem; overflow-x: auto; margin: 0.75rem 0; font-size: 0.75rem; }
        #previewContent pre code { background: transparent; color: inherit; padding: 0; }
        #previewContent img, #previewContent video { max-width: 100%; height: auto; border-radius: 0.375rem; margin: 0.75rem 0; }
        #previewContent table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; }
        #previewContent th, #previewContent td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: left; font-size: 0.75rem; }
        #previewContent ul { list-style-type: disc; }
        #previewContent ol { list-style-type: decimal; }
        #previewContent ul ul { list-style-type: circle; }
        #previewContent ul ul ul { list-style-type: square; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    @php
        $isEdit      = $article !== null;
        $isInternal  = ($article?->visibilitas_akses ?? $visibility) === 'internal';
        $listRoute   = $isInternal ? route('super_admin.pustaka.internal') : route('super_admin.pustaka.opd');
        $formAction  = $isEdit
            ? route('super_admin.pustaka.update', $article->id)
            : route('super_admin.pustaka.store');
        $tagsCurrent = $isEdit
            ? $article->tags->pluck('nama_tag')->implode(', ')
            : '';
        $kodeArtikel = $isEdit
            ? 'KB-' . strtoupper(substr($article->id, 0, 8))
            : '—';

        // Pindahkan Data PHP ke JS Variable agar tidak merusak atribut HTML (Menghindari String Breaking)
        $initialTags = $isEdit && $article->tags->count() ? $article->tags->pluck('nama_tag')->values()->toArray() : [];
        $initialNama = old('nama_artikel_sop', $article?->nama_artikel_sop ?? '');
        $initialDesc = old('deskripsi_singkat', $article?->deskripsi_singkat ?? '');
        $initialFile = $article?->lampiran_file ? basename($article->lampiran_file) : '';
    @endphp

    <script>
        window.kbData = {
            tags: {!! json_encode($initialTags) !!},
            namaArtikel: {!! json_encode($initialNama) !!},
            deskripsiSingkat: {!! json_encode($initialDesc) !!},
            lampiranFileName: {!! json_encode($initialFile) !!}
        };
    </script>


    <div class="ml-64 min-h-screen flex flex-col"
        x-data="{
            status: '{{ $article?->status_publikasi ?? 'draft' }}',
            visibility: '{{ $article?->visibilitas_akses ?? 'opd' }}',
            tags: window.kbData.tags,
            tagInput: '',
            deleteConfirmOpen: false,
            previewModalOpen: false,
            headerPreview: '{{ $article?->header_image ? asset('storage/' . $article->header_image) : '' }}',
            lampiranFileName: window.kbData.lampiranFileName,
            namaArtikel: window.kbData.namaArtikel,
            deskripsiSingkat: window.kbData.deskripsiSingkat,
            init() {},
            addTag() {
                const t = this.tagInput.trim().toLowerCase().replace(/\s+/g, '-');
                if (t && !this.tags.includes(t)) this.tags.push(t);
                this.tagInput = '';
            },
            removeTag(i) { this.tags.splice(i, 1); },
            onHeaderChange(e) {
                const file = e.target.files[0];
                if (file) this.headerPreview = URL.createObjectURL(file);
            },
            onLampiranChange(e) {
                const file = e.target.files[0];
                if (file) this.lampiranFileName = file.name;
            },
            openPreviewModal() {
                this.namaArtikel = document.querySelector('input[name=nama_artikel_sop]').value;
                this.deskripsiSingkat = document.querySelector('textarea[name=deskripsi_singkat]').value;
                this.previewModalOpen = true;

                // Update preview content with current Quill content
                setTimeout(() => {
                    document.getElementById('previewContent').innerHTML = quill.root.innerHTML;
                }, 50);
            },
            get tagsRaw() { return this.tags.join(','); }
        }"
        x-cloak>

        {{-- ── Header ── --}}
        <header class="bg-white border-b border-gray-100 px-6 py-3.5 flex items-center justify-between shrink-0 sticky top-0 z-30">
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ $listRoute }}" class="text-gray-400 text-xs hover:text-gray-600 transition-colors">
                    Pustaka Pengetahuan
                </a>
                <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="font-semibold text-gray-800 text-sm">
                    {{ $isEdit ? 'Edit Artikel' : 'Artikel Baru' }}
                </span>
            </div>

            <div class="flex items-center gap-2">

                {{-- Preview Selalu via Modal --}}
                <button type="button" @click="openPreviewModal()"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Pratinjau
                </button>

                {{-- Delete (edit only) --}}
                @if($isEdit)
                <button type="button" @click="deleteConfirmOpen = true"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90 bg-red-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
                @endif

                <a href="{{ $listRoute }}"
                   class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" form="form-artikel"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold text-white transition-opacity hover:opacity-90"
                        style="background-color:#16A34A;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    Simpan Artikel
                </button>
            </div>
        </header>

        {{-- ── Warning Banner (Internal) ── --}}
        <div x-show="visibility === 'internal'"
             x-transition
             class="bg-red-50 border-t border-red-200 px-6 py-3 text-sm font-semibold text-red-600 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span><strong>Mode INTERNAL</strong> — Artikel ini hanya bisa diakses oleh staf IT dan tidak akan muncul di portal OPD</span>
        </div>

        {{-- ── Main Content ── --}}
        <main class="flex-1 px-6 py-6 overflow-y-auto">

            @if($errors->any())
            <div class="mb-5 flex items-center gap-3 bg-red-50 border border-red-200 rounded-xl px-5 py-3 text-sm text-red-700">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="space-y-1">
                    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                </div>
            </div>
            @endif

            <form id="form-artikel" method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
                @csrf
                @if($isEdit) @method('PUT') @endif

                {{-- Hidden fields --}}
                <input type="hidden" name="status_publikasi"  :value="status">
                <input type="hidden" name="visibilitas_akses" :value="visibility">
                <input type="hidden" name="tags_raw"          :value="tagsRaw">

                <div class="flex gap-8 items-start">

                    {{-- ── EDITOR AREA ── --}}
                    <div class="flex-1 flex flex-col gap-6">

                        {{-- Title Input --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-8">
                            <input type="text" name="nama_artikel_sop"
                                   value="{{ old('nama_artikel_sop', $article?->nama_artikel_sop) }}"
                                   placeholder="Tulis judul artikel di sini..."
                                   class="w-full text-3xl font-bold text-gray-900 border-0 pb-4 focus:outline-none placeholder-gray-300 bg-transparent">
                        </div>

                        {{-- Deskripsi Singkat --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-8">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Deskripsi Singkat (Excerpt)</p>
                            <textarea name="deskripsi_singkat"
                                      placeholder="Ringkasan artikel untuk preview..."
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300 resize-none"
                                      rows="3">{{ old('deskripsi_singkat', $article?->deskripsi_singkat) }}</textarea>
                        </div>

                        {{-- Content Editor --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-8">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-4">Konten Artikel</p>
                            <div class="quill-wrapper">
                                <div id="editor"></div>
                            </div>
                            <input type="hidden" name="isi_konten" id="isi_konten" value="">
                        </div>

                    </div>

                    {{-- ── SIDEBAR ── --}}
                    <div class="w-80 shrink-0 space-y-4">

                        {{-- Status Publikasi --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Status Publikasi</p>
                            <div class="flex rounded-xl border border-gray-200 overflow-hidden">
                                <button type="button" @click="status = 'draft'"
                                        :class="status === 'draft' ? 'bg-yellow-400 text-white font-semibold' : 'bg-white text-gray-600'"
                                        class="flex-1 py-2.5 text-sm transition-all border-r border-gray-200 flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Draft
                                </button>
                                <button type="button" @click="status = 'published'"
                                        :class="status === 'published' ? 'bg-green-500 text-white font-semibold' : 'bg-white text-gray-600'"
                                        class="flex-1 py-2.5 text-sm transition-all flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Published
                                </button>
                            </div>
                        </div>

                        {{-- Visibility --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Visibilitas Akses</p>
                            <div class="space-y-2">
                                <label @click="visibility = 'opd'"
                                       :class="visibility === 'opd' ? 'border-[#01458E] bg-blue-50' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                       class="block rounded-xl border p-3 cursor-pointer transition-all">
                                    <p class="text-sm font-semibold flex items-center gap-1.5" :class="visibility === 'opd' ? 'text-[#01458E]' : 'text-gray-700'">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                        </svg>
                                        Publik (OPD)
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">Artikel tampil di portal OPD dan bisa dicari oleh semua pengguna</p>
                                </label>
                                <label @click="visibility = 'internal'"
                                       :class="visibility === 'internal' ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-white hover:bg-gray-50'"
                                       class="block rounded-xl border p-3 cursor-pointer transition-all">
                                    <p class="text-sm font-semibold flex items-center gap-1.5" :class="visibility === 'internal' ? 'text-red-500' : 'text-gray-700'">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Internal (Rahasia)
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">Hanya staf IT. Tersembunyi dari pencarian dan portal OPD</p>
                                </label>
                            </div>
                        </div>

                        {{-- Kategori --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Kategori</p>
                            <select name="kategori_id"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] bg-white text-gray-700">
                                <option value="">— Pilih Kategori —</option>
                                @foreach($kategoris as $k)
                                <option value="{{ $k->id }}"
                                    {{ old('kategori_id', $article?->kategori_id) === $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Header Image --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Gambar Header
                            </p>
                            <div class="mb-3" x-show="headerPreview">
                                <div class="relative">
                                    <img :src="headerPreview" class="w-full h-32 object-cover rounded-xl border border-gray-200">
                                    <button type="button" @click="headerPreview = ''; document.querySelector('input[name=header_image]').value = ''"
                                            class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <label class="block border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer upload-zone">
                                <input type="file" name="header_image" accept="image/*" @change="onHeaderChange" class="file-input">
                                <div class="mb-2 flex justify-center">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-700">Pilih Gambar Header</div>
                                <div class="text-xs text-gray-400 mt-1">PNG, JPG · Maks. 10MB</div>
                            </label>
                        </div>

                        {{-- Lampiran File --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Lampiran File</p>
                            <div x-show="lampiranFileName" class="mb-3 p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM3.293 7.293a1 1 0 011.414 0A5 5 0 0013.414 2H12a1 1 0 110-2h4.586A1.5 1.5 0 0118 1.5v4.586a1 1 0 01-2 0V3.414A5 5 0 007.293 11.707a1 1 0 01-1.414-1.414z"/>
                                    </svg>
                                    <p class="text-xs font-semibold text-gray-700" x-text="lampiranFileName"></p>
                                </div>
                                <button type="button" @click="lampiranFileName = ''; document.querySelector('input[name=lampiran_file]').value = ''"
                                        class="text-red-600 hover:text-red-700 font-semibold">
                                    ×
                                </button>
                            </div>
                            <label class="block border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer upload-zone">
                                <input type="file" name="lampiran_file" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.png" @change="onLampiranChange" class="file-input">
                                <div class="mb-2 flex justify-center">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-gray-700">Upload File Lampiran</div>
                                <div class="text-xs text-gray-400 mt-1">PDF, DOCX, PNG, JPG · Maks. 10MB</div>
                            </label>
                        </div>

                        {{-- Tags --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Tag</p>
                            <div class="flex flex-wrap gap-2 mb-3">
                                <template x-for="(tag, i) in tags" :key="i">
                                    <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full bg-blue-50 text-[#01458E] font-medium">
                                        <span x-text="tag"></span>
                                        <button type="button" @click="removeTag(i)" class="ml-0.5 text-[#01458E]/50 hover:text-[#01458E] transition-colors">×</button>
                                    </span>
                                </template>
                            </div>
                            <input type="text" x-model="tagInput"
                                   @keydown.enter.prevent="addTag()"
                                   placeholder="Ketik tag lalu Enter..."
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#01458E]/20 focus:border-[#01458E] placeholder-gray-300">
                            <p class="text-[10px] text-gray-300 mt-2">Pisahkan dengan Enter</p>
                        </div>

                        {{-- Info Artikel (edit only) --}}
                        @if($isEdit)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-50 p-5">
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest mb-3">Informasi Artikel</p>
                            <div class="grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <p class="text-gray-400 mb-1">ID Artikel</p>
                                    <p class="font-semibold text-gray-700">#{{ $kodeArtikel }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-1">Status</p>
                                    <div class="flex items-center gap-1" :class="status === 'published' ? 'text-green-600' : 'text-yellow-500'">
                                        <svg x-show="status === 'published'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg x-show="status === 'draft'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span class="font-semibold text-xs" x-text="status === 'published' ? 'Published' : 'Draft'"></span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-1">Total Views</p>
                                    <p class="font-semibold text-gray-700">{{ $article->total_views ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 mb-1">Rating</p>
                                    <p class="font-semibold text-gray-700 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        {{ $article->rating ? number_format($article->rating, 1) : '—' }}/5
                                    </p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-400 mb-1">Dibuat</p>
                                    <p class="font-semibold text-gray-700">{{ $article->created_at->format('d M Y, H:i') }} WIB</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-gray-400 mb-1">Terakhir Edit</p>
                                    <p class="font-semibold text-gray-700">{{ $article->updated_at->format('d M Y, H:i') }} WIB</p>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>{{-- end sidebar --}}

                </div>
            </form>
        </main>

        {{-- ── PREVIEW MODAL (New Article) ── --}}
        <div x-show="previewModalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="previewModalOpen = false"
             class="fixed inset-0 bg-black/45 backdrop-filter backdrop-blur-sm flex items-center justify-center z-50"
             style="display:none;"
             x-cloak>
            <div class="bg-white rounded-2xl shadow-xl max-w-3xl w-full mx-4 h-[90vh] overflow-hidden flex flex-col"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-8 py-4 border-b border-gray-200 shrink-0">
                    <h2 class="text-lg font-bold text-gray-900">Pratinjau Artikel</h2>
                    <button @click="previewModalOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Modal Content --}}
                <div class="flex-1 overflow-y-auto px-8 py-6">

                    {{-- Header Image --}}
                    <div x-show="headerPreview" class="mb-6">
                        <img :src="headerPreview" class="w-full h-32 object-cover rounded-lg border border-gray-200">
                    </div>

                    {{-- Title --}}
                    <div class="mb-6">
                        <div class="flex items-center gap-1 text-xs mb-1" :class="status === 'published' ? 'text-green-600' : 'text-yellow-500'">
                            <svg x-show="status === 'published'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <svg x-show="status === 'draft'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span x-text="status === 'published' ? 'Published' : 'Draft'"></span>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900" x-text="namaArtikel || 'Untitled Article'"></h1>
                    </div>

                    {{-- Meta Info --}}
                    <div class="mb-6 pb-6 border-b border-gray-200 text-sm">
                        <div class="flex gap-6">
                            <div>
                                <p class="text-gray-500">Visibilitas</p>
                                <div class="flex items-center gap-1 font-semibold text-gray-900">
                                    <svg x-show="visibility === 'opd'" class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    <svg x-show="visibility === 'internal'" class="w-3.5 h-3.5 text-red-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span x-text="visibility === 'opd' ? 'Publik (OPD)' : 'Internal'"></span>
                                </div>
                            </div>
                            <div>
                                <p class="text-gray-500">Status</p>
                                <div class="flex items-center gap-1 font-semibold" :class="status === 'published' ? 'text-green-600' : 'text-yellow-500'">
                                    <svg x-show="status === 'published'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <svg x-show="status === 'draft'" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span x-text="status === 'published' ? 'Published' : 'Draft'"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi Singkat --}}
                    <div x-show="deskripsiSingkat" class="mb-6 p-4 bg-blue-50 border border-blue-100 rounded-lg">
                        <p class="text-blue-900 text-sm" x-text="deskripsiSingkat"></p>
                    </div>

                    {{-- Tags --}}
                    <div x-show="tags.length > 0" class="mb-6">
                        <p class="text-gray-500 text-xs mb-2">Tags:</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in tags" :key="tag">
                                <span class="inline-block bg-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-full font-medium" x-text="tag"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Konten Artikel --}}
                    <div id="previewContent" class="prose prose-sm max-w-none mb-6"></div>

                    {{-- Lampiran File --}}
                    <div x-show="lampiranFileName" class="border-t border-gray-200 pt-6">
                        <p class="text-gray-500 text-xs mb-3">Lampiran File:</p>
                        <a :href="'{{ route("super_admin.pustaka.opd") }}?lampiran=' + lampiranFileName"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1 1 0 11-2 0 1 1 0 012 0zM15 7a2 2 0 11-4 0 2 2 0 014 0zM3.293 7.293a1 1 0 011.414 0A5 5 0 0013.414 2H12a1 1 0 110-2h4.586A1.5 1.5 0 0118 1.5v4.586a1 1 0 01-2 0V3.414A5 5 0 007.293 11.707a1 1 0 01-1.414-1.414z"/>
                            </svg>
                            <span x-text="lampiranFileName"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── DELETE MODAL ── --}}
        @if($isEdit)
        <div x-show="deleteConfirmOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="deleteConfirmOpen = false"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,.45);backdrop-filter:blur(4px);"
             x-cloak>

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
                 @click.stop>

                {{-- Modal Header --}}
                <div class="px-6 py-4 text-white rounded-t-3xl" style="background:#DC2626;border-radius:1.5rem 1.5rem 0 0;">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:rgba(255,255,255,.2);">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm">Hapus Artikel?</p>
                                <p class="text-xs mt-0.5" style="color:#FECACA;">Tindakan tidak dapat dibatalkan</p>
                            </div>
                        </div>
                        <button @click="deleteConfirmOpen = false" class="text-red-200 hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Body --}}
                <div class="p-6">
                    <p class="text-sm text-gray-700 mb-2">Anda yakin ingin menghapus artikel ini?</p>
                    <div class="px-4 py-3 rounded-xl bg-red-50 border border-red-200 mb-4">
                        <p class="text-sm font-semibold text-red-900">"{{ $article?->nama_artikel_sop }}"</p>
                        <p class="text-xs text-red-700 mt-1.5">Data yang dihapus tidak dapat dipulihkan kembali.</p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <form action="{{ route('super_admin.pustaka.destroy', $article->id) }}" method="POST" class="contents">
                    @csrf @method('DELETE')
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        <button type="button" @click="deleteConfirmOpen = false"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-white hover:opacity-90 transition-all"
                                style="background:#DC2626;">
                            Hapus Sekarang
                        </button>
                    </div>
                </form>

            </div>
        </div>
        @endif

    </div>

    {{-- Quill.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.js"></script>
    <script>
        // Hidden file input for image upload
        const imageInput = document.createElement('input');
        imageInput.type = 'file';
        imageInput.accept = 'image/*';
        imageInput.style.display = 'none';
        document.body.appendChild(imageInput);

        // Initialize Quill Editor
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Tulis isi artikel di sini...',
            modules: {
                toolbar: {
                    container: [
                        // Text formatting
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],

                        // Lists
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],

                        // Alignment
                        [{ 'align': [] }],

                        // Links and images
                        ['link', 'image'],
                        ['blockquote', 'code-block'],

                        // Reset
                        ['clean'],
                    ],
                    handlers: {
                        'image': handleImageUpload,
                        'link': handleLinkInsertion,
                    }
                }
            }
        });

        /**
         * Handle image upload - trigger file picker and upload to server
         */
        let uploadCursorIndex = 0; // Store cursor position during upload

        function handleImageUpload() {
            // Save current cursor position
            uploadCursorIndex = quill.getSelection()?.index || 0;
            imageInput.click();
        }

        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            // Show loading state at saved cursor position
            const loadingText = 'Mengunggah gambar...';
            quill.insertText(uploadCursorIndex, loadingText);

            fetch('{{ route("super_admin.pustaka.upload-image") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Delete loading text at original position (exact length)
                    quill.deleteText(uploadCursorIndex, loadingText.length);
                    // Insert image at original saved position
                    quill.insertEmbed(uploadCursorIndex, 'image', data.url);
                    // Move cursor after image
                    quill.setSelection(uploadCursorIndex + 1);
                } else {
                    alert('Gagal mengupload gambar: ' + data.message);
                    // Remove loading text on error
                    quill.deleteText(uploadCursorIndex, loadingText.length);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Terjadi kesalahan saat mengupload gambar');
                // Remove loading text on error
                quill.deleteText(uploadCursorIndex, loadingText.length);
            });

            // Reset input
            imageInput.value = '';
        });

        /**
         * Handle link insertion - prompt for URL and open in new tab
         */
        function handleLinkInsertion() {
            const url = prompt('Masukkan URL:');
            if (!url) return;

            const range = quill.getSelection();
            if (range.length === 0) {
                // If no text selected, use URL as text
                quill.insertText(range.index, url, { 'link': url, 'target': '_blank' });
            } else {
                // If text selected, make it a link
                quill.formatText(range.index, range.length, { 'link': url, 'target': '_blank' });
            }
        }

        // Add tooltips to toolbar buttons
        const tooltips = {
            'header': 'Judul (H1-H6)',
            'bold': 'Tebal (Ctrl+B)',
            'italic': 'Miring (Ctrl+I)',
            'underline': 'Garis bawah (Ctrl+U)',
            'strike': 'Coret',
            'color': 'Warna teks',
            'background': 'Warna latar',
            'ordered': 'Daftar bernomor',
            'bullet': 'Daftar poin',
            'indent-1': 'Kurangi indent',
            'indent1': 'Tambah indent',
            'align': 'Perataan',
            'link': 'Sisipkan tautan',
            'image': 'Sisipkan gambar',
            'blockquote': 'Kutipan blok',
            'code-block': 'Blok kode',
            'clean': 'Hapus format'
        };

        const toolbar = document.querySelector('.ql-toolbar');
        if (toolbar) {
            const buttons = toolbar.querySelectorAll('button');
            buttons.forEach(button => {
                let tooltipText = '';

                // Get tooltip based on button class
                for (const [key, value] of Object.entries(tooltips)) {
                    if (button.classList.contains(`ql-${key}`)) {
                        tooltipText = value;
                        break;
                    }
                }

                // Handle indent buttons specifically
                if (!tooltipText && button.classList.contains('ql-indent')) {
                    const value = button.getAttribute('value');
                    if (value === '-1') tooltipText = 'Kurangi indent';
                    else if (value === '+1') tooltipText = 'Tambah indent';
                }

                // Handle picker-label (dropdown headers)
                if (!tooltipText && button.classList.contains('ql-picker-label')) {
                    const select = button.parentElement;
                    if (select.classList.contains('ql-header')) tooltipText = 'Judul';
                    else if (select.classList.contains('ql-color')) tooltipText = 'Warna teks';
                    else if (select.classList.contains('ql-background')) tooltipText = 'Warna latar';
                    else if (select.classList.contains('ql-align')) tooltipText = 'Perataan';
                    else if (select.classList.contains('ql-list')) tooltipText = 'Daftar';
                }

                // Handle picker items (specific list values)
                if (!tooltipText && button.classList.contains('ql-picker-item')) {
                    const select = button.parentElement;
                    if (select.classList.contains('ql-list')) {
                        const value = button.getAttribute('data-value');
                        if (value === 'ordered') tooltipText = 'Daftar bernomor';
                        else if (value === 'bullet') tooltipText = 'Daftar poin';
                    }
                }

                if (tooltipText) {
                    button.setAttribute('data-tooltip', tooltipText);
                    button.style.position = 'relative';
                }
            });
        }

        // Load existing content for edit mode
        @if($isEdit && $article?->isi_konten)
            quill.root.innerHTML = @json($article->isi_konten);
        @endif

        // Sync Quill content to hidden input before form submission
        const form = document.getElementById('form-artikel');
        if (form) {
            form.addEventListener('submit', function() {
                const content = quill.root.innerHTML;
                document.getElementById('isi_konten').value = content;
            });
        }

        // Make Quill available globally for Alpine preview updates
        window.quillEditor = quill;

        // Update preview modal content
        const alpineDiv = document.querySelector('[x-data*="previewModalOpen"]');

        // Watch for preview modal opening using Alpine's reactive system
        if (alpineDiv.__x) {
            alpineDiv.addEventListener('click', function(e) {
                if (alpineDiv.__x?.data?.previewModalOpen) {
                    setTimeout(() => {
                        document.getElementById('previewContent').innerHTML = quill.root.innerHTML;
                    }, 50);
                }
            });
        }

        // Also update on text changes in Quill
        quill.on('text-change', function() {
            if (alpineDiv.__x?.data?.previewModalOpen) {
                document.getElementById('previewContent').innerHTML = quill.root.innerHTML;
            }
        });

        // Optional: Auto-save draft
        let autoSaveTimer;
        quill.on('text-change', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                console.log('Auto-save: Konten artikel diupdate');
                // Bisa tambahkan AJAX call untuk auto-save jika diperlukan
            }, 2000);
        });
    </script>

</body>
</html>
