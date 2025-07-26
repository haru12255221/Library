<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ISBNスキャンページが正常に表示されることをテスト
     */
    public function test_isbn_scan_page_loads_successfully(): void
    {
        $response = $this->get('/isbn-scan');

        $response->assertStatus(200);
        $response->assertSee('書籍登録');
        $response->assertSee('ISBNスキャン');
    }

    /**
     * ISBN検索APIが正常に動作することをテスト
     */
    public function test_isbn_fetch_api_returns_book_data(): void
    {
        // Google Books APIのレスポンスをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者'],
                            'publisher' => 'テスト出版社',
                            'publishedDate' => '2024-01-01',
                            'description' => 'テスト用の書籍です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/thumbnail.jpg'
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->postJson('/isbn-fetch', [
            'isbn' => '9784123456789'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'テスト書籍',
                'author' => 'テスト著者',
                'publisher' => 'テスト出版社',
                'published_date' => '2024-01-01',
                'description' => 'テスト用の書籍です。',
                'thumbnail_url' => 'https://example.com/thumbnail.jpg'
            ]
        ]);
    }

    /**
     * 存在しないISBNの場合に404エラーが返されることをテスト
     */
    public function test_isbn_fetch_api_returns_404_for_nonexistent_isbn(): void
    {
        // 空のレスポンスをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => []
            ], 200)
        ]);

        $response = $this->postJson('/isbn-fetch', [
            'isbn' => '9999999999999'
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => '書籍が見つかりませんでした。手動で入力してください。'
        ]);
    }

    /**
     * 書籍登録が正常に動作することをテスト（認証済みユーザー）
     */
    public function test_book_registration_works_for_authenticated_user(): void
    {
        // 管理者ユーザーを作成
        $user = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784123456789',
            'publisher' => 'テスト出版社',
            'published_date' => '2024-01-01',
            'description' => 'テスト用の書籍です。',
            'thumbnail_url' => 'https://example.com/thumbnail.jpg'
        ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success', '書籍を登録しました');

        // データベースに保存されていることを確認
        $this->assertDatabaseHas('books', [
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784123456789',
            'publisher' => 'テスト出版社',
            'description' => 'テスト用の書籍です。'
        ]);
    }

    /**
     * 重複ISBNでの登録が失敗することをテスト
     */
    public function test_duplicate_isbn_registration_fails(): void
    {
        // 既存の書籍を作成
        Book::factory()->create(['isbn' => '9784123456789']);

        // 管理者ユーザーを作成
        $user = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($user)->post('/books', [
            'title' => '別の書籍',
            'author' => '別の著者',
            'isbn' => '9784123456789', // 重複ISBN
            'publisher' => '別の出版社'
        ]);

        $response->assertSessionHasErrors(['isbn']);
    }

    /**
     * 必須フィールドが空の場合にバリデーションエラーが発生することをテスト
     */
    public function test_required_fields_validation(): void
    {
        // 管理者ユーザーを作成
        $user = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($user)->post('/books', [
            // 必須フィールドを空にする
            'title' => '',
            'author' => '',
            'isbn' => ''
        ]);

        $response->assertSessionHasErrors(['title', 'author', 'isbn']);
    }

    /**
     * 書籍一覧ページが正常に表示されることをテスト
     */
    public function test_books_index_page_displays_books(): void
    {
        // テスト用の書籍を作成
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'publisher' => 'テスト出版社'
        ]);

        $response = $this->get('/books');

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('テスト出版社');
    }

    /**
     * 書籍詳細ページが正常に表示されることをテスト
     */
    public function test_book_show_page_displays_book_details(): void
    {
        // テスト用の書籍を作成
        $book = Book::factory()->create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'publisher' => 'テスト出版社',
            'description' => 'これはテスト用の書籍です。'
        ]);

        $response = $this->get("/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト書籍');
        $response->assertSee('テスト著者');
        $response->assertSee('テスト出版社');
        $response->assertSee('これはテスト用の書籍です。');
    }

    /**
     * 書籍検索機能が正常に動作することをテスト
     */
    public function test_book_search_functionality(): void
    {
        // テスト用の書籍を作成
        Book::factory()->create([
            'title' => 'Laravel入門',
            'author' => '田中太郎'
        ]);
        Book::factory()->create([
            'title' => 'PHP基礎',
            'author' => '佐藤花子'
        ]);

        // タイトルで検索
        $response = $this->get('/books?search=Laravel');
        $response->assertStatus(200);
        $response->assertSee('Laravel入門');
        $response->assertDontSee('PHP基礎');

        // 著者で検索
        $response = $this->get('/books?search=佐藤');
        $response->assertStatus(200);
        $response->assertSee('PHP基礎');
        $response->assertDontSee('Laravel入門');
    }
}
