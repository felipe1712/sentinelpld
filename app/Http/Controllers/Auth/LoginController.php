<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function redirectTo(): string
    {
        return auth()->user()?->hasRole('super_admin')
            ? '/app/admin/dashboard'
            : '/app/dashboard';
    }

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request)
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();

        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            $mins = max(1, now()->diffInMinutes($user->locked_until));
            throw ValidationException::withMessages([
                $this->username() => "Cuenta bloqueada temporalmente por intentos fallidos. Intenta de nuevo en {$mins} minuto(s).",
            ]);
        }

        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $user = User::where($this->username(), $request->input($this->username()))->first();
        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $updates = ['failed_login_attempts' => $attempts];
            if ($attempts >= 5) {
                $updates['locked_until'] = now()->addMinutes(15);
            }
            $user->update($updates);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    protected function authenticated(Request $request, $user)
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
        ]);

        if ($user->mfa_enabled) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $user->update([
                'mfa_code'       => $code,
                'mfa_expires_at' => now()->addMinutes(10),
            ]);

            try {
                Mail::raw("Tu código de verificación Sentinel PLD es: {$code} (válido por 10 minutos).", function ($m) use ($user) {
                    $m->to($user->email)->subject('Sentinel PLD · Código de verificación (MFA)');
                });
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar correo MFA: ' . $e->getMessage());
            }

            Auth::logout();
            session([
                'mfa_pending_user_id' => $user->id,
                'mfa_remember'        => $request->boolean('remember'),
            ]);

            return redirect()->route('mfa.show')
                ->with('success', 'Se envió un código de verificación de 6 dígitos a tu correo.');
        }

        return null;
    }
}
