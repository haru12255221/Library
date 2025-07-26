<?php

namespace Tests\Unit;

use App\Services\GoogleBooksService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleBooksServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;
    
    private GoogleBooksService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GoogleBooksService();
    }

    /**
     * ISBN検索が正常に動作することをテスト
     */
    public function test_fetch_by_isbn_returns_book_data(): void
    {
        // Google Books APIのレスポンスをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'Laravel入門',
                            'authors' => ['田中太郎'],
                            'publisher' => 'テスト出版社',
                            'publishedDate' => '2024-01-01',
                            'description' => 'Laravelの基本を学ぶ本です。',
                            'pageCount' => 300,
                            'language' => 'ja',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/thumbnail.jpg'
                            ],
                            'industryIdentifiers' => [
                                ['identifier' => '9784123456789']
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNotNull($result);
        $this->assertEquals('Laravel入門', $result['title']);
        $this->assertEquals('田中太郎', $result['authors']);
        $this->assertEquals('テスト出版社', $result['publisher']);
        $this->assertEquals('2024-01-01', $result['published_date']);
        $this->assertEquals('Laravelの基本を学ぶ本です。', $result['description']);
        $this->assertEquals('https://example.com/thumbnail.jpg', $result['thumbnail_url']);
        $this->assertEquals(300, $result['page_count']);
        $this->assertEquals('ja', $result['language']);
    }

    /**
     * 存在しないISBNの場合にnullを返すことをテスト
     */
    public function test_fetch_by_isbn_returns_null_when_not_found(): void
    {
        // 空のレスポンスをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => []
            ], 200)
        ]);

        $result = $this->service->fetchByIsbn('9999999999999');

        $this->assertNull($result);
    }

    /**
     * APIエラー時にnullを返すことをテスト
     */
    public function test_fetch_by_isbn_returns_null_on_api_error(): void
    {
        // APIエラーをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([], 500)
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNull($result);
    }

    /**
     * ネットワークエラー時にnullを返すことをテスト
     */
    public function test_fetch_by_isbn_returns_null_on_network_error(): void
    {
        // ネットワークエラーをモック
        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => function () {
                throw new \Exception('Network error');
            }
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNull($result);
    }

    /**
     * ISBN文字列のクリーンアップが正しく動作することをテスト
     */
    public function test_isbn_cleaning(): void
    {
        // プライベートメソッドをテストするためリフレクションを使用
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('cleanIsbn');
        $method->setAccessible(true);

        $this->assertEquals('9784123456789', $method->invoke($this->service, '978-4-12-345678-9'));
        $this->assertEquals('9784123456789', $method->invoke($this->service, '978 4 12 345678 9'));
        $this->assertEquals('123456789X', $method->invoke($this->service, '1-23-456789-X'));
    }

    /**
     * 出版日のパースが正しく動作することをテスト
     */
    public function test_published_date_parsing(): void
    {
        // プライベートメソッドをテストするためリフレクションを使用
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parsePublishedDate');
        $method->setAccessible(true);

        $this->assertEquals('2024-07-26', $method->invoke($this->service, '2024-07-26'));
        $this->assertEquals('2024-07-01', $method->invoke($this->service, '2024-07'));
        $this->assertEquals('2024-01-01', $method->invoke($this->service, '2024'));
        $this->assertNull($method->invoke($this->service, null));
        $this->assertNull($method->invoke($this->service, 'invalid-date'));
    }

    /**
     * サムネイルURL取得が正しく動作することをテスト
     */
    public function test_thumbnail_url_extraction(): void
    {
        // プライベートメソッドをテストするためリフレクションを使用
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('getThumbnailUrl');
        $method->setAccessible(true);

        // thumbnailが優先される
        $imageLinks = [
            'thumbnail' => 'http://example.com/thumb.jpg',
            'smallThumbnail' => 'http://example.com/small.jpg'
        ];
        $this->assertEquals('https://example.com/thumb.jpg', $method->invoke($this->service, $imageLinks));

        // thumbnailがない場合はsmallThumbnailが使用される
        $imageLinks = [
            'smallThumbnail' => 'http://example.com/small.jpg',
            'medium' => 'http://example.com/medium.jpg'
        ];
        $this->assertEquals('https://example.com/small.jpg', $method->invoke($this->service, $imageLinks));

        // 何もない場合はnull
        $this->assertNull($method->invoke($this->service, []));
    }
}
