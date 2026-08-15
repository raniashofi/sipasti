<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\ArtikelOpd;
use App\Models\Bidang;
use App\Models\Opd;
use App\Models\SopInternal;
use App\Models\Tiket;
use App\Models\TimTeknis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell\StringCell;

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

    protected function statusSelesai(): array
    {
        return $this->statusSelesai;
    }

    protected function statusLabel(string $status): string
    {
        return $this->statusLabel[$status] ?? ucfirst($status);
    }

    protected function periodDateRange(Request $request): array
    {
        $period = $request->query('period', 'monthly');

        if ($period === 'daily') {
            return [now()->toDateString(), now()->toDateString(), $period];
        }

        if ($period === 'weekly') {
            return [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString(), $period];
        }

        if ($period === 'yearly') {
            return [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString(), $period];
        }

        if ($period === 'custom') {
            return [
                $request->query('date_from', now()->startOfMonth()->toDateString()),
                $request->query('date_to', now()->toDateString()),
                $period,
            ];
        }

        return [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), 'monthly'];
    }

    protected function exportDateRange(Request $request): array
    {
        return [
            $request->query('date_from', now()->startOfYear()->toDateString()),
            $request->query('date_to', now()->toDateString()),
        ];
    }

    protected function exportFilename(string $dateFrom, string $dateTo): string
    {
        return 'laporan_kinerja_tiket_' . $dateFrom . '_sd_' . $dateTo . '.xlsx';
    }

    protected function resolutionRate(int $totalTiket, int $tiketSelesai): int
    {
        return $totalTiket > 0 ? (int) round(($tiketSelesai / $totalTiket) * 100) : 0;
    }

    protected function damageTrendBuckets(string $dateFrom, string $dateTo, string $period): \Illuminate\Support\Collection
    {
        $start = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $end = \Carbon\Carbon::parse($dateTo)->endOfDay();
        $buckets = collect();

        if ($period === 'daily') {
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = $start->clone()->setHour($hour)->setMinute(0)->setSecond(0);
                $buckets->push([
                    'label' => $hourStart->format('H:00'),
                    'start' => $hourStart,
                    'end' => $hourStart->clone()->setMinute(59)->setSecond(59),
                ]);
            }

            return $buckets;
        }

        if ($period === 'weekly') {
            for ($i = 0; $i < 7; $i++) {
                $day = $start->clone()->addDays($i);
                if ($day->gt($end)) break;

                $buckets->push([
                    'label' => $day->locale('id')->isoFormat('ddd'),
                    'start' => $day->clone()->startOfDay(),
                    'end' => $day->clone()->endOfDay(),
                ]);
            }

            return $buckets;
        }

        if ($period === 'monthly') {
            $current = $start->clone();
            while ($current->lte($end)) {
                $buckets->push([
                    'label' => $current->format('d'),
                    'start' => $current->clone()->startOfDay(),
                    'end' => $current->clone()->endOfDay(),
                ]);
                $current->addDay();
            }

            return $buckets;
        }

        if ($period === 'yearly') {
            for ($i = 0; $i < 12; $i++) {
                $month = $start->clone()->startOfYear()->addMonths($i);
                if ($month->gt($end)) break;

                $buckets->push([
                    'label' => $month->locale('id')->isoFormat('MMM'),
                    'start' => $month->clone()->startOfMonth()->max($start),
                    'end' => $month->clone()->endOfMonth()->min($end),
                ]);
            }

            return $buckets;
        }

        $durationDays = $start->diffInDays($end);

        if ($durationDays <= 1) {
            for ($hour = 0; $hour < 24; $hour++) {
                $hourStart = $start->clone()->setHour($hour)->setMinute(0)->setSecond(0);
                $buckets->push([
                    'label' => $hourStart->format('H:00'),
                    'start' => $hourStart,
                    'end' => $hourStart->clone()->setMinute(59)->setSecond(59),
                ]);
            }

            return $buckets;
        }

        if ($durationDays <= 62) {
            $current = $start->clone();
            while ($current->lte($end)) {
                $buckets->push([
                    'label' => $current->format('d/m'),
                    'start' => $current->clone()->startOfDay(),
                    'end' => $current->clone()->endOfDay(),
                ]);
                $current->addDay();
            }

            return $buckets;
        }

        $current = $start->clone();
        $weekNum = 1;
        while ($current->lte($end)) {
            $weekStart = $current->clone()->startOfDay();
            $weekEnd = $current->clone()->addDays(6)->endOfDay();
            if ($weekEnd->gt($end)) {
                $weekEnd = $end->clone();
            }

            $buckets->push([
                'label' => "W$weekNum",
                'start' => $weekStart,
                'end' => $weekEnd,
            ]);

            $current->addWeek();
            $weekNum++;
        }

        return $buckets;
    }

    public function index(Request $request)
    {
        // ── Filter periode waktu ──────────────────────────────────────
        [$dateFrom, $dateTo, $period] = $this->periodDateRange($request);

        // ── 1. Stat cards overview (keseluruhan) ──────────────────────
        $allTimeQuery = Tiket::query();
        $totalTiketAllTime = (clone $allTimeQuery)->count();
        $tiketAktifAllTime  = (clone $allTimeQuery)->whereHas('latestStatus', fn($q) =>
            $q->whereNotIn('status_tiket', $this->statusSelesai)
        )->count();
        $tiketSelesaiAllTime = (clone $allTimeQuery)->whereHas('latestStatus', fn($q) =>
            $q->whereIn('status_tiket', $this->statusSelesai)
        )->count();
        $avgKepuasanAllTime = (clone $allTimeQuery)->whereNotNull('penilaian')->avg('penilaian') ?? 0;
        $totalOpd     = Opd::count();
        $totalKb      = ArtikelOpd::where('status_publikasi', 'published')->count()
            + SopInternal::where('status_publikasi', 'published')->count();

        // ── 1.b Stat cards dan ringkasan periode aktif ────────────────
        $query = Tiket::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
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
        $trendTitle = 'Tren Tiket';
        $trendSubtitle = '';

        if ($period === 'daily') {
            $trendTitle = 'Tren Tiket — Harian';
            $trendSubtitle = 'Perbandingan tiket masuk vs. diselesaikan per jam hari ini';
        } elseif ($period === 'weekly') {
            $trendTitle = 'Tren Tiket — Mingguan';
            $trendSubtitle = 'Perbandingan tiket masuk vs. diselesaikan per hari minggu ini';
        } elseif ($period === 'monthly') {
            $trendTitle = 'Tren Tiket — Bulanan';
            $trendSubtitle = 'Perbandingan tiket masuk vs. diselesaikan per hari bulan ini';
        } elseif ($period === 'yearly') {
            $trendTitle = 'Tren Tiket — Tahunan';
            $trendSubtitle = 'Perbandingan tiket masuk vs. diselesaikan per bulan tahun ini';
        } else {
            $trendTitle = 'Tren Tiket — Custom Range';
            $trendSubtitle = 'Perbandingan tiket masuk vs. diselesaikan per bulan dalam rentang yang dipilih';
        }

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
            // Custom range selalu ditampilkan per bulan, dengan bucket awal/akhir mengikuti batas tanggal yang dipilih
            $currentMonth = $dateFromCarbon->clone()->startOfMonth();
            $lastMonth = $dateToCarbon->clone()->startOfMonth();

            while ($currentMonth->lte($lastMonth)) {
                $monthStart = $currentMonth->clone()->startOfMonth();
                $monthEnd = $currentMonth->clone()->endOfMonth();

                if ($monthStart->lt($dateFromCarbon)) {
                    $monthStart = $dateFromCarbon->clone();
                }

                if ($monthEnd->gt($dateToCarbon)) {
                    $monthEnd = $dateToCarbon->clone();
                }

                $masuk = Tiket::whereBetween('created_at', [$monthStart->startOfDay(), $monthEnd->endOfDay()])->count();
                $selesai = Tiket::whereBetween('created_at', [$monthStart->startOfDay(), $monthEnd->endOfDay()])
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', $this->statusSelesai)
                    )->count();

                $trendMonths->push([
                    'label'   => $currentMonth->locale('id')->isoFormat('MMMM YYYY'),
                    'masuk'   => $masuk,
                    'selesai' => $selesai,
                ]);

                $currentMonth->addMonthNoOverflow();
            }
        }

        // ── 4. Distribusi per bidang ───────────────────────────────────
        // Tren kerusakan aset per kategori sistem
        $damageTrendBuckets = $this->damageTrendBuckets($dateFrom, $dateTo, $period);
        $damageTrendLabels = $damageTrendBuckets->pluck('label')->values();
        $damageTrendColors = ['#01458E', '#D97706', '#059669', '#7C3AED', '#DC2626'];

        $topDamageCategories = DB::table('tiket as t')
            ->join('node_diagnosis as nd', 't.node_diagnosis_id', '=', 'nd.id')
            ->join('kategori_sistem as ks', 'nd.kategori_id', '=', 'ks.id')
            ->select('ks.id', 'ks.nama_kategori', DB::raw('COUNT(*) as total'))
            ->whereBetween('t.created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->groupBy('ks.id', 'ks.nama_kategori')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $kategoriDamageTrend = $topDamageCategories->map(function ($kategori, $index) use ($damageTrendBuckets, $damageTrendColors) {
            $points = $damageTrendBuckets->map(fn($bucket) =>
                Tiket::whereBetween('created_at', [data_get($bucket, 'start'), data_get($bucket, 'end')])
                    ->whereHas('solutionNode', fn($q) => $q->where('kategori_id', $kategori->id))
                    ->count()
            )->values();

            $first = (int) ($points->first() ?? 0);
            $last = (int) ($points->last() ?? 0);
            $change = $last - $first;

            return [
                'id' => $kategori->id,
                'nama' => $kategori->nama_kategori ?: 'Tanpa kategori',
                'total' => (int) $kategori->total,
                'data' => $points->all(),
                'color' => $damageTrendColors[$index % count($damageTrendColors)],
                'change' => $change,
                'trend' => $change > 0 ? 'Naik' : ($change < 0 ? 'Turun' : 'Stabil'),
            ];
        })->values();

        $damageTrendHighlight = $kategoriDamageTrend->sortByDesc('total')->first();
        $damageTrendDatasets = $kategoriDamageTrend->map(fn($item) => [
            'label' => $item['nama'],
            'data' => $item['data'],
            'color' => $item['color'],
        ])->values();

        $bidangs        = Bidang::all();
        $tiketPerBidang = $bidangs->map(function ($bidang) use ($dateFrom, $dateTo) {
            return [
                'nama'  => (string) ($bidang->nama_bidang ?? $bidang->id),
                'total' => Tiket::whereHas('solutionNode', fn($q) => $q->where('bidang_id', $bidang->id))
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
        $workloadTeknis = TimTeknis::with(['user', 'bidang', 'tiketTeknisi.tiket.statusTiket'])->get()->map(function ($t) use ($dateFrom, $dateTo) {
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

            // Hitung tepat waktu & telat berdasarkan SLA bidang
            $batasHari = $t->bidang?->batas_hari_pengerjaan;
            $tepatWaktu = 0;
            $telat = 0;

            if ($batasHari) {
                $selesaiTugas = $filteredTugas->where('status_tugas', 'selesai');
                foreach ($selesaiTugas as $tt) {
                    $waktuDitugaskan = $tt->waktu_ditugaskan;
                    if (!$waktuDitugaskan) continue;

                    $statusSelesai = $tt->tiket?->statusTiket
                        ?->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
                        ->sortByDesc('created_at')
                        ->first();

                    if (!$statusSelesai || !$statusSelesai->created_at) continue;

                    $deadline = \Carbon\Carbon::parse($waktuDitugaskan)->addDays($batasHari);
                    if (\Carbon\Carbon::parse($statusSelesai->created_at)->gt($deadline)) {
                        $telat++;
                    } else {
                        $tepatWaktu++;
                    }
                }
            } else {
                $tepatWaktu = $selesai;
            }

            return [
                'nama'          => $t->nama_lengkap,
                'bidang'        => $rawBidangT ?: '—',
                'status'        => $t->status_teknisi,
                'total_tugas'   => $total,
                'tugas_aktif'   => $aktif,
                'tugas_selesai' => $selesai,
                'tepat_waktu'   => $tepatWaktu,
                'telat'         => $telat,
                'beban'         => $aktif,
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
                'actual'  => $totalTiketAllTime > 0 ? round(($tiketSelesaiAllTime / $totalTiketAllTime) * 100) : 0,
                'unit'    => '%',
                'color'   => '#059669',
                'bg'      => '#D1FAE5',
            ],
            [
                'label'   => 'Skor Kepuasan',
                'target'  => 4.0,
                'actual'  => round($avgKepuasanAllTime, 1),
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
            'totalTiketAllTime', 'tiketAktifAllTime', 'tiketSelesaiAllTime', 'avgKepuasanAllTime',
            'totalOpd', 'totalKb', 'tiketBulanIni', 'selesaiBulanIni',
            'tiketPerStatus', 'trendMonths', 'tiketPerBidang',
            'performanceAdmin', 'workloadTeknis', 'auditLog', 'kpiData',
            'damageTrendLabels', 'kategoriDamageTrend', 'damageTrendHighlight',
            'damageTrendDatasets',
            'trendTitle', 'trendSubtitle',
            'dateFrom', 'dateTo', 'period'
        ));
    }

    /**
     * Export laporan tiket ke Excel (Fokus Kinerja Karyawan & Detail Teknisi).
     */
    public function exportCsv(Request $request)
    {
        [$dateFrom, $dateTo] = $this->exportDateRange($request);

        // Tambahkan relasi tiketTeknisi untuk mendapatkan semua teknisi yang ditugaskan
        $tikets = Tiket::with([
            'opd', 'kategori', 'kb.kategori', 'latestStatus', 'admin.bidang', 'bidang',
            'tiketTeknisi.timTeknis.bidang', // Relasi ke semua teknisi via TiketTeknisi
            'statusTiket',
        ])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $filename = $this->exportFilename($dateFrom, $dateTo);
        $tempPath = storage_path('temp/' . uniqid() . '.xlsx');
        @mkdir(storage_path('temp'), 0755, true);

        $writer = new Writer();
        $writer->openToFile($tempPath);

        // Header Row
        $writer->addRow(new Row([
            new StringCell('No'),
            new StringCell('ID Tiket'),
            new StringCell('Subjek Masalah'),
            new StringCell('Kategori'),
            new StringCell('Waktu Dibuat'),
            new StringCell('Waktu Diselesaikan'),
            new StringCell('Durasi Penyelesaian (Jam)'),
            new StringCell('Batas SLA (Hari)'),
            new StringCell('Status Ketepatan'),
            new StringCell('Status Akhir'),
            new StringCell('Ditangani Oleh (Admin)'),
            new StringCell('Bidang Admin'),
            new StringCell('Tim Teknis'),
            new StringCell('Peran Tim Teknis'),
            new StringCell('Bidang Tim Teknis'),
            new StringCell('Status Tugas Teknis'),
            new StringCell('Waktu Ditugaskan'),
            new StringCell('Skor Kepuasan (/5)'),
            new StringCell('Asal Instansi (OPD)'),
        ]));

        $statusSelesai = $this->statusSelesai();
        $rowNumber = 0;

        foreach ($tikets as $tiket) {
            $statusAkhir = $tiket->latestStatus?->status_tiket;
            $labelStatus = $statusAkhir ? $this->statusLabel($statusAkhir) : '—';

            // Perhitungan Waktu dan Durasi Penyelesaian (SLA)
            $waktuMasuk = $tiket->created_at;
            $waktuSelesai = null;
            $durasiJam = '—';

            if (in_array($statusAkhir, $statusSelesai) && $tiket->latestStatus) {
                $waktuSelesai = $tiket->latestStatus->created_at;

                if ($waktuMasuk && $waktuSelesai) {
                    $durasiJam = (string) round($waktuMasuk->diffInMinutes($waktuSelesai) / 60, 1);
                }
            }

            $kategori = $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '—');

            // SLA: Batas hari dari bidang tiket
            $batasHariBidang = $tiket->bidang?->batas_hari_pengerjaan;
            $batasSlaLabel = $batasHariBidang ? (string)$batasHariBidang : '—';

            // Hitung status ketepatan per tiket (berdasarkan teknisi utama)
            $statusKetepatan = '—';
            if ($batasHariBidang && in_array($statusAkhir, $statusSelesai)) {
                $teknisiUtama = $tiket->tiketTeknisi->where('peran_teknisi', 'teknisi_utama')->first();
                if ($teknisiUtama && $teknisiUtama->waktu_ditugaskan) {
                    $deadline = \Carbon\Carbon::parse($teknisiUtama->waktu_ditugaskan)->addDays($batasHariBidang);
                    $statusSelesaiRecord = $tiket->statusTiket
                        ->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
                        ->sortByDesc('created_at')
                        ->first();
                    if ($statusSelesaiRecord && $statusSelesaiRecord->created_at) {
                        $waktuPenyelesaian = \Carbon\Carbon::parse($statusSelesaiRecord->created_at);
                        if ($waktuPenyelesaian->gt($deadline)) {
                            $selisihHari = (int) $deadline->diffInDays($waktuPenyelesaian);
                            $statusKetepatan = "Telat ({$selisihHari} hari)";
                        } else {
                            $statusKetepatan = 'Tepat Waktu';
                        }
                    }
                }
            } elseif (!$batasHariBidang && in_array($statusAkhir, $statusSelesai)) {
                $statusKetepatan = 'Tidak Ada Batas';
            }

            // Cek status eskalasi
            $barisTeknisi = $tiket->tiketTeknisi
                ->sortBy(fn($tt) => ($tt->peran_teknisi === 'teknisi_utama' ? '0' : '1') . ($tt->waktu_ditugaskan?->format('YmdHis') ?? ''))
                ->unique(fn($tt) => ($tt->teknis_id ?? 'unknown') . '|' . ($tt->peran_teknisi ?? 'unknown'))
                ->values();

            // Jika tidak ada teknisi, tetap tambahkan 1 baris dengan "—"
            if ($barisTeknisi->isEmpty()) {
                $rowNumber++;
                $writer->addRow(new Row([
                    new StringCell((string)$rowNumber),
                    new StringCell($tiket->id),
                    new StringCell($tiket->subjek_masalah),
                    new StringCell($kategori),
                    new StringCell($waktuMasuk ? $waktuMasuk->format('d/m/Y H:i') : '—'),
                    new StringCell($waktuSelesai ? $waktuSelesai->format('d/m/Y H:i') : '—'),
                    new StringCell($durasiJam),
                    new StringCell($batasSlaLabel),
                    new StringCell($statusKetepatan),
                    new StringCell($labelStatus),
                    new StringCell($tiket->admin?->nama_lengkap ?? '—'),
                    new StringCell('—'),
                    new StringCell((string)($tiket->penilaian ?? 'Belum Dinilai')),
                    new StringCell($tiket->opd?->nama_opd ?? '—'),
                ]));
            } else {
                // Tambahkan satu baris untuk setiap teknisi
                foreach ($barisTeknisi as $teknisi) {
                    $rowNumber++;
                    $writer->addRow(new Row([
                        new StringCell((string)$rowNumber),
                        new StringCell($tiket->id),
                        new StringCell($tiket->subjek_masalah),
                        new StringCell($kategori),
                        new StringCell($waktuMasuk ? $waktuMasuk->format('d/m/Y H:i') : '—'),
                        new StringCell($waktuSelesai ? $waktuSelesai->format('d/m/Y H:i') : '—'),
                        new StringCell($durasiJam),
                        new StringCell($batasSlaLabel),
                        new StringCell($statusKetepatan),
                        new StringCell($labelStatus),
                        new StringCell($tiket->admin?->nama_lengkap ?? '—'),
                        new StringCell($teknisi),
                        new StringCell((string)($tiket->penilaian ?? 'Belum Dinilai')),
                        new StringCell($tiket->opd?->nama_opd ?? '—'),
                    ]));
                }
            }
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export laporan tiket ke Excel dengan satu baris untuk setiap teknisi yang menangani.
     */
    public function exportXlsx(Request $request)
    {
        [$dateFrom, $dateTo] = $this->exportDateRange($request);

        $tikets = Tiket::with([
            'opd',
            'kategori',
            'kb.kategori',
            'latestStatus',
            'admin.bidang',
            'bidang',
            'tiketTeknisi.timTeknis.bidang',
            'statusTiket',
        ])
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->orderByDesc('created_at')
            ->get();

        $filename = $this->exportFilename($dateFrom, $dateTo);
        $tempPath = storage_path('temp/' . uniqid() . '.xlsx');
        @mkdir(storage_path('temp'), 0755, true);

        $writer = new Writer();
        $writer->openToFile($tempPath);

        $writer->addRow(new Row([
            new StringCell('No'),
            new StringCell('ID Tiket'),
            new StringCell('Subjek Masalah'),
            new StringCell('Kategori'),
            new StringCell('Waktu Dibuat'),
            new StringCell('Waktu Diselesaikan'),
            new StringCell('Durasi Penyelesaian (Jam)'),
            new StringCell('Batas SLA (Hari)'),
            new StringCell('Status Ketepatan'),
            new StringCell('Status Akhir'),
            new StringCell('Ditangani Oleh (Admin)'),
            new StringCell('Bidang Admin'),
            new StringCell('Tim Teknis'),
            new StringCell('Peran Tim Teknis'),
            new StringCell('Bidang Tim Teknis'),
            new StringCell('Status Tugas Teknis'),
            new StringCell('Waktu Ditugaskan'),
            new StringCell('Skor Kepuasan (/5)'),
            new StringCell('Asal Instansi (OPD)'),
        ]));

        $statusSelesai = $this->statusSelesai();
        $rowNumber = 0;

        foreach ($tikets as $tiket) {
            $statusAkhir = $tiket->latestStatus?->status_tiket;
            $labelStatus = $statusAkhir ? $this->statusLabel($statusAkhir) : '-';
            $waktuMasuk = $tiket->created_at;
            $waktuSelesai = null;
            $durasiJam = '-';

            if (in_array($statusAkhir, $statusSelesai) && $tiket->latestStatus) {
                $waktuSelesai = $tiket->latestStatus->created_at;
                if ($waktuMasuk && $waktuSelesai) {
                    $durasiJam = (string) round($waktuMasuk->diffInMinutes($waktuSelesai) / 60, 1);
                }
            }

            $kategori = $tiket->kategori?->nama_kategori ?? ($tiket->kb?->kategori?->nama_kategori ?? '-');
            $batasHariBidang = $tiket->bidang?->batas_hari_pengerjaan;
            $batasSlaLabel = $batasHariBidang ? (string) $batasHariBidang : '-';
            $statusKetepatan = '-';

            if ($batasHariBidang && in_array($statusAkhir, $statusSelesai)) {
                $teknisiUtama = $tiket->tiketTeknisi->where('peran_teknisi', 'teknisi_utama')->first();
                $statusSelesaiRecord = $tiket->statusTiket
                    ->whereIn('status_tiket', ['selesai', 'rusak_berat', 'tiket_ditutup'])
                    ->sortByDesc('created_at')
                    ->first();

                if ($teknisiUtama?->waktu_ditugaskan && $statusSelesaiRecord?->created_at) {
                    $deadline = \Carbon\Carbon::parse($teknisiUtama->waktu_ditugaskan)->addDays($batasHariBidang);
                    $waktuPenyelesaian = \Carbon\Carbon::parse($statusSelesaiRecord->created_at);
                    $statusKetepatan = $waktuPenyelesaian->gt($deadline)
                        ? 'Telat (' . (int) $deadline->diffInDays($waktuPenyelesaian) . ' hari)'
                        : 'Tepat Waktu';
                }
            } elseif (!$batasHariBidang && in_array($statusAkhir, $statusSelesai)) {
                $statusKetepatan = 'Tidak Ada Batas';
            }

            $barisTeknisi = $tiket->tiketTeknisi
                ->sortBy(fn($tt) => ($tt->peran_teknisi === 'teknisi_utama' ? '0' : '1') . ($tt->waktu_ditugaskan?->format('YmdHis') ?? ''))
                ->unique(fn($tt) => ($tt->teknis_id ?? 'unknown') . '|' . ($tt->peran_teknisi ?? 'unknown'))
                ->values();

            if ($barisTeknisi->isEmpty()) {
                $barisTeknisi = collect([null]);
            }

            foreach ($barisTeknisi as $tt) {
                $rowNumber++;

                $writer->addRow(new Row([
                    new StringCell((string) $rowNumber),
                    new StringCell($tiket->id),
                    new StringCell($tiket->subjek_masalah),
                    new StringCell($kategori),
                    new StringCell($waktuMasuk ? $waktuMasuk->format('d/m/Y H:i') : '-'),
                    new StringCell($waktuSelesai ? $waktuSelesai->format('d/m/Y H:i') : '-'),
                    new StringCell($durasiJam),
                    new StringCell($batasSlaLabel),
                    new StringCell($statusKetepatan),
                    new StringCell($labelStatus),
                    new StringCell($tiket->admin?->nama_lengkap ?? '-'),
                    new StringCell($tiket->admin?->bidang?->nama_bidang ?? '-'),
                    new StringCell($tt?->timTeknis?->nama_lengkap ?? '-'),
                    new StringCell($tt ? ($tt->peran_teknisi === 'teknisi_utama' ? 'Teknisi Utama' : 'Teknisi Pendamping') : '-'),
                    new StringCell($tt?->timTeknis?->bidang?->nama_bidang ?? '-'),
                    new StringCell($tt ? match ($tt->status_tugas) {
                        'aktif' => 'Aktif',
                        'selesai' => 'Selesai',
                        'dikembalikan' => 'Dikembalikan',
                        default => $tt->status_tugas ?? '-',
                    } : '-'),
                    new StringCell($tt?->waktu_ditugaskan ? $tt->waktu_ditugaskan->format('d/m/Y H:i') : '-'),
                    new StringCell((string) ($tiket->penilaian ?? 'Belum Dinilai')),
                    new StringCell($tiket->opd?->nama_opd ?? '-'),
                ]));
            }
        }

        $writer->close();

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
