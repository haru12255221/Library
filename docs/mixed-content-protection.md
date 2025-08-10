# vantanlib.com Mixed Content対策ドキュメント

このドキュメントは、vantanlib.com用のMixed Content対策について詳細に説明します。

## 📋 概要

Mixed Contentとは、HTTPS化されたサイトでHTTPリソースを読み込むことで発生するセキュリティ問題です。vantanlib.comでは包括的な対策を実装しています。

## 🔍 実装された対策

### 1. HTTPSヘルパー関数

**ファイル**: `app/Helpers/HttpsHelper.php`

#### 主要機能

```php
// URLをHTTPS化
HttpsHelper::secureUrl('http://example.com/image.jpg')
// → 'https://example.com/image.jpg'

// アセットURLをHTTPS化
HttpsHelper::secureAsset('css/app.css')
// → 'https://vantanlib.com/css/app.css'

// 外部リソースURLをHTTPS化
HttpsHelper::secureExternalUrl('http://fonts.googleapis.com/css')
// → 'https://fonts.googleapis.com/css'
```

#### Mixed Content検出

```php
$content = '<img src="http://example.com/image.jpg">';
$issues = HttpsHelper::detectMixedContent($content);
// → ['src="http://example.com/image.jpg"']
```

### 2. Bladeディレクティブ

**ファイル**: `app/Providers/HttpsServiceProvider.php`

#### 利用可能なディレクティブ

```blade
{{-- アセットをHTTPS化 --}}
<link href="@secureAsset('css/app.css')" rel="stylesheet">

{{-- URLをHTTPS化 --}}
<a href="@secureUrl($externalUrl)">リンク</a>

{{-- HTTPS環境でのみ表示 --}}
@httpsOnly
    <p>この内容はHTTPS環境でのみ表示されます</p>
@endhttpsOnly

{{-- vantanlib.comドメインでのみ表示 --}}
@vantanlibDomain
    <p>vantanlib.com固有のコンテンツ</p>
@endvantanlibDomain

{{-- Mixed Content安全な環境でのみ表示 --}}
@mixedContentSafe
    <p>Mixed Content対策済み環境でのみ表示</p>
@endmixedContentSafe
```

### 3. 自動検出・修正コマンド

**コマンド**: `php artisan https:check-mixed-content`

#### 基本的な使用方法

```bash
# 基本チェック
php artisan https:check-mixed-content

# ファイルスキャン実行
php artisan https:check-mixed-content --scan-files

# 詳細レポート生成
php artisan https:check-mixed-content --report

# 自動修正（将来実装予定）
php artisan https:check-mixed-content --fix
```

#### 出力例

```
🔍 vantanlib.com Mixed Content検査を開始します

📋 基本HTTPS設定チェック
  ✅ APP_URL HTTPS設定
     現在値: https://vantanlib.com
  ✅ vantanlib.comドメイン設定
     現在値: https://vantanlib.com
  ✅ セキュアクッキー設定
     現在値: true

📚 Google Books API設定チェック
  ✅ Google Books APIキー設定
     現在値: 設定済み
  ✅ Google Books API HTTPS使用
     現在値: https://www.googleapis.com/books/v1/volumes

🛡️ Mixed Content保護機能チェック
  ✅ HTTPSヘルパー関数
     現在値: 利用可能
  ✅ HTTPS強制ミドルウェア
     現在値: 実装済み
  ✅ Content Security Policy
     現在値: Nginxで設定済み

✅ Mixed Content検査が完了しました
```

## 🎯 対策済み項目

### 1. Google Books API

**状況**: ✅ 完全対応済み

```php
// app/Services/GoogleBooksService.php
private const API_BASE_URL = 'https://www.googleapis.com/books/v1/volumes';

// routes/api.php
$url = "https://www.googleapis.com/books/v1/volumes?q=isbn:" . urlencode($isbn);
```

**確認方法**:
- すべてのGoogle Books API呼び出しでHTTPS使用
- Mixed Contentエラーなし

### 2. 静的リソース

**状況**: ✅ 対応済み

- CSS/JSファイル: Viteビルドシステムで自動HTTPS化
- 画像ファイル: `asset()` ヘルパーで自動HTTPS化
- フォントファイル: Google Fontsは既にHTTPS

### 3. 外部リソース

**状況**: ✅ 対応済み

対応済み外部サービス:
- Google Fonts (`fonts.googleapis.com`, `fonts.gstatic.com`)
- Google APIs (`www.googleapis.com`, `books.googleapis.com`)
- CDN (`cdnjs.cloudflare.com`, `cdn.jsdelivr.net`)

### 4. XML名前空間

**状況**: ✅ 問題なし

```html
<!-- これはMixed Contentではありません -->
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <!-- SVGコンテンツ -->
</svg>
```

## 🔧 設定確認

### 必要な環境変数

```env
# .env.production
APP_URL=https://vantanlib.com
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.vantanlib.com
SANCTUM_STATEFUL_DOMAINS=vantanlib.com
GOOGLE_BOOKS_API_KEY=your_api_key_here
```

