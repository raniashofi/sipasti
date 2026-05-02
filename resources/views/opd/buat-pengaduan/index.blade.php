<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buat Pengaduan — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F0F4F8; }

        .cat-card {
            background: #fff;
            border-radius: 20px;
            border: 2px solid #E9EEF5;
            padding: 36px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            text-decoration: none;
            transition: border-color .2s ease, box-shadow .2s ease, transform .15s ease;
            min-height: 160px;
        }
        .cat-card:hover {
            border-color: #01458E;
            box-shadow: 0 12px 36px rgba(1,69,142,.12);
            transform: translateY(-3px);
        }
        .cat-icon {
            flex-shrink: 0;
            width: 88px;
            height: 88px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #01458E;
        }
    </style>
</head>
<body class="min-h-screen">

<div class="sticky top-0 z-30 shadow-sm">
    @include('layouts.topBarOpd')
</div>

<main class="max-w-screen-xl mx-auto px-6 lg:px-8 py-7 space-y-6">

    <div class="mb-10">
        <h1 class="text-2xl font-bold text-gray-900">Halo, apa yang bisa kami bantu hari ini?</h1>
        <p class="text-sm text-gray-400 mt-1">Pilih kategori di bawah untuk memulai diagnosis cepat</p>
    </div>

    @if($kategori->isEmpty())
    <div class="flex flex-col items-center justify-center py-24 text-center">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4 bg-[#EEF3F9]">
            <svg class="w-7 h-7 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
        </div>
        <p class="text-base font-semibold text-gray-700">Belum ada kategori tersedia</p>
        <p class="text-sm text-gray-400 mt-1">Silakan hubungi administrator sistem.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($kategori as $kat)
        @php
            // Ambil SVG dari config berdasarkan field icon di database
            $iconKey = $kat->icon ?? 'default';
            $presets = config('category_icons.presets', []);
            $svgIcon = $presets[$iconKey]['svg'] ?? $presets['default']['svg'] ?? '';
        @endphp

        <a href="{{ route('opd.diagnosis.mulai', $kat->id) }}" class="cat-card">
            <div class="flex-1 min-w-0">
                <p class="text-xl font-extrabold uppercase text-gray-900 leading-tight mb-2">
                    {{ $kat->nama_kategori }}
                </p>
                @if($kat->deskripsi)
                <p class="text-sm text-gray-500 leading-relaxed">{{ $kat->deskripsi }}</p>
                @endif
            </div>
            <div class="cat-icon">
                {!! $svgIcon !!}
            </div>
        </a>

        @endforeach
    </div>
    @endif

</main>
</body>
</html>
