<?php

namespace Tests\Feature\Module01;

use App\Models\Branch;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * T01-6 — Fitur "Ingat Saya Selama 7 Hari" pada halaman login
 *
 * Memverifikasi:
 * 1. Login dengan remember=1 → remember_token tersimpan di DB.
 * 2. Login tanpa remember → tidak ada remember cookie di response.
 * 3. Remember cookie terset dengan durasi tepat 7 hari (bukan forever/5 tahun).
 * 4. Login dengan remember=1 via email juga bekerja (dual-login).
 * 5. Checkbox remember di-repopulate saat login gagal (old value).
 * 6. Login dengan remember=0 → tidak ada remember cookie.
 */
class T0106RememberMeTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private int $branchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->branchId = Branch::query()->value('id');
    }

    /** @test */
    public function login_with_remember_sets_remember_token_in_database(): void
    {
        $this->assertNull($this->superAdmin->remember_token);

        $this->post(route('login.store'), [
            'login'    => 'superadmin',
            'password' => 'superadmin',
            'remember' => '1',
        ])->assertRedirect(route('dashboard'));

        $this->superAdmin->refresh();
        $this->assertNotNull($this->superAdmin->remember_token);
    }

    /** @test */
    public function login_without_remember_does_not_set_remember_cookie(): void
    {
        $response = $this->post(route('login.store'), [
            'login'    => 'superadmin',
            'password' => 'superadmin',
            // tidak ada 'remember'
        ]);

        $response->assertRedirect(route('dashboard'));

        $recallerName = Auth::guard('web')->getRecallerName();

        $found = false;
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $recallerName) {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found, 'Remember cookie seharusnya tidak di-set saat remember=false.');
    }

    /** @test */
    public function login_with_remember_sets_cookie_expiring_in_7_days(): void
    {
        $response = $this->post(route('login.store'), [
            'login'    => 'superadmin',
            'password' => 'superadmin',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $recallerName   = Auth::guard('web')->getRecallerName();
        $rememberCookie = null;

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $recallerName) {
                $rememberCookie = $cookie;
                break;
            }
        }

        $this->assertNotNull($rememberCookie, 'Remember cookie harus ada di response.');

        $expectedExpiry = time() + (7 * 24 * 60 * 60); // 7 hari dalam detik

        // Toleransi 60 detik untuk waktu eksekusi test
        $this->assertEqualsWithDelta(
            $expectedExpiry,
            $rememberCookie->getExpiresTime(),
            60,
            'Remember cookie harus kadaluarsa dalam 7 hari, bukan 5 tahun (forever).'
        );
    }

    /** @test */
    public function login_with_remember_via_email_also_works(): void
    {
        // Buat user baru dengan email untuk test dual-login via email + remember
        $kasirRole = \App\Models\Role::where('name', 'kasir')->first();
        $user = User::create([
            'name'      => 'Kasir Email',
            'username'  => 'kasir_email_test',
            'email'     => 'kasir@toko.com',
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRole->id,
            'branch_id' => $this->branchId,
            'is_active' => true,
        ]);

        $this->assertNull($user->remember_token);

        $response = $this->post(route('login.store'), [
            'login'    => 'kasir@toko.com',
            'password' => 'Password1',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertNotNull($user->remember_token);
    }

    /** @test */
    public function remember_checkbox_is_repopulated_on_failed_login(): void
    {
        $response = $this->post(route('login.store'), [
            'login'    => 'superadmin',
            'password' => 'wrong-password',
            'remember' => '1',
        ]);

        // Harus redirect kembali dengan error
        $response->assertRedirect();
        $response->assertSessionHasErrors('login');

        // old('remember') harus '1' agar checkbox terpilih kembali
        $this->assertEquals('1', session()->getOldInput('remember'));
    }

    /** @test */
    public function login_with_remember_zero_does_not_set_remember_cookie(): void
    {
        $response = $this->post(route('login.store'), [
            'login'    => 'superadmin',
            'password' => 'superadmin',
            'remember' => '0',
        ]);

        $response->assertRedirect(route('dashboard'));

        $recallerName = Auth::guard('web')->getRecallerName();

        $found = false;
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $recallerName) {
                $found = true;
                break;
            }
        }

        $this->assertFalse($found, 'Remember cookie tidak boleh di-set saat remember=0.');
    }
}
