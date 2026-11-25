<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class MfaController extends Controller
{
    public function showMfaForm()
    {
        if (! session()->has('mfa_pending_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa');
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('mfa_pending_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if (! $user->mfa_code || ! $user->mfa_expires_at || now()->greaterThan($user->mfa_expires_at)) {
            return back()->withErrors(['code' => 'El código de verificación expiró. Inicia sesión nuevamente.']);
        }

        if (! hash_equals($user->mfa_code, trim($request->input('code')))) {
            return back()->withErrors(['code' => 'Código de verificación incorrecto.']);
        }

        $remember = (bool) session('mfa_remember', false);
        session()->forget(['mfa_pending_user_id', 'mfa_remember']);

        $user->update([
            'mfa_code'              => null,
            'mfa_expires_at'        => null,
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended($user->hasRole('super_admin') ? '/app/admin/dashboard' : '/app/dashboard');
    }

    public function showForcePasswordForm()
    {
        return view('auth.force_password');
    }

    public function updateForcePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ], [
            'password.min' => 'La contraseña debe tener al menos 12 caracteres, mayúsculas, minúsculas, números y símbolos.',
        ]);

        $user = auth()->user();
        $user->update([
            'password'             => Hash::make($request->input('password')),
            'must_change_password' => false,
        ]);

        return redirect($user->hasRole('super_admin') ? '/app/admin/dashboard' : '/app/dashboard')
            ->with('success', 'Contraseña actualizada correctamente.');
    }
}
