<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\KnowledgeBase;
use App\Models\StatusTiket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $adminProfile = AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
        $adminId      = $adminProfile?->id;
        $bidangId     = $adminProfile?->bidang_id;

        // Helper: filter tiket berdasarkan bidang admin (via kb → kategori → bidang)
        $filterBidang = function ($query) use ($bidangId) {
            if ($bidangId) {
                $query->whereNotNull('kb_id')
                      ->whereHas('kb.kategori', fn($q) => $q->where('bidang_id', $bidangId));
            }
        };

        // Stat cards — semua difilter per bidang admin
        $menungguVerif = StatusTiket::where('status_tiket', 'verifikasi_admin')
            ->whereIn('id', fn($q) => $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id'))
            ->whereHas('tiket', $filterBidang)
            ->count();

        $pandуanRemote = $adminId
            ? StatusTiket::where('status_tiket', 'panduan_remote')
                ->whereIn('id', fn($q) => $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id'))
                ->whereHas('tiket', fn($q) => $q->where('admin_id', $adminId))
                ->count()
            : 0;

        $eskalasi = $adminId
            ? StatusTiket::where('status_tiket', 'perbaikan_teknis')
                ->whereIn('id', fn($q) => $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id'))
                ->whereHas('tiket', fn($q) => $q->where('admin_id', $adminId))
                ->count()
            : 0;

        $selesai = $adminId
            ? StatusTiket::where('status_tiket', 'selesai')
                ->whereIn('id', fn($q) => $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id'))
                ->whereHas('tiket', fn($q) => $q->where('admin_id', $adminId))
                ->count()
            : 0;

        $stats = [
            'menunggu_verif' => $menungguVerif,
            'panduan_remote' => $pandуanRemote,
            'eskalasi'       => $eskalasi,
            'selesai'        => $selesai,
            'total_kb'       => KnowledgeBase::where('status_publikasi', 'published')->count(),
        ];

        // Distribusi status — semua difilter per bidang admin
        $statusLabels = [
            'verifikasi_admin' => 'Menunggu Verif',
            'perlu_revisi'     => 'Perlu Revisi',
            'panduan_remote'   => 'Panduan Remote',
            'perbaikan_teknis' => 'Perbaikan Teknis',
            'rusak_berat'      => 'Rusak Berat',
            'selesai'          => 'Selesai',
        ];

        $tiketPerStatus = [];
        foreach ($statusLabels as $key => $label) {
            $query = StatusTiket::where('status_tiket', $key)
                ->whereIn('id', fn($q) => $q->selectRaw('MAX(id)')->from('status_tiket')->groupBy('tiket_id'));

            if ($key === 'verifikasi_admin') {
                // Belum ada admin_id → filter lewat bidang (kb → kategori → bidang)
                $query->whereHas('tiket', $filterBidang);
            } elseif ($adminId) {
                // Sudah diterima admin ini → filter lewat admin_id
                $query->whereHas('tiket', fn($q) => $q->where('admin_id', $adminId));
            }

            $tiketPerStatus[$label] = $query->count();
        }

        // Log aktivitas semua admin helpdesk dari berbagai bidang
        $recentActivity = ActivityLog::with('user')
            ->where('role_pelaku', 'admin_helpdesk')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin_helpdesk.dashboard', compact(
            'stats', 'tiketPerStatus', 'recentActivity', 'adminProfile'
        ));
    }
}
