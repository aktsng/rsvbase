<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 未ログインユーザーのテスト
     */
    public function test_guest_is_not_redirected_from_login(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_guest_is_redirected_to_login_from_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_owner(): void
    {
        $response = $this->get('/owner');
        $response->assertRedirect('/login');
    }

    /**
     * Admin権限ユーザーのテスト
     */
    public function test_authenticated_admin_is_redirected_to_admin_dashboard_from_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/login');
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_authenticated_admin_is_redirected_to_admin_dashboard_from_admin_base(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertRedirect(route('admin.dashboard'));
    }

    /**
     * Owner権限ユーザーのテスト
     */
    public function test_authenticated_owner_is_redirected_to_owner_dashboard_from_login(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get('/login');
        $response->assertRedirect(route('owner.dashboard'));
    }

    public function test_authenticated_owner_is_redirected_to_owner_dashboard_from_owner_base(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get('/owner');
        $response->assertRedirect(route('owner.dashboard'));
    }
}
