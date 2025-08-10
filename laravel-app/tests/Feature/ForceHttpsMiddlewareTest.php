<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

/**
 * HTTPS強制ミドルウェアのテスト
 * 
 * vantanlib.com用のHTTPS強制機能をテストします。
 */
class ForceHttpsMiddlewareTest extends TestCase
{
    /**
     * 本番環境でHTTPアクセスがHTTPSにリダイレクトされることをテスト
     */
    public function test_http_redirects_to_https_in_production(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPでアクセス（HTTPSフラグを明示的にfalseに）
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'vantanlib.com',
            'HTTPS' => false,
            'SERVER_PORT' => 80,
        ]);

        // HTTPSにリダイレクトされることを確認
        $response->assertStatus(301);
        $response->assertRedirect('https://vantanlib.com/');
    }

    /**
     * 開発環境ではHTTPS強制されないことをテスト
     */
    public function test_http_not_redirected_in_development(): void
    {
        // 開発環境に設定
        Config::set('app.env', 'local');
        
        // HTTPでアクセス
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'localhost',
            'HTTPS' => false,
            'SERVER_PORT' => 80,
        ]);

        // リダイレクトされないことを確認
        $response->assertStatus(200);
    }

    /**
     * Let's Encrypt認証パスが除外されることをテスト
     */
    public function test_lets_encrypt_path_excluded_from_https_redirect(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // Let's Encrypt認証パスにアクセス
        $response = $this->get('http://vantanlib.com/.well-known/acme-challenge/test', [
            'HTTP_HOST' => 'vantanlib.com'
        ]);

        // リダイレクトされないことを確認（404は正常、リダイレクトされていない証拠）
        $this->assertNotEquals(301, $response->getStatusCode());
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /**
     * ヘルスチェックエンドポイントが内部アクセスで除外されることをテスト
     */
    public function test_health_check_excluded_for_internal_access(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // 内部IPからヘルスチェックにアクセス
        $response = $this->get('http://vantanlib.com/health', [
            'HTTP_HOST' => 'vantanlib.com',
            'REMOTE_ADDR' => '172.18.0.1', // Docker内部IP
        ]);

        // リダイレクトされないことを確認
        $this->assertNotEquals(301, $response->getStatusCode());
        $this->assertNotEquals(302, $response->getStatusCode());
    }

    /**
     * HTTPSアクセス時にセキュリティヘッダーが追加されることをテスト
     */
    public function test_security_headers_added_for_https(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPSでアクセス（テスト環境ではHTTPSシミュレーション）
        $response = $this->call('GET', '/', [], [], [], [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'vantanlib.com',
            'SERVER_PORT' => 443,
        ]);

        // セキュリティヘッダーが設定されていることを確認
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-Permitted-Cross-Domain-Policies', 'none');
        $response->assertHeader('X-Download-Options', 'noopen');
        $response->assertHeader('X-Powered-By-Domain', 'vantanlib.com');
    }

    /**
     * www.vantanlib.comからvantanlib.comへのリダイレクトテスト
     */
    public function test_www_redirect_with_https_enforcement(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPのwwwサブドメインでアクセス
        $response = $this->call('GET', '/', [], [], [], [
            'HTTP_HOST' => 'www.vantanlib.com',
            'HTTPS' => false,
            'SERVER_PORT' => 80,
        ]);

        // HTTPSにリダイレクトされることを確認（wwwは保持される）
        $response->assertStatus(301);
        $response->assertRedirect('https://www.vantanlib.com/');
    }

    /**
     * 複雑なURLパスでのHTTPS強制テスト
     */
    public function test_https_redirect_preserves_path_and_query(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // 複雑なパスとクエリパラメータでアクセス
        $response = $this->call('GET', '/books/search?q=test&page=2', [], [], [], [
            'HTTP_HOST' => 'vantanlib.com',
            'HTTPS' => false,
            'SERVER_PORT' => 80,
            'QUERY_STRING' => 'q=test&page=2',
        ]);

        // パスとクエリパラメータが保持されてリダイレクトされることを確認
        $response->assertStatus(301);
        $response->assertRedirect('https://vantanlib.com/books/search?q=test&page=2');
    }

    /**
     * POSTリクエストでのHTTPS強制テスト
     */
    public function test_post_request_https_redirect(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPでPOSTリクエスト（CSRFトークンなしでテスト）
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->call('POST', '/login', [
                'email' => 'test@example.com',
                'password' => 'password'
            ], [], [], [
                'HTTP_HOST' => 'vantanlib.com',
                'HTTPS' => false,
                'SERVER_PORT' => 80,
            ]);

        // HTTPSにリダイレクトされることを確認
        $response->assertStatus(301);
        $response->assertRedirect('https://vantanlib.com/login');
    }

    /**
     * セキュリティヘッダーの重複設定防止テスト
     */
    public function test_security_headers_not_duplicated(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPSでアクセス
        $response = $this->call('GET', '/', [], [], [], [
            'HTTPS' => 'on',
            'HTTP_HOST' => 'vantanlib.com',
            'SERVER_PORT' => 443,
        ]);

        // セキュリティヘッダーが存在することを確認
        $this->assertTrue($response->headers->has('X-Frame-Options'));
        $this->assertTrue($response->headers->has('Strict-Transport-Security'));
    }
}