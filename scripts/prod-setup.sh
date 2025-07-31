#!/bin/bash

# 本番環境セットアップスクリプト
# laravel-appディレクトリから実行

echo "🏭 本番環境をセットアップしています..."

# laravel-appディレクトリに移動
cd laravel-app

# 本番用.envファイルの確認
if [ ! -f ".env.production" ]; then
    echo "❌ .env.productionファイルが見つかりません"
    echo "先に本番用設定ファイルを作成してください"
    exit 1
fi

# 本番用.envファイルをコピー
cp .env.production .env
echo "✅ 本番用.envファイルを設定しました"

# 既存のコンテナを停止
echo "🛑 既存のコンテナを停止しています..."
docker compose down

# 本番用コンテナをビルド・起動
echo "🔨 本番用コンテナをビルド・起動しています..."
docker compose up -d --build

# コンテナの起動を待つ
echo "⏳ コンテナの起動を待っています..."
sleep 15

# アプリケーションキー生成（初回のみ）
echo "🔑 アプリケーションキーを生成しています..."
docker compose exec app php artisan key:generate --force

# 本番用マイグレーション
echo "📊 本番用データベースマイグレーションを実行しています..."
docker compose exec app php artisan migrate --force

# キャッシュクリア・最適化
echo "⚡ キャッシュクリア・最適化を実行しています..."
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
docker compose exec app php artisan optimize

echo ""
echo "🎉 本番環境のセットアップが完了しました！"
echo ""
echo "📍 アクセス情報:"
echo "   アプリケーション: http://localhost:8000"
echo ""
echo "⚠️  本番環境チェックリスト:"
echo "   □ SSL証明書の設定"
echo "   □ データベース接続の確認"
echo "   □ メール送信の確認"
echo "   □ セキュリティ設定の確認"
echo "   □ バックアップの設定"