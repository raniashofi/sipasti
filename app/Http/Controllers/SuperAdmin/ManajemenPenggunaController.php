<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ActivityLogController;
use App\Models\AdminHelpdesk;
use App\Models\Bidang;
use App\Models\Opd;
use App\Models\Pimpinan;
use App\Models\TimTeknis;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ManajemenPenggunaController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    //  OPD
    // ──────────────────────────────────────────────────────────────

    public function indexOpd(Request $request)
    {
        $bidangs = Bidang::all();
        $opdList = Opd::whereNull('parent_id')->orderBy('nama_opd')->get();
        $opds    = Opd::with('user')->whereNotNull('user_id')->orderBy('nama_opd')->get();

        return view('super_admin.manajemen-pengguna.opd', compact('opds', 'bidangs', 'opdList'));
    }

    public function storeOpd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_opd'       => 'required|string|max:50|unique:opd,kode_opd',
            'nama_opd'       => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'kdunit'         => 'nullable|string|max:50',
            'parent_id'      => 'nullable|integer',
            'is_bagian'      => 'nullable|in:Y,N',
            'bidang_id'      => 'nullable|exists:bidang,id',
            'status_teknisi' => 'nullable|in:online,offline',
        ], [
            'kode_opd.required' => 'Kode OPD wajib diisi.',
            'kode_opd.unique'   => 'Kode OPD sudah digunakan.',
            'nama_opd.required' => 'Nama Instansi (OPD) wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_tambah', true);
        }

        $userId = (string) Str::uuid();
        $opdId  = (string) Str::uuid();

        User::create([
            'id'       => $userId,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'opd',
        ]);

        Opd::create([
            'id'             => $opdId,
            'user_id'        => $userId,
            'kode_opd'       => $request->kode_opd,
            'nama_opd'       => $request->nama_opd,
            'kdunit'         => $request->kdunit ?: null,
            'parent_id'      => $request->parent_id ?: null,
            'is_bagian'      => $request->is_bagian ?: null,
            'bidang_id'      => $request->bidang_id ?: null,
            'status_teknisi' => $request->status_teknisi ?: null,
        ]);

        // Log aktivitas
        ActivityLogController::logCreate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'opd',
            idRecord: $opdId,
            dataAfter: [
                'kode_opd' => $request->kode_opd,
                'nama_opd' => $request->nama_opd,
            ]
        );

        return redirect()->route('super_admin.pengguna.opd')
            ->with('success', 'Akun OPD berhasil ditambahkan.');
    }

    public function updateOpd(Request $request, $id)
    {
        $opd = Opd::findOrFail($id);

        $rules = [
            'kode_opd'       => 'required|string|max:50|unique:opd,kode_opd,' . $opd->id,
            'nama_opd'       => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $opd->user_id,
            'kdunit'         => 'nullable|string|max:50',
            'parent_id'      => 'nullable|integer',
            'is_bagian'      => 'nullable|in:Y,N',
            'bidang_id'      => 'nullable|exists:bidang,id',
            'status_teknisi' => 'nullable|in:online,offline',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validator = Validator::make($request->all(), $rules, [
            'kode_opd.required' => 'Kode OPD wajib diisi.',
            'kode_opd.unique'   => 'Kode OPD sudah digunakan.',
            'nama_opd.required' => 'Nama Instansi (OPD) wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah digunakan.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_edit', true)
                ->with('edit_opd_id', $id);
        }

        $opd->update([
            'kode_opd'       => $request->kode_opd,
            'nama_opd'       => $request->nama_opd,
            'kdunit'         => $request->kdunit ?: null,
            'parent_id'      => $request->parent_id ?: null,
            'is_bagian'      => $request->is_bagian ?: null,
            'bidang_id'      => $request->bidang_id ?: null,
            'status_teknisi' => $request->status_teknisi ?: null,
        ]);

        if ($opd->user) {
            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $opd->user->update($userData);
        }

        // Log aktivitas
        ActivityLogController::logUpdate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'opd',
            idRecord: $id,
            dataBefore: $opd->getOriginal(),
            dataAfter: [
                'kode_opd' => $request->kode_opd,
                'nama_opd' => $request->nama_opd,
                'email' => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.opd')
            ->with('success', 'Data OPD berhasil diperbarui.');
    }

    public function destroyOpd($id)
    {
        $opd  = Opd::findOrFail($id);
        $user = $opd->user;

        // Store data before delete
        $dataBefore = [
            'id' => $opd->id,
            'kode_opd' => $opd->kode_opd,
            'nama_opd' => $opd->nama_opd,
        ];

        $opd->delete();
        if ($user) $user->delete();

        // Log aktivitas
        ActivityLogController::logDelete(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'opd',
            idRecord: $id,
            dataBefore: $dataBefore
        );

        return redirect()->route('super_admin.pengguna.opd')
            ->with('success', 'Akun OPD berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    //  INTERNAL (Tim Teknis + Admin Helpdesk)
    // ──────────────────────────────────────────────────────────────

    public function indexInternal(Request $request)
    {
        $tab = $request->query('tab', 'tim_teknis');

        $timTeknis     = TimTeknis::with('user', 'bidang')->orderBy('nama_lengkap')->get();
        $adminHelpdesk = AdminHelpdesk::with('user', 'bidang')->orderBy('nama_lengkap')->get();
        $pimpinanList  = Pimpinan::with('user')->orderBy('nama_lengkap')->get();
        $bidangs       = Bidang::all();

        return view('super_admin.manajemen-pengguna.internal', compact(
            'timTeknis', 'adminHelpdesk', 'pimpinanList', 'bidangs', 'tab'
        ));
    }

    // ── Tim Teknis ──

    public function storeTimTeknis(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap'   => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'bidang_id'      => 'required|exists:bidang,id',
            'status_teknisi' => 'nullable|in:online,offline',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 6 karakter.',
            'bidang_id.required'    => 'Bidang wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_tambah_tt', true)
                ->with('tab', 'tim_teknis');
        }

        $userId = (string) Str::uuid();
        $ttId   = (string) Str::uuid();

        User::create([
            'id'       => $userId,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'tim_teknis',
        ]);

        TimTeknis::create([
            'id'             => $ttId,
            'user_id'        => $userId,
            'nama_lengkap'   => $request->nama_lengkap,
            'bidang_id'      => $request->bidang_id ?: null,
            'status_teknisi' => $request->status_teknisi ?: 'online',
        ]);

        // Log aktivitas
        ActivityLogController::logCreate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'tim_teknis',
            idRecord: $ttId,
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'tim_teknis'])
            ->with('success', 'Akun Tim Teknis berhasil ditambahkan.');
    }

    public function updateTimTeknis(Request $request, $id)
    {
        $tt = TimTeknis::findOrFail($id);

        $rules = [
            'nama_lengkap'   => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $tt->user_id,
            'bidang_id'      => 'nullable|exists:bidang,id',
            'status_teknisi' => 'nullable|in:online,offline',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.min'          => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_edit_tt', true)
                ->with('edit_tt_id', $id)
                ->with('tab', 'tim_teknis');
        }

        // Cek apakah bidang berubah
        $newBidangId = $request->bidang_id ?: null;
        if ($newBidangId !== $tt->bidang_id) {
            $hasActiveTickets = $tt->tiketTeknisi()
                ->whereHas('tiket', fn($q) => $q->whereHas('latestStatus', fn($q2) =>
                    $q2->whereNotIn('status_tiket', ['selesai', 'rusak_berat'])
                ))
                ->exists();

            if ($hasActiveTickets) {
                return back()
                    ->withErrors(['bidang_id' => 'Bidang tidak dapat dipindahkan karena teknisi masih memiliki tiket aktif yang belum selesai.'])
                    ->withInput()
                    ->with('open_edit_tt', true)
                    ->with('edit_tt_id', $id)
                    ->with('tab', 'tim_teknis');
            }
        }

        $tt->update([
            'nama_lengkap'   => $request->nama_lengkap,
            'bidang_id'      => $newBidangId,
            'status_teknisi' => $request->status_teknisi ?: null,
        ]);

        if ($tt->user) {
            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $tt->user->update($userData);
        }

        // Log aktivitas
        ActivityLogController::logUpdate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'tim_teknis',
            idRecord: $id,
            dataBefore: $tt->getOriginal(),
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'tim_teknis'])
            ->with('success', 'Data Tim Teknis berhasil diperbarui.');
    }

    public function destroyTimTeknis($id)
    {
        $tt   = TimTeknis::findOrFail($id);
        $user = $tt->user;

        // Store data before delete
        $dataBefore = [
            'id' => $tt->id,
            'nama_lengkap' => $tt->nama_lengkap,
        ];

        $tt->delete();
        if ($user) $user->delete();

        // Log aktivitas
        ActivityLogController::logDelete(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'tim_teknis',
            idRecord: $id,
            dataBefore: $dataBefore
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'tim_teknis'])
            ->with('success', 'Akun Tim Teknis berhasil dihapus.');
    }

    // ── Admin Helpdesk ──

    public function storeAdminHelpdesk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'bidang_id'    => 'required|exists:bidang,id',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 6 karakter.',
            'bidang_id.required'    => 'Bidang wajib dipilih.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_tambah_ah', true)
                ->with('tab', 'admin_helpdesk');
        }

        $userId = (string) Str::uuid();
        $ahId   = (string) Str::uuid();

        User::create([
            'id'       => $userId,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin_helpdesk',
        ]);

        AdminHelpdesk::create([
            'id'           => $ahId,
            'user_id'      => $userId,
            'nama_lengkap' => $request->nama_lengkap,
            'bidang_id'    => $request->bidang_id ?: null,
        ]);

        // Log aktivitas
        ActivityLogController::logCreate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'admin_helpdesk',
            idRecord: $ahId,
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'admin_helpdesk'])
            ->with('success', 'Akun Admin Helpdesk berhasil ditambahkan.');
    }

    public function updateAdminHelpdesk(Request $request, $id)
    {
        $ah = AdminHelpdesk::findOrFail($id);

        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $ah->user_id,
            'bidang_id'    => 'nullable|exists:bidang,id',
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.min'          => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_edit_ah', true)
                ->with('edit_ah_id', $id)
                ->with('tab', 'admin_helpdesk');
        }

        $ah->update([
            'nama_lengkap' => $request->nama_lengkap,
            'bidang_id'    => $request->bidang_id ?: null,
        ]);

        if ($ah->user) {
            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $ah->user->update($userData);
        }

        // Log aktivitas
        ActivityLogController::logUpdate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'admin_helpdesk',
            idRecord: $id,
            dataBefore: $ah->getOriginal(),
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'admin_helpdesk'])
            ->with('success', 'Data Admin Helpdesk berhasil diperbarui.');
    }

    public function destroyAdminHelpdesk($id)
    {
        $ah   = AdminHelpdesk::findOrFail($id);
        $user = $ah->user;

        // Store data before delete
        $dataBefore = [
            'id' => $ah->id,
            'nama_lengkap' => $ah->nama_lengkap,
        ];

        $ah->delete();
        if ($user) $user->delete();

        // Log aktivitas
        ActivityLogController::logDelete(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'admin_helpdesk',
            idRecord: $id,
            dataBefore: $dataBefore
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'admin_helpdesk'])
            ->with('success', 'Akun Admin Helpdesk berhasil dihapus.');
    }

    // ──────────────────────────────────────────────────────────────
    //  PIMPINAN
    // ──────────────────────────────────────────────────────────────

    public function storePimpinan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_tambah_pimpinan', true)
                ->with('tab', 'pimpinan');
        }

        $userId     = (string) Str::uuid();
        $pimpinanId = (string) Str::uuid();

        User::create([
            'id'       => $userId,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'pimpinan',
        ]);

        Pimpinan::create([
            'id'           => $pimpinanId,
            'user_id'      => $userId,
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        ActivityLogController::logCreate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'pimpinan',
            idRecord: $pimpinanId,
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email'        => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'pimpinan'])
            ->with('success', 'Akun Pimpinan berhasil ditambahkan.');
    }

    public function updatePimpinan(Request $request, $id)
    {
        $pimpinan = Pimpinan::findOrFail($id);

        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $pimpinan->user_id,
        ];
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:6';
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah digunakan.',
            'password.min'          => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()
                ->with('open_edit_pimpinan', true)
                ->with('edit_pimpinan_id', $id)
                ->with('tab', 'pimpinan');
        }

        $pimpinan->update([
            'nama_lengkap' => $request->nama_lengkap,
        ]);

        if ($pimpinan->user) {
            $userData = ['email' => $request->email];
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            $pimpinan->user->update($userData);
        }

        ActivityLogController::logUpdate(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'pimpinan',
            idRecord: $id,
            dataBefore: $pimpinan->getOriginal(),
            dataAfter: [
                'nama_lengkap' => $request->nama_lengkap,
                'email'        => $request->email,
            ]
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'pimpinan'])
            ->with('success', 'Data Pimpinan berhasil diperbarui.');
    }

    public function destroyPimpinan($id)
    {
        $pimpinan = Pimpinan::findOrFail($id);
        $user     = $pimpinan->user;

        $dataBefore = [
            'id'           => $pimpinan->id,
            'nama_lengkap' => $pimpinan->nama_lengkap,
        ];

        $pimpinan->delete();
        if ($user) $user->delete();

        ActivityLogController::logDelete(
            userId: Auth::id(),
            rolePelaku: Auth::user()->role,
            namaTabel: 'pimpinan',
            idRecord: $id,
            dataBefore: $dataBefore
        );

        return redirect()->route('super_admin.pengguna.internal', ['tab' => 'pimpinan'])
            ->with('success', 'Akun Pimpinan berhasil dihapus.');
    }
}
