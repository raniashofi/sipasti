{{--
    Komponen Notification Bell
    Props:
      $layout = 'topbar'  → dropdown muncul ke bawah, cocok untuk topbar OPD
      $layout = 'sidebar' → dropdown muncul ke kanan sidebar, cocok untuk sidebar role lain
--}}
@props(['layout' => 'topbar'])

<div x-data="notifBell()" x-init="init()" class="relative" @click.outside="open = false">

    {{-- ── Tombol Bell ── --}}
    @if($layout === 'topbar')
        {{-- Topbar: icon saja --}}
        <button @click="toggle()"
                class="relative text-gray-500 hover:text-gray-800 transition-colors focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            {{-- Badge unread --}}
            <span x-show="unread > 0"
                  x-text="unread > 9 ? '9+' : unread"
                  class="absolute -top-1.5 -right-1.5 min-w-[16px] h-4 px-0.5 rounded-full text-[9px] font-bold text-white flex items-center justify-center leading-none"
                  style="background:#DC2626;"></span>
        </button>
    @else
        {{-- Sidebar: row item dengan label --}}
        <button @click="toggle()"
                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors text-gray-400 hover:text-gray-700 hover:bg-gray-50 focus:outline-none">
            <div class="relative shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="unread > 0"
                      x-text="unread > 9 ? '9+' : unread"
                      class="absolute -top-1.5 -right-1.5 min-w-[14px] h-3.5 px-0.5 rounded-full text-[8px] font-bold text-white flex items-center justify-center leading-none"
                      style="background:#DC2626;"></span>
            </div>
            <span class="text-sm flex-1">Notifikasi</span>
        </button>
    @endif

    {{-- ── Dropdown Panel (DI SINI PERBAIKAN RESPONSIVE TENGAHNYA) ── --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="z-[9999] bg-white rounded-2xl shadow-2xl border border-gray-100
                {{ $layout === 'topbar'
                    ? 'fixed top-[70px] left-1/2 -translate-x-1/2 w-[92vw] max-w-[360px] sm:absolute sm:top-full sm:left-auto sm:transform-none sm:right-0 sm:w-80 sm:mt-2'
                    : 'absolute left-0 bottom-full mb-2 w-[90vw] max-w-[320px] sm:left-full sm:ml-2 sm:bottom-0 sm:mb-0 sm:w-80' }}">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <p class="text-sm font-bold text-gray-900">Notifikasi</p>
                <span x-show="unread > 0"
                      x-text="unread + ' baru'"
                      class="text-[10px] font-semibold px-2 py-0.5 rounded-full text-white"
                      style="background:#DC2626;"></span>
            </div>
            <button x-show="unread > 0" @click="markAll()"
                    class="text-[11px] font-medium text-[#01458E] hover:underline transition-colors">
                Tandai semua dibaca
            </button>
        </div>

        {{-- List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">

            {{-- Empty state --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                    <div class="w-10 h-10 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-400">Belum ada notifikasi</p>
                </div>
            </template>

            {{-- Notification items --}}
            <template x-for="n in items" :key="n.id">
                <a :href="n.url || '#'"
                   @click.prevent="handleClick(n)"
                   class="flex items-start gap-3 px-4 py-3 transition-colors cursor-pointer"
                   :class="n.read ? 'hover:bg-gray-50' : 'bg-blue-50/60 hover:bg-blue-50'">

                    {{-- Dot indicator --}}
                    <div class="mt-1.5 shrink-0">
                        <div class="w-2 h-2 rounded-full transition-colors"
                             :class="n.read ? 'bg-gray-200' : 'bg-[#01458E]'"></div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-900 leading-snug"
                           :class="n.read ? 'font-medium text-gray-600' : 'font-semibold text-gray-900'"
                           x-text="n.title"></p>
                        <p class="text-[11px] text-gray-500 mt-0.5 leading-relaxed line-clamp-2"
                           x-text="n.body"></p>
                        <p class="text-[10px] text-gray-400 mt-1" x-text="n.created_at"></p>
                    </div>
                </a>
            </template>
        </div>

        {{-- Footer Dinamis Maksimal 10 --}}
        <div x-show="items.length > 0" class="px-4 py-2.5 border-t border-gray-100 text-center bg-gray-50/50">
            <p class="text-[11px] text-gray-400" x-text="'Menampilkan ' + items.length + ' notifikasi terbaru'"></p>
        </div>
    </div>
</div>

<script>
if (typeof notifBell === 'undefined') {
    function notifBell() {
        return {
            open:   false,
            unread: 0,
            items:  [],

            init() {
                this.fetchAll();

                if (window.Echo) {
                    window.Echo.private('notifications.{{ Auth::id() }}')
                        .notification((data) => {
                            if (this.items.some(item => item.id === data.id)) return;

                            this.items.unshift({
                                id:         data.id,
                                icon:       data.icon  ?? 'default',
                                title:      data.title ?? '',
                                body:       data.body  ?? '',
                                url:        data.url   ?? null,
                                read:       false,
                                created_at: 'Baru saja',
                            });

                            // Batasi 10 notifikasi di dropdown agar tidak terlalu panjang
                            if (this.items.length > 10) {
                                this.items.pop();
                            }

                            this.unread++;
                        });
                }
            },

            toggle() {
                this.open = !this.open;
                if (this.open) this.fetchAll();
            },

            async fetchAll() {
                try {
                    const res = await fetch('/notif', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                    });
                    const data = await res.json();

                    // Slice data maksimal 10
                    this.items  = (data.notifications || []).slice(0, 10);
                    this.unread = data.unread_count;
                } catch (e) { /* silent fail */ }
            },

            async handleClick(n) {
                if (!n.read) {
                    n.read = true;
                    this.unread = Math.max(0, this.unread - 1);
                    await fetch(`/notif/${n.id}/read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        },
                    });
                }
                if (n.url) window.location.href = n.url;
            },

            async markAll() {
                this.items.forEach(n => { n.read = true; });
                this.unread = 0;
                await fetch('/notif/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                    },
                });
            },
        };
    }
}
</script>
