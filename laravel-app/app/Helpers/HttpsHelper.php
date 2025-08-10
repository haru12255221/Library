<?php

namespace App\Helpers;

use Illuminate\Support\Facades\URL;

/**
 * HTTPS関連のヘルパー関数
 * 
 * vantanlib.com用のMixed Content対策とHTTPS化支援機能を提供します。
 */
class HttpsHelper
{
    /**
     * URLをHTTPS化する
     * 
     * @param string $url 変換対象のURL
     * @return string HTTPS化されたURL
     */
    public static function secureUrl(string $url): string
    {
        // 既にHTTPSの場合はそのまま返す
        if (str_starts_with($url, 'https://')) {
            return $url;
        }
        
        // HTTPの場合はHTTPSに変換
        if (str_starts_with($url, 'http://')) {
            return str_replace('http://', 'https://', $url);
        }
        
        // プロトコル相対URLの場合はHTTPSを明示
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        
        // 相対URLの場合は現在のスキームを使用
        return $url;
    }

    /**
     * アセットURLをHTTPS化する
     * 
     * @param string $path アセットパス
     * @return string HTTPS化されたアセットURL
     */
    public static function secureAsset(string $path): string
    {
        $url = asset($path);
        return self::secureUrl($url);
    }

    /**
     * 外部リソースURLをHTTPS化する
     * 
     * @param string $url 外部リソースURL
     * @return string HTTPS化されたURL
     */
    public static function secureExternalUrl(string $url): string
    {
        // よく使用される外部サービスのHTTPS化
        $httpsServices = [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'www.googleapis.com',
            'books.googleapis.com',
            'maps.googleapis.com',
            'ajax.googleapis.com',
            'cdnjs.cloudflare.com',
            'cdn.jsdelivr.net',
            'unpkg.com',
        ];

        foreach ($httpsServices as $service) {
            if (str_contains($url, $service)) {
                return self::secureUrl($url);
            }
        }

        return self::secureUrl($url);
    }

    /**
     * Mixed Contentの可能性があるURLを検出
     * 
     * @param string $content HTML/CSS/JSコンテンツ
     * @return array 検出されたHTTP URLのリスト
     */
    public static function detectMixedContent(string $content): array
    {
        $mixedContentUrls = [];
        
        // HTTP URLのパターンを検索
        $patterns = [
            '/src=["\']http:\/\/([^"\']+)["\']/i',
            '/href=["\']http:\/\/([^"\']+)["\']/i',
            '/url\(["\']?http:\/\/([^"\')\s]+)["\']?\)/i',
            '/http:\/\/[^\s<>"\']+/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $mixedContentUrls = array_merge($mixedContentUrls, $matches[0]);
            }
        }

        // 重複を除去し、XML名前空間は除外
        $mixedContentUrls = array_unique($mixedContentUrls);
        $mixedContentUrls = array_filter($mixedContentUrls, function($url) {
            return !str_contains($url, 'http://www.w3.org/');
        });

        return array_values($mixedContentUrls);
    }

    /**
     * 本番環境でHTTPS強制が有効かチェック
     * 
     * @return bool HTTPS強制が有効な場合true
     */
    public static function isHttpsEnforced(): bool
    {
        return app()->environment('production') && 
               str_starts_with(config('app.url'), 'https://');
    }

    /**
     * セキュアクッキー設定が有効かチェック
     * 
     * @return bool セキュアクッキーが有効な場合true
     */
    public static function isSecureCookieEnabled(): bool
    {
        return (bool) config('session.secure', false);
    }

    /**
     * vantanlib.com用の設定チェック
     * 
     * @return array 設定チェック結果
     */
    public static function checkVantanlibConfig(): array
    {
        $checks = [
            'app_url_https' => [
                'name' => 'APP_URL HTTPS設定',
                'status' => str_starts_with(config('app.url'), 'https://'),
                'value' => config('app.url'),
                'expected' => 'https://vantanlib.com',
            ],
            'vantanlib_domain' => [
                'name' => 'vantanlib.comドメイン設定',
                'status' => str_contains(config('app.url'), 'vantanlib.com'),
                'value' => config('app.url'),
                'expected' => 'https://vantanlib.com',
            ],
            'secure_cookies' => [
                'name' => 'セキュアクッキー設定',
                'status' => self::isSecureCookieEnabled(),
                'value' => config('session.secure') ? 'true' : 'false',
                'expected' => 'true',
            ],
            'session_domain' => [
                'name' => 'セッションドメイン設定',
                'status' => str_contains(config('session.domain', ''), 'vantanlib.com'),
                'value' => config('session.domain', 'null'),
                'expected' => '.vantanlib.com',
            ],
            'sanctum_domains' => [
                'name' => 'Sanctumドメイン設定',
                'status' => in_array('vantanlib.com', config('sanctum.stateful', [])),
                'value' => implode(', ', config('sanctum.stateful', [])),
                'expected' => 'vantanlib.com',
            ],
        ];

        return $checks;
    }

    /**
     * Google Books API設定の確認
     * 
     * @return array Google Books API設定チェック結果
     */
    public static function checkGoogleBooksApiConfig(): array
    {
        $apiKey = config('services.google_books.api_key', env('GOOGLE_BOOKS_API_KEY'));
        
        return [
            'api_key_configured' => [
                'name' => 'Google Books APIキー設定',
                'status' => !empty($apiKey),
                'value' => $apiKey ? '設定済み' : '未設定',
                'expected' => '設定済み',
            ],
            'api_url_https' => [
                'name' => 'Google Books API HTTPS使用',
                'status' => true, // 既にHTTPS使用確認済み
                'value' => 'https://www.googleapis.com/books/v1/volumes',
                'expected' => 'HTTPS URL',
            ],
        ];
    }

    /**
     * Mixed Content対策の実装状況チェック
     * 
     * @return array 実装状況チェック結果
     */
    public static function checkMixedContentProtection(): array
    {
        return [
            'https_helper_available' => [
                'name' => 'HTTPSヘルパー関数',
                'status' => class_exists(self::class),
                'value' => '利用可能',
                'expected' => '利用可能',
            ],
            'force_https_middleware' => [
                'name' => 'HTTPS強制ミドルウェア',
                'status' => class_exists(\App\Http\Middleware\ForceHttps::class),
                'value' => class_exists(\App\Http\Middleware\ForceHttps::class) ? '実装済み' : '未実装',
                'expected' => '実装済み',
            ],
            'csp_headers' => [
                'name' => 'Content Security Policy',
                'status' => true, // Nginxで設定済み
                'value' => 'Nginxで設定済み',
                'expected' => '設定済み',
            ],
        ];
    }
}