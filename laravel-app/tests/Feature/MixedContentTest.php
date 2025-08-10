<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Helpers\HttpsHelper;
use Illuminate\Support\Facades\Config;

/**
 * Mixed Content対策のテスト
 * 
 * vantanlib.com用のMixed Content対策機能をテストします。
 */
class MixedContentTest extends TestCase
{
    /**
     * HTTPSヘルパーのURL変換テスト
     */
    public function test_https_helper_converts_http_to_https(): void
    {
        $httpUrl = 'http://example.com/image.jpg';
        $httpsUrl = HttpsHelper::secureUrl($httpUrl);
        
        $this->assertEquals('https://example.com/image.jpg', $httpsUrl);
    }

    /**
     * 既にHTTPSのURLはそのまま返すテスト
     */
    public function test_https_helper_preserves_https_urls(): void
    {
        $httpsUrl = 'https://example.com/image.jpg';
        $result = HttpsHelper::secureUrl($httpsUrl);
        
        $this->assertEquals($httpsUrl, $result);
    }

    /**
     * プロトコル相対URLのHTTPS化テスト
     */
    public function test_https_helper_converts_protocol_relative_urls(): void
    {
        $protocolRelativeUrl = '//example.com/image.jpg';
        $result = HttpsHelper::secureUrl($protocolRelativeUrl);
        
        $this->assertEquals('https://example.com/image.jpg', $result);
    }

    /**
     * 相対URLはそのまま返すテスト
     */
    public function test_https_helper_preserves_relative_urls(): void
    {
        $relativeUrl = '/images/logo.png';
        $result = HttpsHelper::secureUrl($relativeUrl);
        
        $this->assertEquals($relativeUrl, $result);
    }

    /**
     * Google Books API URLのHTTPS確認テスト
     */
    public function test_google_books_api_uses_https(): void
    {
        $googleBooksUrl = 'https://www.googleapis.com/books/v1/volumes';
        $result = HttpsHelper::secureExternalUrl($googleBooksUrl);
        
        $this->assertEquals($googleBooksUrl, $result);
        $this->assertStringStartsWith('https://', $result);
    }

    /**
     * Mixed Content検出テスト
     */
    public function test_mixed_content_detection(): void
    {
        $htmlContent = '
            <img src="http://example.com/image.jpg" alt="Test">
            <link href="http://fonts.googleapis.com/css" rel="stylesheet">
            <script src="https://secure.example.com/script.js"></script>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"></svg>
        ';
        
        $mixedContentUrls = HttpsHelper::detectMixedContent($htmlContent);
        
        // HTTP URLが検出されることを確認（XML名前空間は除外）
        $this->assertGreaterThanOrEqual(2, count($mixedContentUrls));
        
        // 検出されたURLに期待するものが含まれていることを確認
        $detectedContent = implode(' ', $mixedContentUrls);
        $this->assertStringContainsString('http://example.com/image.jpg', $detectedContent);
        $this->assertStringContainsString('http://fonts.googleapis.com/css', $detectedContent);
    }

    /**
     * XML名前空間は除外されることをテスト
     */
    public function test_xml_namespaces_excluded_from_mixed_content_detection(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"></svg>';
        
        $mixedContentUrls = HttpsHelper::detectMixedContent($svgContent);
        
        // XML名前空間は検出されないことを確認
        $this->assertEmpty($mixedContentUrls);
    }

    /**
     * vantanlib.com設定チェックテスト
     */
    public function test_vantanlib_config_check(): void
    {
        // テスト用設定
        Config::set('app.url', 'https://vantanlib.com');
        Config::set('session.secure', true);
        Config::set('session.domain', '.vantanlib.com');
        Config::set('sanctum.stateful', ['vantanlib.com']);
        
        $checks = HttpsHelper::checkVantanlibConfig();
        
        // 全ての設定が正しいことを確認
        $this->assertTrue($checks['app_url_https']['status']);
        $this->assertTrue($checks['vantanlib_domain']['status']);
        $this->assertTrue($checks['secure_cookies']['status']);
        $this->assertTrue($checks['session_domain']['status']);
        $this->assertTrue($checks['sanctum_domains']['status']);
    }

    /**
     * HTTPS強制判定テスト
     */
    public function test_https_enforcement_detection(): void
    {
        // 本番環境 + HTTPS URLの場合
        $this->app['env'] = 'production';
        Config::set('app.env', 'production');
        Config::set('app.url', 'https://vantanlib.com');
        
        $this->assertTrue(HttpsHelper::isHttpsEnforced());
        
        // 開発環境の場合
        $this->app['env'] = 'local';
        Config::set('app.env', 'local');
        
        $this->assertFalse(HttpsHelper::isHttpsEnforced());
    }

    /**
     * セキュアクッキー設定チェックテスト
     */
    public function test_secure_cookie_check(): void
    {
        // セキュアクッキー有効
        Config::set('session.secure', true);
        $this->assertTrue(HttpsHelper::isSecureCookieEnabled());
        
        // セキュアクッキー無効
        Config::set('session.secure', false);
        $this->assertFalse(HttpsHelper::isSecureCookieEnabled());
    }

    /**
     * Google Books API設定チェックテスト
     */
    public function test_google_books_api_config_check(): void
    {
        // APIキー設定済みの場合
        Config::set('services.google_books.api_key', 'test-api-key');
        
        $checks = HttpsHelper::checkGoogleBooksApiConfig();
        
        $this->assertTrue($checks['api_key_configured']['status']);
        $this->assertTrue($checks['api_url_https']['status']);
    }

    /**
     * Mixed Content保護機能チェックテスト
     */
    public function test_mixed_content_protection_check(): void
    {
        $checks = HttpsHelper::checkMixedContentProtection();
        
        // HttpsHelperクラスが利用可能であることを確認
        $this->assertTrue($checks['https_helper_available']['status']);
        
        // ForceHttpsミドルウェアが実装されていることを確認
        $this->assertTrue($checks['force_https_middleware']['status']);
        
        // CSPヘッダーが設定されていることを確認
        $this->assertTrue($checks['csp_headers']['status']);
    }
}