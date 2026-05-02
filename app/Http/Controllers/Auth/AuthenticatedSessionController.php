<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ActivityLogController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\StatusTiket;
use App\Models\Tiket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();

        // Update waktu login terakhir
        $user->last_login_at = now();
        $user->save();

        // Mencatat aktivitas login ke activity_log
        ActivityLogController::logLogin($user);

        $role = $user->role;

        // Kasus 1: auto-close tiket OPD yang sudah > 7 hari belum dikonfirmasi
        if ($role === 'opd') {
            $opd = $user->opd;
            if ($opd) {
                $batas = now()->subDays(7);
                Tiket::where('opd_id', $opd->id)
                    ->whereNull('penilaian')
                    ->whereHas('latestStatus', fn($q) =>
                        $q->whereIn('status_tiket', ['selesai', 'rusak_berat'])
                          ->where('created_at', '<=', $batas)
                    )
                    ->each(function (Tiket $tiket) {
                        StatusTiket::create([
                            'id'           => 'STS-' . strtoupper(Str::random(10)),
                            'tiket_id'     => $tiket->id,
                            'status_tiket' => 'tiket_ditutup',
                            'catatan'      => 'Tiket ditutup otomatis oleh sistem karena tidak dikonfirmasi dalam 7 hari.',
                            'created_at'   => now(),
                        ]);
                    });
            }
        }

        return match ($role) {
            'super_admin'    => redirect()->route('super_admin.dashboard'),
            'admin_helpdesk' => redirect()->route('admin_helpdesk.dashboard'),
            'tim_teknis'     => redirect()->route('tim_teknis.antrean'),
            'opd'            => redirect()->route('opd.dashboard'),
            'pimpinan'       => redirect()->route('pimpinan.dashboard'),
            default          => redirect('/'),
        };
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        Auth::guard('web')->logout();

        // Mencatat aktivitas logout ke activity_log
        if ($user) {
            ActivityLogController::logLogout($user);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
