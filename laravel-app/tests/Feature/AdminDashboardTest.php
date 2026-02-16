<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    /** @test */
    public function admin_can_view_dashboard()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function dashboard_shows_correct_statistics()
    {
        $books = Book::factory()->count(3)->create();

        // 1冊貸出中
        Loan::create([
            'user_id' => $this->admin->id,
            'book_id' => $books[0]->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        // 1冊延滞
        Loan::create([
            'user_id' => $this->admin->id,
            'book_id' => $books[1]->id,
            'borrowed_at' => now()->subDays(20),
            'due_date' => now()->subDays(6),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('totalBooks', 3);
        $response->assertViewHas('loanedBooks', 2);
        $response->assertViewHas('overdueBooks', 1);
    }

    /** @test */
    public function admin_can_view_books_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/books');

        $response->assertStatus(200);
        $response->assertViewIs('admin.books.index');
    }

    /** @test */
    public function admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }
}
