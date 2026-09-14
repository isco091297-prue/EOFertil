<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Services\UserCleanupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $search = request('search');

        $users = User::with([
            'role',
            'warehouse',
            'zone',
            'branch',
        ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('identification', 'like', "%{$search}%");
                });
            })
            ->orderBy('first_name')
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $zones = Zone::where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'users.create',
            compact(
                'roles',
                'warehouses',
                'zones',
                'branches'
            )
        );
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::where('is_active', true)
            ->orderBy('name')
            ->get();

        $zones = Zone::where('is_active', true)
            ->orderBy('name')
            ->get();

        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'users.edit',
            compact(
                'user',
                'roles',
                'warehouses',
                'zones',
                'branches'
            )
        );
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ) {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function approve(User $user)
    {
        if ($user->is_active) {
            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'El usuario ya se encuentra activo.'
                );
        }

        $user->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Usuario aprobado correctamente.'
            );
    }

    public function cleanupPreview(
        User $user,
        UserCleanupService $cleanupService
    ) {
        try {
            $summary = $cleanupService->preview($user);

            return view(
                'users.cleanup',
                compact('user', 'summary')
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function clean(
        Request $request,
        User $user,
        UserCleanupService $cleanupService
    ): RedirectResponse {
        try {
            $cleanupService->cleanTestData($user);

            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'Los datos de prueba del usuario fueron limpiados correctamente. La cuenta del usuario se conserva.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function deletePreview(
        User $user,
        UserCleanupService $cleanupService
    ) {
        try {
            $summary = $cleanupService->preview($user);

            return view(
                'users.delete',
                compact('user', 'summary')
            );
        } catch (\Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function destroy(
        User $user,
        UserCleanupService $cleanupService
    ): RedirectResponse {
        try {
            $cleanupService->deleteUser($user);

            return redirect()
                ->route('users.index')
                ->with(
                    'success',
                    'El usuario y todos sus datos asociados fueron eliminados correctamente.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->route('users.index')
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}