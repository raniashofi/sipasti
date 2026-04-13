<style>
    /* Menyembunyikan elemen Alpine sebelum benar-benar siap */
    [x-cloak] { display: none !important; }
</style>

<aside class="fixed top-0 left-0 h-screen w-64 flex flex-col bg-white border-r border-gray-100 z-40"
       style="font-family:'Inter',sans-serif;">

    {{-- Logo --}}
    <div class="px-6 py-6 shrink-0">
        <a href="{{ route('admin_helpdesk.dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti" class="h-7 w-auto">
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">

        @php
        $navItems = [
            [
                'label'  => 'Dashboard',
                'route'  => 'admin_helpdesk.dashboard',
                'match'  => ['admin_helpdesk.dashboard'],
                'icon'   => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            ],
            [
                'label'  => 'Manajemen Tiket',
                'route'  => 'admin_helpdesk.tiket.menunggu',
                'match'  => ['admin_helpdesk.tiket.*'],
                'icon'   => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                'sub'    => [
                    ['label' => 'Menunggu Verif',      'route' => 'admin_helpdesk.tiket.menunggu'],
                    ['label' => 'Panduan Remote',       'route' => 'admin_helpdesk.tiket.panduan'],
                    ['label' => 'Distribusi & Eskalasi','route' => 'admin_helpdesk.tiket.distribusi'],
                    ['label' => 'Riwayat Tiket',        'route' => 'admin_helpdesk.tiket.riwayat'],
                ],
            ],
            [
                'label'  => 'Pustaka Solusi',
                'route'  => 'admin_helpdesk.pustaka',
                'match'  => ['admin_helpdesk.pustaka*'],
                'icon'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
            ],
            [
                'label'  => 'Log Aktivitas',
                'route'  => 'admin_helpdesk.log',
                'match'  => ['admin_helpdesk.log*'],
                'icon'   => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ],
        ];
        @endphp

        @foreach($navItems as $item)
        @php
            $isActive = false;
            foreach ($item['match'] as $_p) { if (request()->routeIs($_p)) { $isActive = true; break; } }
            $hasSub   = !empty($item['sub']);
        @endphp

        @if($hasSub)
        {{-- Item with sub-menu --}}
        <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors
                           {{ $isActive ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-[#01458E]' : '' }}"
                     fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span class="flex-1 text-sm">{{ $item['label'] }}</span>
                <svg class="w-3.5 h-3.5 text-gray-300 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="ml-7 mt-0.5 space-y-0.5">
                @foreach($item['sub'] as $sub)
                @php $subActive = request()->routeIs($sub['route']); @endphp
                <a href="{{ Route::has($sub['route']) ? route($sub['route']) : '#' }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs transition-colors
                          {{ $subActive
                              ? 'text-[#01458E] font-semibold bg-blue-50'
                              : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50' }}">
                    <span class="w-1 h-1 rounded-full {{ $subActive ? 'bg-[#01458E]' : 'bg-gray-300' }}"></span>
                    {{ $sub['label'] }}
                </a>
                @endforeach
            </div>
        </div>

        @else
        {{-- Item without sub-menu --}}
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors
                  {{ $isActive
                      ? 'bg-blue-50 text-gray-900 font-semibold'
                      : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50' }}">
            <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-[#01458E]' : '' }}"
                 fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
            </svg>
            <span class="text-sm">{{ $item['label'] }}</span>
        </a>
        @endif

        @endforeach
    </nav>

    {{-- User + Logout --}}
    @php
        $sidebarAdminProfile = \App\Models\AdminHelpdesk::with('bidang')
            ->where('user_id', Auth::id())->first();
        $bidangLabels = [
            'e_government'                      => 'E-Government',
            'infrastruktur_teknologi_informasi'  => 'Infrastruktur TI',
            'statistik_persandian'               => 'Statistik & Persandian',
        ];
        $bidangNama = $sidebarAdminProfile?->bidang
            ? ($bidangLabels[$sidebarAdminProfile->bidang->nama_bidang] ?? $sidebarAdminProfile->bidang->nama_bidang)
            : '—';
        $namaLengkap = $sidebarAdminProfile?->nama_lengkap ?? 'Admin Helpdesk';
    @endphp

    <div class="shrink-0 px-5 py-5 border-t border-gray-100">

        {{-- User info --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                 style="background-color:#01458E;">
                {{-- Headphone icon --}}
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                     stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 18v-6a9 9 0 0118 0v6"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $namaLengkap }}</p>
                <p class="text-[11px] text-gray-400 truncate">{{ $bidangNama }}</p>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2.5 w-full text-sm text-gray-400 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>

</aside>
