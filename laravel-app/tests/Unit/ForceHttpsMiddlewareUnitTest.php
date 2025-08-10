<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\ForceHttps;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

/**
 * HTTPS強制ミドルウェアのユニットテスト
 */
class ForceHttpsMiddlewareUnitTest extends TestCase
{
    private ForceHttps $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new ForceHttps();
    }

    /**
     * 本番環境でHTTP接続がリダイレクトされることをテスト
     */
    public function test_redirects_http_to_https_in_production(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPリクエストを作成
        $request = Request::create('http://vantanlib.com/test', 'GET');
        $request->server->set('HTTPS', 'off');
        $request->server->set('SERVER_PORT', 80);
        $request->server->set('REQUEST_SCHEME', 'http');
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        });
        
        // リダイレクトレスポンスであることを確認
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('https://vantanlib.com/test', $response->headers->get('Location'));
    }

    /**
     * 開発環境ではリダイレクトされないことをテスト
     */
    public function test_does_not_redirect_in_development(): void
    {
        // 開発環境に設定
        Config::set('app.env', 'local');
        
        // HTTPリクエストを作成
        $request = Request::create('http://localhost/test', 'GET');
        $request->server->set('HTTPS', false);
        $request->server->set('SERVER_PORT', 80);
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Success');
        });
        
        // 正常なレスポンスであることを確認
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /**
     * Let's Encrypt認証パスが除外されることをテスト
     */
    public function test_excludes_lets_encrypt_path(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // Let's Encrypt認証パスのリクエストを作成
        $request = Request::create('http://vantanlib.com/.well-known/acme-challenge/test', 'GET');
        $request->server->set('HTTPS', false);
        $request->server->set('SERVER_PORT', 80);
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Let\'s Encrypt OK');
        });
        
        // リダイレクトされないことを確認
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Let\'s Encrypt OK', $response->getContent());
    }

    /**
     * HTTPSアクセス時にセキュリティヘッダーが追加されることをテスト
     */
    public function test_adds_security_headers_for_https(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // HTTPSリクエストを作成
        $request = Request::create('https://vantanlib.com/test', 'GET');
        $request->server->set('HTTPS', 'on');
        $request->server->set('SERVER_PORT', 443);
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('HTTPS Success');
        });
        
        // セキュリティヘッダーが追加されていることを確認
        $this->assertEquals('max-age=31536000; includeSubDomains; preload', 
            $response->headers->get('Strict-Transport-Security'));
        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
        $this->assertEquals('vantanlib.com', $response->headers->get('X-Powered-By-Domain'));
    }

    /**
     * 内部ヘルスチェックが除外されることをテスト
     */
    public function test_excludes_internal_health_check(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // 内部IPからのヘルスチェックリクエストを作成
        $request = Request::create('http://vantanlib.com/health', 'GET');
        $request->server->set('HTTPS', false);
        $request->server->set('SERVER_PORT', 80);
        $request->server->set('REMOTE_ADDR', '172.18.0.1'); // Docker内部IP
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Health OK');
        });
        
        // リダイレクトされないことを確認
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Health OK', $response->getContent());
    }

    /**
     * 複雑なURLパスが保持されることをテスト
     */
    public function test_preserves_complex_url_path(): void
    {
        // 本番環境に設定
        Config::set('app.env', 'production');
        
        // 複雑なパスのHTTPリクエストを作成
        $request = Request::create('http://vantanlib.com/books/search?q=test&page=2', 'GET');
        $request->server->set('HTTPS', 'off');
        $request->server->set('SERVER_PORT', 80);
        $request->server->set('REQUEST_SCHEME', 'http');
        
        // ミドルウェアを実行
        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Should not reach here');
        });
        
        // パスとクエリが保持されてリダイレクトされることを確認
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('https://vantanlib.com/books/search?q=test&page=2', 
            $response->headers->get('Location'));
    }
}