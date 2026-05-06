<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya — SiPasti</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #F4F6FA; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fu  { animation: fadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }
        .fu1 { animation-delay: 0.04s; }
        .fu2 { animation-delay: 0.10s; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarAdminHelpdesk')

    <div class="ml-0 lg:ml-64 min-h-screen flex flex-col">

        {{-- Top Bar --}}
        <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 pl-14 pr-4 lg:px-8 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Profil Saya</h1>
                <p class="text-sm text-gray-400 mt-0.5">Informasi akun dan keamanan</p>
            </div>
        </header>

        <main class="flex-1 px-8 py-8">
            <div class="max-w-3xl space-y-6">

                {{-- Alert --}}
                @if(session('success'))
                <div class="fu fu1 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif

                {{-- Informasi Profil --}}
                <div class="fu fu1 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Informasi Akun</h2>
                    </div>
                    <div class="px-6 py-5 space-y-4">

                        {{-- Avatar --}}
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0"
                                 style="background-color:#01458E;">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                     stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 18v-6a9 9 0 0118 0v6"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3zM3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-base font-bold text-gray-900">{{ $profil?->nama_lengkap ?? '—' }}</p>
                                <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#01458E]">
                                    Admin Helpdesk
                                </span>
                            </div>
                        </div>

                        @php
                            $bidangNama = $profil?->bidang?->nama_bidang ?? '—';
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Nama Lengkap</p>
                                <p class="text-sm font-medium text-gray-800">{{ $profil?->nama_lengkap ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Bidang</p>
                                <p class="text-sm font-medium text-gray-800">{{ $bidangNama }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Email</p>
                                <p class="text-sm font-medium text-gray-800">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 mb-1">Login Terakhir</p>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $user->last_login_at ? $user->last_login_at->locale('id')->isoFormat('D MMM YYYY, HH:mm') : '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ubah Password --}}
                <div class="fu fu2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Ubah Password</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Pastikan password baru minimal 8 karakter</p>
                    </div>
                    <form method="POST" action="{{ route('admin_helpdesk.profile.password') }}" class="px-6 py-5 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Password Lama</label>
                            <div class="relative">
                                <input type="password" name="password_lama" id="password_lama"
                                       class="w-full px-4 py-2.5 pr-10 rounded-xl border text-sm
                                              @error('password_lama') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                              focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-[#01458E] transition"
                                       placeholder="Masukkan password lama">
                                <button type="button" onclick="togglePassword('password_lama', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password_lama')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Password Baru</label>
                            <div class="relative">
                                <input type="password" name="password_baru" id="password_baru"
                                       class="w-full px-4 py-2.5 pr-10 rounded-xl border text-sm
                                              @error('password_baru') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                              focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-[#01458E] transition"
                                       placeholder="Minimal 8 karakter">
                                <button type="button" onclick="togglePassword('password_baru', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password_baru')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input type="password" name="password_baru_confirmation" id="password_baru_confirmation"
                                       class="w-full px-4 py-2.5 pr-10 rounded-xl border text-sm
                                              @error('password_baru_confirmation') border-red-400 bg-red-50 @else border-gray-200 @enderror
                                              focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-[#01458E] transition"
                                       placeholder="Ulangi password baru">
                                <button type="button" onclick="togglePassword('password_baru_confirmation', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password_baru_confirmation')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                                    style="background-color:#01458E;">
                                Simpan Password
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>

</body>
</html>
