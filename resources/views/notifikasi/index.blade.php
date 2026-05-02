@php
    $user = Auth::user();
    $role = $user->role ?? '';

    $sidebarMap = [
        'super_admin'    => 'layouts.sidebarSuperAdmin',
        'admin_helpdesk' => 'layouts.sidebarAdminHelpdesk',
        'tim_teknis'     => 'layouts.sidebarTimTeknis',
        'pimpinan'       => 'layouts.sidebarPimpinan',
    ];

    $dashboardMap = [
        'super_admin'    => 'super_admin.dashboard',
        'admin_helpdesk' => 'admin_helpdesk.dashboard',
        'tim_teknis'     => 'tim_teknis.antrean',
        'pimpinan'       => 'pimpinan.dashboard',
    ];

    $useTopBar  = ($role === 'opd');
    $sidebar    = $sidebarMap[$role] ?? null;
    $dashRoute  = $dashboardMap[$role] ?? '#';

    // Batasi notifikasi maksimal 10 di frontend
    $displayNotifs = $notifications->take(10);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifikasi — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>* { font-family: 'Inter', sans-serif; } [x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-50 min-h-screen">

    {{-- ── Layout: OPD pakai top bar, role lain pakai sidebar ── --}}
    @if($useTopBar)
        @include('layouts.topBarOpd')
        <div class="pt-16 min-h-screen">
    @elseif($sidebar)
        @include($sidebar)
        <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">
            {{-- Top bar sederhana --}}
            <header class="bg-white border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Notifikasi</h1>
                    <p class="text-sm text-gray-400 mt-0.5">Semua pemberitahuan masuk</p>
                </div>
            </header>
    @else
        <div class="min-h-screen">
    @endif

    {{-- ── Konten ── --}}
    <div class="{{ $useTopBar ? 'max-w-3xl mx-auto px-4 py-6' : 'px-4 sm:px-8 py-8 max-w-3xl' }}">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">
                @if(!$useTopBar) Semua Notifikasi @else Notifikasi @endif
            </h2>
            @if($unreadCount > 0)
            <form method="POST" action="{{ route('notif.readAll') }}">
                @csrf
                <button type="submit"
                        class="text-sm text-[#01458E] hover:underline font-medium transition-colors">
                    Tandai semua dibaca
                </button>
            </form>
            @endif
        </div>

        {{-- Daftar notifikasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100 overflow-hidden">

            @if($displayNotifs->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 px-4 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-gray-400">Belum ada notifikasi</p>
                    <p class="text-xs text-gray-400 mt-1">Notifikasi akan muncul di sini</p>
                </div>
            @else
                @foreach($displayNotifs as $notif)
                @php
                    $data = (array) $notif->getAttribute('data');
                    $read = $notif->getAttribute('read_at') !== null;
                @endphp
                <div class="flex items-start gap-3 sm:gap-4 px-4 sm:px-6 py-4 transition-colors hover:bg-gray-50 group {{ !$read ? 'bg-blue-50/60' : '' }}">
                    {{-- Dot --}}
                    <div class="mt-1.5 shrink-0">
                        <div class="w-2.5 h-2.5 rounded-full {{ !$read ? 'bg-[#01458E]' : 'bg-gray-200' }}"></div>
                    </div>

                    {{-- Konten --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3">
                            <div class="flex-1">
                                <p class="text-sm {{ !$read ? 'font-semibold text-gray-900' : 'font-medium text-gray-600' }}">
                                    {{ $data['title'] ?? 'Notifikasi' }}
                                </p>
                                <p class="text-sm text-gray-500 mt-1 leading-relaxed line-clamp-2">
                                    {{ $data['body'] ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ $notif->getAttribute('created_at')->diffForHumans() }}
                                </p>
                            </div>

                            @if(!$read)
                            <form method="POST" action="{{ route('notif.read', $notif->getKey()) }}" class="shrink-0 self-start">
                                @csrf
                                <button type="submit"
                                        class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 text-xs px-3 py-1 rounded-lg bg-[#01458E] text-white hover:bg-blue-950 transition-all duration-200">
                                    Baca
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @endif
        </div>

        @if(!$displayNotifs->isEmpty())
        <p class="text-xs text-gray-400 text-center mt-5">
            Menampilkan {{ $displayNotifs->count() }} notifikasi terbaru
        </p>
        @endif
    </div>

    </div>{{-- /.ml-64 atau .pt-16 --}}
</body>
</html>
