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
            $teknis = $this->findTeknisProfile();

            if (!$teknis) {
                abort(403, 'Data Tim Teknis tidak ditemukan untuk user ini.');
            }

            $lastLogin = $this->lastLoginFormatted();
            $stats = $this->buildStats($teknis);
            $tiketAktif = $this->activeTickets($teknis->id);
            $distribusiSelesai = $this->completedDistribution($teknis->id);

            return $this->renderView('tim_teknis.dashboard', compact(
                'teknis', 'stats', 'tiketAktif', 'lastLogin', 'distribusiSelesai'
            ));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('TimTeknis Dashboard Error: ' . $e->getMessage());
            return redirect()->route('tim_teknis.antrean');
        }
    }

    // ── Protected methods ────────────────────────────────────

    protected function findTeknisProfile()
    {
        return TimTeknis::with('bidang')->where('user_id', Auth::id())->first();
    }

    protected function lastLoginFormatted()
    {
        return ActivityLogController::getLastLoginFormatted(Auth::id());
    }

    protected function buildStats($teknis): array
    {
        $notDibukaKembali = fn($tq) => $tq->whereHas('latestStatus',
            fn($lq) => $lq->where('status_tiket', '!=', 'dibuka_kembali')
        );

        $stats = [
            'aktif' => TiketTeknisi::where('teknis_id', $teknis->id)
                ->where('status_tugas', 'aktif')->count(),
            'selesai_utama' => TiketTeknisi::where('teknis_id', $teknis->id)
                ->where('status_tugas', 'selesai')->where('peran_teknisi', 'teknisi_utama')
                ->whereHas('tiket', $notDibukaKembali)->count(),
            'selesai_pendamping' => TiketTeknisi::where('teknis_id', $teknis->id)
                ->where('status_tugas', 'selesai')->where('peran_teknisi', 'teknisi_pendamping')
                ->whereHas('tiket', $notDibukaKembali)->count(),
            'total_selesai' => TiketTeknisi::where('teknis_id', $teknis->id)
                ->where('status_tugas', 'selesai')->whereHas('tiket', $notDibukaKembali)->count(),
        ];

        $batasHari = $teknis->bidang?->batas_hari_pengerjaan;
        $stats['tepat_waktu'] = 0;
        $stats['telat'] = 0;

        if ($batasHari) {
            $selesaiTugas = TiketTeknisi::with(['tiket.statusTiket', 'tiket.bidang'])
                ->where('teknis_id', $teknis->id)->where('status_tugas', 'selesai')
                ->whereHas('tiket', $notDibukaKembali)->get();

            foreach ($selesaiTugas as $tt) {
                $waktuDitugaskan = $tt->waktu_ditugaskan;
                if (!$waktuDitugaskan) continue;
                $statusSelesai = $tt->tiket->statusTiket
                    ->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
                    ->sortByDesc('created_at')->first();
                if (!$statusSelesai || !$statusSelesai->created_at) continue;
                if ($tt->tiket->isCompletedOnTime($statusSelesai->created_at, $batasHari)) {
                    $stats['tepat_waktu']++;
                } else {
                    $stats['telat']++;
                }
            }
        } else {
            $stats['tepat_waktu'] = $stats['total_selesai'];
        }

        return $stats;
    }

    protected function activeTickets(string $teknisId)
    {
        return TiketTeknisi::with(['tiket.opd', 'tiket.kategori', 'tiket.latestStatus'])
            ->where('teknis_id', $teknisId)->where('status_tugas', 'aktif')
            ->latest('waktu_ditugaskan')->limit(5)->get();
    }

    protected function completedDistribution(string $teknisId)
    {
        $notDibukaKembali = fn($tq) => $tq->whereHas('latestStatus',
            fn($lq) => $lq->where('status_tiket', '!=', 'dibuka_kembali')
        );

        return TiketTeknisi::select('waktu_ditugaskan')
            ->where('teknis_id', $teknisId)->where('status_tugas', 'selesai')
            ->whereHas('tiket', $notDibukaKembali)
            ->where('waktu_ditugaskan', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn($t) => \Carbon\Carbon::parse($t->waktu_ditugaskan)->format('Y-n'))
            ->map(fn($group, $key) => (object)[
                'tahun' => (int) explode('-', $key)[0],
                'bulan' => (int) explode('-', $key)[1],
                'jumlah' => $group->count(),
            ])
            ->values()
            ->sortBy(fn($item) => $item->tahun * 100 + $item->bulan)
            ->values();
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
