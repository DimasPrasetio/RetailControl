<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $query = User::with('role')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn($w) => $w->where('name', 'like', "%{$q}%")->orWhere('username', 'like', "%{$q}%"));
        }
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }
        if ($request->filled('active')) {
            $query->where('is_active', $request->input('active'));
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = \App\Models\Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
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
