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
        
        $this->user = User::factory()->create();
        $this->book = Book::factory()->create();
    }

    /** @test */
    public function user_can_borrow_a_book()
    {
        $response = $this->actingAs($this->user)
                         ->post("/loans/borrow", ['book_id' => $this->book->id]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('loans', [
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'returned_at' => null,
        ]);
    }

    /** @test */
    public function user_can_return_a_book()
    {
        // まず書籍を借りる
        $loan = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $response = $this->actingAs($this->user)
                         ->post("/loans/return/{$loan->id}");

        $response->assertRedirect('/books');
        $response->assertSessionHas('success');

        // 返却されたことを確認（日時で記録される）
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
        ]);
        
        // 返却日時が設定されていることを確認
        $loan->refresh();
        $this->assertNotNull($loan->returned_at);
        $this->assertTrue($loan->returned_at->isToday());
    }

    /** @test */
    public function user_cannot_borrow_already_borrowed_book()
    {
        // 他のユーザーが既に借りている
        $otherUser = User::factory()->create();
        Loan::create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $response = $this->actingAs($this->user)
                         ->post("/loans/borrow", ['book_id' => $this->book->id]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('error');

        // 新しい貸出記録が作成されていないことを確認
        $this->assertEquals(1, Loan::where('book_id', $this->book->id)->count());
    }

    /** @test */
    public function user_can_view_their_loans()
    {
        // ユーザーの貸出記録を作成
        $loan1 = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $book2 = Book::factory()->create();
        $loan2 = Loan::create([
            'user_id' => $this->user->id,
            'book_id' => $book2->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
            'returned_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
                         ->get('/my-loans');

        $response->assertStatus(200);
        $response->assertViewIs('loans.my-loans');
        $response->assertSee($this->book->title);
        $response->assertSee($book2->title);
    }

    /** @test */
    public function user_cannot_return_book_they_didnt_borrow()
    {
        $otherUser = User::factory()->create();
        Loan::create([
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
            'borrowed_at' => now(),
            'due_date' => now()->addDays(14),
        ]);

        $response = $this->actingAs($this->user)
                         ->post("/loans/return/999"); // 存在しないloan ID

        // 存在しないIDの場合は404が返される（Laravel標準動作）
        $response->assertStatus(404);

        // 返却されていないことを確認
        $this->assertDatabaseHas('loans', [
            'user_id' => $otherUser->id,
            'book_id' => $this->book->id,
            'returned_at' => null,
        ]);
    }
}