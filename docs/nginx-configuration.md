# vantanlib.com Nginx設定ドキュメント

このドキュメントは、vantanlib.com本番環境用のNginx設定について詳細に説明します。

## 📋 設定概要

- **ファイル**: `docker/nginx/default.prod.conf`
- **対象ドメイン**: vantanlib.com, www.vantanlib.com
- **プロトコル**: HTTP/2 over HTTPS
- **SSL証明書**: Let's Encrypt
- **バックエンド**: Laravel アプリケーション (app:8000)

## 🏗️ アーキテクチャ

```
Internet → Nginx (Port 80/443) → Laravel App (Port 8000)
           ↓
       Let's Encrypt SSL
       Static File Cache
       Security Headers
       Rate Limiting
```

## 🔧 主要設定

### 1. アップストリーム設定

```nginx
upstream app_backend {
    server app:8000;
    keepalive 32;
}
```

- **目的**: Laravel アプリケーションへの負荷分散とコネクション管理
- **keepalive**: 32個のコネクションをプールして再利用
- **サーバー**: Dockerコンテナ `app` のポート8000

### 2. HTTP設定（ポート80）

```nginx
server {
    listen 80;
    server_name vantanlib.com www.vantanlib.com;
    
    # Let's Encrypt認証用
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
        allow all;
    }
    
    # ヘルスチェック用
    location /health {
        proxy_pass http://app_backend;
    }
    
    # HTTPS リダイレクト
    location / {
        return 301 https://vantanlib.com$request_uri;
    }
}
```

**機能**:
- ✅ Let's Encrypt証明書の自動取得をサポート
- ✅ ヘルスチェックエンドポイントの提供
- ✅ 全HTTPトラフィックをHTTPSにリダイレクト

### 3. HTTPS設定（ポート443）

#### SSL/TLS設定

```nginx
server {
    listen 443 ssl;
    http2 on;
    server_name vantanlib.com www.vantanlib.com;
    
    # SSL証明書
    ssl_certificate /etc/letsencrypt/live/vantanlib.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/vantanlib.com/privkey.pem;
    
    # SSL設定
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256...;
    ssl_prefer_server_ciphers off;
    
    # パフォーマンス最適化
    ssl_session_cache shared:SSL:50m;
    ssl_session_timeout 1d;
    ssl_session_tickets off;
    
    # OCSP Stapling
    ssl_stapling on;
    ssl_stapling_verify on;
}
```

**セキュリティ機能**:
- 🔒 TLS 1.2+ のみサポート
- 🔒 強力な暗号化スイート
- 🔒 Perfect Forward Secrecy
- ⚡ SSL セッションキャッシュ
- ⚡ OCSP Stapling

#### セキュリティヘッダー

```nginx
# HSTS (HTTP Strict Transport Security)
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

# フレーム埋め込み防止
add_header X-Frame-Options DENY always;

# MIME タイプスニッフィング防止
add_header X-Content-Type-Options nosniff always;

# CSP (Content Security Policy)
add_header Content-Security-Policy "
    default-src 'self';
    script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googleapis.com;
    style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
    font-src 'self' https://fonts.gstatic.com;
    img-src 'self' data: https: blob:;
    media-src 'self' blob: mediastream:;
    connect-src 'self' https://www.googleapis.com https://books.googleapis.com;
    frame-src 'none';
    object-src 'none';
" always;
```

**セキュリティ機能**:
- 🛡️ HSTS: HTTPS強制（1年間）
- 🛡️ フレーム埋め込み防止
- 🛡️ MIME スニッフィング防止
- 🛡️ CSP: XSS攻撃防止
- 📱 カメラ機能対応 (`mediastream:`)
- 📚 Google Books API対応

### 4. パフォーマンス最適化

#### Gzip圧縮

```nginx
gzip on;
gzip_vary on;
gzip_min_length 1024;
gzip_proxied any;
gzip_comp_level 6;
gzip_types
    text/plain
    text/css
    text/xml
    text/javascript
    application/json
    application/javascript
    application/xml+rss
    application/atom+xml
    image/svg+xml;
```

#### 静的ファイルキャッシュ

```nginx
# CSS/JS ファイル
location ~* \.(css|js)$ {
    expires 1M;
    add_header Cache-Control "public, immutable";
    gzip_static on;
}

# 画像ファイル
location ~* \.(png|jpg|jpeg|gif|webp|avif)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# フォントファイル
location ~* \.(ico|svg|woff|woff2|ttf|eot|otf)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    add_header Access-Control-Allow-Origin "*";
}
```

### 5. レート制限

```nginx
# レート制限ゾーン定義
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
limit_req_zone $binary_remote_addr zone=api:10m rate=30r/m;

# ログイン試行制限
location ~ ^/(login|admin) {
    limit_req zone=login burst=3 nodelay;
    proxy_pass http://app_backend;
}

# API制限
location ~ ^/api/ {
    limit_req zone=api burst=10 nodelay;
    proxy_pass http://app_backend;
}
```

**保護機能**:
- 🚦 ログイン試行: 5回/分（バースト3回）
- 🚦 API呼び出し: 30回/分（バースト10回）
- 🚦 DDoS攻撃防止

