<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SubuserController extends Controller
{
    private const PERMISSIONS = [
        'create_clients' => 'Cadastrar clientes',
        'create_charges' => 'Criar empréstimos',
        'record_payments' => 'Registrar pagamentos',
        'change_due_dates' => 'Alterar vencimentos',
    ];

    public function index(Request $request)
    {
        $subusers = $request->user()->subusers()->with('subuserPermissions')->orderBy('name')->get();

        return view('subusers.index', compact('subusers'));
    }

    public function create()
    {
        return view('subusers.create', ['permissionLabels' => self::PERMISSIONS]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $temporaryPassword = Str::random(24);

        $subuser = DB::transaction(function () use ($request, $validated, $temporaryPassword) {
            $subuser = $request->user()->subusers()->create([
                ...$validated,
                'password' => Hash::make($temporaryPassword),
            ]);

            $subuser->forceFill([
                'is_active' => true,
                'must_change_password' => true,
            ])->save();

            $subuser->subuserPermissions()->create(array_fill_keys(array_keys(self::PERMISSIONS), true));

            return $subuser;
        });

        return redirect()->route('subusers.edit', $subuser)
            ->with('temporary_password', $temporaryPassword)
            ->with('success', 'Subusuário criado. Copie a senha provisória agora; ela será exibida uma única vez.');
    }

    public function edit(Request $request, User $subuser)
    {
        $subuser = $this->ownedSubuser($request, $subuser->id);
        $subuser->load('subuserPermissions');

        return view('subusers.edit', [
            'subuser' => $subuser,
            'permissionLabels' => self::PERMISSIONS,
        ]);
    }

    public function update(Request $request, User $subuser)
    {
        $subuser = $this->ownedSubuser($request, $subuser->id);
        $permissions = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['in:' . implode(',', array_keys(self::PERMISSIONS))],
        ])['permissions'] ?? [];

        $values = [];
        foreach (self::PERMISSIONS as $key => $_label) {
            $values[$key] = in_array($key, $permissions, true);
        }

        $subuser->subuserPermissions()->updateOrCreate(['user_id' => $subuser->id], $values);

        return redirect()->route('subusers.edit', $subuser)->with('success', 'Permissões atualizadas.');
    }

    public function toggleActive(Request $request, User $subuser)
    {
        $subuser = $this->ownedSubuser($request, $subuser->id);
        $subuser->is_active = !$subuser->is_active;
        $subuser->save();

        return redirect()->route('subusers.index')->with('success', $subuser->is_active
            ? 'Subusuário ativado.'
            : 'Subusuário desativado.');
    }

    public function resetPassword(Request $request, User $subuser)
    {
        $subuser = $this->ownedSubuser($request, $subuser->id);
        $temporaryPassword = Str::random(24);

        $subuser->forceFill([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ])->save();

        return redirect()->route('subusers.edit', $subuser)
            ->with('temporary_password', $temporaryPassword)
            ->with('success', 'Senha provisória redefinida. Copie-a agora; ela será exibida uma única vez.');
    }

    private function ownedSubuser(Request $request, int $subuserId): User
    {
        return $request->user()->subusers()->whereKey($subuserId)->firstOrFail();
    }
}
