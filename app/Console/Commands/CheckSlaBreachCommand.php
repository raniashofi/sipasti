<?php

namespace App\Console\Commands;

use App\Models\Tiket;
use App\Notifications\SlaBreachMailNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSlaBreachCommand extends Command
{
    protected $signature = 'tiket:check-sla-breach';

    protected $description = 'Cek tiket aktif yang sudah melewati batas SLA dan kirim notifikasi email ke admin dan teknisi terkait.';

    /**
     * Status tiket yang dianggap masih aktif / sedang ditangani.
     */
    private const ACTIVE_STATUSES = [
        'panduan_remote',
        'perbaikan_teknis',
        'dibuka_kembali',
    ];

    public function handle(): int
    {
        $this->info('Memulai pengecekan SLA breach...');

        // Ambil semua tiket aktif beserta relasi yang diperlukan
        $tikets = Tiket::with([
                'admin.user',
                'tiketTeknisi' => fn($q) => $q->where('status_tugas', 'aktif'),
                'tiketTeknisi.timTeknis.user',
                'solutionNode.bidang',
                'latestStatus',
            ])
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', self::ACTIVE_STATUSES))
            ->get();

        $breachedCount = 0;
        $notifSentCount = 0;

        foreach ($tikets as $tiket) {
            $bidang = $tiket->solutionNode?->bidang;
            $batasHari = $bidang?->batas_hari_pengerjaan;

            // Lewati tiket yang bidangnya tidak punya SLA
            if (! $batasHari) {
                continue;
            }

            // Cek apakah tiket sudah lewat SLA
            if ($tiket->isCompletedOnTime()) {
                continue;
            }

            // Hitung berapa hari keterlambatan
            $slaPeriodStart = $tiket->getSlaPeriodStartDate();
            $deadline = Carbon::parse($slaPeriodStart)->addDays($batasHari);
            $hariTerlambat = (int) $deadline->diffInDays(now(), absolute: true);

            $breachedCount++;

            // Kirim notifikasi ke Admin Helpdesk pemilik tiket
            $adminUser = $tiket->admin?->user;
            if ($adminUser) {
                $adminUser->notify(new SlaBreachMailNotification(
                    kodeTiket:     $tiket->id,
                    subjekMasalah: $tiket->subjek_masalah,
                    batasHari:     $batasHari,
                    hariTerlambat: $hariTerlambat,
                ));
                $notifSentCount++;
            }

            // Kirim notifikasi ke semua Teknisi aktif yang ditugaskan
            foreach ($tiket->tiketTeknisi as $assignment) {
                $teknisiUser = $assignment->timTeknis?->user;
                if ($teknisiUser) {
                    $teknisiUser->notify(new SlaBreachMailNotification(
                        kodeTiket:     $tiket->id,
                        subjekMasalah: $tiket->subjek_masalah,
                        batasHari:     $batasHari,
                        hariTerlambat: $hariTerlambat,
                    ));
                    $notifSentCount++;
                }
            }
        }

        $this->info("Selesai. Tiket breach: {$breachedCount}, Email terkirim: {$notifSentCount}.");

        return self::SUCCESS;
    }
}
