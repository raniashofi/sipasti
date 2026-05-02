<?php

namespace App\Http\Controllers;

use App\Models\TiketTeknisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    /**
     * Tampilkan halaman profil sesuai role.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        switch ($role) {
            case 'opd':
                $profil = \App\Models\Opd::where('user_id', $user->id)->first();
                return view('opd.profil', compact('user', 'profil'));

            case 'admin_helpdesk':
                $profil = \App\Models\AdminHelpdesk::with('bidang')
                    ->where('user_id', $user->id)->first();
                return view('admin_helpdesk.profil', compact('user', 'profil'));

            case 'tim_teknis':
                $profil = \App\Models\TimTeknis::with('bidang')
                    ->where('user_id', $user->id)->first();
                $tiketAktifCount = $profil
                    ? TiketTeknisi::where('teknis_id', $profil->id)->where('status_tugas', 'aktif')->count()
                    : 0;
                return view('tim_teknis.profil', compact('user', 'profil', 'tiketAktifCount'));

            case 'pimpinan':
                $profil = \App\Models\Pimpinan::where('user_id', $user->id)->first();
                return view('pimpinan.profil', compact('user', 'profil'));

            default:
                abort(403);
        }
    }

    /**
     * Ubah status ketersediaan tim teknis (online / offline).
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'status_teknisi' => ['required', 'in:online,offline'],
        ]);

        $user   = $request->user();
        $profil = \App\Models\TimTeknis::where('user_id', $user->id)->firstOrFail();

        if ($request->status_teknisi === 'offline') {
            $tiketAktif = TiketTeknisi::where('teknis_id', $profil->id)
                ->where('status_tugas', 'aktif')
                ->count();

            if ($tiketAktif > 0) {
                return back()->withErrors([
                    'status_teknisi' => "Anda masih memiliki {$tiketAktif} tiket aktif. Selesaikan atau kembalikan tiket terlebih dahulu sebelum mengubah status ke Offline.",
                ]);
            }
        }

        $profil->update(['status_teknisi' => $request->status_teknisi]);

        $label = $request->status_teknisi === 'online' ? 'Online' : 'Offline';
        return back()->with('success', "Status berhasil diubah menjadi {$label}.");
    }

    /**
     * Ubah password pengguna.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password_lama'              => ['required', 'string'],
            'password_baru'              => ['required', 'string', 'min:8'],
            'password_baru_confirmation' => ['required', 'string'],
        ], [
            'password_lama.required'              => 'Password lama wajib diisi.',
            'password_baru.required'              => 'Password baru wajib diisi.',
            'password_baru.min'                   => 'Password baru minimal 8 karakter.',
            'password_baru_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        $user = $request->user();

        // Skenario 2: password lama salah
        if (!Hash::check($request->password_lama, $user->password)) {
            return back()
                ->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah.'])
                ->withInput();
        }

        // Skenario 1: password baru sama dengan password lama
        if ($request->password_lama === $request->password_baru) {
            return back()
                ->withErrors(['password_baru' => 'Password baru tidak boleh sama dengan password lama.'])
                ->withInput();
        }

        // Skenario 4: konfirmasi password tidak cocok
        if ($request->password_baru !== $request->password_baru_confirmation) {
            return back()
                ->withErrors(['password_baru_confirmation' => 'Konfirmasi password tidak cocok dengan password baru.'])
                ->withInput();
        }

        $user->password = Hash::make($request->password_baru);
        $user->save();

        return back()->with('success', 'Password berhasil diubah.');
    }
}
