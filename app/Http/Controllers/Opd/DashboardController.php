<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\Tiket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $opd   = Auth::user()->opd;
        $opdId = $opd->id;

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

        return view('opd.dashboard', compact('opd', 'stats', 'tiketTerbaru'));
    }
}
