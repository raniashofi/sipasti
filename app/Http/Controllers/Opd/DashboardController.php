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
        $user = $this->authenticatedUser();
        $opd  = $user->opd;

        if (!$opd) {
            $stats        = ['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0];
            $tiketAktif   = 0;
            $tiketSelesai = 0;
            $tiketTotal   = 0;
            $tiketTerbaru = collect();
            $lastLogin    = null;
            return $this->renderView('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        }

        try {
            $opdId = $opd->id;

            $lastLogin = $this->lastLoginFormatted($user->id);

            $stats = $this->ticketStats($opdId);

            $tiketAktif   = $stats['aktif'];
            $tiketSelesai = $stats['selesai'];
            $tiketTotal   = $stats['total'];

            $tiketTerbaru = $this->latestTickets($opdId);

            return $this->renderView('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        } catch (\Exception $e) {
            $this->logError('Opd Dashboard Error: ' . $e->getMessage());
            $stats        = ['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0];
            $tiketAktif   = 0;
            $tiketSelesai = 0;
            $tiketTotal   = 0;
            $tiketTerbaru = collect();
            $lastLogin    = null;
            return $this->renderView('opd.dashboard', compact('opd', 'stats', 'tiketAktif', 'tiketSelesai', 'tiketTotal', 'tiketTerbaru', 'lastLogin'));
        }
    }

    protected function authenticatedUser()
    {
        return Auth::user();
    }

    protected function lastLoginFormatted(string $userId)
    {
        return ActivityLogController::getLastLoginFormatted($userId);
    }

    protected function ticketStats(string $opdId): array
    {
        $aktifStatus = ['verifikasi_admin', 'panduan_remote', 'perbaikan_teknis', 'rusak_berat'];

        return [
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
    }

    protected function latestTickets(string $opdId)
    {
        return Tiket::where('opd_id', $opdId)
            ->with('latestStatus')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    protected function logError(string $message): void
    {
        Log::error($message);
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }
}
