<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // テスト用ユーザーを作成
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_view_books_index()
    {
        // 認証済みユーザーとしてアクセス
        $response = $this->actingAs($this->user)
                         ->get('/books');

        $response->assertStatus(200);
        $response->assertViewIs('books.index');
    }

    /** @test */
    public function user_can_create_a_book()
    {
        $bookData = [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name,
            'isbn' => $this->faker->isbn13,
        ];

        $response = $this->actingAs($this->user)
                         ->post('/books', $bookData);

        $response->assertRedirect('/books');
        $this->assertDatabaseHas('books', $bookData);
    }

    /** @test */
    public function user_can_view_book_creation_form()
    {
        $response = $this->actingAs($this->user)
                         ->get('/books/create');

        $response->assertStatus(200);
        $response->assertViewIs('books.create');
    }

    /** @test */
    public function user_can_search_books()
    {
        // テスト用の書籍を作成
        $book1 = Book::factory()->create(['title' => 'Laravel入門']);
        $book2 = Book::factory()->create(['title' => 'PHP基礎']);
        $book3 = Book::factory()->create(['author' => '山田太郎']);

        // タイトルで検索
        $response = $this->actingAs($this->user)
                         ->get('/books?search=Laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');

        // 著者で検索
        $response = $this->actingAs($this->user)
                         ->get('/books?search=山田太郎');

        $response->assertStatus(200);
        $response->assertSee($book3->title);
    }

    /** @test */
    public function book_creation_requires_valid_data()
    {
        // 必須フィールドが空の場合
        $response = $this->actingAs($this->user)
                         ->post('/books', []);

        $response->assertSessionHasErrors(['title', 'author', 'isbn']);

        // 重複するISBNの場合
        $existingBook = Book::factory()->create(['isbn' => '9784000000000']);
        
        $response = $this->actingAs($this->user)
                         ->post('/books', [
                             'title' => 'テスト書籍',
                             'author' => 'テスト著者',
                             'isbn' => '9784000000000', // 既存のISBN
                         ]);

        $response->assertSessionHasErrors(['isbn']);
    }

    /** @test */
    public function guest_can_view_books_but_cannot_manage()
    {
        // ゲストは書籍一覧を見ることができる
        $response = $this->get('/books');
        $response->assertStatus(200);

        // ゲストは書籍作成フォームにアクセスできない
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');

        // ゲストは書籍を作成できない
        $response = $this->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784000000000',
        ]);
        $response->assertRedirect('/login');
    }
}