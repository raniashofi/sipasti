<?php

namespace App\Http\Controllers\AdminHelpdesk;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\ChatRoom;
use App\Models\Opd;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TimTeknis;
use App\Models\TiketTeknisi;
use App\Notifications\StatusTiketNotification;
use App\Notifications\TiketMasukNotification;
use App\Notifications\TiketTransferNotification;
use App\Notifications\TugasBaruNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManajemenTiketController extends Controller
{
    private const PANDUAN_STATUSES = ['panduan_remote'];
    private const EXPORT_CSV_HEADER = [
        'ID Tiket', 'OPD', 'Subjek Masalah', 'Kategori',
        'Rekomendasi Penanganan', 'Teknisi Utama', 'Status Akhir',
        'Waktu Masuk', 'Waktu Selesai',
    ];

    private function adminProfile()
    {
        return AdminHelpdesk::with('bidang')->where('user_id', Auth::id())->first();
    }

    private function logAktivitas(string $jenis, string $detail, string $namaTable, string $idRecord): void
    {
        ActivityLog::create([
            'user_id'         => Auth::id(),
            'role_pelaku'     => 'admin_helpdesk',
            'jenis_aktivitas' => $jenis,
            'detail_tindakan' => $detail,
            'ip_address'      => request()->ip(),
            'waktu_eksekusi'  => now(),
            'nama_tabel'      => $namaTable,
            'id_record'       => $idRecord,
        ]);
    }

    /**
     * Cek apakah admin bisa menerima tiket.
     * Bidang tiket dibaca dari node solusi, atau dari status transfer terbaru.
     */
    protected function bisaTerima($tiket, $admin): bool
    {
        if (! $admin->bidang_id) {
            return false;
        }

        if ($tiket->bidang_id) {
            return $tiket->bidang_id === $admin->bidang_id;
        }

        return false;
    }

    // ─── Menunggu Verifikasi ───────────────────────────────────────────────
    public function menungguVerif(Request $request)
    {
        $admin          = $this->adminProfile();
        $prefixKembali  = '[Dikembalikan oleh Tim Teknis] ';
        $prefixTransfer = '[Transfer ke ';

        $applyFilters = function ($q) use ($request) {
            if ($request->filled('opd_id')) $q->where('opd_id', $request->opd_id);
            if ($request->filled('rekomendasi_penanganan')) {
                $q->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
            }
            if ($request->filled('search')) {
                $s = $request->search;
                $q->where(fn($q2) => $q2->where('id', 'like', "%$s%")->orWhere('subjek_masalah', 'like', "%$s%"));
            }
            return $q;
        };

        // 1. Tiket masuk: admin_id null, bidang_id sesuai admin, status verifikasi_admin
        $queryMasuk = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'buktiFoto', 'solutionNode'])
            ->whereNull('admin_id')
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin'));

        if ($admin && $admin->bidang_id) {
            $queryMasuk->where('bidang_id', $admin->bidang_id);
        }
        $applyFilters($queryMasuk);

        // 3. Tiket dikembalikan teknisi: masih milik admin ini
        $queryKembali = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'buktiFoto', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixKembali . '%'));
        $applyFilters($queryKembali);

        $mapTiket = function ($tiket) use ($admin, $prefixKembali, $prefixTransfer) {
            $tiket->can_terima        = $admin ? $this->bisaTerima($tiket, $admin) : false;
            $catatan                  = $tiket->latestStatus?->catatan ?? '';
            $tiket->is_transfer       = str_starts_with($catatan, $prefixTransfer);

            // Check for returned ticket
            $tiket->dikembalikan      = str_starts_with($catatan, $prefixKembali);
            $tiket->alasan_kembalikan = $tiket->dikembalikan ? substr($catatan, strlen($prefixKembali)) : null;
            $tiket->can_revisi        = ! $tiket->is_transfer && ! $tiket->dikembalikan;

            // Extract transfer message
            $tiket->transfer_message = null;
            if ($tiket->is_transfer) {
                // Format: [Transfer ke BIDANG_ID] MESSAGE
                // Find the closing bracket
                $closePos = strpos($catatan, '] ');
                if ($closePos !== false) {
                    $tiket->transfer_message = substr($catatan, $closePos + 2); // +2 for '] '
                }
            }

            return $tiket;
        };

        $tiketsVerif = $queryMasuk->latest()->get()
            ->map($mapTiket)
            ->sortByDesc(fn($t) => $t->rekomendasi_penanganan === 'eskalasi' ? 1 : 0)
            ->values();

        $tiketsDikembalikan = $queryKembali->latest()->get()
            ->map($mapTiket)
            ->values();

        $opds     = Opd::orderBy('nama_opd')->get();
        $bidangs  = Bidang::all();
        $teknisis = TimTeknis::with('bidang')
            ->where('bidang_id', $admin?->bidang_id)
            ->withCount(['tiketTeknisi as tiket_aktif_count' => function ($q) {
                $q->where('status_tugas', 'aktif')
                  ->whereHas('tiket', function ($tq) {
                      $tq->whereHas('latestStatus', function ($sq) {
                          $sq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']);
                      });
                  });
            }])
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin_helpdesk.manajemen-tiket.menunggu-verif', compact(
            'tiketsVerif', 'tiketsDikembalikan', 'opds', 'bidangs', 'teknisis', 'admin'
        ));
    }

    // ─── Terima & Proses Tiket → panduan_remote ───────────────────────────
    public function terimaProses(Request $request, string $id)
    {
        $admin = $this->adminProfile();
        $tiket = Tiket::with(['kategori', 'kb'])->findOrFail($id);

        // Validasi: tiket harus punya KB + kategori + bidang yang sama dengan admin
        if (! $this->bisaTerima($tiket, $admin)) {
            return back()->with('error', 'Anda tidak dapat menerima tiket ini. Tiket harus memiliki KB, kategori, dan bidang yang sesuai dengan bidang Anda.');
        }

        $tiket->update(['admin_id' => $admin->id]);

        // ─── HANDLE CHAT ROOM (Multi-Admin Tracking via Pivot) ───
        $chatRoom = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => 'admin'],
            [
                'is_active' => true,
            ]
        );

        $opdUserId = $tiket->opd?->user_id;
        if ($opdUserId) {
            $chatRoom->users()->syncWithoutDetaching([
                $opdUserId => ['bidang_id' => $tiket->bidang_id],
            ]);
        }

        if ($chatRoom) {
            // Chat room sudah ada (transferred ticket)
            if (!$chatRoom->is_active) {
                // Re-enable chat room untuk transferred ticket
                $chatRoom->update(['is_active' => true]);

                // Add admin baru ke chat room dengan tracking
                if ($admin->user_id) {
                    // Get next sequence number
                    $lastSequence = $chatRoom->users()
                        ->where('users.role', 'admin_helpdesk')
                        ->max('sequence_number') ?? 0;

                    $chatRoom->users()->syncWithoutDetaching([
                        $admin->user_id => [
                        'bidang_id'      => $admin->bidang_id,
                        'sequence_number'=> $lastSequence + 1,
                        'started_at'     => now(),
                        'is_active'      => true,
                        ],
                    ]);
                }
            }
        }

        if (! $chatRoom->users()->wherePivot('user_id', $admin->user_id)->exists()) {
            if ($admin->user_id) {
                $lastSequence = $chatRoom->users()
                    ->where('users.role', 'admin_helpdesk')
                    ->max('sequence_number') ?? 0;

                $chatRoom->users()->syncWithoutDetaching([
                    $admin->user_id => [
                        'bidang_id'      => $admin->bidang_id,
                        'sequence_number'=> $lastSequence + 1,
                        'started_at'     => now(),
                        'is_active'      => true,
                    ],
                ]);
            }
        }

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'panduan_remote',
            'catatan'     => 'Tiket diterima dan diproses oleh admin helpdesk. OPD akan dihubungi via panduan remote (chat).',
            'created_at'  => now(),
        ]);

        // Notifikasi ke OPD bahwa tiket sudah diverifikasi
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'panduan_remote',
            keterangan : 'Tiket Anda telah diverifikasi dan admin helpdesk siap memberikan panduan remote.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('approve', "Menerima tiket #{$tiket->id} — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return redirect()->route('admin_helpdesk.tiket.panduan')
                         ->with('success', "Tiket #{$tiket->id} berhasil diterima dan masuk ke Panduan Remote.");
    }

    // ─── Minta Revisi ─────────────────────────────────────────────────────
    public function revisi(Request $request, string $id)
    {
        $request->validate(['alasan_revisi' => 'required|string|max:1000']);

        $tiket = Tiket::with('latestStatus')->findOrFail($id);
        $catatan = $tiket->latestStatus?->catatan ?? '';

        if (str_starts_with($catatan, '[Transfer ke ')) {
            return back()->with('error', 'Tiket transfer tidak dapat diminta revisi. Silakan terima, transfer ulang, atau eskalasi tiket.');
        }

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perlu_revisi',
            'catatan'      => $request->alasan_revisi,
            'created_at'   => now(),
        ]);

        // Notifikasi ke OPD bahwa tiket perlu direvisi
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'perlu_revisi',
            keterangan : 'Tiket Anda perlu direvisi: ' . $request->alasan_revisi,
            url        : route('opd.tiket.edit', $tiket->id),
        ));

        $this->logAktivitas('reject', "Meminta revisi tiket #{$tiket->id} — {$request->alasan_revisi}", 'tiket', $tiket->id);

        return back()->with('success', "Permintaan revisi untuk tiket #{$tiket->id} berhasil dikirim.");
    }

    // ─── Transfer ke Admin Helpdesk Bidang Lain ───────────────────────────
    public function transfer(Request $request, string $id)
    {
        $request->validate(['bidang_id' => 'required|string|exists:bidang,id']);

        $admin = $this->adminProfile();
        $tiket = Tiket::findOrFail($id);

        $instruksi = $request->instruksi ?? 'Dialihkan oleh admin helpdesk.';

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => '[Transfer ke ' . $request->bidang_id . '] ' . $instruksi,
            'created_at'   => now(),
        ]);

        // ─── HANDLE CHAT ROOM TRANSFER (Multi-Admin Tracking via Pivot) ───
        $chatRoom = ChatRoom::where('tiket_id', $tiket->id)
            ->where('nama_roomchat', 'admin')
            ->first();
        if ($chatRoom && $chatRoom->is_active) {
            // Tiket sudah dalam panduan remote, perlu track untuk transfer
            $oldAdminId = $admin?->user_id;

            // Mark admin lama sebagai history di pivot table
            if ($oldAdminId) {
                $chatRoom->users()->updateExistingPivot($oldAdminId, [
                    'is_active' => false,
                    'ended_at' => now(),
                ]);
            }

            // Disable chat room sementara sampai admin baru terima
            $chatRoom->update([
                'is_active' => false,
                'transferred_at' => now(),
            ]);
        }

        // PERBAIKAN: admin_id di-SET NULL agar semua admin di bidang tujuan
        // bisa melihatnya di halaman Menunggu Verifikasi.
        $tiket->update([
            'admin_id' => null,
            'bidang_id' => $request->bidang_id
        ]);

        $this->logAktivitas('update', "Transfer tiket #{$tiket->id} ke bidang {$request->bidang_id} — {$instruksi}", 'tiket', $tiket->id);

        // ─── PERBAIKAN: Kirim notifikasi ke semua admin di bidang tujuan ───
        $adminsTujuan = AdminHelpdesk::with('user')
            ->where('bidang_id', $request->bidang_id)
            ->get();

        $namaOpd = $tiket->opd?->nama_opd ?? 'OPD';
        $url     = route('admin_helpdesk.tiket.menunggu');

        $adminsTujuan->each(function ($admin) use ($tiket, $namaOpd, $instruksi, $url) {
            $admin->user?->notify(new TiketTransferNotification(
                kodeTiket: $tiket->id,
                namaOpd: $namaOpd,
                instruksi: $instruksi,
                url: $url
            ));
        });

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditransfer ke bidang tujuan dan notifikasi telah dikirim.");
    }

    // ─── Eskalasi ke Tim Teknis → perbaikan_teknis ───────────────────────
    public function eskalasi(Request $request, string $id)
    {
        $request->validate([
            'teknisi_utama_id'         => 'required|string|exists:tim_teknis,id',
            'teknisi_pendamping_ids'   => 'nullable|array',
            'teknisi_pendamping_ids.*' => 'string|exists:tim_teknis,id',
        ]);

        $admin = $this->adminProfile();
        $tiket = Tiket::findOrFail($id);

        // 1. Kumpulkan semua ID teknisi (Utama + Pendamping) yang akan ditugaskan
        $allTeknisiIds = [$request->teknisi_utama_id];
        if ($request->filled('teknisi_pendamping_ids') && is_array($request->teknisi_pendamping_ids)) {
            $allTeknisiIds = array_merge($allTeknisiIds, $request->teknisi_pendamping_ids);
        }
        $allTeknisiIds = array_unique($allTeknisiIds);

        // 2. CEK BATAS MAKSIMAL TIKET (Maksimal 3 tiket aktif per teknisi)
        // Hanya hitung tiket yang benar-benar masih aktif dikerjakan (perbaikan_teknis / dibuka_kembali).
        // Tidak mengandalkan status_tugas saja karena bisa stale akibat race condition.
        $teknisiOverload = TimTeknis::whereIn('id', $allTeknisiIds)
            ->withCount(['tiketTeknisi as tiket_aktif_count' => function ($q) {
                $q->where('status_tugas', 'aktif')
                  ->whereHas('tiket', function ($tq) {
                      $tq->whereHas('latestStatus', function ($sq) {
                          $sq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']);
                      });
                  });
            }])
            ->get()
            ->filter(fn($t) => $t->tiket_aktif_count >= 3);

        // Jika ada teknisi yang sudah memegang 3 tiket atau lebih, batalkan proses
        if ($teknisiOverload->isNotEmpty()) {
            $namaTeknisi = $teknisiOverload->pluck('nama_lengkap')->join(', ');
            return back()->with('error', "Eskalasi gagal. Teknisi berikut telah mencapai batas maksimal 3 tiket aktif: {$namaTeknisi}.");
        }

        // 3. Jika aman, lanjut update status tiket
        $tiket->update(['admin_id' => $admin?->id]);

        // ─── HANDLE CHAT ROOM UNTUK ESKALASI ───
        $chatRoom = ChatRoom::where('tiket_id', $tiket->id)
            ->where('nama_roomchat', 'admin')
            ->first();
        if ($chatRoom) {
            // Jika ada chat room (transferred atau tidak)
            // Disable chat room, artinya tidak bisa chat lagi (read-only history)
            if ($chatRoom->is_active) {
                $chatRoom->update(['is_active' => false]);
            }

            // Add tim teknis utama ke chat room agar bisa lihat history chat
            $teknisPrincipal = TimTeknis::with('user')->find($request->teknisi_utama_id);
            if ($teknisPrincipal?->user_id) {
                $chatRoom->users()->syncWithoutDetaching([
                    $teknisPrincipal->user_id => ['bidang_id' => $tiket->bidang_id],
                ]);
            }

            // Add tim teknis pendamping ke chat room juga
            if ($request->filled('teknisi_pendamping_ids') && is_array($request->teknisi_pendamping_ids)) {
                $pendamping = TimTeknis::with('user')
                    ->whereIn('id', array_unique($request->teknisi_pendamping_ids))
                    ->get();

                foreach ($pendamping as $t) {
                    if ($t->user_id) {
                        $chatRoom->users()->syncWithoutDetaching([
                            $t->user_id => ['bidang_id' => $tiket->bidang_id],
                        ]);
                    }
                }
            }
        }

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perbaikan_teknis',
            'catatan'      => $request->instruksi ?? 'Tiket dieskalasi ke tim teknis untuk penanganan langsung.',
            'created_at'   => now(),
        ]);

        // 4. Masukkan Teknisi Utama
        TiketTeknisi::create([
            'tiket_id'         => $tiket->id,
            'teknis_id'        => $request->teknisi_utama_id,
            'peran_teknisi'    => 'teknisi_utama',
            'waktu_ditugaskan' => now(),
            'status_tugas'     => 'aktif',
        ]);

        // 5. Masukkan Teknisi Pendamping
        $pendampingIds = [];
        if ($request->filled('teknisi_pendamping_ids') && is_array($request->teknisi_pendamping_ids)) {
            foreach (array_unique($request->teknisi_pendamping_ids) as $pendampingId) {
                if ($pendampingId !== $request->teknisi_utama_id) {
                    TiketTeknisi::create([
                        'tiket_id'         => $tiket->id,
                        'teknis_id'        => $pendampingId,
                        'peran_teknisi'    => 'teknisi_pendamping',
                        'waktu_ditugaskan' => now(),
                        'status_tugas'     => 'aktif',
                    ]);
                    $pendampingIds[] = $pendampingId;
                }
            }
        }

        $teknisRoom = ChatRoom::firstOrCreate(
            ['tiket_id' => $tiket->id, 'nama_roomchat' => 'teknis'],
            [
                'is_active' => true,
            ]
        );

        $tiket->load('opd');
        if ($tiket->opd?->user_id) {
            $teknisRoom->users()->syncWithoutDetaching([
                $tiket->opd->user_id => ['bidang_id' => $tiket->bidang_id],
            ]);
        }

        $teknisiDitugaskan = TimTeknis::with('user')->whereIn('id', $allTeknisiIds)->get();
        foreach ($teknisiDitugaskan as $t) {
            if ($t->user_id) {
                $teknisRoom->users()->syncWithoutDetaching([
                    $t->user_id => ['bidang_id' => $tiket->bidang_id],
                ]);
            }
        }

        // 6. Notifikasi ke Tim Teknis yang ditugaskan
        $judulMasalah = $tiket->subjek_masalah;
        $urlAntrean   = route('tim_teknis.antrean');

        $teknisiDitugaskan
            ->each(fn ($t) => $t->user?->notify(new TugasBaruNotification(
                kodeTiket    : $tiket->id,
                judulMasalah : $judulMasalah,
                url          : $urlAntrean,
            )));

        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'perbaikan_teknis',
            keterangan : 'Tiket Anda telah dieskalasi ke Tim Teknis untuk penanganan lebih lanjut.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('escalate', "Eskalasi tiket #{$tiket->id} ke tim teknis", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil dieskalasi ke Tim Teknis.");
    }

    // ─── Selesaikan Tiket dari Panduan Remote ────────────────────────────
    public function selesaiOlehAdmin(Request $request, string $id)
    {
        $request->validate(['catatan' => 'nullable|string|max:1000']);

        $admin = $this->adminProfile();
        $tiket = Tiket::where('admin_id', $admin?->id)
                      ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'))
                      ->findOrFail($id);

        StatusTiket::create([
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'catatan'      => '[Diselesaikan oleh Admin Helpdesk] ' . ($request->catatan ?? 'Tiket berhasil diselesaikan melalui panduan remote.'),
            'created_at'   => now(),
        ]);

        // Kasus 2: tiket sudah mencapai batas maksimal pembukaan → langsung tutup
        $tiket->refresh();
        if (!$tiket->canBeReopened()) {
            StatusTiket::create([
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'tiket_ditutup',
                'catatan'      => 'Tiket ditutup otomatis setelah mencapai batas maksimal pembukaan (' . $tiket->reopened_count . 'x).',
                'created_at'   => now(),
            ]);
        }

        // Notifikasi ke OPD bahwa tiket selesai
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'selesai',
            keterangan : 'Tiket Anda telah diselesaikan melalui panduan remote oleh admin helpdesk.',
            url        : route('opd.tiket.show', $tiket->id),
        ));

        $this->logAktivitas('approve', "Tiket #{$tiket->id} diselesaikan oleh admin helpdesk — {$tiket->subjek_masalah}", 'tiket', $tiket->id);

        return back()->with('success', "Tiket #{$tiket->id} berhasil ditandai selesai.");
    }

    // ─── Panduan Remote ───────────────────────────────────────────────────
    public function panduan(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'chatRooms', 'teknisiUtama.timTeknis', 'buktiFoto', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $this->panduanStatuses()));

        if ($request->filled('opd_id'))   $query->where('opd_id', $request->opd_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->paginate(10);
        $this->attachUnreadChatCounts($tikets->getCollection(), Auth::id(), 'admin');
        $opds     = Opd::orderBy('nama_opd')->get();
        $bidangs  = Bidang::orderBy('nama_bidang')->get();
        $teknisis = TimTeknis::with('bidang')
            ->where('bidang_id', $admin?->bidang_id)
            ->withCount(['tiketTeknisi as tiket_aktif_count' => function ($q) {
                $q->where('status_tugas', 'aktif')
                  ->whereHas('tiket', function ($tq) {
                      $tq->whereHas('latestStatus', function ($sq) {
                          $sq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']);
                      });
                  });
            }])
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin_helpdesk.manajemen-tiket.panduan-remote', compact('tikets','opds','bidangs','teknisis','admin'));
    }

    private function attachUnreadChatCounts($tikets, string $userId, string $roomType): void
    {
        $roomMap = $tikets
            ->flatMap(fn($tiket) => $tiket->chatRooms ?? collect())
            ->where('nama_roomchat', $roomType)
            ->keyBy('tiket_id');

        $roomIds = $roomMap->pluck('id')->values();

        $unreadMap = collect();
        if ($roomIds->isNotEmpty()) {
            $unreadMap = DB::table('chat_messages as m')
                ->select('m.room_id', DB::raw('COUNT(*) as count'))
                ->whereIn('m.room_id', $roomIds)
                ->where('m.sender_id', '!=', $userId)
                ->whereRaw("m.created_at > COALESCE(
                    (SELECT cru.last_read_at FROM chat_room_users cru
                     WHERE cru.room_id = m.room_id AND cru.user_id = ?),
                    '1970-01-01 00:00:00'
                )", [$userId])
                ->groupBy('m.room_id')
                ->pluck('count', 'room_id');
        }

        $tikets->each(function ($tiket) use ($roomMap, $unreadMap) {
            $room = $roomMap->get($tiket->id);
            $tiket->chat_room_id = $room?->id;
            $tiket->unread_count = $room ? (int) ($unreadMap->get($room->id, 0)) : 0;
        });
    }

    // ─── Distribusi & Eskalasi ────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'statusTiket', 'teknisiUtama.timTeknis', 'tiketTeknisi.timTeknis', 'buktiFoto', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->where(function($q) {
                // Tiket dengan status perbaikan_teknis atau dibuka_kembali
                $q->whereHas('latestStatus', fn($lq) => $lq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']))
                  // ATAU tiket yang masih punya TiketTeknisi aktif (untuk kasus 2 teknisi, salah satu sudah selesai)
                  ->orWhereHas('tiketTeknisi', fn($tq) => $tq->where('status_tugas', 'aktif'));
            });

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('kategori_id')) {
            $query->whereHas('solutionNode', fn($q) => $q->where('kategori_id', $request->kategori_id));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->paginate(10);
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\KategoriSistem::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.distribusi', compact('tikets','opds','kategori','admin'));
    }

    // ─── Riwayat Tiket ────────────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $admin = $this->adminProfile();

        $statusSelesai = ['selesai', 'rusak_berat', 'tiket_ditutup'];

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis', 'tiketTeknisi.timTeknis', 'buktiFoto', 'solutionNode', 'chatRooms'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $statusSelesai));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('rekomendasi_penanganan')) {
            $query->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
        }
        if ($request->filled('kategori_id')) {
            $query->whereHas('solutionNode', fn($q) => $q->where('kategori_id', $request->kategori_id));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->get();
        $opds     = Opd::orderBy('nama_opd')->get();
        $kategori = \App\Models\KategoriSistem::orderBy('nama_kategori')->get();

        return view('admin_helpdesk.manajemen-tiket.riwayat', compact('tikets','opds','kategori','admin'));
    }

    // ─── Export CSV Riwayat Tiket ─────────────────────────────────────────
    public function exportCsv(Request $request)
    {
        $admin         = $this->adminProfile();
        $statusSelesai = ['selesai', 'rusak_berat', 'tiket_ditutup'];

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis', 'tiketTeknisi.timTeknis'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $statusSelesai));

        if ($request->filled('rekomendasi_penanganan')) {
            $query->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
        }
        if ($request->filled('kategori_id')) {
            $query->whereHas('solutionNode', fn($q) => $q->where('kategori_id', $request->kategori_id));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%$s%")->orWhere('subjek_masalah', 'like', "%$s%"));
        }

        $tikets = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="riwayat-tiket-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($tikets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->exportCsvHeader());
            foreach ($tikets as $t) {
                $rekomendasiLabel = match($t->rekomendasi_penanganan) {
                    'eskalasi' => 'Perlu Dieskalasi ke Tim Teknis',
                    default    => 'Ditangani Admin',
                };
                $statusLabel = match($t->latestStatus?->status_tiket) {
                    'selesai'       => 'Selesai',
                    'rusak_berat'   => 'Rusak Berat',
                    'tiket_ditutup' => 'Tiket Ditutup',
                    default         => ucfirst(str_replace('_', ' ', $t->latestStatus?->status_tiket ?? '—')),
                };
                $teknisiNama = $t->teknisiUtama?->timTeknis?->nama_lengkap ?? 'Admin';
                fputcsv($handle, [
                    $t->id,
                    $t->opd?->nama_opd ?? '—',
                    $t->subjek_masalah,
                    $t->kategori?->nama_kategori ?? '—',
                    $rekomendasiLabel,
                    $teknisiNama,
                    $statusLabel,
                    $t->created_at?->format('d M Y H:i'),
                    $t->latestStatus?->created_at?->format('d M Y H:i') ?? '—',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function panduanStatuses(): array
    {
        return self::PANDUAN_STATUSES;
    }

    protected function exportCsvHeader(): array
    {
        return self::EXPORT_CSV_HEADER;
    }
}
