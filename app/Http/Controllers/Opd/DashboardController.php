<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ActivityLogController;
use App\Models\Tiket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $opd  = $user->opd;

        if (!$opd) {
            $stats        = ['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0];
            $tiketAktif   = 0;
            $tiketSelesai = 0;
            $tiketTotal   = 0;
            $tiketTerbaru = collect();
            $lastLogin    = null;
            return view('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        }

        try {
            $opdId = $opd->id;

            $lastLogin = ActivityLogController::getLastLoginFormatted($user->id);

            $aktifStatus = ['verifikasi_admin', 'panduan_remote', 'perbaikan_teknis', 'rusak_berat'];

            $stats = [
                'total'   => Tiket::where('opd_id', $opdId)->count(),
                'aktif'   => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->whereIn('status_tiket', $aktifStatus))
                                  ->count(),
                'revisi'  => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->where('status_tiket', 'perlu_revisi'))
                                  ->count(),
                'selesai' => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->where('status_tiket', 'selesai'))
                                  ->count(),
            ];

            $tiketAktif   = $stats['aktif'];
            $tiketSelesai = $stats['selesai'];
            $tiketTotal   = $stats['total'];

            $tiketTerbaru = Tiket::where('opd_id', $opdId)
                ->with('latestStatus')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            return view('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        } catch (\Exception $e) {
            Log::error('Opd Dashboard Error: ' . $e->getMessage());
            $stats        = ['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0];
            $tiketAktif   = 0;
            $tiketSelesai = 0;
            $tiketTotal   = 0;
            $tiketTerbaru = collect();
            $lastLogin    = null;
            return view('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        }
    }
}
