<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ActivityLogController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        // Mencatat aktivitas login ke activity_log
        ActivityLogController::logLogin($user);
        
        $role = $user->role;

        return match ($role) {
            'super_admin'    => redirect()->route('super_admin.dashboard'),
            'admin_helpdesk' => redirect()->route('admin_helpdesk.dashboard'),
            'tim_teknis'     => redirect()->route('tim_teknis.dashboard'),
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
