<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $artikel->nama_artikel_sop }} — Pusat Bantuan SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        /* Scrollbar sidebar daftar isi */
        .toc-scroll::-webkit-scrollbar { width: 3px; }
        .toc-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 9999px; }

        /* ── Konten Quill (prose) ── */
        .prose-artikel { font-size: 0.875rem; line-height: 1.8; color: #374151; }
        .prose-artikel h1 { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 1.5rem 0 0.75rem; }
        .prose-artikel h2 { font-size: 1.05rem; font-weight: 700; color: #1e3a5f; margin: 1.5rem 0 0.6rem; padding-bottom: 0.4rem; border-bottom: 2px solid #EEF3F9; }
        .prose-artikel h3 { font-size: 0.9rem; font-weight: 600; color: #374151; margin: 1.2rem 0 0.5rem; }
        .prose-artikel p  { margin: 0 0 0.85rem; }
        .prose-artikel ul { list-style: disc; padding-left: 1.5rem; margin: 0 0 0.85rem; }
        .prose-artikel ol { list-style: decimal; padding-left: 1.5rem; margin: 0 0 0.85rem; }
        .prose-artikel li { margin-bottom: 0.3rem; }
        .prose-artikel a  { color: #01458E; text-decoration: underline; }
        .prose-artikel strong { font-weight: 600; color: #111827; }
        .prose-artikel blockquote {
            border-left: 3px solid #01458E;
            padding: 0.5rem 1rem;
            background: #F0F7FF;
            border-radius: 0 0.5rem 0.5rem 0;
            margin: 0.85rem 0;
            color: #374151;
        }
        .prose-artikel pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            font-size: 0.8rem;
            margin: 0.85rem 0;
        }
        .prose-artikel code {
            background: #F1F5F9;
            padding: 0.1rem 0.35rem;
            border-radius: 0.25rem;
            font-size: 0.8rem;
            color: #DC2626;
        }
        .prose-artikel pre code { background: transparent; color: inherit; padding: 0; }
        .prose-artikel img { max-width: 100%; border-radius: 0.75rem; margin: 0.85rem 0; }
        .prose-artikel table {
            width: 100%; border-collapse: collapse; margin: 0.85rem 0; font-size: 0.825rem;
        }
        .prose-artikel th {
            background: #F1F5F9; font-weight: 600; text-align: left;
            padding: 0.5rem 0.75rem; border: 1px solid #E5E7EB;
        }
        .prose-artikel td { padding: 0.5rem 0.75rem; border: 1px solid #E5E7EB; }

        /* Daftar isi active link */
        .toc-link.active { color: #01458E; font-weight: 600; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="flex-1 max-w-screen-xl w-full mx-auto px-5 md:px-8 py-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-400 mb-7 flex-wrap">
        <a href="{{ route('opd.bantuan') }}" class="hover:text-[#01458E] transition-colors">Pusat Bantuan</a>
        @if($artikel->kategori)
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <a href="{{ route('opd.bantuan.kategori', $artikel->kategori->id) }}"
           class="hover:text-[#01458E] transition-colors">{{ $artikel->kategori->nama_kategori }}</a>
        @endif
        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-gray-600 font-medium truncate max-w-xs">{{ $artikel->nama_artikel_sop }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">

        {{-- ══════════════════════════════════════
             KIRI: Konten Artikel
        ══════════════════════════════════════ --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

                {{-- Header artikel --}}
                <div class="px-8 py-7 border-b border-gray-100">
                    @if($artikel->kategori)
                    <span class="inline-block text-[11px] font-bold px-3 py-1 rounded-full mb-3"
                          style="background:#EEF3F9;color:#01458E;">{{ $artikel->kategori->nama_kategori }}</span>
                    @endif
                    <h1 class="text-xl font-bold text-gray-900 leading-snug">{{ $artikel->nama_artikel_sop }}</h1>
                    @if($artikel->deskripsi_singkat)
                    <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $artikel->deskripsi_singkat }}</p>
                    @endif
                    <div class="flex items-center gap-4 mt-4 text-xs text-gray-400">
                        @if($artikel->updated_at)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Terakhir diperbarui: {{ \Carbon\Carbon::parse($artikel->updated_at)->locale('id')->isoFormat('D MMMM YYYY') }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ number_format($artikel->total_views) }} views
                        </span>
                    </div>
                </div>

                {{-- Gambar header --}}
                @if($artikel->header_image)
                <div class="px-8 pt-6">
                    <img src="{{ Storage::url($artikel->header_image) }}"
                         alt="{{ $artikel->nama_artikel_sop }}"
                         class="w-full max-h-64 object-cover rounded-xl">
                </div>
                @endif

                {{-- Isi Konten --}}
                <div class="px-8 py-7">
                    <div class="prose-artikel">{!! $konten !!}</div>
                </div>

                {{-- Lampiran file --}}
                @if($artikel->lampiran_file)
                <div class="px-8 pb-7">
                    <div class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                             style="background:#EEF3F9;">
                            <svg class="w-4.5 h-4.5" style="color:#01458E;width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-700 truncate">Lampiran</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ basename($artikel->lampiran_file) }}</p>
                        </div>
                        <a href="{{ Storage::url($artikel->lampiran_file) }}" target="_blank"
                           class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-lg text-white transition hover:opacity-90"
                           style="background:#01458E;">
                            Unduh
                        </a>
                    </div>
                </div>
                @endif

                {{-- Footer artikel: Rating --}}
                <div class="px-8 py-6 border-t border-gray-100"
                     x-data="{
                        rating: {{ $myRating ?? 0 }},
                        hover: 0,
                        submitted: {{ $myRating ? 'true' : 'false' }},
                        avgRating: {{ $artikel->rating ?? 0 }},
                        ratingCount: {{ $artikel->rating_count ?? 0 }},
                        myRating: {{ $myRating ?? 0 }},

                        async submit(val) {
                            this.rating = val;
                            try {
                                const res = await window.axios.post(
                                    '{{ route('opd.bantuan.rating', $artikel->id) }}',
                                    { rating: val }
                                );
                                this.avgRating   = res.data.rating;
                                this.ratingCount = res.data.rating_count;
                                this.myRating    = res.data.my_rating;
                                this.submitted   = true;
                            } catch(e) { console.error(e); }
                        }
                     }">

                    {{-- Sebelum submit: form rating --}}
                    <div x-show="!submitted" class="flex flex-col items-center gap-3">
                        <p class="text-sm font-semibold text-gray-700">Apakah artikel ini membantu?</p>
                        <p class="text-xs text-gray-400">Beri penilaian Anda untuk artikel ini</p>

                        <div class="flex items-center gap-2">
                            <template x-for="i in 5" :key="i">
                                <button type="button"
                                        @click="submit(i)"
                                        @mouseenter="hover = i"
                                        @mouseleave="hover = 0"
                                        class="text-4xl transition-transform hover:scale-110 focus:outline-none leading-none">
                                    <span :class="(hover || rating) >= i ? 'text-yellow-400' : 'text-gray-200'">★</span>
                                </button>
                            </template>
                        </div>

                        @if(($artikel->rating_count ?? 0) > 0)
                        <p class="text-[11px] text-gray-400">
                            Rata-rata {{ number_format($artikel->rating, 1) }} dari {{ number_format($artikel->rating_count) }} penilaian
                        </p>
                        @endif
                    </div>

                    {{-- Setelah submit: tampilkan hasil --}}
                    <div x-show="submitted" x-transition class="flex flex-col items-center gap-2" style="display:none;">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mb-1">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $myRating ? 'Anda sudah memberi penilaian' : 'Terima kasih atas penilaian Anda!' }}
                        </p>

                        {{-- Bintang penilaian user --}}
                        <div class="flex items-center gap-1">
                            <template x-for="i in 5" :key="i">
                                <span class="text-2xl leading-none"
                                      :class="i <= myRating ? 'text-yellow-400' : 'text-gray-200'">★</span>
                            </template>
                            <span class="text-xs text-gray-500 ml-1.5" x-text="'(' + myRating + '/5)'"></span>
                        </div>

                        {{-- Rata-rata semua penilaian --}}
                        <p class="text-[11px] text-gray-400"
                           x-text="'Rata-rata ' + avgRating.toFixed(1) + ' dari ' + ratingCount + ' penilaian'"></p>

                        {{-- Tombol ubah penilaian --}}
                        <button @click="submitted = false; hover = 0"
                                class="text-xs text-[#01458E] hover:underline mt-1">
                            Ubah penilaian
                        </button>
                    </div>

                </div>

            </div>

            {{-- Artikel Terkait --}}
            @if($terkait->isNotEmpty())
            <div class="mt-6">
                <h3 class="text-sm font-bold text-gray-700 mb-3">Artikel Terkait</h3>
                <div class="space-y-2">
                    @foreach($terkait as $rel)
                    <a href="{{ route('opd.bantuan.artikel', $rel->id) }}"
                       class="flex items-center justify-between bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3.5
                              hover:border-[#01458E]/30 hover:shadow-md transition-all group">
                        <span class="text-sm text-gray-700 group-hover:text-[#01458E] transition-colors">
                            {{ $rel->nama_artikel_sop }}
                        </span>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#01458E] shrink-0 ml-4 transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ══════════════════════════════════════
             KANAN: Daftar Isi + CTA
        ══════════════════════════════════════ --}}
        <div class="lg:col-span-1 space-y-4 sticky top-24">

            {{-- Daftar Isi --}}
            @if(count($toc) > 0)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-5 py-5"
                 x-data="{ active: '' }"
                 x-init="
                    const headings = document.querySelectorAll('.prose-artikel h1,.prose-artikel h2,.prose-artikel h3');
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(e => { if (e.isIntersecting) active = e.target.id; });
                    }, { rootMargin: '-20% 0px -70% 0px' });
                    headings.forEach(h => observer.observe(h));
                 ">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Daftar Isi</p>
                <nav class="space-y-0.5 toc-scroll max-h-72 overflow-y-auto">
                    @foreach($toc as $item)
                    <a href="#{{ $item['slug'] }}"
                       :class="active === '{{ $item['slug'] }}' ? 'text-[#01458E] font-semibold bg-blue-50' : 'text-gray-600 hover:text-[#01458E] hover:bg-gray-50'"
                       class="toc-link block py-1.5 px-2 rounded-lg text-xs transition-colors leading-snug"
                       style="{{ $item['level'] === 2 ? 'padding-left:1rem;' : ($item['level'] === 3 ? 'padding-left:1.75rem;' : '') }}">
                        {{ $item['text'] }}
                    </a>
                    @endforeach
                </nav>
            </div>
            @endif

            {{-- CTA Butuh Bantuan --}}
            <div class="rounded-2xl px-5 py-6 text-center"
                 style="background:linear-gradient(135deg,#01458E 0%,#0369a1 100%);">
                <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                    </svg>
                </div>
                <p class="text-sm font-bold text-white mb-1">Butuh Bantuan?</p>
                <p class="text-xs text-blue-200 mb-4 leading-relaxed">Tim support kami siap membantu Anda 24/7</p>
                <a href="{{ route('opd.diagnosis.index') }}"
                   class="block w-full py-2.5 rounded-xl text-xs font-bold text-[#01458E] bg-white
                          transition hover:shadow-md hover:-translate-y-0.5 active:scale-95">
                    Buat Pengaduan
                </a>
            </div>

        </div>

    </div>
</main>

<footer class="text-center py-6 mt-auto border-t border-gray-200 bg-white text-gray-400 text-xs font-medium">
    &copy; {{ date('Y') }} SiPasti &mdash; Dinas Komunikasi dan Informatika Kota Padang
</footer>

</body>
</html>
