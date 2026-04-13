<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\KnowledgeBase;
use App\Models\KnowledgeBaseRating;
use App\Models\User;
use App\Models\Opd;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TimTeknis;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_opd'         => User::where('role', 'opd')->count(),
            'total_internal'    => User::whereIn('role', ['admin_helpdesk', 'tim_teknis', 'pimpinan'])->count(),
            'total_tiket'       => Tiket::count(),
            'total_kb'          => KnowledgeBase::count(),
            // Metrik baru
            'kb_published'      => KnowledgeBase::where('status_publikasi', 'published')->count(),
            'total_kb_ratings'  => KnowledgeBaseRating::count(),
            'avg_penilaian'     => round(Tiket::whereNotNull('penilaian')->avg('penilaian') ?? 0, 1),
            'tiket_selesai'     => Tiket::whereHas('statusTiket', fn($q) =>
                                       $q->where('status_tiket', 'selesai')
                                   )->count(),
        ];

        // Tiket per status for chart
        $statusLabels = [
            'verifikasi_admin'  => 'Verifikasi Admin',
            'panduan_remote'    => 'Panduan Remote',
            'perbaikan_teknis'  => 'Perbaikan Teknis',
            'rusak_berat'       => 'Rusak Berat',
            'perlu_revisi'      => 'Perlu Revisi',
            'selesai'           => 'Selesai',
            'dibuka_kembali'    => 'Dibuka Kembali',
        ];

        $tiketPerStatus = [];
        foreach ($statusLabels as $key => $label) {
            $tiketPerStatus[$label] = StatusTiket::where('status_tiket', $key)
                ->whereIn('id', function ($q) {
                    $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id');
                })
                ->count();
        }

        // Recent activity logs
        $recentActivity = ActivityLog::with('user')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('super_admin.dashboard', compact('stats', 'tiketPerStatus', 'recentActivity'));
    }
}
