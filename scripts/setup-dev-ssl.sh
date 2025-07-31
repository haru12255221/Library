#!/bin/bash

# 開発環境用自己署名証明書作成スクリプト

echo "🔐 開発環境用SSL証明書を作成しています..."

# SSL証明書用ディレクトリを作成
mkdir -p docker/ssl

# 自己署名証明書を作成
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout docker/ssl/localhost.key \
    -out docker/ssl/localhost.crt \
    -subj "/C=JP/ST=Tokyo/L=Tokyo/O=Development/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,IP:192.168.0.101"

echo "✅ SSL証明書が作成されました"
echo "📁 場所: docker/ssl/"
echo ""
echo "📋 次のステップ:"
echo "1. docker-compose.yml にHTTPS設定を追加"
echo "2. Nginx設定でSSLを有効化"
echo "3. ブラウザで https://192.168.0.101:8443 にアクセス"
echo ""
echo "⚠️  ブラウザで「安全でない」警告が表示されますが、"
echo "   「詳細設定」→「安全でないサイトに進む」で続行してください"