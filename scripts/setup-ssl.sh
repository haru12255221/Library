#!/bin/bash

# Let's Encrypt SSL証明書セットアップスクリプト
# 本番環境でのみ実行してください

set -e

# 設定変数
DOMAIN=${1:-"your-domain.com"}
EMAIL=${2:-"admin@your-domain.com"}

if [ "$DOMAIN" = "your-domain.com" ] || [ "$EMAIL" = "admin@your-domain.com" ]; then
    echo "❌ ドメイン名とメールアドレスを指定してください"
    echo "使用方法: $0 <domain> <email>"
    echo "例: $0 library.example.com admin@example.com"
    exit 1
fi

echo "🔐 Let's Encrypt SSL証明書をセットアップしています..."
echo "ドメイン: $DOMAIN"
echo "メール: $EMAIL"

# 必要なディレクトリを作成
mkdir -p docker/certbot/conf
mkdir -p docker/certbot/www

# 一時的なNginx設定でHTTP認証を有効化
echo "📝 一時的なNginx設定を作成しています..."
cat > docker/nginx/temp.conf << EOF
server {
    listen 80;
    server_name $DOMAIN;
    
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
    
    location / {
        return 200 'Let\'s Encrypt setup in progress...';
        add_header Content-Type text/plain;
    }
}
EOF

# Nginxコンテナを起動（一時設定で）
echo "🚀 一時的なNginxコンテナを起動しています..."
docker run -d --name temp-nginx \
    -p 80:80 \
    -v $(pwd)/docker/nginx/temp.conf:/etc/nginx/conf.d/default.conf \
    -v $(pwd)/docker/certbot/www:/var/www/certbot \
    nginx:alpine

# Let's Encrypt証明書を取得
echo "📜 Let's Encrypt証明書を取得しています..."
docker run --rm \
    -v $(pwd)/docker/certbot/conf:/etc/letsencrypt \
    -v $(pwd)/docker/certbot/www:/var/www/certbot \
    certbot/certbot \
    certonly --webroot \
    -w /var/www/certbot \
    --email $EMAIL \
    -d $DOMAIN \
    --agree-tos \
    --no-eff-email \
    --force-renewal

# 一時的なNginxコンテナを停止・削除
echo "🛑 一時的なコンテナを停止しています..."
docker stop temp-nginx
docker rm temp-nginx

# 本番用Nginx設定を更新
echo "📝 本番用Nginx設定を更新しています..."
sed -i.bak "s/your-domain.com/$DOMAIN/g" docker/nginx/default.prod.conf

# 本番用環境変数を更新
echo "📝 環境変数を更新しています..."
if [ -f "laravel-app/.env.production" ]; then
    sed -i.bak "s|APP_URL=.*|APP_URL=https://$DOMAIN|g" laravel-app/.env.production
    sed -i.bak "s|SANCTUM_STATEFUL_DOMAINS=.*|SANCTUM_STATEFUL_DOMAINS=$DOMAIN|g" laravel-app/.env.production
else
    echo "⚠️  .env.productionファイルが見つかりません"
fi

echo ""
echo "🎉 SSL証明書のセットアップが完了しました！"
echo ""
echo "📋 次のステップ:"
echo "1. DNS設定でドメインがサーバーIPを指していることを確認"
echo "2. 本番環境のDocker Composeを起動:"
echo "   cd laravel-app && docker compose -f docker-compose.prod.yml up -d"
echo "3. ブラウザで https://$DOMAIN にアクセスして確認"
echo ""
echo "🔄 証明書の自動更新:"
echo "   ./scripts/renew-ssl.sh を定期実行するようにcronを設定してください"