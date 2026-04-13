<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Keamanan & Audit — Super Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* ─── Action badges ─── */
        .action-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 9px; border-radius: 5px;
            font-size: 11px; font-weight: 700; font-family: 'Courier New', monospace;
            white-space: nowrap; letter-spacing: .01em;
        }
        .ab-create   { background:#f0fdf4; color:#16a34a; }
        .ab-update   { background:#fffbeb; color:#d97706; }
        .ab-delete   { background:#fef2f2; color:#dc2626; }
        .ab-escalate { background:#ecfeff; color:#0891b2; }
        .ab-login    { background:#eff6ff; color:#1d4ed8; }
        .ab-logout   { background:#f9fafb; color:#6b7280; border:1px solid #e5e7eb; }
        .ab-approve  { background:#f0fdf4; color:#16a34a; }
        .ab-reject   { background:#fef2f2; color:#dc2626; }

        /* ─── Bidang badges ─── */
        .bb { display:inline-block; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:600; font-family:'Courier New',monospace; }
        .bb-infra    { background:#dbeafe; color:#1e40af; }
        .bb-egov     { background:#ede9fe; color:#5b21b6; }
        .bb-statistik{ background:#d1fae5; color:#065f46; }
        .bb-opd      { background:#fce7f3; color:#9d174d; }
        .bb-default  { background:#f3f4f6; color:#6b7280; }

        /* ─── Delete row highlight ─── */
        tr.row-critical { background:#fff8f8; }
        tr.row-critical:hover { background:#fff0f0 !important; }

        /* ─── Drawer ─── */
        #auditOverlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.32); z-index:100; }
        #auditOverlay.open { display:block; }
        #auditDrawer {
            position:fixed; top:0; right:-450px; bottom:0; width:430px;
            background:#fff; box-shadow:-4px 0 24px rgba(0,0,0,.12);
            z-index:101; transition:right .25s cubic-bezier(.4,0,.2,1); overflow-y:auto;
        }
        #auditDrawer.open { right:0; }

        /* ─── Custom dropdown ─── */
        .cdd-trigger {
            display: flex; align-items: center; justify-content: space-between; gap: 8px;
            padding: 8px 12px; border-radius: 12px;
            border: 1px solid #e5e7eb; background: #F0F4F8;
            font-size: 13px; color: #374151; font-family: 'Inter', sans-serif;
            cursor: pointer; user-select: none; white-space: nowrap;
            transition: border-color .15s, background .15s;
            min-width: 160px;
        }
        .cdd-trigger:hover { border-color: #c7d2fe; background: #eef2ff; }
        .cdd-trigger.active { border-color: #6366f1; background: #eef2ff; }
        .cdd-chevron { transition: transform .2s ease; flex-shrink: 0; }
        .cdd-chevron.open { transform: rotate(180deg); }
        .cdd-menu {
            position: absolute; top: calc(100% + 6px); left: 0;
            min-width: 100%; background: #fff;
            border: 1px solid #e5e7eb; border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            z-index: 50; overflow: hidden;
            transform-origin: top center;
        }
        .cdd-option {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 14px; font-size: 13px; color: #374151;
            cursor: pointer; transition: background .1s;
        }
        .cdd-option:hover { background: #F0F4F8; }
        .cdd-option.selected { color: #01458E; font-weight: 600; background: #EEF3F9; }
        .cdd-option .dot { width:6px; height:6px; border-radius:50%; background:#01458E; opacity:0; flex-shrink:0; }
        .cdd-option.selected .dot { opacity:1; }

        /* ─── Diff boxes in drawer ─── */
        .diff-before { background:#fff7ed; border:1px solid #fed7aa; color:#7c2d12; }
        .diff-after  { background:#fef2f2; border:1px solid #fecaca; color:#dc2626; }
        .diff-after-ok { background:#f0fdf4; border:1px solid #bbf7d0; color:#14532d; }
    </style>
</head>
<body class="bg-[#F0F4F8] min-h-screen">

    @include('layouts.sidebarSuperAdmin')

    {{-- Overlay --}}
    <div id="auditOverlay" onclick="auditCloseDrawer()"></div>

    {{-- Drawer --}}
    <div id="auditDrawer">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:18px 20px;border-bottom:1px solid #f3f4f6;position:sticky;top:0;background:#fff;z-index:1;">
            <div>
                <p style="font-size:14px;font-weight:700;color:#111827;">Detail Log Aktivitas</p>
                <p id="auditDrawerTime" style="font-size:11px;color:#9ca3af;margin-top:2px;"></p>
            </div>
            <button onclick="auditCloseDrawer()"
                    style="width:30px;height:30px;border-radius:8px;background:#f3f4f6;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#6b7280;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="auditDrawerBody" style="padding:20px;"></div>
    </div>

    <div class="ml-64 min-h-screen flex flex-col">

        {{-- ── Top bar ── --}}
        <header class="bg-white border-b border-gray-100 px-8 py-4 flex items-center justify-between sticky top-0 z-30">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Log Aktivitas & Audit Keamanan</h1>
                <p class="text-xs text-gray-400 mt-0.5">Rekam jejak semua aktivitas pengguna dalam sistem</p>
            </div>
            <a href="{{ route('super_admin.audit.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white"
               style="background-color:#01458E;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
        </header>

        <main class="flex-1 px-8 py-7 space-y-5">

            {{-- ── Stat Cards ── --}}
            <div class="grid grid-cols-3 gap-5">

                {{-- Aktivitas Kritis --}}
                <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#FEE2E2;">
                        <svg class="w-5 h-5" style="color:#DC2626;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-tight" style="color:#DC2626;">{{ number_format($aktivitasKritis) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 leading-snug">Aktivitas Kritis Hari Ini<br>(Penghapusan Data)</p>
                    </div>
                </div>

                {{-- Login Berhasil --}}
                <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#D1FAE5;">
                        <svg class="w-5 h-5" style="color:#16A34A;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-tight" style="color:#16A34A;">{{ number_format($loginBerhasil) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Total Login Berhasil Hari Ini</p>
                    </div>
                </div>

                {{-- Total Aktivitas --}}
                <div class="bg-white rounded-2xl p-5 border border-gray-100 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" style="background:#FEF3C7;">
                        <svg class="w-5 h-5" style="color:#D97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold leading-tight" style="color:#D97706;">{{ number_format($totalAktivitas) }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Total Semua Aktivitas Sistem</p>
                    </div>
                </div>
            </div>

            {{-- ── Filter Area ── --}}
            <form method="GET" action="{{ route('super_admin.audit') }}" id="filterForm"
                  class="bg-white rounded-2xl border border-gray-100 px-5 py-4">

                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Filter &amp; Pencarian</p>

                <div class="flex flex-wrap gap-2 items-center">

                    {{-- Tanggal --}}
                    <input type="date" name="tanggal" value="{{ $tanggal }}"
                           onchange="document.getElementById('filterForm').submit()"
                           class="px-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">

                    {{-- ── Role dropdown ── --}}
                    @php
                        $roleOptions = ['' => 'Semua Pengguna', 'super_admin' => 'Super Admin', 'admin_helpdesk' => 'Admin Helpdesk', 'tim_teknis' => 'Tim Teknis', 'pimpinan' => 'Pimpinan', 'opd' => 'OPD'];
                        $roleSelected      = $role ?: '';
                        $roleTriggerLabel  = $roleOptions[$roleSelected] ?? 'Semua Pengguna';
                    @endphp
                    <input type="hidden" name="role_pelaku" id="roleInput" value="{{ $roleSelected }}">
                    <div class="relative"
                         x-data="{
                            open: false,
                            selected: '{{ $roleSelected }}',
                            label: '{{ addslashes($roleTriggerLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('roleInput').value = val;
                                this.open = false;
                                document.getElementById('filterForm').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button" class="cdd-trigger" :class="{ 'active': open }" @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span x-text="label">{{ $roleTriggerLabel }}</span>
                            </span>
                            <svg class="cdd-chevron w-3.5 h-3.5 text-gray-400" :class="{ 'open': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="cdd-menu" x-show="open"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             style="display:none;">
                            @foreach($roleOptions as $val => $lbl)
                            <div class="cdd-option {{ $roleSelected === $val ? 'selected' : '' }}"
                                 :class="{ 'selected': selected === '{{ $val }}' }"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="dot"></span>{{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ── Jenis Aktivitas dropdown ── --}}
                    @php
                        $jenisOptions      = ['' => 'Semua Aktivitas', 'login' => 'LOGIN', 'logout' => 'LOGOUT', 'create' => 'CREATE', 'update' => 'UPDATE', 'delete' => 'DELETE', 'escalate' => 'ESCALATE', 'approve' => 'APPROVE', 'reject' => 'REJECT'];
                        $jenisSelected     = $jenis ?: '';
                        $jenisTriggerLabel = $jenisOptions[$jenisSelected] ?? 'Semua Aktivitas';
                    @endphp
                    <input type="hidden" name="jenis_aktivitas" id="jenisInput" value="{{ $jenisSelected }}">
                    <div class="relative"
                         x-data="{
                            open: false,
                            selected: '{{ $jenisSelected }}',
                            label: '{{ addslashes($jenisTriggerLabel) }}',
                            choose(val, lbl) {
                                this.selected = val; this.label = lbl;
                                document.getElementById('jenisInput').value = val;
                                this.open = false;
                                document.getElementById('filterForm').submit();
                            }
                         }"
                         @click.outside="open = false">
                        <button type="button" class="cdd-trigger" :class="{ 'active': open }" @click="open = !open">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span x-text="label">{{ $jenisTriggerLabel }}</span>
                            </span>
                            <svg class="cdd-chevron w-3.5 h-3.5 text-gray-400" :class="{ 'open': open }"
                                 fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="cdd-menu" x-show="open"
                             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95 -translate-y-1" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"  x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                             style="display:none;">
                            @foreach($jenisOptions as $val => $lbl)
                            <div class="cdd-option {{ $jenisSelected === $val ? 'selected' : '' }}"
                                 :class="{ 'selected': selected === '{{ $val }}' }"
                                 @click="choose('{{ $val }}', '{{ addslashes($lbl) }}')">
                                <span class="dot"></span>{{ $lbl }}
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="flex-1 min-w-[200px] relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/>
                        </svg>
                        <input type="text" name="search" id="searchInput" value="{{ $search }}"
                               placeholder="Cari ID Tiket, IP Address, atau detail tindakan..."
                               oninput="clearTimeout(window._st); window._st = setTimeout(() => document.getElementById('filterForm').submit(), 500)"
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-gray-200 text-sm text-gray-700 bg-[#F0F4F8] focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    {{-- Reset --}}
                    <a href="{{ route('super_admin.audit') }}"
                       class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 border border-gray-200 bg-white hover:bg-gray-50 shrink-0 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset
                    </a>

                </div>
            </form>

            {{-- ── Table Card ── --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between">
                    <p class="text-sm font-semibold text-gray-900">Data Log Audit</p>
                    <p class="text-xs text-gray-400">
                        Menampilkan {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}
                        dari {{ $logs->total() }} entri
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">Waktu (Timestamp)</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">User ID & Nama</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Role & Bidang</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">Jenis Aktivitas</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide">Detail Tindakan</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">IP Address</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $i => $log)
                            @php
                                $nama = $log->user?->opd?->nama_opd
                                    ?? $log->user?->adminHelpdesk?->nama_lengkap
                                    ?? $log->user?->timTeknis?->nama_lengkap
                                    ?? $log->user?->email
                                    ?? '—';

                                $bidangKey = $log->user?->adminHelpdesk?->bidang?->nama_bidang
                                    ?? $log->user?->timTeknis?->bidang?->nama_bidang
                                    ?? null;
                                $bidangMap = [
                                    'e_government'                     => ['label' => 'E-Government',        'css' => 'bb bb-egov'],
                                    'infrastruktur_teknologi_informasi' => ['label' => 'Infrastruktur TI',    'css' => 'bb bb-infra'],
                                    'statistik_persandian'             => ['label' => 'Statistik & Persandian','css' => 'bb bb-statistik'],
                                ];
                                $bidangInfo = $bidangKey ? ($bidangMap[$bidangKey] ?? null) : null;

                                $actionMap = [
                                    'create'   => ['css' => 'ab-create',   'icon' => '✦', 'label' => 'CREATE'],
                                    'update'   => ['css' => 'ab-update',   'icon' => '✎', 'label' => 'UPDATE'],
                                    'delete'   => ['css' => 'ab-delete',   'icon' => '✕', 'label' => 'DELETE'],
                                    'escalate' => ['css' => 'ab-escalate', 'icon' => '↑', 'label' => 'ESCALATE'],
                                    'login'    => ['css' => 'ab-login',    'icon' => '→', 'label' => 'LOGIN'],
                                    'logout'   => ['css' => 'ab-logout',   'icon' => '←', 'label' => 'LOGOUT'],
                                    'approve'  => ['css' => 'ab-approve',  'icon' => '✓', 'label' => 'APPROVE'],
                                    'reject'   => ['css' => 'ab-reject',   'icon' => '✗', 'label' => 'REJECT'],
                                ];
                                $action     = $actionMap[$log->jenis_aktivitas] ?? ['css' => 'ab-logout', 'icon' => '·', 'label' => strtoupper($log->jenis_aktivitas)];
                                $isCritical = $log->jenis_aktivitas === 'delete';
                                $userId = $log->user_id ? strtoupper($log->user_id) : '—';
                            @endphp
                            <tr class="border-b border-gray-50 cursor-pointer transition-colors {{ $isCritical ? 'row-critical' : 'hover:bg-gray-50' }}"
                                onclick="auditOpenDrawer({{ $i }})">

                                {{-- Waktu --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <p class="text-xs font-semibold text-gray-900 font-mono">{{ $log->waktu_eksekusi?->format('d M Y') ?? '—' }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $log->waktu_eksekusi?->format('H:i:s') ?? '' }} WIB</p>
                                </td>

                                {{-- User ID & Nama --}}
                                <td class="px-5 py-3.5">
                                    <p class="text-xs text-gray-400 font-mono">{{ $userId }}</p>
                                    <p class="text-sm font-semibold text-gray-900 truncate max-w-[150px]">{{ $nama }}</p>
                                </td>

                                {{-- Role & Bidang --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs text-gray-500">{{ $roleLabel[$log->role_pelaku] ?? $log->role_pelaku }}</span>
                                        @if($bidangInfo)
                                        <span class="{{ $bidangInfo['css'] }}">{{ $bidangInfo['label'] }}</span>
                                        @elseif($log->role_pelaku === 'opd')
                                        <span class="bb bb-opd">OPD</span>
                                        @elseif($log->role_pelaku === 'super_admin')
                                        <span class="bb bb-default">Super Admin</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Jenis Aktivitas --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="action-badge {{ $action['css'] }}">{{ $action['icon'] }} {{ $action['label'] }}</span>
                                </td>

                                {{-- Detail Tindakan --}}
                                <td class="px-5 py-3.5">
                                    <p class="text-xs text-gray-500 truncate max-w-[200px]">{{ $log->detail_tindakan ?? '—' }}</p>
                                    @if($log->id_record)
                                    <p class="text-xs font-mono font-semibold mt-0.5 truncate max-w-[200px]" style="color:#01458E;">{{ $log->id_record }}</p>
                                    @endif
                                </td>

                                {{-- IP Address --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="text-xs font-mono text-gray-400">{{ $log->ip_address ?? '—' }}</span>
                                </td>

                                {{-- Arrow --}}
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-gray-300 text-base">›</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-400">Belum ada aktivitas yang tercatat</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($logs->hasPages())
                <div class="px-6 py-4 border-t border-gray-50 flex items-center justify-between">
                    <p class="text-xs text-gray-400">
                        Halaman {{ $logs->currentPage() }} dari {{ $logs->lastPage() }} · Total {{ $logs->total() }} entri
                    </p>
                    <div class="flex items-center gap-1">

                        @if($logs->onFirstPage())
                        <span class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-100 text-gray-300 cursor-not-allowed">‹</span>
                        @else
                        <a href="{{ $logs->previousPageUrl() }}" class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50">‹</a>
                        @endif

                        @foreach($logs->getUrlRange(max(1,$logs->currentPage()-2), min($logs->lastPage(),$logs->currentPage()+2)) as $page => $url)
                        <a href="{{ $url }}"
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold border transition-colors
                                  {{ $page == $logs->currentPage() ? 'text-white border-transparent' : 'text-gray-600 border-gray-200 hover:bg-gray-50' }}"
                           @if($page == $logs->currentPage()) style="background-color:#01458E;" @endif>
                            {{ $page }}
                        </a>
                        @endforeach

                        @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-500 hover:bg-gray-50">›</a>
                        @else
                        <span class="px-2 py-1.5 rounded-lg text-xs font-semibold border border-gray-100 text-gray-300 cursor-not-allowed">›</span>
                        @endif

                    </div>
                </div>
                @endif

            </div>{{-- end table card --}}

        </main>
    </div>

    <script>
    const _logsData = @json($logsJs);

    const _actionBadge = {
        create:   { css:'ab-create',   icon:'✦', label:'CREATE' },
        update:   { css:'ab-update',   icon:'✎', label:'UPDATE' },
        delete:   { css:'ab-delete',   icon:'✕', label:'DELETE' },
        escalate: { css:'ab-escalate', icon:'↑', label:'ESCALATE' },
        login:    { css:'ab-login',    icon:'→', label:'LOGIN' },
        logout:   { css:'ab-logout',   icon:'←', label:'LOGOUT' },
        approve:  { css:'ab-approve',  icon:'✓', label:'APPROVE' },
        reject:   { css:'ab-reject',   icon:'✗', label:'REJECT' },
    };

    const _bidangBadge = {
        e_government:                     { css:'bb bb-egov',       label:'E-Government' },
        infrastruktur_teknologi_informasi: { css:'bb bb-infra',      label:'Infrastruktur TI' },
        statistik_persandian:             { css:'bb bb-statistik',  label:'Statistik & Persandian' },
    };

    const _roleLabel = {
        super_admin:    'Super Admin',
        admin_helpdesk: 'Admin Helpdesk',
        tim_teknis:     'Tim Teknis',
        pimpinan:       'Pimpinan',
        opd:            'OPD',
    };

    function escHtml(s) {
        return String(s ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function sectionTitle(t) {
        return `<div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;
                            margin-bottom:10px;padding-bottom:6px;border-bottom:1px solid #f3f4f6;">${t}</div>`;
    }

    function detailRow(label, valHtml) {
        return `<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;">
                    <span style="font-size:12px;color:#9ca3af;white-space:nowrap;">${label}</span>
                    <span style="font-size:12px;font-weight:600;text-align:right;max-width:230px;word-break:break-all;">${valHtml}</span>
                </div>`;
    }

    function monoVal(v) {
        return `<span style="font-family:'Courier New',monospace;font-size:11px;">${escHtml(v)}</span>`;
    }

    function auditOpenDrawer(index) {
        const d = _logsData[index];
        if (!d) return;

        const ab       = _actionBadge[d.jenis_aktivitas] ?? { css:'ab-logout', icon:'·', label:(d.jenis_aktivitas??'').toUpperCase() };
        const bb       = _bidangBadge[d.bidang_key] ?? null;
        const isDelete = d.jenis_aktivitas === 'delete';

        const badgeHtml  = `<span class="action-badge ${ab.css}">${ab.icon} ${ab.label}</span>`;
        const bidangHtml = bb
            ? `${escHtml(_roleLabel[d.role_pelaku]??d.role_pelaku)} · <span class="${bb.css}">${bb.label}</span>`
            : escHtml(_roleLabel[d.role_pelaku] ?? d.role_pelaku);

        const beforeJson = d.data_before ? JSON.stringify(d.data_before, null, 2) : '[ Tidak ada data sebelumnya ]';
        const afterJson  = d.data_after  ? JSON.stringify(d.data_after, null, 2)  : (isDelete ? '[ DATA DELETED ]' : '[ Tidak ada perubahan ]');
        const afterCss   = isDelete ? 'diff-after' : 'diff-after-ok';
        const afterIcon  = isDelete ? 'AFTER (Data Dihapus)' : 'AFTER (Data Baru)';

        document.getElementById('auditDrawerTime').textContent = d.waktu ?? '';
        document.getElementById('auditDrawerBody').innerHTML = `

            <div style="margin-bottom:20px;">
                ${sectionTitle('Informasi Aksi')}
                ${detailRow('Jenis Aktivitas', badgeHtml)}
                ${detailRow('Waktu Eksekusi',  monoVal(d.waktu))}
                ${detailRow('IP Address',      monoVal(d.ip_address))}
                ${detailRow('Session ID',      monoVal(d.session_id))}
                ${detailRow('Status',
                    `<span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Berhasil</span>`
                )}
            </div>

            <div style="margin-bottom:20px;">
                ${sectionTitle('Informasi Aktor')}
                ${detailRow('Nama',        escHtml(d.nama))}
                ${detailRow('Email',       monoVal(d.email))}
                ${detailRow('Role & Bidang', bidangHtml)}
            </div>

            ${(d.nama_tabel && d.nama_tabel !== '—') ? `
            <div style="margin-bottom:20px;">
                ${sectionTitle('Target Data')}
                ${detailRow('Tabel / Modul',
                    `Tabel <code style="background:#f1f5f9;padding:1px 5px;border-radius:3px;font-size:11px;">${escHtml(d.nama_tabel)}</code>`
                )}
                ${detailRow('ID Record',
                    `<code style="font-family:'Courier New',monospace;font-size:11px;color:#01458E;font-weight:600;">${escHtml(d.id_record)}</code>`
                )}
            </div>
            ` : ''}

            <div>
                ${sectionTitle('Perubahan Data (Before → After)')}
                <div class="diff-before" style="border-radius:10px;padding:12px;margin-bottom:10px;font-family:'Courier New',monospace;font-size:11px;line-height:1.6;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;opacity:.7;">BEFORE (Data Lama)</div>
                    <pre style="white-space:pre-wrap;word-break:break-all;margin:0;">${escHtml(beforeJson)}</pre>
                </div>
                <div class="${afterCss}" style="border-radius:10px;padding:12px;font-family:'Courier New',monospace;font-size:11px;line-height:1.6;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;opacity:.7;">${afterIcon}</div>
                    <pre style="white-space:pre-wrap;word-break:break-all;margin:0;">${escHtml(afterJson)}</pre>
                </div>
            </div>
        `;

        document.getElementById('auditOverlay').classList.add('open');
        document.getElementById('auditDrawer').classList.add('open');
    }

    function auditCloseDrawer() {
        document.getElementById('auditOverlay').classList.remove('open');
        document.getElementById('auditDrawer').classList.remove('open');
    }
    </script>

</body>
</html>
