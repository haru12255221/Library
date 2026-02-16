<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    /** @test */
    public function admin_can_change_user_role_to_admin()
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$user->id}/role");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $user->role);
    }

    /** @test */
    public function admin_can_change_admin_role_to_user()
    {
        $otherAdmin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$otherAdmin->id}/role");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $otherAdmin->refresh();
        $this->assertEquals(User::ROLE_USER, $otherAdmin->role);
    }

    /** @test */
    public function admin_cannot_change_own_role()
    {
        $response = $this->actingAs($this->admin)
            ->patch("/admin/users/{$this->admin->id}/role");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->admin->refresh();
        $this->assertEquals(User::ROLE_ADMIN, $this->admin->role);
    }
}
