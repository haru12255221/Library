#!/bin/bash

# 開発環境セットアップスクリプト
# Libraryディレクトリから実行

echo "🚀 開発環境をセットアップしています..."

# 開発用.envファイルをコピー
if [ -f "laravel-app/.env.local" ]; then
    cp laravel-app/.env.local laravel-app/.env
    echo "✅ 開発用.envファイルを設定しました"
else
    echo "❌ .env.localファイルが見つかりません"
    exit 1
fi

# 既存のコンテナを停止
echo "🛑 既存のコンテナを停止しています..."
docker compose down

# コンテナをビルド・起動
echo "🔨 コンテナをビルド・起動しています..."
docker compose up -d --build

# コンテナの起動を待つ
echo "⏳ コンテナの起動を待っています..."
sleep 10

# マイグレーション実行
echo "📊 データベースマイグレーションを実行しています..."
docker compose exec app php artisan migrate

# シーダー実行
echo "🌱 シーダーを実行しています..."
docker compose exec app php artisan db:seed

echo ""
echo "🎉 開発環境のセットアップが完了しました！"
echo ""
echo "📍 アクセス情報:"
echo "   アプリケーション: http://localhost:8001"
echo "   メール確認:       http://localhost:8026"
echo "   MySQL:           localhost:3306"
echo "   Redis:           localhost:6380"
echo ""
echo "🔧 便利なコマンド:"
echo "   ログ確認:         docker compose logs -f app"
echo "   コンテナ停止:     docker compose down"
echo "   コンテナ再起動:   docker compose restart"