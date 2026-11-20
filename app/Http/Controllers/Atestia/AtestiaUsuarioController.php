<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AtestiaUsuarioController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $isSuper = $authUser->hasRole('super_admin');

        $query = User::with('roles')->latest();
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
            'password'  => 'required|string|min:8',
            'role'      => "required|in:{$allowedRoles}",
            'tenant_id' => $isSuper ? 'nullable|exists:tenants,id' : 'nullable',
        ]);

        if ($isSuper && $data['role'] !== 'super_admin' && empty($data['tenant_id'])) {
            return back()->withErrors(['tenant_id' => 'Selecciona la notaría/cliente para este usuario.'])->withInput();
        }

        $tenantId = $isSuper
            ? ($data['role'] === 'super_admin' ? null : (int) $data['tenant_id'])
            : $authUser->tenant_id;

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'tenant_id' => $tenantId,
            'avatar'    => 'avatar-1.jpg',
            'activo'    => true,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('atestia.usuarios.index')
            ->with('success', "Usuario {$user->email} creado con perfil {$data['role']}.");
    }

    public function toggle(int $id)
    {
        $authUser = auth()->user();
        $target = User::findOrFail($id);

        if (! $authUser->hasRole('super_admin') && $target->tenant_id !== $authUser->tenant_id) {
            abort(403);
        }

        if ($target->id === $authUser->id) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $target->update(['activo' => ! $target->activo]);

        return redirect()->route('atestia.usuarios.index')
            ->with('success', 'Estado del usuario actualizado.');
    }
}
