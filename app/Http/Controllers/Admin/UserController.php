<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Tenant;
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

        $query = User::with(['role', 'branch', 'tenant'])
            ->forTenant($request->user()->getAccessibleTenantId())
            ->orderBy('name');

        if (! $request->user()->isGlobal()) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(fn ($w) => $w
                ->where('name', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%"));
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->input('role_id'));
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->input('active'));
        }

        if ($request->filled('tenant_id') && $request->user()->isPlatformAdmin()) {
            $query->where('tenant_id', $request->integer('tenant_id'));
        }

        if ($request->filled('branch_id') && $request->user()->isGlobal()) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        $users = $query->paginate(10)->withQueryString();
        $roles = Role::orderBy('name')->get();
        $tenants = $this->availableTenants($request->user(), $request->integer('tenant_id'));
        $branches = $this->availableBranches($request->user(), $request->integer('branch_id'));

        return view('admin.users.index', compact('users', 'roles', 'tenants', 'branches'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        $roles = Role::orderBy('name')->get();
        $tenants = $this->availableTenants(request()->user());
        $branches = $this->availableBranches(request()->user());

        return view('admin.users.create', compact('roles', 'tenants', 'branches'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::findOrFail($data['role_id']);
        if ($role->name === \App\Enums\RoleEnum::SuperAdmin) {
            $data['tenant_id'] = null;
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
        $tenants = $this->availableTenants(request()->user(), $user->tenant_id);
        $branches = $this->availableBranches(request()->user(), $user->branch_id);

        return view('admin.users.edit', compact('user', 'roles', 'tenants', 'branches'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $role = Role::findOrFail($data['role_id']);
        if ($role->name === \App\Enums\RoleEnum::SuperAdmin) {
            $data['tenant_id'] = null;
            $data['branch_id'] = null;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        Gate::authorize('deactivate', $user);

        $user->update(['is_active' => false]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dinonaktifkan.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function availableBranches(User $actingUser, ?int $selectedBranchId = null)
    {
        $query = Branch::query()
            ->forTenant($actingUser->getAccessibleTenantId())
            ->orderBy('name');

        if (! $actingUser->isGlobal()) {
            return $query->whereKey($actingUser->branch_id)->get();
        }

        $query->where(function ($inner) use ($selectedBranchId) {
            $inner->where('is_active', true);

            if ($selectedBranchId) {
                $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedBranchId);
            }
        });

        return $query->get();
    }

    private function availableTenants(User $actingUser, ?int $selectedTenantId = null)
    {
        $query = Tenant::query()->orderBy('name');

        if (! $actingUser->isPlatformAdmin()) {
            return $query->whereKey($actingUser->tenant_id)->get();
        }

        $query->where(function ($inner) use ($selectedTenantId) {
            $inner->where('is_active', true);

            if ($selectedTenantId) {
                $inner->orWhere($inner->getModel()->getQualifiedKeyName(), $selectedTenantId);
            }
        });

        return $query->get();
    }
}
