<x-guest-layout>

    {{-- Logo --}}
    <div class="anim-fade-in mb-8 text-center">
        <a href="{{ url('/') }}">
            <img src="{{ asset('storage/logo/logo_sipasti.png') }}" alt="SiPasti"
                 class="h-14 w-auto mx-auto mb-3">
        </a>
        <p class="text-xs text-gray-400 tracking-widest uppercase font-semibold">Buat Password Baru</p>
    </div>

    {{-- Card --}}
    <div class="anim-fade-in-up d-100 w-full max-w-sm bg-white rounded-2xl border border-gray-100
                shadow-[0_8px_40px_rgba(1,69,142,0.08)] px-8 py-8">

        {{-- Error --}}
        @if ($errors->any())
            <div class="flex items-start gap-3 mb-5 px-4 py-3.5 rounded-xl border border-red-200 bg-red-50">
                <div class="shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <div>
                    @foreach ($errors->all() as $error)
                        <p class="text-xs text-red-600">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Email
                </label>
                <input id="email"
                       type="email"
                       name="email"
                       value="{{ old('email', $request->email) }}"
                       required
                       autofocus
                       autocomplete="username"
                       placeholder="nama@email.com"
                       class="w-full px-4 py-2.5 text-sm text-black bg-[#F9FAFC] border border-gray-200
                              rounded-lg outline-none transition-all duration-200
                              focus:border-[#01458E] focus:ring-2 focus:ring-[#01458E]/10
                              placeholder:text-gray-300">
            </div>

            {{-- Password Baru --}}
            <div class="mb-5">
                <label for="password" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Password Baru
                </label>
                <input id="password"
                       type="password"
                       name="password"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 text-sm text-black bg-[#F9FAFC] border border-gray-200
                              rounded-lg outline-none transition-all duration-200
                              focus:border-[#01458E] focus:ring-2 focus:ring-[#01458E]/10
                              placeholder:text-gray-300">
            </div>

            {{-- Konfirmasi Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Konfirmasi Password
                </label>
                <input id="password_confirmation"
                       type="password"
                       name="password_confirmation"
                       required
                       autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 text-sm text-black bg-[#F9FAFC] border border-gray-200
                              rounded-lg outline-none transition-all duration-200
                              focus:border-[#01458E] focus:ring-2 focus:ring-[#01458E]/10
                              placeholder:text-gray-300">
            </div>

            <button type="submit"
                    class="w-full py-3 bg-[#01458E] text-white text-sm font-semibold rounded-xl
                           transition-all duration-200 hover:bg-[#003672] hover:-translate-y-px
                           hover:shadow-[0_6px_20px_rgba(1,69,142,0.30)] active:translate-y-0">
                Simpan Password Baru &rarr;
            </button>
        </form>
    </div>

    {{-- Kembali ke login --}}
    <div class="anim-fade-in-up d-200 mt-6">
        <a href="{{ route('login') }}"
           class="text-xs text-gray-400 hover:text-[#01458E] transition-colors duration-200 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Login
        </a>
    </div>

</x-guest-layout>
