# HTTPS強制ミドルウェア ドキュメント

このドキュメントは、vantanlib.com用のHTTPS強制ミドルウェアについて詳細に説明します。

## 📋 概要

`ForceHttps` ミドルウェアは、本番環境でHTTP接続を自動的にHTTPSにリダイレクトし、セキュリティヘッダーを追加するミドルウェアです。

- **ファイル**: `app/Http/Middleware/ForceHttps.php`
- **対象環境**: 本番環境（production）のみ
- **対象ドメイン**: vantanlib.com
- **機能**: HTTP→HTTPSリダイレクト、セキュリティヘッダー追加

## 🔧 主要機能

### 1. HTTPS強制リダイレクト

```php
// HTTP接続の場合、HTTPSにリダイレクト
if (!$request->secure() && $this->shouldForceHttps($request)) {
    return redirect()->secure($request->getRequestUri(), 301);
}
```

**動作**:
- HTTP接続を検出すると301リダイレクトでHTTPSに転送
- URLパスとクエリパラメータを保持
- 本番環境でのみ動作

### 2. 例外パスの処理

以下のパスはHTTPS強制から除外されます：

#### Let's Encrypt認証パス
```php
if ($request->is('.well-known/acme-challenge/*')) {
    return false; // HTTPS強制しない
}
```

#### 内部ヘルスチェック
```php
if ($request->is('health') && $this->isInternalHealthCheck($request)) {
    return false; // HTTPS強制しない
}
```

**内部IPアドレス範囲**:
- `172.16.0.0/12` - Docker default bridge
- `10.0.0.0/8` - Private network
- `192.168.0.0/16` - Private network
- `127.0.0.1` - Localhost

### 3. セキュリティヘッダーの追加

HTTPS接続時に以下のセキュリティヘッダーを自動追加：

```php
// HSTS (HTTP Strict Transport Security)
'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload'

// クリックジャッキング防止
'X-Frame-Options' => 'DENY'

// MIME スニッフィング防止
'X-Content-Type-Options' => 'nosniff'

// XSS攻撃防止
'X-XSS-Protection' => '1; mode=block'

// リファラー情報制御
'Referrer-Policy' => 'strict-origin-when-cross-origin'

// クロスドメインポリシー制御
'X-Permitted-Cross-Domain-Policies' => 'none'

// IE8+のダウンロード動作制御
'X-Download-Options' => 'noopen'

// vantanlib.com固有ヘッダー
'X-Powered-By-Domain' => 'vantanlib.com'
```

## 🚀 設定方法

### 1. ミドルウェア登録

`bootstrap/app.php` でミドルウェアを登録：

```php
->withMiddleware(function (Middleware $middleware): void {
    // エイリアス設定
    $middleware->alias([
        'force.https' => \App\Http\Middleware\ForceHttps::class,
    ]);

    // グローバルミドルウェア（本番環境のみ）
    if (app()->environment('production')) {
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceHttps::class,
        ]);
    }
})
```

### 2. 環境設定

`.env.production` での推奨設定：

```env
# 基本設定
APP_ENV=production
APP_URL=https://vantanlib.com

# セッション設定
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_DOMAIN=.vantanlib.com

# Sanctum設定
SANCTUM_STATEFUL_DOMAINS=vantanlib.com,www.vantanlib.com
```

## 🧪 テスト

### テストファイル

`tests/Feature/ForceHttpsMiddlewareTest.php` で包括的なテストを実装：

```bash
# テスト実行
php artisan test --filter=ForceHttpsMiddlewareTest

# 特定のテストメソッド実行
php artisan test --filter=test_http_redirects_to_https_in_production
```

### テストケース

1. **基本的なHTTPS強制**
   - HTTP → HTTPS リダイレクト
   - パス・クエリパラメータの保持

2. **環境別動作**
   - 本番環境：HTTPS強制有効
   - 開発環境：HTTPS強制無効

3. **例外パス**
   - Let's Encrypt認証パス除外
   - 内部ヘルスチェック除外

4. **セキュリティヘッダー**
   - HTTPS時のヘッダー追加
   - 重複ヘッダーの防止

5. **複雑なシナリオ**
   - POSTリクエストの処理
   - wwwサブドメインの処理

## 🔍 設定確認

### Artisanコマンド

```bash
# 基本的な設定確認
php artisan https:check

# 詳細な設定確認
php artisan https:check --detailed
```

### 手動確認

