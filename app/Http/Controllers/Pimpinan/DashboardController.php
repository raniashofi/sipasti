<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\KnowledgeBase;
use App\Models\Opd;
use App\Models\Tiket;
use App\Models\TimTeknis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Label human-readable untuk status tiket
    private array $statusLabel = [
        'verifikasi_admin' => 'Verifikasi Admin',
        'panduan_remote'   => 'Panduan Remote',
        'perlu_revisi'     => 'Perlu Revisi',
        'perbaikan_teknis' => 'Perbaikan Teknis',
        'selesai'          => 'Selesai',
        'rusak_berat'      => 'Rusak Berat',
        'tiket_ditutup'    => 'Tiket Ditutup',
        'dibuka_kembali'   => 'Dibuka Kembali',
    ];

    // Status yang dianggap sebagai tiket selesai
    private array $statusSelesai = ['selesai', 'rusak_berat', 'tiket_ditutup'];

    public function index(Request $request)
    {
        // ── Filter periode waktu ──────────────────────────────────────
        $period = $request->query('period', 'monthly'); // harian, mingguan, bulanan (default), tahunan, custom

        // Hitung rentang tanggal berdasarkan period
        if ($period === 'daily') {
            $dateFrom = now()->toDateString();
            $dateTo   = now()->toDateString();
        } elseif ($period === 'weekly') {
            $dateFrom = now()->startOfWeek()->toDateString();
            $dateTo   = now()->endOfWeek()->toDateString();
        } elseif ($period === 'yearly') {
            $dateFrom = now()->startOfYear()->toDateString();
            $dateTo   = now()->endOfYear()->toDateString();
        } elseif ($period === 'custom') {
            $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
            $dateTo   = $request->query('date_to', now()->toDateString());
        } else { // monthly (default)
            $dateFrom = now()->startOfMonth()->toDateString();
            $dateTo   = now()->endOfMonth()->toDateString();
        }

        // ── 1. Stat cards overview ─────────────────────────────────────
        $query = Tiket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        $totalTiket   = (clone $query)->count();
        $tiketAktif   = (clone $query)->whereHas('latestStatus', fn($q) =>
            $q->whereNotIn('status_tiket', $this->statusSelesai)
        )->count();
        $tiketSelesai = (clone $query)->whereHas('latestStatus', fn($q) =>
            $q->whereIn('status_tiket', $this->statusSelesai)
        )->count();
        $avgKepuasan  = (clone $query)->whereNotNull('penilaian')->avg('penilaian') ?? 0;
        $totalOpd     = Opd::count();
        $totalKb      = KnowledgeBase::where('status_publikasi', 'published')->count();

        // Tiket dalam periode
        $tiketBulanIni  = (clone $query)->count();
        $selesaiBulanIni = (clone $query)->whereHas('latestStatus', fn($q) =>
            $q->whereIn('status_tiket', $this->statusSelesai)
        )->count();

        // ── 2. Distribusi per status (latest status per tiket dalam periode) ─────────
        $latestStatuses = DB::table('status_tiket as st1')
            ->select('st1.status_tiket', DB::raw('COUNT(*) as total'))
            ->join('tiket as t', 'st1.tiket_id', '=', 't.id')
            ->whereBetween('t.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->whereRaw('st1.created_at = (SELECT MAX(st2.created_at) FROM status_tiket st2 WHERE st2.tiket_id = st1.tiket_id)')
            ->groupBy('st1.status_tiket')
            ->get();

        $tiketPerStatus = [];
        foreach ($latestStatuses as $row) {
            // Merge tiket_ditutup ke dalam kategori selesai
            if ($row->status_tiket === 'tiket_ditutup') {
                $label = $this->statusLabel['selesai'];
            } else {
                $label = $this->statusLabel[$row->status_tiket] ?? ucfirst($row->status_tiket);
            }

            $tiketPerStatus[$label] = ($tiketPerStatus[$label] ?? 0) + $row->total;
        }

        // ── 3. Tren tiket (disesuaikan dengan periode) ──────────────────
        $trendMonths = collect();
        $dateFromCarbon = \Carbon\Carbon::parse($dateFrom);
        $dateToCarbon = \Carbon\Carbon::parse($dateTo);

        if ($period === 'daily') {
            // Hourly breakdown untuk hari ini
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = $dateFromCarbon->clone()->setHour($hour)->setMinute(0)->setSecond(0);
                $hourEnd = $dateFromCarbon->clone()->setHour($hour)->setMinute(59)->setSecond(59);

                $masuk = Tiket::whereBetween('created_at', [$hourStart, $hourEnd])->count();
                $selesai = Tiket::whereBetween('created_at', [$hourStart, $hourEnd])
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', $this->statusSelesai)
                    )->count();

                $trendMonths->push([
                    'label'   => $hourStart->format('H:00'),
                    'masuk'   => $masuk,
                    'selesai' => $selesai,
                ]);
            }
        } elseif ($period === 'weekly') {
            // Daily breakdown untuk minggu ini
            for ($i = 0; $i < 7; $i++) {
                $date = $dateFromCarbon->clone()->addDays($i);
                if ($date > $dateToCarbon) break;

                $masuk = Tiket::whereDate('created_at', $date->toDateString())->count();
                $selesai = Tiket::whereDate('created_at', $date->toDateString())
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', $this->statusSelesai)
                    )->count();

                $trendMonths->push([
                    'label'   => $date->locale('id')->isoFormat('ddd'),
                    'masuk'   => $masuk,
                    'selesai' => $selesai,
                ]);
            }
        } elseif ($period === 'monthly') {
            // Daily breakdown untuk bulan ini
            for ($i = 0; $i < 31; $i++) {
                $date = $dateFromCarbon->clone()->addDays($i);
                if ($date > $dateToCarbon) break;

                $masuk = Tiket::whereDate('created_at', $date->toDateString())->count();
                $selesai = Tiket::whereDate('created_at', $date->toDateString())
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', $this->statusSelesai)
                    )->count();

                $trendMonths->push([
                    'label'   => $date->format('d'),
                    'masuk'   => $masuk,
                    'selesai' => $selesai,
                ]);
            }
        } elseif ($period === 'yearly') {
            // Monthly breakdown untuk tahun ini
            for ($i = 0; $i < 12; $i++) {
                $month = $dateFromCarbon->clone()->addMonths($i);
                if ($month > $dateToCarbon) break;

                $masuk = Tiket::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)->count();
                $selesai = Tiket::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', $this->statusSelesai)
                    )->count();

                $trendMonths->push([
                    'label'   => $month->locale('id')->isoFormat('MMM'),
                    'masuk'   => $masuk,
                    'selesai' => $selesai,
                ]);
            }
        } else {
            // Custom: tentukan interval berdasarkan durasi
            $durationDays = $dateToCarbon->diffInDays($dateFromCarbon);

            if ($durationDays <= 1) {
                // Hourly
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourStart = $dateFromCarbon->clone()->setHour($hour)->setMinute(0)->setSecond(0);
                    $hourEnd = $hourStart->clone()->setMinute(59)->setSecond(59);

                    $masuk = Tiket::whereBetween('created_at', [$hourStart, $hourEnd])->count();
                    $selesai = Tiket::whereBetween('created_at', [$hourStart, $hourEnd])
                        ->whereHas('latestStatus', fn($q) =>
                            $q->whereIn('status_tiket', $this->statusSelesai)
                        )->count();

                    $trendMonths->push([
                        'label'   => $hourStart->format('H:00'),
                        'masuk'   => $masuk,
                        'selesai' => $selesai,
                    ]);
                }
            } elseif ($durationDays <= 62) {
                // Daily
                for ($i = 0; $i <= $durationDays; $i++) {
                    $date = $dateFromCarbon->clone()->addDays($i);
                    if ($date > $dateToCarbon) break;

                    $masuk = Tiket::whereDate('created_at', $date->toDateString())->count();
                    $selesai = Tiket::whereDate('created_at', $date->toDateString())
                        ->whereHas('latestStatus', fn($q) =>
                            $q->whereIn('status_tiket', $this->statusSelesai)
                        )->count();

                    $trendMonths->push([
                        'label'   => $date->format('d/m'),
                        'masuk'   => $masuk,
                        'selesai' => $selesai,
                    ]);
                }
            } else {
                // Weekly
                $current = $dateFromCarbon->clone();
                $weekNum = 1;
                while ($current <= $dateToCarbon) {
                    $weekStart = $current->clone();
                    $weekEnd = $current->clone()->addDays(6)->setTime(23, 59, 59);
                    if ($weekEnd > $dateToCarbon) $weekEnd = $dateToCarbon;

                    $masuk = Tiket::whereBetween('created_at', [$weekStart, $weekEnd])->count();
                    $selesai = Tiket::whereBetween('created_at', [$weekStart, $weekEnd])
                        ->whereHas('latestStatus', fn($q) =>
                            $q->whereIn('status_tiket', $this->statusSelesai)
                        )->count();

                    $trendMonths->push([
                        'label'   => "W$weekNum",
                        'masuk'   => $masuk,
                        'selesai' => $selesai,
                    ]);

                    $current->addWeeks(1);
                    $weekNum++;
                }
            }
        }

        // ── 4. Distribusi per bidang ───────────────────────────────────
        $bidangs        = Bidang::all();
        $tiketPerBidang = $bidangs->map(function ($bidang) use ($dateFrom, $dateTo) {
            return [
                'nama'  => (string) ($bidang->nama_bidang ?? $bidang->id),
                'total' => Tiket::where('bidang_id', $bidang->id)
                    ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->count(),
            ];
        })->filter(fn($b) => $b['total'] > 0)->values();

        // ── 5. Performa Admin Helpdesk ─────────────────────────────────
        $performanceAdmin = AdminHelpdesk::with(['user', 'bidang'])->get()->map(function ($admin) use ($dateFrom, $dateTo) {
            $tiketDitangani  = Tiket::where('admin_id', $admin->id)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->count();
            $tiketDiselesaikan = Tiket::where('admin_id', $admin->id)
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->whereHas('latestStatus', fn($q) =>
                    $q->whereIn('status_tiket', $this->statusSelesai)
                )->count();
            $rawBidang = (string) ($admin->bidang?->nama_bidang ?? '');
            return [
                'nama'             => $admin->nama_lengkap,
                'bidang'           => $rawBidang ?: '—',
                'ditangani'        => $tiketDitangani,
                'diselesaikan'     => $tiketDiselesaikan,
                'rate'             => $tiketDitangani > 0
                    ? round(($tiketDiselesaikan / $tiketDitangani) * 100)
                    : 0,
            ];
        })->sortByDesc('ditangani')->values();

        // ── 6. Beban kerja Tim Teknis ──────────────────────────────────
        $workloadTeknis = TimTeknis::with(['user', 'bidang', 'tiketTeknisi'])->get()->map(function ($t) use ($dateFrom, $dateTo) {
            $filteredTugas = $t->tiketTeknisi->filter(fn($tt) =>
                \Carbon\Carbon::parse($tt->created_at)->between(
                    \Carbon\Carbon::parse($dateFrom),
                    \Carbon\Carbon::parse($dateTo)->endOfDay()
                )
            );
            $total     = $filteredTugas->count();
            $selesai   = $filteredTugas->where('status_tugas', 'selesai')->count();
            $aktif     = $filteredTugas->where('status_tugas', 'aktif')->count();
            $rawBidangT = (string) ($t->bidang?->nama_bidang ?? '');
            return [
                'nama'          => $t->nama_lengkap,
                'bidang'        => $rawBidangT ?: '—',
                'status'        => $t->status_teknisi,
                'total_tugas'   => $total,
                'tugas_aktif'   => $aktif,
                'tugas_selesai' => $selesai,
                'beban'         => $aktif,   // workload = tugas aktif saat ini
            ];
        })->sortByDesc('tugas_aktif')->values();

        // ── 7. Audit trail internal roles (tanpa login/logout) ────────
        $auditLog = ActivityLog::with('user')
            ->whereIn('role_pelaku', ['super_admin', 'admin_helpdesk', 'tim_teknis'])
            ->whereNotIn('jenis_aktivitas', ['login', 'logout'])
            ->orderByDesc('waktu_eksekusi')
            ->limit(100)
            ->get();

        // ── 8. KPI targets ────────────────────────────────────────────
        $kpiData = [
            [
                'label'   => 'Tingkat Resolusi',
                'target'  => 90,   // %
                'actual'  => $totalTiket > 0 ? round(($tiketSelesai / $totalTiket) * 100) : 0,
                'unit'    => '%',
                'color'   => '#059669',
                'bg'      => '#D1FAE5',
            ],
            [
                'label'   => 'Skor Kepuasan',
                'target'  => 4.0,
                'actual'  => round($avgKepuasan, 1),
                'unit'    => '/ 5',
                'color'   => '#D97706',
                'bg'      => '#FEF3C7',
            ],
            [
                'label'   => 'Artikel KB Terbit',
                'target'  => 50,
                'actual'  => $totalKb,
                'unit'    => 'artikel',
                'color'   => '#0263C8',
                'bg'      => '#EBF3FF',
            ],
        ];

        return view('pimpinan.dashboard', compact(
            'totalTiket', 'tiketAktif', 'tiketSelesai', 'avgKepuasan',
            'totalOpd', 'totalKb', 'tiketBulanIni', 'selesaiBulanIni',
            'tiketPerStatus', 'trendMonths', 'tiketPerBidang',
            'performanceAdmin', 'workloadTeknis', 'auditLog', 'kpiData',
            'dateFrom', 'dateTo', 'period'
        ));
    }

    /**
     * Export laporan tiket ke CSV.
     */
    public function exportCsv(Request $request)
    {
        $dateFrom = $request->query('date_from', now()->startOfYear()->toDateString());
        $dateTo   = $request->query('date_to', now()->toDateString());

        $tikets = Tiket::with(['opd', 'kategori', 'latestStatus', 'admin', 'solutionNode'])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'laporan_tiket_' . $dateFrom . '_sd_' . $dateTo . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tikets) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 agar Excel tidak rusak
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'No', 'ID Tiket', 'OPD', 'Kategori',
                'Subjek Masalah', 'Prioritas', 'Status Terakhir',
                'Admin Helpdesk', 'Penilaian', 'Tanggal Buat',
            ]);

            $statusLabel = [
                'verifikasi_admin' => 'Verifikasi Admin',
                'panduan_remote'   => 'Panduan Remote',
                'perlu_revisi'     => 'Perlu Revisi',
                'perbaikan_teknis' => 'Perbaikan Teknis',
                'selesai'          => 'Selesai',
                'rusak_berat'      => 'Rusak Berat',
                'dibuka_kembali'   => 'Dibuka Kembali',
            ];

            foreach ($tikets as $i => $tiket) {
                fputcsv($handle, [
                    $i + 1,
                    $tiket->id,
                    $tiket->opd?->nama_opd ?? '—',
                    $tiket->kategori?->nama_kategori ?? '—',
                    $tiket->subjek_masalah,
                    match($tiket->rekomendasi_penanganan) {
                        'eskalasi' => 'Perlu Dieskalasi ke Tim Teknis',
                        'admin'    => 'Dapat Ditangani Admin',
                        default    => '—',
                    },
                    $statusLabel[$tiket->latestStatus?->status_tiket] ?? ($tiket->latestStatus?->status_tiket ?? '—'),
                    $tiket->admin?->nama_lengkap ?? '—',
                    $tiket->penilaian ?? '—',
                    $tiket->created_at?->format('d/m/Y H:i') ?? '—',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
