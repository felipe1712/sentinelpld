<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AtestiaUsuarioController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $isSuper = $authUser->hasRole('super_admin');

        $query = User::with(['roles', 'tenant'])->latest();
        if (! $isSuper) {
            $query->where('tenant_id', $authUser->tenant_id);
        }

        $usuarios = $query->paginate(20);
        $tenants = $isSuper ? Tenant::orderBy('name')->get() : collect();

        return view('atestia.usuarios.index', compact('usuarios', 'tenants', 'isSuper'));
    }

    public function store(Request $request)
    {
        $authUser = auth()->user();
        $isSuper = $authUser->hasRole('super_admin');

        $allowedRoles = $isSuper
            ? 'super_admin,administrador,operador,auditor_ro'
            : 'administrador,operador,auditor_ro';

        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
            'role'      => "required|in:{$allowedRoles}",
            'tenant_id' => $isSuper ? 'nullable|exists:tenants,id' : 'nullable',
        ], [
            'password.min' => 'La contraseña debe tener al menos 12 caracteres, incluyendo mayúsculas, minúsculas, números y símbolos.',
        ]);

        if ($isSuper && $data['role'] !== 'super_admin' && empty($data['tenant_id'])) {
            return back()->withErrors(['tenant_id' => 'Selecciona la notaría/cliente para este usuario.'])->withInput();
        }

        $tenantId = $isSuper
            ? ($data['role'] === 'super_admin' ? null : (int) $data['tenant_id'])
            : $authUser->tenant_id;

        $user = User::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'password'             => Hash::make($data['password']),
            'tenant_id'            => $tenantId,
            'avatar'               => 'avatar-1.jpg',
            'activo'               => true,
            'must_change_password' => true,
            'mfa_enabled'          => $request->boolean('mfa_enabled', false),
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('atestia.usuarios.index')
            ->with('success', "Usuario {$user->email} creado con perfil {$data['role']}. Se exigirá cambio de contraseña en su primer inicio.");
    }

    public function toggle(int $id)
    {
        $authUser = auth()->user();
        $target = $this->authorizeTarget($authUser, $id);

        if ($target->id === $authUser->id) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $target->update(['activo' => ! $target->activo]);

        return redirect()->route('atestia.usuarios.index')
            ->with('success', 'Estado del usuario actualizado.');
    }

    public function toggleMfa(int $id)
    {
        $authUser = auth()->user();
        $target = $this->authorizeTarget($authUser, $id);

        $nuevoEstado = ! $target->mfa_enabled;
        $target->update([
            'mfa_enabled'    => $nuevoEstado,
            'mfa_code'       => $nuevoEstado ? $target->mfa_code : null,
            'mfa_expires_at' => $nuevoEstado ? $target->mfa_expires_at : null,
        ]);

        $estadoTxt = $nuevoEstado ? 'activado' : 'desactivado';

        return redirect()->route('atestia.usuarios.index')
            ->with('success', "Doble factor (MFA por correo) {$estadoTxt} para {$target->email}.");
    }

    public function updatePassword(Request $request, int $id)
    {
        $authUser = auth()->user();
        $target = $this->authorizeTarget($authUser, $id);

        $data = $request->validate([
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        $target->update([
            'password'             => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        return redirect()->route('atestia.usuarios.index')
            ->with('success', "Contraseña actualizada para {$target->email}.");
    }

    public function destroy(int $id)
    {
        $authUser = auth()->user();
        $target = $this->authorizeTarget($authUser, $id);

        if ($target->id === $authUser->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $email = $target->email;
        $target->delete();

        return redirect()->route('atestia.usuarios.index')
            ->with('success', "Usuario {$email} eliminado.");
    }

    protected function authorizeTarget(User $authUser, int $id): User
    {
        $target = User::findOrFail($id);

        if (! $authUser->hasRole('super_admin')) {
            if ($target->tenant_id !== $authUser->tenant_id || $target->hasRole('super_admin')) {
                abort(403);
            }
        }

        return $target;
    }
}