```bash
# 1. ミドルウェアファイルの存在確認
ls -la app/Http/Middleware/ForceHttps.php

# 2. 設定ファイルの確認
grep -n "ForceHttps" bootstrap/app.php

# 3. 環境設定の確認
grep -E "(APP_URL|SESSION_SECURE)" .env.production
```

## 🚨 トラブルシューティング

### よくある問題

#### 1. 無限リダイレクトループ

**症状**: ページが無限にリダイレクトされる

**原因**: 
- プロキシ設定の問題
- `X-Forwarded-Proto` ヘッダーの不正

**解決方法**:
```php
// config/trustedproxy.php で信頼できるプロキシを設定
'proxies' => ['172.16.0.0/12'],
'headers' => Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO,
```

#### 2. Let's Encrypt認証失敗

**症状**: SSL証明書の取得・更新が失敗する

**原因**: `.well-known/acme-challenge/` パスがHTTPSにリダイレクトされる

**解決方法**: ミドルウェアで既に除外済み（確認方法）
```bash
curl -I http://vantanlib.com/.well-known/acme-challenge/test
# 301リダイレクトが返されないことを確認
```

#### 3. ヘルスチェック失敗

**症状**: ロードバランサーのヘルスチェックが失敗する

**原因**: 内部IPからのHTTPアクセスがHTTPSにリダイレクトされる

**解決方法**: 内部IP範囲の確認・調整
```php
// 必要に応じてIP範囲を追加
$internalRanges = [
    '172.16.0.0/12',
    '10.0.0.0/8',
    '192.168.0.0/16',
    '127.0.0.1',
    'your-loadbalancer-ip/32', // 追加
];
```

#### 4. セキュリティヘッダーの重複

**症状**: レスポンスヘッダーが重複する

**原因**: Nginxとミドルウェアで同じヘッダーを設定

**解決方法**: ミドルウェアで重複チェック済み
```php
// 既存ヘッダーがある場合は追加しない
if (!$response->headers->has('X-Frame-Options')) {
    $response->headers->set('X-Frame-Options', 'DENY');
}
```

### デバッグ方法

#### 1. ログ出力の追加

```php
// ミドルウェア内でデバッグログ
\Log::info('ForceHttps middleware', [
    'secure' => $request->secure(),
    'environment' => app()->environment(),
    'host' => $request->getHost(),
    'path' => $request->getPathInfo(),
]);
```

#### 2. ヘッダー確認

```bash
# レスポンスヘッダーの確認
curl -I https://vantanlib.com/

# 特定のヘッダーの確認
curl -I https://vantanlib.com/ | grep -i "strict-transport"
```

#### 3. リダイレクト確認

```bash
# HTTPアクセスのリダイレクト確認
curl -I http://vantanlib.com/

# 期待される結果:
# HTTP/1.1 301 Moved Permanently
# Location: https://vantanlib.com/
```

## 📊 パフォーマンス考慮事項

### 1. ミドルウェアの実行順序

```php
// 最初に実行されるよう設定（prepend使用）
$middleware->web(prepend: [
    \App\Http\Middleware\ForceHttps::class,
]);
```

### 2. IP範囲チェックの最適化

```php
// 頻繁にアクセスされるIPは先頭に配置
$internalRanges = [
    '127.0.0.1',        // 最も頻繁
    '172.16.0.0/12',    // Docker内部
    '10.0.0.0/8',       // その他
];
```

### 3. ヘッダー設定の最適化

```php
// 必要最小限のヘッダーのみ設定
// 既存ヘッダーの重複チェックで無駄な処理を回避
```

## 🔄 メンテナンス

### 定期的な確認項目

1. **セキュリティヘッダーの更新**
   - 新しいセキュリティ要件への対応
   - ブラウザサポートの変更

2. **IP範囲の見直し**
   - インフラ変更時の内部IP範囲更新
   - 新しいロードバランサーIP追加

3. **テストの実行**
   - 定期的なテスト実行
   - 新機能追加時のテスト更新

### アップデート手順

```bash
# 1. テスト実行
php artisan test --filter=ForceHttpsMiddlewareTest

# 2. 設定確認
php artisan https:check --detailed

# 3. 本番環境での動作確認
curl -I http://vantanlib.com/
curl -I https://vantanlib.com/
```

---

**最終更新**: 2025-02-09  
**バージョン**: 1.0.0  
**対象環境**: vantanlib.com 本番環境