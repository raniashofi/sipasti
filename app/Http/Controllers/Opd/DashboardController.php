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
        try {
            $user = Auth::user();
            $opd  = $user->opd;

            if (!$opd) {
                abort(403, 'Data OPD tidak ditemukan untuk user ini.');
            }

            $opdId = $opd->id;

            // Ambil last login dari activity_log
            $lastLogin = $user->id ? ActivityLogController::getLastLoginFormatted($user->id) : null;

            $aktifStatus = ['verifikasi_admin', 'panduan_remote', 'perbaikan_teknis', 'rusak_berat'];

            $stats = [
                'total'    => Tiket::where('opd_id', $opdId)->count(),
                'aktif'    => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->whereIn('status_tiket', $aktifStatus))
                                  ->count(),
                'revisi'   => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->where('status_tiket', 'perlu_revisi'))
                                  ->count(),
                'selesai'  => Tiket::where('opd_id', $opdId)
                                  ->whereHas('statusTiket', fn($q) => $q->where('status_tiket', 'selesai'))
                                  ->count(),
            ];

            $tiketTerbaru = Tiket::where('opd_id', $opdId)
                ->with('latestStatus')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

            return view('opd.dashboard', compact('opd', 'stats', 'tiketTerbaru', 'lastLogin'));
        } catch (\Exception $e) {
            Log::error('Opd Dashboard Error: ' . $e->getMessage());
            return view('dashboard');
        }
    }
}
