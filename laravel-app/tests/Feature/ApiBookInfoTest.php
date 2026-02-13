<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiBookInfoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function api_returns_book_info_from_openbd()
    {
        // openBD APIのモックレスポンス
        Http::fake([
            'api.openbd.jp/*' => Http::response([
                [
                    'summary' => [
                        'title' => 'Laravel実践入門',
                        'author' => '山田太郎, 佐藤花子',
                        'publisher' => '技術評論社',
                        'cover' => 'https://example.com/cover.jpg',
                        'pubdate' => '2024-01',
                    ],
                    'hanmoto' => [
                        'dateshuppan' => '2024-01-15',
                    ],
                    'onix' => [
                        'CollateralDetail' => [
                            'TextContent' => [
                                ['Text' => 'Laravelの実践的な入門書']
                            ]
                        ]
                    ],
                ]
            ], 200),
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
    public function api_falls_back_to_ndl_when_openbd_has_no_result()
    {
        // openBDが見つからない + NDLで見つかる
        Http::fake([
            'api.openbd.jp/*' => Http::response([null], 200),
            'ndlsearch.ndl.go.jp/*' => Http::response(
                '<?xml version="1.0" encoding="UTF-8"?>
                <rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
                    <channel>
                        <item>
                            <title>NDLで見つかった本</title>
                            <dc:creator>国会太郎</dc:creator>
                            <dc:publisher>国会出版</dc:publisher>
                            <dc:date>2023-05-01</dc:date>
                        </item>
                    </channel>
                </rss>',
                200,
                ['Content-Type' => 'application/xml']
            ),
        ]);

        $isbn = '9784000000000';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'title' => 'NDLで見つかった本',
                'author' => '国会太郎',
                'isbn' => $isbn,
            ]
        ]);
    }

    /** @test */
    public function api_returns_error_when_both_apis_find_nothing()
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

        $isbn = '9999999999999';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'error' => 'この ISBN の書籍情報が見つかりませんでした。'
        ]);
    }

    /** @test */
    public function api_handles_network_timeout()
    {
        // BookSearchServiceは内部で例外をキャッチしnullを返すため、APIは404を返す
        Http::fake([
            'api.openbd.jp/*' => function () {
                throw new \Exception('Connection timeout');
            },
            'ndlsearch.ndl.go.jp/*' => function () {
                throw new \Exception('Connection timeout');
            },
        ]);

        $isbn = '9784000000000';
        $response = $this->get("/api/book/info/{$isbn}");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