### 6. プロキシ設定

```nginx
location / {
    proxy_pass http://app_backend;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-Port $server_port;
    proxy_set_header X-Forwarded-Host $server_name;
    
    # HTTP/1.1とKeep-Alive対応
    proxy_http_version 1.1;
    proxy_set_header Connection "";
    
    # タイムアウト設定
    proxy_connect_timeout 60s;
    proxy_send_timeout 60s;
    proxy_read_timeout 60s;
    
    # バッファリング設定
    proxy_buffering on;
    proxy_buffer_size 4k;
    proxy_buffers 8 4k;
}
```

## 🔍 機能別詳細

### HTML5カメラ機能対応

```nginx
# CSP設定でカメラアクセスを許可
media-src 'self' blob: mediastream:;
```

- **mediastream:**: カメラ・マイクアクセスを許可
- **blob:**: メディアデータの処理を許可

### Google Books API対応

```nginx
# CSP設定でGoogle APIアクセスを許可
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googleapis.com;
connect-src 'self' https://www.googleapis.com https://books.googleapis.com;
```

- **googleapis.com**: Google Books APIへのアクセス許可
- **books.googleapis.com**: 書籍データの取得許可

### www → non-www リダイレクト

```nginx
# SEO最適化のためwwwを削除
if ($host = www.vantanlib.com) {
    return 301 https://vantanlib.com$request_uri;
}
```

## 📊 パフォーマンス指標

### 期待される改善

| 項目 | 改善内容 | 効果 |
|------|----------|------|
| **HTTP/2** | 多重化通信 | 30-50% 高速化 |
| **Gzip圧縮** | テキスト圧縮 | 60-80% サイズ削減 |
| **静的ファイルキャッシュ** | ブラウザキャッシュ | 90% 転送量削減 |
| **SSL セッションキャッシュ** | SSL再利用 | 20-30% SSL高速化 |
| **Keep-Alive** | コネクション再利用 | 10-20% 高速化 |

### セキュリティ評価

| 項目 | 設定 | セキュリティレベル |
|------|------|-------------------|
| **SSL/TLS** | TLS 1.2+ | A+ |
| **暗号化スイート** | 強力な暗号化 | A+ |
| **HSTS** | 1年間強制 | A+ |
| **CSP** | 厳格なポリシー | A |
| **セキュリティヘッダー** | 全て設定済み | A+ |

## 🚀 デプロイ手順

### 1. 設定ファイルの確認

```bash
# 構文チェック
./scripts/test-nginx-basic.sh

# 設定内容の確認
cat docker/nginx/default.prod.conf
```

### 2. Docker環境での起動

```bash
# 本番環境起動
cd laravel-app
docker-compose -f docker-compose.prod.yml up -d nginx

# ログ確認
docker-compose -f docker-compose.prod.yml logs nginx
```

### 3. SSL証明書の取得

```bash
# Let's Encrypt証明書取得
docker-compose -f docker-compose.prod.yml run --rm certbot

# 証明書確認
docker-compose -f docker-compose.prod.yml exec nginx ls -la /etc/letsencrypt/live/vantanlib.com/
```

## 🔧 トラブルシューティング

### よくある問題

#### 1. SSL証明書エラー

```bash
# 証明書の状態確認
openssl x509 -in /path/to/fullchain.pem -text -noout

# 証明書の有効期限確認
openssl x509 -in /path/to/fullchain.pem -enddate -noout
```

#### 2. アップストリーム接続エラー

```bash
# アプリケーションコンテナの状態確認
docker-compose -f docker-compose.prod.yml ps app

# ネットワーク接続確認
docker-compose -f docker-compose.prod.yml exec nginx ping app
```

#### 3. レート制限の調整

```nginx
# より厳しい制限
limit_req_zone $binary_remote_addr zone=login:10m rate=3r/m;

# より緩い制限
limit_req_zone $binary_remote_addr zone=api:10m rate=60r/m;
```

## 📈 監視とメンテナンス

### ログ監視

```bash
# アクセスログ
docker-compose -f docker-compose.prod.yml exec nginx tail -f /var/log/nginx/vantanlib_access.log

# エラーログ
docker-compose -f docker-compose.prod.yml exec nginx tail -f /var/log/nginx/vantanlib_error.log
```

### パフォーマンス監視

```bash
# Nginx統計
docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload

# SSL証明書の有効期限監視
openssl s_client -connect vantanlib.com:443 -servername vantanlib.com 2>/dev/null | openssl x509 -enddate -noout
```

## 🔄 更新手順

### 設定変更時

```bash
# 1. 設定ファイル編集
nano docker/nginx/default.prod.conf

# 2. 構文チェック
./scripts/test-nginx-basic.sh

# 3. 設定リロード
docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

### SSL証明書更新

```bash
# 自動更新（Cronで実行）
./scripts/renew-ssl.sh

# 手動更新
docker-compose -f docker-compose.prod.yml run --rm certbot certbot renew
docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

---

**最終更新**: 2025-02-09  
**バージョン**: 1.0.0  
**対象環境**: vantanlib.com 本番環境