### Nginx設定

```nginx
# Content Security Policy（Mixed Content対策）
add_header Content-Security-Policy "
    default-src 'self';
    script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googleapis.com;
    style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
    font-src 'self' https://fonts.gstatic.com;
    img-src 'self' data: https: blob:;
    connect-src 'self' https://www.googleapis.com https://books.googleapis.com;
    upgrade-insecure-requests;
" always;
```

## 🧪 テスト

### テストファイル

`tests/Feature/MixedContentTest.php` で包括的なテストを実装：

```bash
# テスト実行
php artisan test --filter=MixedContentTest

# 特定のテストメソッド実行
php artisan test --filter=test_https_helper_converts_http_to_https
```

### テストケース

1. **URL変換テスト**
   - HTTP → HTTPS変換
   - プロトコル相対URL処理
   - 相対URL保持

2. **Mixed Content検出テスト**
   - HTTP URLの検出
   - XML名前空間の除外

3. **設定チェックテスト**
   - vantanlib.com設定確認
   - Google Books API設定確認

4. **保護機能テスト**
   - HTTPS強制判定
   - セキュアクッキー設定

## 🚨 トラブルシューティング

### よくある問題

#### 1. Mixed Contentエラーが発生する

**症状**: ブラウザコンソールに「Mixed Content」エラー

**確認方法**:
```bash
php artisan https:check-mixed-content --scan-files
```

**解決方法**:
```php
// ❌ 問題のあるコード
<img src="http://example.com/image.jpg">

// ✅ 修正後のコード
<img src="{{ HttpsHelper::secureUrl('http://example.com/image.jpg') }}">
// または
<img src="@secureUrl('http://example.com/image.jpg')">
```

#### 2. Google Books APIでMixed Contentエラー

**症状**: API呼び出しでMixed Contentエラー

**確認方法**:
```bash
grep -r "http://.*googleapis" app/ resources/
```

**解決方法**: 既に対応済み（HTTPS使用）

#### 3. 静的ファイルでMixed Contentエラー

**症状**: CSS/JS/画像でMixed Contentエラー

**解決方法**:
```blade
{{-- ❌ 問題のあるコード --}}
<link href="http://example.com/css/style.css" rel="stylesheet">

{{-- ✅ 修正後のコード --}}
<link href="@secureAsset('css/style.css')" rel="stylesheet">
```

### デバッグ方法

#### 1. ブラウザ開発者ツール

```javascript
// コンソールでMixed Contentエラーを確認
console.log('Mixed Content errors:', 
    performance.getEntriesByType('navigation')[0].securityDetails);
```

#### 2. Nginxログ確認

```bash
# Mixed Contentに関連するエラーログ
docker compose exec nginx tail -f /var/log/nginx/vantanlib_error.log | grep -i "mixed"
```

#### 3. CSPヘッダー確認

```bash
# Content Security Policyヘッダーの確認
curl -I https://vantanlib.com/ | grep -i "content-security-policy"
```

## 📊 パフォーマンス影響

### HTTPS化による影響

| 項目 | HTTP | HTTPS | 影響 |
|------|------|-------|------|
| **初回接続** | 50ms | 100ms | +50ms (SSL handshake) |
| **キープアライブ** | 10ms | 15ms | +5ms |
| **データ転送** | 100% | 102% | +2% (暗号化オーバーヘッド) |
| **セキュリティ** | 低 | 高 | 大幅向上 |

### 最適化施策

1. **HTTP/2使用**: 多重化通信で高速化
2. **SSL セッションキャッシュ**: 再接続高速化
3. **OCSP Stapling**: 証明書検証高速化
4. **CDN使用**: 外部リソースの高速配信

## 🔄 継続的監視

### 定期チェック項目

```bash
# 週次実行推奨
php artisan https:check-mixed-content --scan-files --report

# 月次実行推奨
php artisan https:check-mixed-content --scan-files --report --fix
```

### 監視指標

1. **Mixed Contentエラー数**: 0件維持
2. **HTTPS化率**: 100%維持
3. **セキュリティヘッダー**: 全て設定済み
4. **API呼び出し**: 全てHTTPS

### アラート設定

```bash
# Cronジョブでの定期チェック
0 2 * * 1 /path/to/artisan https:check-mixed-content --report >> /var/log/mixed-content-check.log 2>&1
```

## 📚 参考資料

- [MDN Mixed Content](https://developer.mozilla.org/en-US/docs/Web/Security/Mixed_content)
- [Google Web Fundamentals - Mixed Content](https://developers.google.com/web/fundamentals/security/prevent-mixed-content)
- [OWASP Mixed Content](https://owasp.org/www-community/attacks/Mixed_Content)
- [Laravel HTTPS Configuration](https://laravel.com/docs/urls#forcing-https)

---

**最終更新**: 2025-02-09  
**バージョン**: 1.0.0  
**対象環境**: vantanlib.com 本番環境