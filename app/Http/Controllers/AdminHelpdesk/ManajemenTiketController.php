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
use Illuminate\Support\Str;

class ManajemenTiketController extends Controller
{
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
     * Cek apakah admin bisa menerima tiket:
     * tiket harus punya kategori_id → kategori_sistem.bidang_id == admin.bidang_id
     */
    private function bisaTerima(Tiket $tiket, AdminHelpdesk $admin): bool
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

        // 1. Tiket baru dari OPD: admin_id null, bidang sesuai, bukan tiket transfer
        // PERBAIKAN: Filter berdasarkan kategori.bidang_id ATAU tiket.bidang_id secara langsung
        $queryBaru = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->whereNull('admin_id')
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where(fn($q2) => $q2->whereNull('catatan')->orWhere('catatan', 'not like', '[Transfer ke %]')));
        if ($admin && $admin->bidang_id) {
            $queryBaru->where(function($q) use ($admin) {
                // Tiket dengan kategori yang sesuai bidang admin
                $q->whereHas('kategori', fn($q2) => $q2->where('bidang_id', $admin->bidang_id))
                  // ATAU tiket dengan bidang_id langsung yang sesuai (fallback jika kategori null)
                  ->orWhere('bidang_id', $admin->bidang_id);
            });
        }
        $applyFilters($queryBaru);

        // 2. Tiket ditransfer masuk ke bidang admin ini
        // PERBAIKAN: Izinkan admin_id sesuai dengan admin saat ini, karena saat transfer admin_id sudah diubah
        $queryTransfer = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->where(function($q) use ($admin) {
                $q->whereNull('admin_id')
                  ->orWhere('admin_id', $admin?->id);
            })
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixTransfer . ($admin?->bidang_id ?? '') . ']%'));
        $applyFilters($queryTransfer);

        // 3. Tiket dikembalikan teknisi: masih milik admin ini
        $queryKembali = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'verifikasi_admin')
                ->where('catatan', 'like', $prefixKembali . '%'));
        $applyFilters($queryKembali);

        $mapTiket = function ($tiket) use ($admin, $prefixKembali, $prefixTransfer) {
            $tiket->can_terima        = $admin ? $this->bisaTerima($tiket, $admin) : false;
            $catatan                  = $tiket->latestStatus?->catatan ?? '';

            // Check for returned ticket
            $tiket->dikembalikan      = str_starts_with($catatan, $prefixKembali);
            $tiket->alasan_kembalikan = $tiket->dikembalikan ? substr($catatan, strlen($prefixKembali)) : null;

            // Extract transfer message
            $tiket->transfer_message = null;
            if (str_starts_with($catatan, $prefixTransfer)) {
                // Format: [Transfer ke BIDANG_ID] MESSAGE
                // Find the closing bracket
                $closePos = strpos($catatan, '] ');
                if ($closePos !== false) {
                    $tiket->transfer_message = substr($catatan, $closePos + 2); // +2 for '] '
                }
            }

            return $tiket;
        };

        $tiketsVerif = $queryBaru->latest()->get()
            ->merge($queryTransfer->latest()->get())
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
            ->withCount(['tiketTeknisi as tiket_aktif_count' => fn($q) => $q->where('status_tugas', 'aktif')])
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
        $chatRoom = ChatRoom::where('tiket_id', $tiket->id)->first();
        if ($chatRoom) {
            // Chat room sudah ada (transferred ticket)
            if (!$chatRoom->is_active && $chatRoom->transferred_from_admin_id) {
                // Re-enable chat room untuk transferred ticket
                $chatRoom->update([
                    'is_active' => true,
                    'current_admin_id' => $admin->user_id,
                ]);

                // Add admin baru ke chat room dengan tracking
                if ($admin->user_id) {
                    // Get next sequence number
                    $lastSequence = $chatRoom->users()
                        ->wherePivot('role_di_room', 'admin_helpdesk')
                        ->max('sequence_number') ?? 0;

                    $chatRoom->users()->attach($admin->user_id, [
                        'role_di_room'   => 'admin_helpdesk',
                        'sequence_number'=> $lastSequence + 1,
                        'started_at'     => now(),
                        'is_active'      => true,
                    ]);
                }
            }
        } else {
            // Buat chat room baru untuk tiket ini
            $chatRoom = ChatRoom::create([
                'id'               => (string) \Illuminate\Support\Str::uuid(),
                'tiket_id'         => $tiket->id,
                'nama_roomchat'    => 'Chat: ' . $tiket->subjek_masalah,
                'is_active'        => true,
                'current_admin_id' => $admin->user_id,
                'transferred_from_admin_id' => null,
                'transferred_from_bidang_id' => null,
            ]);

            // Add OPD dan admin helpdesk ke chat room
            $opdUserId = $tiket->opd?->user_id;
            if ($opdUserId) {
                $chatRoom->users()->attach($opdUserId, ['role_di_room' => 'opd']);
            }
            if ($admin->user_id) {
                $chatRoom->users()->attach($admin->user_id, [
                    'role_di_room'   => 'admin_helpdesk',
                    'sequence_number'=> 1, // Admin pertama
                    'started_at'     => now(),
                    'is_active'      => true,
                ]);
            }
        }

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
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

        $tiket = Tiket::findOrFail($id);

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perlu_revisi',
            'catatan'      => $request->alasan_revisi,
            'created_at'   => now(),
        ]);

        // Notifikasi ke OPD bahwa tiket perlu direvisi
        $tiket->load('opd.user');
        $tiket->opd?->user?->notify(new StatusTiketNotification(
            kodeTiket  : $tiket->id,
            status     : 'diverifikasi',
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
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan'      => '[Transfer ke ' . $request->bidang_id . '] ' . $instruksi,
            'created_at'   => now(),
        ]);

        // ─── HANDLE CHAT ROOM TRANSFER (Multi-Admin Tracking via Pivot) ───
        $chatRoom = ChatRoom::where('tiket_id', $tiket->id)->first();
        if ($chatRoom && $chatRoom->is_active) {
            // Tiket sudah dalam panduan remote, perlu track untuk transfer
            $oldAdminId = $admin?->user_id;
            $oldBidangId = $tiket->bidang_id;

            // Mark admin lama sebagai history di pivot table
            if ($oldAdminId) {
                $chatRoom->users()
                    ->wherePivot('user_id', $oldAdminId)
                    ->wherePivot('role_di_room', 'admin_helpdesk')
                    ->update([
                        'is_active' => false,
                        'ended_at' => now(),
                    ]);
            }

            // Update chat room status
            $chatRoom->update([
                'is_active'                 => false, // Disable sementara sampai admin baru terima
                'current_admin_id'          => null,  // Will be set ketika admin baru accept
                'transferred_from_admin_id' => $oldAdminId,
                'transferred_from_bidang_id'=> $oldBidangId,
                'transferred_at'            => now(),
            ]);

            // Hapus admin lama dari chat_room_users
            if ($oldAdminId) {
                $chatRoom->users()->detach($oldAdminId);
            }
        }

        // PERBAIKAN: admin_id di-SET NULL agar semua admin di bidang tujuan
        // bisa melihatnya di halaman Menunggu Verifikasi.
        $tiket->update([
            'admin_id'  => null,
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
        $teknisiOverload = TimTeknis::whereIn('id', $allTeknisiIds)
            ->withCount(['tiketTeknisi as tiket_aktif_count' => function ($q) {
                // Hanya hitung tiket yang status tugasnya masih aktif
                $q->where('status_tugas', 'aktif');
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
        $chatRoom = ChatRoom::where('tiket_id', $tiket->id)->first();
        if ($chatRoom) {
            // Jika ada chat room (transferred atau tidak)
            // Disable chat room, artinya tidak bisa chat lagi (read-only history)
            if ($chatRoom->is_active) {
                $chatRoom->update(['is_active' => false]);
            }

            // Add tim teknis utama ke chat room agar bisa lihat history chat
            $teknisPrincipal = TimTeknis::with('user')->find($request->teknisi_utama_id);
            if ($teknisPrincipal?->user_id) {
                $chatRoom->users()->attach($teknisPrincipal->user_id, ['role_di_room' => 'tim_teknis']);
            }

            // Add tim teknis pendamping ke chat room juga
            if ($request->filled('teknisi_pendamping_ids') && is_array($request->teknisi_pendamping_ids)) {
                $pendamping = TimTeknis::with('user')
                    ->whereIn('id', array_unique($request->teknisi_pendamping_ids))
                    ->get();

                foreach ($pendamping as $t) {
                    if ($t->user_id) {
                        $chatRoom->users()->attach($t->user_id, ['role_di_room' => 'tim_teknis']);
                    }
                }
            }
        }

        StatusTiket::create([
            'id'           => 'STS-' . strtoupper(Str::random(10)),
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

        // 6. Notifikasi ke Tim Teknis yang ditugaskan
        $judulMasalah = $tiket->subjek_masalah;
        $urlAntrean   = route('tim_teknis.antrean');

        TimTeknis::with('user')->whereIn('id', $allTeknisiIds)->get()
            ->each(fn ($t) => $t->user?->notify(new TugasBaruNotification(
                kodeTiket    : $tiket->id,
                judulMasalah : $judulMasalah,
                url          : $urlAntrean,
            )));

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
            'id'           => 'STS-' . strtoupper(Str::random(10)),
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'selesai',
            'catatan'      => '[Diselesaikan oleh Admin Helpdesk] ' . ($request->catatan ?? 'Tiket berhasil diselesaikan melalui panduan remote.'),
            'created_at'   => now(),
        ]);

        // Kasus 2: tiket pernah dibuka kembali lewat jalur panduan remote → langsung tutup
        $pernahDibukaKembaliRemote = StatusTiket::where('tiket_id', $tiket->id)
            ->where('status_tiket', 'panduan_remote')
            ->where('catatan', 'like', '[Dibuka Kembali oleh OPD]%')
            ->exists();
        if ($pernahDibukaKembaliRemote) {
            StatusTiket::create([
                'id'           => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'     => $tiket->id,
                'status_tiket' => 'tiket_ditutup',
                'catatan'      => 'Tiket ditutup otomatis setelah diselesaikan kembali oleh Admin Helpdesk.',
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

        $query = Tiket::with(['opd', 'kategori.bidang', 'latestStatus', 'sopInternal', 'chatRooms', 'teknisiUtama.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->where('status_tiket', 'panduan_remote'));

        if ($request->filled('opd_id'))   $query->where('opd_id', $request->opd_id);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('id','like',"%$s%")->orWhere('subjek_masalah','like',"%$s%"));
        }

        $tikets   = $query->latest()->paginate(10);
        $opds     = Opd::orderBy('nama_opd')->get();
        $bidangs  = Bidang::orderBy('nama_bidang')->get();
        $teknisis = TimTeknis::with('bidang')->where('bidang_id', $admin?->bidang_id)->orderBy('nama_lengkap')->get();

        return view('admin_helpdesk.manajemen-tiket.panduan-remote', compact('tikets','opds','bidangs','teknisis','admin'));
    }

    // ─── Distribusi & Eskalasi ────────────────────────────────────────────
    public function distribusi(Request $request)
    {
        $admin = $this->adminProfile();

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'sopInternal', 'statusTiket', 'teknisiUtama.timTeknis', 'tiketTeknisi.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->where(function($q) {
                // Tiket dengan status perbaikan_teknis atau dibuka_kembali
                $q->whereHas('latestStatus', fn($lq) => $lq->whereIn('status_tiket', ['perbaikan_teknis', 'dibuka_kembali']))
                  // ATAU tiket yang masih punya TiketTeknisi aktif (untuk kasus 2 teknisi, salah satu sudah selesai)
                  ->orWhereHas('tiketTeknisi', fn($tq) => $tq->where('status_tugas', 'aktif'));
            });

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
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

        $query = Tiket::with(['opd', 'kategori', 'latestStatus', 'teknisiUtama.timTeknis', 'tiketTeknisi.timTeknis', 'solutionNode'])
            ->where('admin_id', $admin?->id)
            ->whereHas('latestStatus', fn($q) => $q->whereIn('status_tiket', $statusSelesai));

        if ($request->filled('opd_id'))    $query->where('opd_id', $request->opd_id);
        if ($request->filled('rekomendasi_penanganan')) {
            $query->where('rekomendasi_penanganan', $request->rekomendasi_penanganan);
        }
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
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
            $query->where('kategori_id', $request->kategori_id);
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
            fputcsv($handle, [
                'ID Tiket', 'OPD', 'Subjek Masalah', 'Kategori',
                'Rekomendasi Penanganan', 'Teknisi Utama', 'Status Akhir',
                'Waktu Masuk', 'Waktu Selesai',
            ]);
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
}
