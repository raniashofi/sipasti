<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SiPasti') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Inter', sans-serif; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes blobMove {
            0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: scale(1); }
            50%       { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; transform: scale(1.05); }
        }

        .anim-fade-in-up { opacity: 0; animation: fadeInUp 0.65s ease-out forwards; }
        .anim-fade-in    { opacity: 0; animation: fadeIn 0.8s ease-out forwards; }
        .anim-blob       { animation: blobMove 9s ease-in-out infinite; }

        .d-100 { animation-delay: 0.10s; }
        .d-200 { animation-delay: 0.20s; }
        .d-300 { animation-delay: 0.30s; }
        .d-400 { animation-delay: 0.40s; }

        .dot-bg {
            background-image: radial-gradient(circle, rgba(1,69,142,0.18) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="min-h-screen bg-[#F9FAFC] flex flex-col overflow-x-hidden antialiased">

    {{-- Background decorations --}}
    <div class="fixed inset-0 pointer-events-none select-none" aria-hidden="true">
        <div class="dot-bg absolute inset-0"></div>
        <div class="anim-blob absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full blur-3xl"
             style="background-color: rgba(1,69,142,0.10);"></div>
        <div class="anim-blob absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full blur-3xl"
             style="background-color: rgba(1,69,142,0.10); animation-delay:4s;"></div>
    </div>

    <div class="relative z-10 flex-1 flex flex-col items-center justify-center px-4 py-12">
        {{ $slot }}
    </div>

</body>
</html>
