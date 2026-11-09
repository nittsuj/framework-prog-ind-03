<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DemoLoginTest extends TestCase
{
    use RefreshDatabase;

    private function seedAccount(string $email, string $role): User
    {
        $user = User::factory()->create(['email' => $email]);
        $user->assignRole(Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']));

        return $user;
    }

    public function test_admin_demo_button_logs_in_admin(): void
    {
        config(['app.demo_login' => true]);
        $admin = $this->seedAccount('dosenpbkk@its.ac.id', 'Admin');

        $this->post('/demo-login/admin')->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_staf_demo_button_logs_in_staf(): void
    {
        config(['app.demo_login' => true]);
        $staf = $this->seedAccount('staf@its.ac.id', 'Staf');

        $this->post('/demo-login/staf')->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($staf);
    }

    public function test_demo_login_disabled_returns_404(): void
    {
        config(['app.demo_login' => false]);
        $this->seedAccount('dosenpbkk@its.ac.id', 'Admin');

        $this->post('/demo-login/admin')->assertNotFound();
        $this->assertGuest();
    }

    public function test_unknown_role_is_rejected(): void
    {
        config(['app.demo_login' => true]);

        $this->post('/demo-login/root')->assertNotFound();
        $this->assertGuest();
    }
}
