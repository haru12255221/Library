<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->regularUser()->create();
        $this->book = Book::factory()->create();
    }

    // ---- 管理者のみ貸出・返却できる ----

    /** @test */
    public function non_admin_cannot_borrow_book()
    {
        $response = $this->actingAs($this->user)
                         ->post('/loans/borrow', ['book_id' => $this->book->id, 'user_id' => $this->user->id]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('loans', ['book_id' => $this->book->id]);
    }

    /** @test */
    public function non_admin_cannot_return_book()
    {
        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->post("/loans/return/{$loan->id}");

        $response->assertForbidden();
        $loan->refresh();
        $this->assertNull($loan->returned_at);
    }

    /** @test */
    public function admin_can_borrow_book_for_a_user()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/loans/borrow', [
                             'book_id' => $this->book->id,
                             'user_id' => $this->user->id,
                         ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null,
        ]);
    }

    /** @test */
    public function admin_can_return_any_loan()
    {
        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->admin)
                         ->post("/loans/return/{$loan->id}");

        $response->assertRedirect(route('admin.loans.index'));
        $response->assertSessionHas('success');
        $loan->refresh();
        $this->assertNotNull($loan->returned_at);
        $this->assertTrue($loan->returned_at->isToday());
    }

    /** @test */
    public function admin_cannot_borrow_already_borrowed_book()
    {
        $anotherUser = User::factory()->regularUser()->create();
        Loan::create([
            'user_id' => $anotherUser->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->admin)
                         ->post('/loans/borrow', [
                             'book_id' => $this->book->id,
                             'user_id' => $this->user->id,
                         ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('error');
        $this->assertEquals(1, Loan::where('book_id', $this->book->id)->count());
    }

    /** @test */
    public function borrow_requires_user_id()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/loans/borrow', ['book_id' => $this->book->id]);

        $response->assertSessionHasErrors('user_id');
    }

    // ---- マイページは一般ユーザーも閲覧できる ----

    /** @test */
    public function user_can_view_their_loans()
    {
        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans');

        $response->assertStatus(200);
        $response->assertViewIs('loans.my-loans');
        $response->assertSee($this->book->title);
    }

    /** @test */
    public function user_can_search_their_borrowed_books_by_title()
    {
        $book1 = Book::factory()->create(['title' => 'Laravel入門']);
        $book2 = Book::factory()->create(['title' => 'PHP基礎']);
        $book3 = Book::factory()->create(['title' => 'JavaScript実践']);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book1->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book2->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book3->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');
        $response->assertDontSee('JavaScript実践');
    }

    /** @test */
    public function user_can_search_their_borrowed_books_by_author()
    {
        $book1 = Book::factory()->create(['author' => '田中太郎']);
        $book2 = Book::factory()->create(['author' => '佐藤花子']);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book1->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book2->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans?search=田中');

        $response->assertStatus(200);
        $response->assertSee('田中太郎');
        $response->assertDontSee('佐藤花子');
    }

    /** @test */
    public function user_can_search_their_borrowed_books_by_isbn()
    {
        $book1 = Book::factory()->create(['isbn' => '9784123456789']);
        $book2 = Book::factory()->create(['isbn' => '9784987654321']);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book1->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book2->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans?search=9784123456789');

        $response->assertStatus(200);
        $response->assertSee('9784123456789');
        $response->assertDontSee('9784987654321');
    }

    /** @test */
    public function user_search_only_shows_their_own_borrowed_books()
    {
        $otherUser = User::factory()->regularUser()->create();
        $otherBook = Book::factory()->create(['title' => 'Laravel入門']);

        Loan::create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $userBook = Book::factory()->create(['title' => 'PHP基礎']);
        Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $userBook->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'status' => Loan::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans?search=Laravel');

        $response->assertStatus(200);
        $response->assertDontSee('Laravel入門');
        $response->assertSee('検索結果: 0件');
    }
}
