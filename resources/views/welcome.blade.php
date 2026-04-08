<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SiPasti') }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ── Keyframes ── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        @keyframes blobMove {
            0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: scale(1); }
            50%       { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; transform: scale(1.05); }
        }
        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.85); }
        }

        /* ── Utility ── */
        .anim-fade-in-up  { opacity: 0; animation: fadeInUp 0.65s ease-out forwards; }
        .anim-fade-in     { opacity: 0; animation: fadeIn 0.8s ease-out forwards; }
        .anim-float       { animation: float 5s ease-in-out infinite; }
        .anim-blob        { animation: blobMove 9s ease-in-out infinite; }
        .anim-pulse-dot   { animation: pulseDot 1.8s ease-in-out infinite; }

        .d-100  { animation-delay: 0.10s; }
        .d-200  { animation-delay: 0.20s; }
        .d-300  { animation-delay: 0.30s; }
        .d-400  { animation-delay: 0.40s; }
        .d-500  { animation-delay: 0.55s; }
        .d-600  { animation-delay: 0.70s; }

        /* ── Dot pattern ── */
        .dot-bg {
            background-image: radial-gradient(circle, rgba(1,69,142,0.18) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
        }

        /* ── Gradient text ── */
        .text-gradient {
            background: linear-gradient(130deg, #01458E 30%, #1d84c8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Card hover ── */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 40px rgba(1, 69, 142, 0.08);
        }

        /* ── Primary button glow ── */
        .btn-primary-glow {
            transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary-glow:hover {
            background-color: #003672;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(1, 69, 142, 0.30);
        }

        /* ── Outline button ── */
        .btn-outline-anim {
            transition: background-color 0.2s, color 0.2s, border-color 0.2s, transform 0.2s;
        }
        .btn-outline-anim:hover {
            background-color: #01458E;
            color: #fff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen bg-[#F9FAFC] text-black flex flex-col overflow-x-hidden">

    {{-- ── Background decorations ── --}}
    <div class="fixed inset-0 pointer-events-none select-none" aria-hidden="true">
        {{-- Dot grid --}}
        <div class="dot-bg absolute inset-0"></div>
        {{-- Blobs --}}
        <div class="anim-blob absolute -top-40 -left-40 w-[500px] h-[500px] rounded-full blur-3xl"
             style="background-color: rgba(1,69,142,0.10);"></div>
        <div class="anim-blob absolute -bottom-40 -right-40 w-[500px] h-[500px] rounded-full blur-3xl"
             style="background-color: rgba(1,69,142,0.10); animation-delay:4s;"></div>
        <div class="anim-blob absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] rounded-full blur-3xl"
             style="background-color: rgba(29,132,200,0.08); animation-delay:2s;"></div>
    </div>

    {{-- ── Header ── --}}
    <header class="anim-fade-in relative z-20 flex justify-between items-center px-10 py-5 border-b border-gray-200"
            style="background-color: rgba(249,250,252,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
        <div class="flex items-center gap-3">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti Logo" class="h-5 w-auto">
        </div>

        <nav class="flex items-center gap-5">
            @auth
                <a href="{{
                        match(auth()->user()?->role) {
                            'super_admin'    => route('super_admin.dashboard'),
                            'admin_helpdesk' => route('admin_helpdesk.dashboard'),
                            'tim_teknis'     => route('tim_teknis.dashboard'),
                            'opd'            => route('opd.dashboard'),
                            'pimpinan'       => route('pimpinan.dashboard'),
                            default          => url('/'),
                        }
                }}"
                class="text-sm text-gray-500 hover:text-[#01458E] transition-colors duration-200 font-medium">
                    Dashboard
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="text-sm text-gray-500 hover:text-[#01458E] transition-colors duration-200 font-medium cursor-pointer">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="text-sm px-5 py-2 bg-[#01458E] text-white rounded-lg font-semibold btn-primary-glow">
                    Login
                </a>
            @endauth
        </nav>
    </header>

    {{-- ── Hero ── --}}
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-6 pt-16 pb-10">

        {{-- Floating logo card --}}
        <div class="anim-fade-in d-100 mb-8">
            <div class="anim-float">
                <div class="w-124 h-24 flex items-center justify-center p-4">
                    <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti"
                         class="w-full h-full object-contain">
                </div>
            </div>
        </div>

        {{-- Status badge --}}
        <div class="anim-fade-in-up d-100 mb-6">
            <span class="inline-flex items-center gap-2 bg-[#01458E]/[0.07] text-[#01458E]
                         text-[11px] font-semibold px-4 py-1.5 rounded-full border border-[#01458E]/20
                         tracking-wide uppercase">
                <span class="anim-pulse-dot w-1.5 h-1.5 bg-[#01458E] rounded-full"></span>
                Sistem Online
            </span>
        </div>

        {{-- Headline --}}
        <h1 class="anim-fade-in-up d-200 text-center text-[52px] font-bold text-black leading-[1.15] mb-5
                   max-w-xl tracking-tight">
            Satu Platform,<br>
            <span class="text-gradient">Semua Layanan TIK</span>
        </h1>

        {{-- Subtitle --}}
        <p class="anim-fade-in-up d-300 text-center text-gray-500 text-[15px] leading-relaxed mb-10 max-w-md">
            SiPasti memudahkan pengelolaan tiket gangguan, koordinasi tim teknis,
            dan akses basis pengetahuan dalam satu sistem terintegrasi.
        </p>

        {{-- CTA Buttons --}}
        <div class="anim-fade-in-up d-400 flex gap-3 justify-center flex-wrap">
            @auth
                <a href="{{
                        match(auth()->user()?->role) {
                            'super_admin'    => route('super_admin.dashboard'),
                            'admin_helpdesk' => route('admin_helpdesk.dashboard'),
                            'tim_teknis'     => route('tim_teknis.dashboard'),
                            'opd'            => route('opd.dashboard'),
                            'pimpinan'       => route('pimpinan.dashboard'),
                            default          => url('/'),
                        }
                }}"
                   class="px-8 py-3 bg-[#01458E] text-white rounded-xl text-sm font-semibold shadow-sm btn-primary-glow">
                    Buka Dashboard &rarr;
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-8 py-3 bg-[#01458E] text-white rounded-xl text-sm font-semibold shadow-sm btn-primary-glow">
                    Masuk ke Akun &rarr;
                </a>
            @endauth
        </div>

        {{-- Divider --}}
        <div class="anim-fade-in-up d-500 mt-16 flex items-center gap-4 w-full max-w-4xl">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-[11px] text-gray-400 font-medium uppercase tracking-widest">Fitur Unggulan</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        {{-- Feature Cards --}}
        <div class="anim-fade-in-up d-600 mt-10 grid grid-cols-1 md:grid-cols-3 gap-5 w-full max-w-4xl">

            <div class="card-hover bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-[#01458E]/[0.08] rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[13px] text-black mb-1.5">Manajemen Tiket</h3>
                <p class="text-[12px] text-gray-400 leading-relaxed">
                    Buat, pantau, dan selesaikan tiket gangguan dengan alur kerja yang jelas dan terstruktur.
                </p>
            </div>

            <div class="card-hover bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-[#01458E]/[0.08] rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[13px] text-black mb-1.5">Live Chat</h3>
                <p class="text-[12px] text-gray-400 leading-relaxed">
                    Komunikasi real-time antara pengguna dan tim teknis untuk penanganan gangguan yang cepat.
                </p>
            </div>

            <div class="card-hover bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                <div class="w-11 h-11 bg-[#01458E]/[0.08] rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-[#01458E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-[13px] text-black mb-1.5">Knowledge Base</h3>
                <p class="text-[12px] text-gray-400 leading-relaxed">
                    Temukan solusi mandiri dari basis pengetahuan dan panduan troubleshooting yang lengkap.
                </p>
            </div>

        </div>
    </main>

    {{-- ── Footer ── --}}
    <footer class="relative z-10 text-center py-6 border-t border-gray-200/70 text-gray-400 text-[11px]">
        &copy; {{ date('Y') }} SiPasti &mdash; Semua hak dilindungi.
    </footer>

</body>
</html>
