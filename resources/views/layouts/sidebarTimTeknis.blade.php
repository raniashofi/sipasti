<style>
    [x-cloak] { display: none !important; }
    @media (max-width: 1023px) {
        .sidebar-drawer { transform: translateX(-100%); }
    }
</style>

<div x-data="">
    {{-- Mobile overlay --}}
    <div x-show="$store.sidebar.open"
         x-cloak
         @click="$store.sidebar.close()"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 z-30 lg:hidden"
         aria-hidden="true"></div>

    {{-- Mobile hamburger toggle --}}
    <button @click="$store.sidebar.toggle()"
            class="fixed top-0 left-0 z-50 h-16 w-14 flex items-center justify-center lg:hidden
                   bg-white border-r border-b border-gray-100 text-gray-500 hover:text-gray-700 transition-colors">
        <svg x-show="!$store.sidebar.open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
        <svg x-show="$store.sidebar.open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <aside class="sidebar-drawer fixed top-0 left-0 h-screen w-64 flex flex-col bg-white border-r border-gray-100 z-40
                  transition-transform duration-300 ease-in-out"
           :style="$store.sidebar.open ? 'transform:translateX(0)' : ''"
           style="font-family:'Inter',sans-serif;">

        {{-- Logo --}}
        <div class="px-6 py-6 shrink-0">
            <a href="{{ route('tim_teknis.antrean') }}" class="flex items-center gap-2">
                <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti" class="h-7 w-auto">
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
            @php
            $navItems = [
                [
                    'label' => 'Dashboard',
                    'route' => 'tim_teknis.dashboard',
                    'match' => ['tim_teknis.dashboard'],
                    'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                ],
                [
                    'label' => 'Antrean Tugas',
                    'route' => 'tim_teknis.antrean',
                    'match' => ['tim_teknis.antrean', 'tim_teknis.tiket.*'],
                    'icon'  => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
                ],
                [
                    'label' => 'Riwayat Tugas',
                    'route' => 'tim_teknis.riwayat',
                    'match' => ['tim_teknis.riwayat'],
                    'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label' => 'Pustaka Teknis (SOP)',
                    'route' => 'tim_teknis.pustaka',
                    'match' => ['tim_teknis.pustaka*'],
                    'icon'  => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                ],
            ];
            @endphp

            @foreach($navItems as $item)
            @php
                $isActive = false;
                foreach ($item['match'] as $_p) { if (request()->routeIs($_p)) { $isActive = true; break; } }
            @endphp
            <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors
                      {{ $isActive ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-400 hover:text-gray-700 hover:bg-gray-50' }}">
                <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-[#01458E]' : '' }}"
                     fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                <span class="text-sm">{{ $item['label'] }}</span>
            </a>
            @endforeach
        </nav>

        {{-- User + Logout --}}
        @php
            $sidebarTeknis = \App\Models\TimTeknis::with('bidang')
                ->where('user_id', Auth::id())->first();
            $bidangLabels = [
                'e_government'                     => 'E-Government',
                'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
                'statistik_persandian'              => 'Statistik & Persandian',
            ];
            $teknisBidang = $sidebarTeknis?->bidang
                ? ($bidangLabels[$sidebarTeknis->bidang->nama_bidang] ?? $sidebarTeknis->bidang->nama_bidang)
                : '—';
            $teknisNama = $sidebarTeknis?->nama_lengkap ?? 'Tim Teknis';
        @endphp

        {{-- Notification Bell --}}
        <div class="shrink-0 px-3 pb-2">
            <x-notification-bell layout="sidebar" />
        </div>

        <div class="shrink-0 px-5 py-5 border-t border-gray-100">
            <a href="{{ route('tim_teknis.profile') }}"
               class="flex items-center gap-3 mb-4 rounded-xl px-2 py-1.5 -mx-2 transition-colors hover:bg-gray-50
                      {{ request()->routeIs('tim_teknis.profile*') ? 'bg-blue-50' : '' }} group">
                <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0" style="background:#01458E;">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-[#01458E] transition-colors
                              {{ request()->routeIs('tim_teknis.profile*') ? 'text-[#01458E]' : '' }}">
                        {{ $teknisNama }}
                    </p>
                    <p class="text-[11px] text-gray-400 truncate">Tim Teknis · {{ $teknisBidang }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-gray-300 shrink-0 group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <div class="border-t border-gray-100 pt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2.5 w-full text-sm text-gray-400 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>

    </aside>
</div>
