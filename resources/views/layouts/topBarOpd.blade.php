<nav class="bg-white border-b border-gray-200 px-8 py-0" style="font-family: 'Inter', sans-serif;">
    <div class="max-w-screen-xl mx-auto flex items-center justify-between h-16">

        {{-- Logo --}}
        <a href="{{ route('opd.dashboard') }}" class="flex items-center gap-2 shrink-0">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SIPASTI Logo" class="h-8 w-auto">
        </a>

        {{-- Navigation Links --}}
        <div class="flex items-center gap-8">

            {{-- Dashboard --}}
            <a href="{{ route('opd.dashboard') }}"
               class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                      {{ request()->routeIs('opd.dashboard')
                           ? 'text-gray-900 font-semibold border-blue-600'
                           : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                Dashboard
            </a>

            {{-- Buat Pengaduan (seluruh alur diagnosis) --}}
            <a href="{{ route('opd.diagnosis.index') }}"
               class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                      {{ request()->routeIs('opd.diagnosis.*')
                           ? 'text-gray-900 font-semibold border-blue-600'
                           : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                Buat Pengaduan
            </a>

            {{-- Pengaduan Saya --}}
            <a href="{{ route('opd.tiket.index') }}"
               class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                      {{ request()->routeIs('opd.tiket.*')
                           ? 'text-gray-900 font-semibold border-blue-600'
                           : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                Pengaduan Saya
            </a>

            {{-- Pusat Bantuan --}}
            <a href="{{ route('opd.bantuan') }}"
               class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                      {{ request()->routeIs('opd.bantuan') || request()->routeIs('opd.bantuan.*')
                           ? 'text-gray-900 font-semibold border-blue-600'
                           : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                Pusat Bantuan
            </a>

        </div>

        {{-- Right: Bell + User Dropdown --}}
        <div class="flex items-center gap-4 shrink-0">

            {{-- Notification Bell --}}
            <button class="relative text-gray-500 hover:text-gray-800 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </button>

            {{-- User Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 text-sm font-semibold text-gray-900 hover:text-gray-700 transition-colors">
                    <span>{{ Auth::user()->opd?->nama_opd ?? 'Nama OPD' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-4 w-4 text-gray-400 transition-transform duration-200"
                         :class="open ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     @click.outside="open = false"
                     class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1.5 z-50">

                    <div class="px-4 py-2 border-b border-gray-100 mb-1">
                        <p class="text-xs font-semibold text-gray-900 truncate">
                            {{ Auth::user()->opd?->nama_opd ?? 'Nama OPD' }}
                        </p>
                        <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                    </div>

                    <a href="{{ route('opd.profile') }}"
                       class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                             stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Profil Saya
                    </a>

                    <div class="border-t border-gray-100 mt-1 pt-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                     stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</nav>
