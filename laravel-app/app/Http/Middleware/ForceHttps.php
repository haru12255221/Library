<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTPS強制ミドルウェア
 * 
 * 本番環境でHTTP接続をHTTPSにリダイレクトします。
 * vantanlib.com用に最適化されています。
 */
class ForceHttps
{
    /**
     * リクエストを処理します
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 本番環境でのみHTTPS強制を実行
        if (!$request->secure() && $this->shouldForceHttps($request)) {
            // HTTPSにリダイレクト
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // HTTPSの場合、セキュリティヘッダーを追加
        $response = $next($request);
        
        if ($request->secure()) {
            $this->addSecurityHeaders($response);
        }

        return $response;
    }

    /**
     * HTTPS強制が必要かどうかを判定
     */
    private function shouldForceHttps(Request $request): bool
    {
        // 本番環境でない場合はHTTPS強制しない
        if (!app()->environment('production')) {
            return false;
        }

        // Let's Encrypt認証パスは除外
        if ($request->is('.well-known/acme-challenge/*')) {
            return false;
        }

        // ヘルスチェックエンドポイントは除外（ロードバランサー対応）
        if ($request->is('health') && $this->isInternalHealthCheck($request)) {
            return false;
        }

        // その他のリクエストはHTTPS強制
        return true;
    }

    /**
     * 内部ヘルスチェックかどうかを判定
     */
    private function isInternalHealthCheck(Request $request): bool
    {
        // Docker内部からのアクセスかチェック
        $remoteAddr = $request->ip();
        
        // Docker内部ネットワークのIPアドレス範囲
        $internalRanges = [
            '172.16.0.0/12',    // Docker default bridge
            '10.0.0.0/8',       // Private network
            '192.168.0.0/16',   // Private network
            '127.0.0.1',        // Localhost
        ];

        foreach ($internalRanges as $range) {
            if ($this->ipInRange($remoteAddr, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * IPアドレスが指定された範囲内かチェック
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if ($ip === $range) {
            return true;
        }

        if (strpos($range, '/') === false) {
            return $ip === $range;
        }

        list($subnet, $bits) = explode('/', $range);
        
        if ($bits === null) {
            $bits = 32;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        
        // ip2longが失敗した場合の処理
        if ($ipLong === false || $subnetLong === false) {
            return false;
        }
        
        $mask = -1 << (32 - (int)$bits);
        $subnetLong &= $mask;

        return ($ipLong & $mask) === $subnetLong;
    }

    /**
     * セキュリティヘッダーを追加
     */
    private function addSecurityHeaders(Response $response): void
    {
        // HSTS (HTTP Strict Transport Security)
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains; preload'
        );

        // X-Frame-Options (クリックジャッキング防止)
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        // X-Content-Type-Options (MIME スニッフィング防止)
        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        // X-XSS-Protection (XSS攻撃防止)
        if (!$response->headers->has('X-XSS-Protection')) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Referrer-Policy (リファラー情報制御)
        if (!$response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        // X-Permitted-Cross-Domain-Policies (Flash/PDF等のクロスドメインポリシー)
        if (!$response->headers->has('X-Permitted-Cross-Domain-Policies')) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        }

        // X-Download-Options (IE8+のダウンロード動作制御)
        if (!$response->headers->has('X-Download-Options')) {
            $response->headers->set('X-Download-Options', 'noopen');
        }

        // vantanlib.com固有のセキュリティヘッダー
        $response->headers->set('X-Powered-By-Domain', 'vantanlib.com');
        $response->headers->remove('X-Powered-By'); // PHPバージョン情報を隠す
    }
}