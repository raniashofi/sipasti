<aside class="fixed top-0 left-0 h-screen w-64 flex flex-col bg-white border-r border-gray-100 z-40"
       style="font-family:'Inter',sans-serif;">

    {{-- Logo --}}
    <div class="px-6 py-6 shrink-0">
        <a href="{{ route('super_admin.dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti" class="h-7 w-auto">
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">

        @php
        $navItems = [
            [
                'label'  => 'Dashboard',
                'route'  => 'super_admin.dashboard',
                'match'  => ['super_admin.dashboard'],
                'icon'   => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
            ],
            [
                'label'  => 'Manajemen Pengguna',
                'route'  => 'super_admin.pengguna.opd',
                'match'  => ['super_admin.pengguna.*'],
                'icon'   => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'sub'    => [
                    ['label' => 'Pengguna OPD',      'route' => 'super_admin.pengguna.opd'],
                    ['label' => 'Pengguna Internal',  'route' => 'super_admin.pengguna.internal'],
                ],
            ],
            [
                'label'  => 'Konfigurasi Sistem',
                'route'  => 'super_admin.konfigurasi.kategori',
                'match'  => ['super_admin.konfigurasi.*'],
                'icon'   => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'sub'    => [
                    ['label' => 'Kategori Gejala',   'route' => 'super_admin.konfigurasi.kategori'],
                    ['label' => 'Alur Diagnosis',     'route' => 'super_admin.konfigurasi.diagnosis'],
                ],
            ],
            [
                'label'  => 'Pustaka Pengetahuan',
                'route'  => 'super_admin.pustaka.opd',
                'match'  => ['super_admin.pustaka.*'],
                'icon'   => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'sub'    => [
                    ['label' => 'KB untuk OPD',      'route' => 'super_admin.pustaka.opd'],
                    ['label' => 'KB Internal Teknis', 'route' => 'super_admin.pustaka.internal'],
                ],
            ],
            [
                'label'  => 'Keamanan & Audit',
                'route'  => 'super_admin.audit',
                'match'  => ['super_admin.audit'],
                'icon'   => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
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

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="ml-7 mt-0.5 space-y-0.5">
                @foreach($item['sub'] as $sub)
                @php $subActive = request()->routeIs($sub['route']); @endphp
                <a href="{{ route($sub['route']) }}"
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
        <a href="{{ route($item['route']) }}"
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
    <div class="shrink-0 px-5 py-5 border-t border-gray-100">

        {{-- User info --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                 style="background-color:#01458E;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                     stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">Super Admin</p>
                <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
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
