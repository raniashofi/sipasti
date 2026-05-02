<?php

namespace App\Http\Controllers\TimTeknis;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Controller;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $teknis = TimTeknis::with('bidang')->where('user_id', Auth::id())->first();

            if (!$teknis) {
                abort(403, 'Data Tim Teknis tidak ditemukan untuk user ini.');
            }

            $lastLogin = ActivityLogController::getLastLoginFormatted(Auth::id());

            $notDibukaKembali = fn($tq) => $tq->whereHas('latestStatus',
                fn($lq) => $lq->where('status_tiket', '!=', 'dibuka_kembali')
            );

            $stats = [
                'aktif' => TiketTeknisi::where('teknis_id', $teknis->id)
                    ->where('status_tugas', 'aktif')
                    ->count(),

                'selesai_utama' => TiketTeknisi::where('teknis_id', $teknis->id)
                    ->where('status_tugas', 'selesai')
                    ->where('peran_teknisi', 'teknisi_utama')
                    ->whereHas('tiket', $notDibukaKembali)
                    ->count(),

                'selesai_pendamping' => TiketTeknisi::where('teknis_id', $teknis->id)
                    ->where('status_tugas', 'selesai')
                    ->where('peran_teknisi', 'teknisi_pendamping')
                    ->whereHas('tiket', $notDibukaKembali)
                    ->count(),

                'total_selesai' => TiketTeknisi::where('teknis_id', $teknis->id)
                    ->where('status_tugas', 'selesai')
                    ->whereHas('tiket', $notDibukaKembali)
                    ->count(),
            ];

            // 5 tugas aktif terbaru
            $tiketAktif = TiketTeknisi::with(['tiket.opd', 'tiket.kategori', 'tiket.latestStatus'])
                ->where('teknis_id', $teknis->id)
                ->where('status_tugas', 'aktif')
                ->latest('waktu_ditugaskan')
                ->limit(5)
                ->get();

            // Distribusi selesai per bulan (6 bulan terakhir)
            $distribusiSelesai = TiketTeknisi::select('waktu_ditugaskan')
                ->where('teknis_id', $teknis->id)
                ->where('status_tugas', 'selesai')
                ->whereHas('tiket', $notDibukaKembali)
                ->where('waktu_ditugaskan', '>=', now()->subMonths(6))
                ->get()
                ->groupBy(fn($t) => \Carbon\Carbon::parse($t->waktu_ditugaskan)->format('Y-n'))
                ->map(fn($group, $key) => (object)[
                    'tahun'  => (int) explode('-', $key)[0],
                    'bulan'  => (int) explode('-', $key)[1],
                    'jumlah' => $group->count(),
                ])
                ->values()
                ->sortBy(fn($item) => $item->tahun * 100 + $item->bulan)
                ->values();

            return view('tim_teknis.dashboard', compact(
                'teknis', 'stats', 'tiketAktif', 'lastLogin', 'distribusiSelesai'
            ));
        } catch (\Exception $e) {
            Log::error('TimTeknis Dashboard Error: ' . $e->getMessage());
            return redirect()->route('tim_teknis.antrean');
        }
    }
}
