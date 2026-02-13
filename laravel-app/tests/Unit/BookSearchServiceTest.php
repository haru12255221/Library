<?php

namespace Tests\Unit;

use App\Services\BookSearchService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookSearchServiceTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    private BookSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookSearchService();
    }

    /**
     * openBDから書籍情報を正常に取得できることをテスト
     */
    public function test_fetch_by_isbn_returns_book_data_from_openbd(): void
    {
        Http::fake([
            'api.openbd.jp/*' => Http::response([
                [
                    'summary' => [
                        'title' => 'Laravel入門',
                        'author' => '田中太郎',
                        'publisher' => 'テスト出版社',
                        'cover' => 'https://example.com/thumbnail.jpg',
                        'pubdate' => '2024-01',
                    ],
                    'hanmoto' => [
                        'dateshuppan' => '2024-01-15',
                    ],
                    'onix' => [
                        'CollateralDetail' => [
                            'TextContent' => [
                                ['Text' => 'Laravelの基本を学ぶ本です。']
                            ]
                        ]
                    ],
                ]
            ], 200),
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNotNull($result);
        $this->assertEquals('Laravel入門', $result['title']);
        $this->assertEquals('田中太郎', $result['authors']);
        $this->assertEquals('テスト出版社', $result['publisher']);
        $this->assertEquals('2024-01-15', $result['published_date']);
        $this->assertEquals('Laravelの基本を学ぶ本です。', $result['description']);
        $this->assertEquals('https://example.com/thumbnail.jpg', $result['thumbnail_url']);
    }

    /**
     * openBDで見つからない場合にNDLにフォールバックすることをテスト
     */
    public function test_fetch_by_isbn_falls_back_to_ndl(): void
    {
        Http::fake([
            'api.openbd.jp/*' => Http::response([null], 200),
            'ndlsearch.ndl.go.jp/*' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>
                <rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
                    <channel>
                        <item>
                            <title>NDLの本</title>
                            <dc:creator>山田花子</dc:creator>
                            <dc:publisher>NDL出版</dc:publisher>
                            <dc:date>2023-06-01</dc:date>
                        </item>
                    </channel>
                </rss>',
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNotNull($result);
        $this->assertEquals('NDLの本', $result['title']);
        $this->assertEquals('山田花子', $result['authors']);
        $this->assertEquals('NDL出版', $result['publisher']);
        $this->assertEquals('2023-06-01', $result['published_date']);
    }

    /**
     * 両方のAPIで見つからない場合にnullを返すことをテスト
     */
    public function test_fetch_by_isbn_returns_null_when_not_found(): void
    {
        Http::fake([
            'api.openbd.jp/*' => Http::response([null], 200),
            'ndlsearch.ndl.go.jp/*' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>
                <rss version="2.0"><channel></channel></rss>',
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $result = $this->service->fetchByIsbn('9999999999999');

        $this->assertNull($result);
    }

    /**
     * ネットワークエラー時にnullを返すことをテスト
     */
    public function test_fetch_by_isbn_returns_null_on_network_error(): void
    {
        Http::fake([
            'api.openbd.jp/*' => function () {
                throw new \Exception('Network error');
            },
            'ndlsearch.ndl.go.jp/*' => function () {
                throw new \Exception('Network error');
            },
        ]);

        $result = $this->service->fetchByIsbn('9784123456789');

        $this->assertNull($result);
    }

    /**
     * ISBN文字列のクリーンアップが正しく動作することをテスト
     */
    public function test_isbn_cleaning(): void
    {
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
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('parsePublishedDate');
        $method->setAccessible(true);

        $this->assertEquals('2024-07-26', $method->invoke($this->service, '2024-07-26'));
        $this->assertEquals('2024-07-01', $method->invoke($this->service, '2024-07'));
        $this->assertEquals('2024-01-01', $method->invoke($this->service, '2024'));
        $this->assertNull($method->invoke($this->service, null));
        $this->assertNull($method->invoke($this->service, 'invalid-date'));
    }
}
