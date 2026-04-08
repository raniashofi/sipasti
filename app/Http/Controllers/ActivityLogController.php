<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Mencatat aktivitas user ke tabel activity_log
     *
     * @param string $userId
     * @param string $rolePelaku (super_admin, admin_helpdesk, tim_teknis, pimpinan, opd)
     * @param string $jenisAktivitas (login, logout, create, update, delete, escalate, approve, reject)
     * @param array $data
     * @return ActivityLog
     */
    public static function log(
        string $userId,
        string $rolePelaku,
        string $jenisAktivitas,
        array $data = []
    ) {
        $ipAddress = request()->ip();
        $sessionId = session()->getId();

        return ActivityLog::create([
            'user_id'           => $userId,
            'role_pelaku'       => $rolePelaku,
            'jenis_aktivitas'   => $jenisAktivitas,
            'detail_tindakan'   => $data['detail_tindakan'] ?? null,
            'ip_address'        => $ipAddress,
            'session_id'        => $sessionId,
            'waktu_eksekusi'    => now(),
            'nama_tabel'        => $data['nama_tabel'] ?? null,
            'id_record'         => $data['id_record'] ?? null,
            'data_before'       => $data['data_before'] ?? null,
            'data_after'        => $data['data_after'] ?? null,
        ]);
    }

    /**
     * Mencatat login user
     *
     * @param User $user
     * @return ActivityLog
     */
    public static function logLogin(User $user)
    {
        return self::log(
            userId: $user->id,
            rolePelaku: $user->role,
            jenisAktivitas: 'login',
            data: [
                'detail_tindakan' => 'User login ke sistem',
            ]
        );
    }

    /**
     * Mencatat logout user
     *
     * @param User $user
     * @return ActivityLog
     */
    public static function logLogout(User $user)
    {
        return self::log(
            userId: $user->id,
            rolePelaku: $user->role,
            jenisAktivitas: 'logout',
            data: [
                'detail_tindakan' => 'User logout dari sistem',
            ]
        );
    }

    /**
     * Mencatat create record
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $namaTabel
     * @param string $idRecord
     * @param array $dataAfter
     * @return ActivityLog
     */
    public static function logCreate(
        string $userId,
        string $rolePelaku,
        string $namaTabel,
        string $idRecord,
        array $dataAfter = []
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'create',
            data: [
                'detail_tindakan' => "Membuat record baru di tabel {$namaTabel}",
                'nama_tabel'      => $namaTabel,
                'id_record'       => $idRecord,
                'data_after'      => $dataAfter,
            ]
        );
    }

    /**
     * Mencatat update record
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $namaTabel
     * @param string $idRecord
     * @param array $dataBefore
     * @param array $dataAfter
     * @return ActivityLog
     */
    public static function logUpdate(
        string $userId,
        string $rolePelaku,
        string $namaTabel,
        string $idRecord,
        array $dataBefore = [],
        array $dataAfter = []
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'update',
            data: [
                'detail_tindakan' => "Memperbarui record di tabel {$namaTabel}",
                'nama_tabel'      => $namaTabel,
                'id_record'       => $idRecord,
                'data_before'     => $dataBefore,
                'data_after'      => $dataAfter,
            ]
        );
    }

    /**
     * Mencatat delete record
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $namaTabel
     * @param string $idRecord
     * @param array $dataBefore
     * @return ActivityLog
     */
    public static function logDelete(
        string $userId,
        string $rolePelaku,
        string $namaTabel,
        string $idRecord,
        array $dataBefore = []
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'delete',
            data: [
                'detail_tindakan' => "Menghapus record di tabel {$namaTabel}",
                'nama_tabel'      => $namaTabel,
                'id_record'       => $idRecord,
                'data_before'     => $dataBefore,
            ]
        );
    }

    /**
     * Mencatat escalate/assign ticket
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $idTicket
     * @param string $detail
     * @return ActivityLog
     */
    public static function logEscalate(
        string $userId,
        string $rolePelaku,
        string $idTicket,
        string $detail = ''
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'escalate',
            data: [
                'detail_tindakan' => $detail ?: 'Melakukan escalate/assign tiket',
                'nama_tabel'      => 'tiket',
                'id_record'       => $idTicket,
            ]
        );
    }

    /**
     * Mencatat approve action
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $namaTabel
     * @param string $idRecord
     * @param string $detail
     * @return ActivityLog
     */
    public static function logApprove(
        string $userId,
        string $rolePelaku,
        string $namaTabel,
        string $idRecord,
        string $detail = ''
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'approve',
            data: [
                'detail_tindakan' => $detail ?: "Melakukan approve di tabel {$namaTabel}",
                'nama_tabel'      => $namaTabel,
                'id_record'       => $idRecord,
            ]
        );
    }

    /**
     * Mencatat reject action
     *
     * @param string $userId
     * @param string $rolePelaku
     * @param string $namaTabel
     * @param string $idRecord
     * @param string $detail
     * @return ActivityLog
     */
    public static function logReject(
        string $userId,
        string $rolePelaku,
        string $namaTabel,
        string $idRecord,
        string $detail = ''
    ) {
        return self::log(
            userId: $userId,
            rolePelaku: $rolePelaku,
            jenisAktivitas: 'reject',
            data: [
                'detail_tindakan' => $detail ?: "Melakukan reject di tabel {$namaTabel}",
                'nama_tabel'      => $namaTabel,
                'id_record'       => $idRecord,
            ]
        );
    }

    /**
     * Mengambil last login user berdasarkan user_id
     *
     * @param string $userId
     * @return \Illuminate\Support\Carbon|null
     */
    public static function getLastLogin(string $userId)
    {
        return ActivityLog::where('user_id', $userId)
            ->where('jenis_aktivitas', 'login')
            ->orderBy('waktu_eksekusi', 'desc')
            ->value('waktu_eksekusi');
    }

    /**
     * Mengambil last login user dengan formatting
     *
     * @param string $userId
     * @param string $format (d M Y, H:i)
     * @return string|null
     */
    public static function getLastLoginFormatted(string $userId, string $format = 'd M Y, H:i')
    {
        $lastLogin = self::getLastLogin($userId);

        if ($lastLogin) {
            return \Carbon\Carbon::parse($lastLogin)->translatedFormat($format);
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────
    // Keamanan & Audit page methods
    // ─────────────────────────────────────────────────────────────

    private static array $badgeClass = [
        'login'    => 'bg-blue-100 text-blue-700',
        'logout'   => 'bg-gray-100 text-gray-600',
        'create'   => 'bg-emerald-100 text-emerald-700',
        'update'   => 'bg-amber-100 text-amber-700',
        'delete'   => 'bg-red-100 text-red-700',
        'escalate' => 'bg-orange-100 text-orange-700',
        'approve'  => 'bg-green-100 text-green-700',
        'reject'   => 'bg-rose-100 text-rose-700',
    ];

    private static array $roleLabel = [
        'super_admin'    => 'Super Admin',
        'admin_helpdesk' => 'Admin Helpdesk',
        'tim_teknis'     => 'Tim Teknis',
        'pimpinan'       => 'Pimpinan',
        'opd'            => 'OPD',
    ];

    public function showAudit(Request $request)
    {
        // ── Stat cards ──
        $totalAktivitas  = ActivityLog::count();
        $loginBerhasil   = ActivityLog::where('jenis_aktivitas', 'login')->count();
        $aktivitasKritis = ActivityLog::where('jenis_aktivitas', 'delete')->count();

        // ── Filters ──
        $search  = $request->query('search', '');
        $role    = $request->query('role_pelaku', '');
        $jenis   = $request->query('jenis_aktivitas', '');
        $tanggal = $request->query('tanggal', '');

        $query = ActivityLog::with(['user.opd', 'user.adminHelpdesk.bidang', 'user.timTeknis.bidang'])
            ->orderByDesc('waktu_eksekusi');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('detail_tindakan', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('id_record', 'like', "%{$search}%")
                  ->orWhere('user_id', 'like', "%{$search}%");
            });
        }
        if ($role)    { $query->where('role_pelaku', $role); }
        if ($jenis)   { $query->where('jenis_aktivitas', $jenis); }
        if ($tanggal) { $query->whereDate('waktu_eksekusi', $tanggal); }

        $logs   = $query->paginate(20)->withQueryString();
        $logsJs = $logs->getCollection()->map(fn($l) => $this->toJs($l))->values();

        $badgeClass = self::$badgeClass;
        $roleLabel  = self::$roleLabel;

        return view('super_admin.audit', compact(
            'logs', 'logsJs', 'badgeClass', 'roleLabel',
            'totalAktivitas', 'loginBerhasil', 'aktivitasKritis',
            'search', 'role', 'jenis', 'tanggal'
        ));
    }

    public function exportCsv(Request $request)
    {
        $search  = $request->query('search', '');
        $role    = $request->query('role_pelaku', '');
        $jenis   = $request->query('jenis_aktivitas', '');
        $tanggal = $request->query('tanggal', '');

        $query = ActivityLog::with(['user.opd', 'user.adminHelpdesk.bidang', 'user.timTeknis.bidang'])
            ->orderByDesc('waktu_eksekusi');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('detail_tindakan', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('id_record', 'like', "%{$search}%");
            });
        }
        if ($role)    { $query->where('role_pelaku', $role); }
        if ($jenis)   { $query->where('jenis_aktivitas', $jenis); }
        if ($tanggal) { $query->whereDate('waktu_eksekusi', $tanggal); }

        $logs     = $query->get();
        $filename = 'audit_log_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['Waktu', 'User ID', 'Nama', 'Role', 'Jenis Aktivitas', 'Detail', 'IP Address', 'Tabel', 'ID Record']);
            foreach ($logs as $log) {
                fputcsv($f, [
                    $log->waktu_eksekusi?->format('Y-m-d H:i:s') ?? '',
                    $log->user_id ?? '',
                    $this->namaUser($log),
                    self::$roleLabel[$log->role_pelaku] ?? $log->role_pelaku,
                    $log->jenis_aktivitas,
                    $log->detail_tindakan ?? '',
                    $log->ip_address ?? '',
                    $log->nama_tabel ?? '',
                    $log->id_record ?? '',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    private static array $bidangLabel = [
        'e_government'                     => 'E-Government',
        'infrastruktur_teknologi_informasi' => 'Infrastruktur TI',
        'statistik_persandian'             => 'Statistik & Persandian',
    ];

    private function namaUser(ActivityLog $log): string
    {
        if (!$log->user) return '—';
        return $log->user->opd?->nama_opd
            ?? $log->user->adminHelpdesk?->nama_lengkap
            ?? $log->user->timTeknis?->nama_lengkap
            ?? $log->user->email;
    }

    private function bidangUser(ActivityLog $log): string
    {
        $nama = $log->user?->adminHelpdesk?->bidang?->nama_bidang
            ?? $log->user?->timTeknis?->bidang?->nama_bidang
            ?? null;
        return self::$bidangLabel[$nama] ?? '—';
    }

    private function toJs(ActivityLog $log): array
    {
        return [
            'id'              => $log->id,
            'waktu'           => $log->waktu_eksekusi?->format('d M Y, H:i:s') ?? '—',
            'user_id'         => $log->user_id ?? '—',
            'nama'            => $this->namaUser($log),
            'email'           => $log->user?->email ?? '—',
            'role_pelaku'     => $log->role_pelaku,
            'role_label'      => self::$roleLabel[$log->role_pelaku] ?? $log->role_pelaku,
            'bidang'          => $this->bidangUser($log),
            'bidang_key'      => $log->user?->adminHelpdesk?->bidang?->nama_bidang
                                    ?? $log->user?->timTeknis?->bidang?->nama_bidang
                                    ?? '',
            'jenis_aktivitas' => $log->jenis_aktivitas,
            'detail_tindakan' => $log->detail_tindakan ?? '—',
            'ip_address'      => $log->ip_address ?? '—',
            'session_id'      => $log->session_id ?? '—',
            'nama_tabel'      => $log->nama_tabel ?? '—',
            'id_record'       => $log->id_record ?? '—',
            'data_before'     => $log->data_before,
            'data_after'      => $log->data_after,
        ];
    }
}
