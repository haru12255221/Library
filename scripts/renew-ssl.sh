#!/bin/bash

# SSL証明書自動更新スクリプト
# Cronで定期実行することを推奨

set -e

echo "🔄 SSL証明書の更新を確認しています..."

# 証明書の更新を試行
docker run --rm \
    -v $(pwd)/docker/certbot/conf:/etc/letsencrypt \
    -v $(pwd)/docker/certbot/www:/var/www/certbot \
    certbot/certbot \
    renew --quiet

# Nginxの設定をリロード
if docker ps | grep -q "nginx"; then
    echo "🔄 Nginxの設定をリロードしています..."
    docker exec $(docker ps -q -f name=nginx) nginx -s reload
    echo "✅ Nginxの設定をリロードしました"
else
    echo "⚠️  Nginxコンテナが見つかりません"
fi

echo "✅ SSL証明書の更新確認が完了しました"

# ログに記録
echo "$(date): SSL certificate renewal check completed" >> /var/log/ssl-renew.log