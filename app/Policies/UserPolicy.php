<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /** Hanya Super Admin yang dapat melihat daftar user */
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasPermission('users.view');
    }

    public function view(User $authUser, User $target): bool
    {
        return $authUser->hasPermission('users.view');
    }

    public function create(User $authUser): bool
    {
        return $authUser->hasPermission('users.create');
    }

    public function update(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.update')) {
            return false;
        }

        // Tidak boleh mengubah Super Admin lain (kecuali dirinya sendiri)
        if ($target->isSuperAdmin() && $authUser->id !== $target->id) {
            return false;
        }

        return true;
    }

    public function delete(User $authUser, User $target): bool
    {
        if (! $authUser->hasPermission('users.delete')) {
            return false;
        }

        // Tidak boleh menghapus diri sendiri
        if ($authUser->id === $target->id) {
            return false;
        }

        // Tidak boleh menghapus Super Admin lain
        if ($target->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
