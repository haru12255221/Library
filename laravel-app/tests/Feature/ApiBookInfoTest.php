<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiBookInfoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_book_info_for_valid_isbn()
    {
        // Google Books APIのモックレスポンス
        Http::fake([
            'googleapis.com/*' => Http::response([
                'totalItems' => 1,
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Laravel実践入門',
                            'authors' => ['山田太郎', '佐藤花子'],
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/cover.jpg'
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $isbn = '9784000000000';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'Laravel実践入門',
                'author' => '山田太郎, 佐藤花子',
                'isbn' => $isbn,
                'cover' => 'https://example.com/cover.jpg',
            ]
        ]);
    }

    /** @test */
    public function api_returns_error_for_invalid_isbn()
    {
        // Google Books APIが書籍を見つけられない場合のモック
        Http::fake([
            'googleapis.com/*' => Http::response([
                'totalItems' => 0,
                'items' => []
            ], 200)
        ]);

        $isbn = '9999999999999';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => 'この ISBN の書籍情報が見つかりませんでした。'
        ]);
    }

    /** @test */
    public function api_handles_google_books_api_failure()
    {
        // Google Books APIがエラーを返す場合のモック
        Http::fake([
            'googleapis.com/*' => Http::response([], 500)
        ]);

        $isbn = '9784000000000';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonStructure([
            'success',
            'error'
        ]);
    }

    /** @test */
    public function api_handles_network_timeout()
    {
        // ネットワークタイムアウトのシミュレーション
        Http::fake([
            'googleapis.com/*' => function () {
                throw new \Exception('Connection timeout');
            }
        ]);

        $isbn = '9784000000000';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
        ]);
    }
}