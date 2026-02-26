<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::with('role')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Branch null untuk role global (super_admin, owner)
        $role = Role::findOrFail($data['role_id']);
        if ($role->name->isGlobal()) {
            $data['branch_id'] = null;
        }

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();

        // Jangan update password jika tidak diisi
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Branch null untuk role global
        $role = Role::findOrFail($data['role_id']);
        if ($role->name->isGlobal()) {
            $data['branch_id'] = null;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Nonaktifkan user (set is_active=false) tanpa menghapus dari DB.
     * Sesi user yang sedang aktif akan diusir oleh EnsureUserIsActive middleware
     * pada request berikutnya.
     */
    public function deactivate(User $user): RedirectResponse
    {
        Gate::authorize('deactivate', $user);

        $user->update(['is_active' => false]); // Auditable trait mencatat update ke audit_logs

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dinonaktifkan.');
    }

    /** Hapus permanen (soft delete) — hanya super_admin */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete(); // soft delete — Auditable trait mencatat ke audit_logs

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
