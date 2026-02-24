<?php

namespace Tests\Feature\Module01;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T01-1 — Admin creates a user
 *
 * Given: Super Admin is logged in
 * When:  POST /admin/users with valid payload
 * Then:  User created, audit_logs row created with action=create,
 *        auditable_type=User, new_values contains user data (no password)
 */
class T0101AdminCreatesUserTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
    }

    /** @test */
    public function super_admin_can_create_a_user(): void
    {
        $roleId = Role::where('name', 'kasir')->value('id');

        $payload = [
            'name'                  => 'Kasir Baru',
            'username'              => 'kasir_baru',
            'email'                 => 'kasir@test.com',
            'password'              => 'Password1',
            'password_confirmation' => 'Password1',
            'role_id'               => $roleId,
            'branch_id'             => 1,
            'is_active'             => 1,
        ];

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), $payload)
            ->assertRedirect(route('admin.users.index'));

        // User tersimpan di DB
        $this->assertDatabaseHas('users', [
            'username' => 'kasir_baru',
            'email'    => 'kasir@test.com',
        ]);

        // Audit log tercatat
        $newUser = User::where('username', 'kasir_baru')->firstOrFail();

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'create',
            'auditable_type' => User::class,
            'auditable_id'   => $newUser->id,
        ]);

        // new_values tidak mengandung password
        $log = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $newUser->id)
            ->where('action', 'create')
            ->firstOrFail();

        $newValues = $log->new_values ?? [];
        $this->assertArrayNotHasKey('password', $newValues);
        $this->assertEquals('kasir_baru', $newValues['username'] ?? null);
    }

    /** @test */
    public function duplicate_username_is_rejected(): void
    {
        $roleId = Role::where('name', 'kasir')->value('id');

        // First user
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name'                  => 'User Satu',
                'username'              => 'duplikat',
                'email'                 => 'duplikat@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $roleId,
                'branch_id'             => 1,
                'is_active'             => 1,
            ]);

        // Second user with same username
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name'                  => 'User Dua',
                'username'              => 'duplikat',
                'email'                 => 'duplikat2@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $roleId,
                'branch_id'             => 1,
                'is_active'             => 1,
            ])
            ->assertSessionHasErrors('username');
    }

    /** @test */
    public function global_role_cannot_have_branch_id(): void
    {
        $ownerRoleId = Role::where('name', 'owner')->value('id');

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name'                  => 'Owner Baru',
                'username'              => 'owner_baru',
                'email'                 => 'owner@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $ownerRoleId,
                'branch_id'             => 1, // harus ditolak untuk role global
                'is_active'             => 1,
            ])
            ->assertSessionHasErrors('branch_id');
    }

    /** @test */
    public function non_global_role_requires_branch_id(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name'                  => 'Kasir Tanpa Cabang',
                'username'              => 'kasir_tanpa_cabang',
                'email'                 => 'kasir_notbranch@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $kasirRoleId,
                'branch_id'             => null, // wajib untuk kasir
                'is_active'             => 1,
            ])
            ->assertSessionHasErrors('branch_id');
    }
}
