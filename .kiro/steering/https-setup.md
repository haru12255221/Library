# HTTPS化ガイドライン

このドキュメントは、開発環境と本番環境でHTTPS化を実装するためのガイドラインです。

## 🔒 HTTPS化の重要性

### なぜHTTPS化が必要か
1. **データ暗号化**: 通信内容の盗聴防止
2. **認証**: サーバーの正当性確認
3. **完全性**: データ改ざんの検出
4. **SEO**: Googleの検索ランキング向上
5. **ブラウザ要件**: モダンブラウザの必須要件

## 🏗️ 環境別実装方法

### 開発環境（localhost）

#### 方法1: 自己署名証明書（推奨）
```bash
# 証明書生成
./docker/ssl/generate-cert.sh

# Nginx設定でHTTPS有効化
# docker/nginx/default.dev.conf
```

#### 方法2: mkcert（より簡単）
```bash
# mkcertをインストール
brew install mkcert  # macOS
# または
choco install mkcert  # Windows

# ローカルCA作成
mkcert -install

# localhost用証明書生成
mkcert localhost 127.0.0.1 ::1
```

### 本番環境（実際のドメイン）

#### Let's Encrypt + Certbot（推奨）

**前提条件**:
- 実際のドメイン名が必要
- ドメインがサーバーのIPアドレスに向いている
- ポート80, 443が開いている

**実装手順**:

1. **Certbotコンテナの追加**
```yaml
# docker-compose.prod.yml
services:
  certbot:
    image: certbot/certbot
    container_name: library-certbot
    volumes:
      - ./docker/certbot/conf:/etc/letsencrypt
      - ./docker/certbot/www:/var/www/certbot
    command: certonly --webroot -w /var/www/certbot --email your-email@domain.com -d your-domain.com --agree-tos --no-eff-email
```

2. **Nginx設定の更新**
```nginx
# docker/nginx/default.prod.conf
server {
    listen 80;
    server_name your-domain.com;
    
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    
    location / {
        return 301 https://$server_name$request_uri;
    }
}

server {
    listen 443 ssl;
    server_name your-domain.com;
    
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    
    # SSL設定
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    location / {
        proxy_pass http://app:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

#### 自動更新の設定

**更新スクリプト**:
```bash
#!/bin/bash
# scripts/renew-ssl.sh

echo "🔄 SSL証明書の更新を確認しています..."

# Certbotで証明書更新
docker compose exec certbot certbot renew --quiet

# Nginxをリロード
docker compose exec nginx nginx -s reload

echo "✅ SSL証明書の更新確認が完了しました"
```

**Cronジョブ設定**:
```bash
# 毎日午前2時に実行
0 2 * * * /path/to/your/project/scripts/renew-ssl.sh >> /var/log/ssl-renew.log 2>&1
```

## 🛠️ 実装例

### 開発環境用Docker設定

```yaml
# docker-compose.dev.yml
services:
  nginx-dev:
    image: nginx:alpine
    container_name: library-nginx-dev
    ports:
      - "8001:80"
      - "8443:443"  # HTTPS用ポート
    volumes:
      - ./docker/nginx/default.dev.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/nginx/ssl  # SSL証明書
    depends_on:
      - app
```

### 本番環境用Docker設定

```yaml
# docker-compose.prod.yml
services:
  nginx-prod:
    image: nginx:alpine
    container_name: library-nginx-prod
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/default.prod.conf:/etc/nginx/conf.d/default.conf
      - ./docker/certbot/conf:/etc/letsencrypt
      - ./docker/certbot/www:/var/www/certbot
    depends_on:
      - app
  
  certbot:
    image: certbot/certbot
    container_name: library-certbot
    volumes:
      - ./docker/certbot/conf:/etc/letsencrypt
      - ./docker/certbot/www:/var/www/certbot
```

## 🔧 Laravel設定

### 環境変数の更新

**本番環境（.env.production）**:
```env
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### ミドルウェア設定

```php
// app/Http/Middleware/ForceHttps.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
```

## 📋 チェックリスト

### 開発環境
- [ ] 自己署名証明書の生成
- [ ] Nginx HTTPS設定
- [ ] ブラウザでの動作確認

### 本番環境
- [ ] ドメイン名の取得・設定
- [ ] DNS設定の確認
- [ ] Let's Encrypt証明書の取得
- [ ] Nginx HTTPS設定
- [ ] 自動更新の設定
- [ ] セキュリティヘッダーの設定

## 🚨 トラブルシューティング

### よくある問題

1. **証明書取得失敗**
   - ドメインのDNS設定を確認
   - ポート80が開いているか確認
   - ファイアウォール設定を確認

2. **証明書更新失敗**
   - Cronジョブの実行権限を確認
   - ログファイルでエラー内容を確認

3. **Mixed Content エラー**
   - すべてのリソースをHTTPS化
   - Content Security Policyの設定

## 🔗 参考リンク

- [Let's Encrypt公式サイト](https://letsencrypt.org/)
- [Certbot公式ドキュメント](https://certbot.eff.org/)
- [mkcert GitHub](https://github.com/FiloSottile/mkcert)