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
            // Ambil nama bidang dari relasi (pastikan penamaan relasi modelnya benar, misal: $kat->bidang->nama_bidang)
            $bidang = strtolower($kat->bidang->nama_bidang ?? '');

            if ($bidang === 'e_government' || str_contains($bidang, 'government')) {
                // Ilustrasi: Jendela Browser / Aplikasi Layanan
                $svgIcon = <<<'SVG'
<svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:88px;height:88px;color:#01458E;">
  <rect x="8" y="18" width="64" height="44" rx="5" stroke="currentColor" stroke-width="3"/>
  <line x1="8" y1="30" x2="72" y2="30" stroke="currentColor" stroke-width="3"/>
  <circle cx="17" cy="24" r="2.5" fill="currentColor"/>
  <circle cx="25" cy="24" r="2.5" fill="currentColor"/>
  <circle cx="33" cy="24" r="2.5" fill="currentColor"/>
  <rect x="16" y="38" width="22" height="16" rx="3" stroke="currentColor" stroke-width="2.5"/>
  <line x1="46" y1="42" x2="64" y2="42" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
  <line x1="46" y1="50" x2="56" y2="50" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
</svg>
SVG;
            } elseif ($bidang === 'infrastruktur_teknologi_informasi' || str_contains($bidang, 'infrastruktur')) {
                // Ilustrasi: Rak Server / Jaringan / Hardware
                $svgIcon = <<<'SVG'
<svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:88px;height:88px;color:#01458E;">
  <rect x="16" y="20" width="48" height="16" rx="3" stroke="currentColor" stroke-width="3"/>
  <circle cx="24" cy="28" r="2" fill="currentColor"/>
  <circle cx="30" cy="28" r="2" fill="currentColor"/>
  <line x1="44" y1="28" x2="56" y2="28" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
  <rect x="16" y="44" width="48" height="16" rx="3" stroke="currentColor" stroke-width="3"/>
  <circle cx="24" cy="52" r="2" fill="currentColor"/>
  <circle cx="30" cy="52" r="2" fill="currentColor"/>
  <line x1="44" y1="52" x2="56" y2="52" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
  <path d="M64 28 C74 28 74 52 64 52" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
</svg>
SVG;
            } elseif ($bidang === 'statistik_persandian' || str_contains($bidang, 'statistik')) {
                // Ilustrasi: Grafik Data & Perisai Keamanan (Sandi)
                $svgIcon = <<<'SVG'
<svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:88px;height:88px;color:#01458E;">
  <rect x="12" y="44" width="10" height="20" rx="2" stroke="currentColor" stroke-width="2.5"/>
  <rect x="26" y="32" width="10" height="32" rx="2" stroke="currentColor" stroke-width="2.5"/>
  <rect x="40" y="20" width="10" height="44" rx="2" stroke="currentColor" stroke-width="2.5"/>
  <line x1="8" y1="64" x2="60" y2="64" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
  <path d="M52 38 L68 38 L68 48 C68 56 60 62 52 66 C44 62 36 56 36 48 L36 38 Z" fill="#fff" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
  <circle cx="52" cy="48" r="3" fill="currentColor"/>
  <path d="M51 50 L53 50 L54 55 L50 55 Z" fill="currentColor"/>
</svg>
SVG;
            } else {
                // Default Icon (Bantuan/Lainnya)
                $svgIcon = <<<'SVG'
<svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:88px;height:88px;color:#01458E;">
  <circle cx="40" cy="40" r="26" stroke="currentColor" stroke-width="3" stroke-dasharray="8 8"/>
  <circle cx="40" cy="40" r="16" stroke="currentColor" stroke-width="3"/>
  <circle cx="40" cy="40" r="4" fill="currentColor"/>
</svg>
SVG;
            }
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
