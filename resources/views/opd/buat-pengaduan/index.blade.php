<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Pengaduan — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        .kategori-card {
            background: #fff;
            border-radius: 20px;
            border: 2px solid #E5E7EB;
            padding: 32px 28px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            text-decoration: none;
        }
        .kategori-card:hover {
            border-color: #01458E;
            box-shadow: 0 8px 28px rgba(1,69,142,0.12);
            transform: translateY(-2px);
        }
        .kategori-card.selected {
            border-color: #01458E;
            box-shadow: 0 8px 28px rgba(1,69,142,0.15);
        }
    </style>
</head>
<body class="min-h-screen">

    <div class="sticky top-0 z-30 shadow-sm">
        @include('layouts.topBarOpd')
    </div>

    <main class="max-w-screen-lg mx-auto px-6 lg:px-8 py-10">

        {{-- Heading --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-1">Halo, apa yang bisa kami bantu hari ini?</h1>
            <p class="text-sm text-gray-400">Pilih kategori di bawah untuk memulai diagnosis cepat</p>
        </div>

        {{-- Kategori Grid --}}
        @if($kategori->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                     style="background-color:rgba(1,69,142,0.08);">
                    <svg class="w-8 h-8" style="color:#01458E;" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <p class="text-base font-semibold text-gray-700">Belum ada kategori tersedia</p>
                <p class="text-sm text-gray-400 mt-1">Silakan hubungi administrator sistem.</p>
            </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach($kategori as $kat)
            @php
                $nama = strtolower($kat->nama_kategori ?? '');
                if (str_contains($nama, 'jaringan') || str_contains($nama, 'internet')) {
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/>';
                } elseif (str_contains($nama, 'aplikasi') || str_contains($nama, 'website')) {
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3"/>';
                } elseif (str_contains($nama, 'hardware') || str_contains($nama, 'perangkat')) {
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3"/>';
                } elseif (str_contains($nama, 'keamanan') || str_contains($nama, 'akun')) {
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>';
                } else {
                    $icon = '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>';
                }
            @endphp

            <a href="{{ route('opd.diagnosis.mulai', $kat->id) }}" class="kategori-card">
                <div class="flex-1 min-w-0">
                    <p class="text-base font-bold text-gray-900 uppercase tracking-wide mb-2">
                        {{ $kat->nama_kategori }}
                    </p>
                    @if($kat->deskripsi)
                    <p class="text-sm text-gray-400 leading-relaxed">{{ $kat->deskripsi }}</p>
                    @endif
                </div>
                <div class="shrink-0 w-16 h-16 flex items-center justify-center rounded-2xl"
                     style="background-color:rgba(1,69,142,0.08);">
                    <svg class="w-9 h-9" style="color:#01458E;" fill="none" stroke="currentColor"
                         stroke-width="1.5" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
        @endif

        {{-- Lewati diagnosis langsung buat tiket --}}
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-400">
                Sudah tahu masalahnya?
                <a href="{{ route('opd.diagnosis.tiket') }}"
                   class="font-semibold hover:underline" style="color:#01458E;">
                    Langsung buat tiket →
                </a>
            </p>
        </div>

    </main>
</body>
</html>
