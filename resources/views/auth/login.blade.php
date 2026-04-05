<x-guest-layout>

    {{-- Logo --}}
    <div class="anim-fade-in mb-8 text-center">
        <a href="{{ url('/') }}">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti"
                 class="h-14 w-auto mx-auto mb-3">
        </a>
        <p class="text-xs text-gray-400 tracking-widest uppercase font-semibold">Masuk ke Akun Anda</p>
    </div>

    {{-- Card --}}
    <div class="anim-fade-in-up d-100 w-full max-w-sm bg-white rounded-2xl border border-gray-100
                shadow-[0_8px_40px_rgba(1,69,142,0.08)] px-8 py-8">

        {{-- Session status --}}
        <x-auth-session-status class="mb-4 text-sm text-green-600" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Email
                </label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="nama@email.com"
                       class="w-full px-4 py-2.5 text-sm text-black bg-[#F9FAFC] border border-gray-200
                              rounded-lg outline-none transition-all duration-200
                              focus:border-[#01458E] focus:ring-2 focus:ring-[#01458E]/10
                              placeholder:text-gray-300">
                <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-500" />
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label for="password" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Password
                </label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 text-sm text-black bg-[#F9FAFC] border border-gray-200
                              rounded-lg outline-none transition-all duration-200
                              focus:border-[#01458E] focus:ring-2 focus:ring-[#01458E]/10
                              placeholder:text-gray-300">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-500" />
            </div>

            {{-- Remember me + Forgot password --}}
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="flex items-center gap-2 cursor-pointer select-none">
                    <input id="remember_me"
                           type="checkbox"
                           name="remember"
                           class="w-4 h-4 rounded border-gray-300 text-[#01458E] focus:ring-[#01458E]/30 cursor-pointer">
                    <span class="text-xs text-gray-500">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-[#01458E] hover:underline font-medium">
                        Lupa password?
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-3 bg-[#01458E] text-white text-sm font-semibold rounded-xl
                           transition-all duration-200 hover:bg-[#003672] hover:-translate-y-px
                           hover:shadow-[0_6px_20px_rgba(1,69,142,0.30)] active:translate-y-0">
                Masuk &rarr;
            </button>
        </form>
    </div>

    {{-- Back to home --}}
    <div class="anim-fade-in-up d-200 mt-6">
        <a href="{{ url('/') }}"
           class="text-xs text-gray-400 hover:text-[#01458E] transition-colors duration-200 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Beranda
        </a>
    </div>

</x-guest-layout>
