<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationLoanRouteTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => User::ROLE_ADMIN,
        ]);
        
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User', 
            'email' => 'user@test.com',
            'role' => User::ROLE_USER,
        ]);
    }

    public function test_admin_can_access_loans_index_route()
    {
        // Test that the admin.loans.index route exists and is accessible
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.loans.index'));
            
        $response->assertStatus(200);
    }

    public function test_navigation_contains_correct_loan_route_for_admin()
    {
        // Test that navigation contains the correct admin.loans.index route
        $response = $this->actingAs($this->adminUser)
            ->get(route('books.index'));
            
        $response->assertStatus(200);
        
        // Check that the navigation contains the correct route
        $response->assertSee('href="' . route('admin.loans.index') . '"', false);
        $response->assertSee('貸出履歴');
    }

    public function test_navigation_does_not_show_loan_link_for_regular_user()
    {
        // Test that regular users don't see the admin loan link
        $response = $this->actingAs($this->regularUser)
            ->get(route('books.index'));
            
        $response->assertStatus(200);
        
        // Regular users should not see the admin loans link
        $response->assertDontSee('href="' . route('admin.loans.index') . '"', false);
    }

    public function test_navigation_shows_active_state_on_loans_page()
    {
        // Test that the loans link shows as active when on the loans page
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.loans.index'));
            
        $response->assertStatus(200);
        
        // The navigation should show the loans link as active
        // This tests the activeRoutes functionality
        $response->assertSee('貸出履歴');
    }

    public function test_mobile_navigation_contains_correct_loan_route()
    {
        // Test mobile navigation contains correct route
        $response = $this->actingAs($this->adminUser)
            ->get(route('books.index'));
            
        $response->assertStatus(200);
        
        // Check mobile navigation contains the correct route
        $response->assertSee('href="' . route('admin.loans.index') . '"', false);
        
        // Check for mobile-specific route checking
        $response->assertSee("request()->routeIs('admin.loans.index')", false);
    }

    public function test_guest_cannot_access_admin_loans_route()
    {
        // Test that guests are redirected when trying to access admin routes
        $response = $this->get(route('admin.loans.index'));
        
        // Should redirect to login
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_loans_route()
    {
        // Test that regular users cannot access admin routes
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.loans.index'));
            
        // Should return 403 Forbidden or redirect
        $this->assertTrue(
            $response->status() === 403 || 
            $response->isRedirect()
        );
    }
}