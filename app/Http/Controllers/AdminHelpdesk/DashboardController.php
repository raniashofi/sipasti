<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\SopInternal;
use App\Models\Tiket;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $adminProfile = $this->findAdminProfile();
        $adminId      = $adminProfile?->id;
        $bidangId     = $adminProfile?->bidang_id;

        $filterBidang = function ($query) use ($bidangId) {
            if ($bidangId) {
                $query->whereHas('solutionNode', fn($q) => $q->where('bidang_id', $bidangId));
            }
        };

        $latestStatus = fn($statuses) => fn($q) => $q->whereIn('status_tiket', (array) $statuses);

        $menungguIds = $this->menungguVerifikasiIds($adminProfile, $filterBidang);
        $menungguVerif = $menungguIds->count();

        $panduanRemote = $this->countTiketByAdmin($adminId, $latestStatus('panduan_remote'));

        $distribusi = $this->countDistribusi($adminId);

        $selesai = $this->countTiketByAdmin($adminId, $latestStatus(['selesai', 'rusak_berat', 'tiket_ditutup']));

        $rusakBerat = $this->countTiketByAdmin($adminId, $latestStatus('rusak_berat'));

        $selesaiDitutup = $this->countTiketByAdmin($adminId, $latestStatus(['selesai', 'tiket_ditutup']));

        $stats = [
            'menunggu_verif' => $menungguVerif,
            'panduan_remote' => $panduanRemote,
            'eskalasi'       => $distribusi,
            'selesai'        => $selesai,
            'total_kb'       => $this->countPublishedSop(),
        ];

        $tiketPerStatus = [
            'Menunggu Verif'    => $menungguVerif,
            'Panduan Remote'    => $panduanRemote,
            'Distribusi Teknis' => $distribusi,
            'Rusak Berat'       => $rusakBerat,
            'Selesai/Ditutup'   => $selesaiDitutup,
        ];

        $recentActivity = $this->recentAdminActivity();

        return $this->renderView('admin_helpdesk.dashboard', compact(
            'stats', 'tiketPerStatus', 'recentActivity', 'adminProfile'
        ));
    }

    // ── Protected methods (overridable for unit testing) ─────

    protected function findAdminProfile()
    {
        return AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
    }

    protected function countTiketByAdmin(?string $adminId, callable $statusFilter): int
    {
        if (!$adminId) return 0;
        return Tiket::where('admin_id', $adminId)->whereHas('latestStatus', $statusFilter)->count();
    }

    protected function countPublishedSop(): int
    {
        return SopInternal::where('status_publikasi', 'published')->count();
    }

    protected function recentAdminActivity()
    {
        return ActivityLog::with('user')
            ->where('role_pelaku', 'admin_helpdesk')
            ->orderByDesc('id')
            ->limit(8)
            ->get();
    }

    protected function countDistribusi(?string $adminId): int
    {
        if (!$adminId) return 0;
        return Tiket::where('admin_id', $adminId)
            ->where(fn($q) => $q
                ->whereHas('latestStatus', fn($sq) => $sq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']))
                ->orWhereHas('tiketTeknisi', fn($tq) => $tq->where('status_tugas', 'aktif')))
            ->count();
    }

    protected function renderView(string $view, array $data)
    {
        return view($view, $data);
    }

    protected function menungguVerifikasiIds($admin, callable $filterBidang)
    {
        $prefixKembali = '[Dikembalikan oleh Tim Teknis] ';
        $prefixTransfer = '[Transfer ke ';

        $queryBaru = Tiket::query()
            ->whereNull('admin_id')
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where(fn($q2) => $q2->whereNull('catatan')->orWhere('catatan', 'not like', '[Transfer ke %]')));
        $filterBidang($queryBaru);

        $queryTransfer = Tiket::query()
            ->where(fn($q) => $q->whereNull('admin_id')->orWhere('admin_id', $admin?->id))
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixTransfer . ($admin?->bidang_id ?? '') . ']%'));

        $queryKembali = Tiket::query()
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixKembali . '%'));

        return $queryBaru->pluck('id')
            ->merge($queryTransfer->pluck('id'))
            ->merge($queryKembali->pluck('id'))
            ->unique()
            ->values();
    }
}
