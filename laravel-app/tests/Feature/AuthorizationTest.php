<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => User::ROLE_USER]);
    }

    /** @test */
    public function regular_user_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_admin_books()
    {
        $response = $this->actingAs($this->user)->get('/admin/books');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_admin_users()
    {
        $response = $this->actingAs($this->user)->get('/admin/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_change_user_role()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->patch("/admin/users/{$otherUser->id}/role");

        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_create_books()
    {
        $response = $this->actingAs($this->user)->get('/books/create');
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->post('/books', [
            'title' => 'テスト',
            'author' => 'テスト',
            'isbn' => '9784000000000',
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_edit_books()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)->get("/books/{$book->id}/edit");
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->put("/books/{$book->id}", [
            'title' => '変更',
            'author' => '変更',
            'isbn' => $book->isbn,
        ]);
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_delete_books()
    {
        $book = Book::factory()->create();

        $response = $this->actingAs($this->user)->delete("/books/{$book->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_access_loan_management()
    {
        $response = $this->actingAs($this->user)->get('/loans');
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)->get('/loans/overdue');
        $response->assertStatus(403);
    }

    /** @test */
    public function regular_user_cannot_force_return()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $book = Book::factory()->create();
        $loan = Loan::create([
            'user_id' => $admin->id,
            'book_id' => $book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
            ->post("/loans/{$loan->id}/force-return");

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        // authミドルウェアが先に動き、ログインページにリダイレクトされる
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
}
