<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaNotaria;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AtestiaAdminNotariaController extends Controller
{
    public function index()
    {
        $notarias = AtestiaNotaria::withoutGlobalScopes()
            ->with('tenant')
            ->latest()
            ->paginate(15);

        return view('atestia.admin.notarias.index', compact('notarias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_comercial'   => 'required|string|max:255',
            'rfc'                => 'required|string|max:13',
            'titular'            => 'required|string|max:255',
            'tipo_fedatario'     => 'required|in:NOTARIO,CORREDOR',
            'perfil_servicio'    => 'required|in:lite,pro',
            'numero_fedatario'   => 'nullable|string|max:32',
            'entidad_federativa' => 'nullable|string|max:64',
            'admin_name'         => 'required|string|max:255',
            'admin_email'        => 'required|email|unique:users,email',
            'admin_password'     => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name'   => $data['nombre_comercial'],
                'activo' => true,
            ]);

            AtestiaNotaria::create([
                'tenant_id'          => $tenant->id,
                'rfc'                => strtoupper(trim($data['rfc'])),
                'titular'            => $data['titular'],
                'tipo_fedatario'     => $data['tipo_fedatario'],
                'perfil_apartado'    => $data['tipo_fedatario'] === 'CORREDOR' ? 'B' : 'A',
                'perfil_servicio'    => $data['perfil_servicio'],
                'numero_fedatario'   => $data['numero_fedatario'] ?? null,
                'entidad_federativa' => $data['entidad_federativa'] ?? null,
                'activo'             => true,
            ]);

            $admin = User::create([
                'name'      => $data['admin_name'],
                'email'     => $data['admin_email'],
                'password'  => Hash::make($data['admin_password']),
                'tenant_id' => $tenant->id,
                'avatar'    => 'avatar-1.jpg',
                'activo'    => true,
            ]);

            $admin->assignRole('administrador');
        });

        return redirect()->route('atestia.admin.notarias.index')
            ->with('success', "Notaría '{$data['nombre_comercial']}' y su Administrador creados correctamente.");
    }

    public function toggle(int $id)
    {
        $notaria = AtestiaNotaria::withoutGlobalScopes()->findOrFail($id);
        $nuevoEstado = ! $notaria->activo;

        $notaria->update(['activo' => $nuevoEstado]);
        if ($notaria->tenant) {
            $notaria->tenant->update(['activo' => $nuevoEstado]);
        }

        return redirect()->route('atestia.admin.notarias.index')
            ->with('success', 'Estado de la notaría actualizado.');
    }
}
