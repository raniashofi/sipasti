<nav class="bg-white border-b border-gray-200 px-4 sm:px-8 py-0" style="font-family: 'Inter', sans-serif;">
    <div x-data="{ mobileOpen: false }" class="max-w-screen-xl mx-auto">

        {{-- Desktop / Mobile top row --}}
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('opd.dashboard') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SIPASTI Logo" class="h-8 w-auto">
            </a>

            {{-- Desktop Navigation Links --}}
            <div class="hidden lg:flex items-center gap-8">

                <a href="{{ route('opd.dashboard') }}"
                   class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                          {{ request()->routeIs('opd.dashboard')
                               ? 'text-gray-900 font-semibold border-blue-600'
                               : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                    Dashboard
                </a>

                <a href="{{ route('opd.diagnosis.index') }}"
                   class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                          {{ request()->routeIs('opd.diagnosis.*')
                               ? 'text-gray-900 font-semibold border-blue-600'
                               : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                    Buat Pengaduan
                </a>

                <a href="{{ route('opd.tiket.index') }}"
                   class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                          {{ request()->routeIs('opd.tiket.*')
                               ? 'text-gray-900 font-semibold border-blue-600'
                               : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                    Pengaduan Saya
                </a>

                <a href="{{ route('opd.bantuan') }}"
                   class="relative flex items-center h-16 text-sm font-medium transition-colors border-b-2
                          {{ request()->routeIs('opd.bantuan') || request()->routeIs('opd.bantuan.*')
                               ? 'text-gray-900 font-semibold border-blue-600'
                               : 'text-gray-400 hover:text-gray-700 border-transparent' }}">
                    Pusat Bantuan
                </a>

            </div>

            {{-- Right: Bell + User Dropdown + Mobile Hamburger --}}
            <div class="flex items-center gap-3 shrink-0">

                {{-- Notification Bell --}}
                <x-notification-bell layout="topbar" />

                {{-- User Dropdown (desktop) --}}
                <div x-data="{ open: false }" class="relative hidden lg:block">
                    <button @click="open = !open"
                            class="flex items-center gap-1.5 text-sm font-semibold text-gray-900 hover:text-gray-700 transition-colors">
                        <span>{{ Auth::user()->opd?->nama_opd ?? 'Nama OPD' }}</span>
                        <svg class="h-4 w-4 text-gray-400 transition-transform duration-200"
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
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profil Saya
                        </a>

                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                    <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

            </div>
        </div>

        {{-- Mobile menu dropdown --}}
        <div x-show="mobileOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-gray-100 py-3 space-y-1">

            <a href="{{ route('opd.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('opd.dashboard') ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                Dashboard
            </a>

            <a href="{{ route('opd.diagnosis.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('opd.diagnosis.*') ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                Buat Pengaduan
            </a>

            <a href="{{ route('opd.tiket.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('opd.tiket.*') ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                Pengaduan Saya
            </a>

            <a href="{{ route('opd.bantuan') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                      {{ request()->routeIs('opd.bantuan') || request()->routeIs('opd.bantuan.*') ? 'bg-blue-50 text-gray-900 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                Pusat Bantuan
            </a>

            {{-- User info + logout --}}
            <div class="border-t border-gray-100 pt-3 mt-2 px-1">
                <div class="flex items-center gap-3 mb-3 px-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0" style="background-color:#01458E;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->opd?->nama_opd ?? 'Nama OPD' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <a href="{{ route('opd.profile') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    Profil Saya
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm text-red-500 hover:bg-red-50 transition-colors">
                        Keluar
                    </button>
                </form>
            </div>

        </div>

    </div>
</nav>
