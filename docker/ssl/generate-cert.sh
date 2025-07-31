#!/bin/bash

# 開発環境用SSL証明書生成スクリプト

echo "🔐 開発環境用SSL証明書を生成しています..."

# SSL証明書ディレクトリを作成
mkdir -p docker/ssl

# 秘密鍵を生成
openssl genrsa -out docker/ssl/localhost.key 2048

# 証明書署名要求（CSR）を生成
openssl req -new -key docker/ssl/localhost.key -out docker/ssl/localhost.csr -subj "/C=JP/ST=Tokyo/L=Tokyo/O=Library Dev/CN=localhost"

# 自己署名証明書を生成
openssl x509 -req -days 365 -in docker/ssl/localhost.csr -signkey docker/ssl/localhost.key -out docker/ssl/localhost.crt

# CSRファイルを削除（不要）
rm docker/ssl/localhost.csr

echo "✅ SSL証明書が生成されました:"
echo "   証明書: docker/ssl/localhost.crt"
echo "   秘密鍵: docker/ssl/localhost.key"
echo ""
echo "⚠️  これは開発環境用の自己署名証明書です。"
echo "   ブラウザで「安全でない」警告が表示されますが、"
echo "   「詳細設定」→「localhost にアクセスする（安全ではありません）」"
echo "   をクリックして進んでください。"