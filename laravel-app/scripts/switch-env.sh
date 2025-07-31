#!/bin/bash

# 環境切り替えスクリプト
# 使用方法: ./scripts/switch-env.sh local|production

if [ $# -eq 0 ]; then
    echo "使用方法: $0 [local|production]"
    exit 1
fi

ENV=$1

case $ENV in
    "local")
        echo "開発環境に切り替えています..."
        cp .env.local .env
        echo "✅ .env.local を .env にコピーしました"
        
        # Docker Composeファイルも切り替え
        if [ -f "docker-compose.local.yml" ]; then
            cp docker-compose.local.yml docker-compose.yml
            echo "✅ docker-compose.local.yml を docker-compose.yml にコピーしました"
        fi
        
        echo "🚀 開発環境の準備完了"
        echo "次のコマンドでコンテナを起動してください:"
        echo "docker compose up -d --build"
        ;;
        
    "production")
        echo "本番環境に切り替えています..."
        cp .env.production .env
        echo "✅ .env.production を .env にコピーしました"
        
        # Docker Composeファイルも切り替え
        if [ -f "docker-compose.production.yml" ]; then
            cp docker-compose.production.yml docker-compose.yml
            echo "✅ docker-compose.production.yml を docker-compose.yml にコピーしました"
        fi
        
        echo "⚠️  本番環境設定に切り替わりました"
        echo "本番環境では以下を確認してください:"
        echo "- APP_KEYの設定"
        echo "- データベース接続情報"
        echo "- SSL証明書の設定"
        echo "- セキュリティ設定の確認"
        ;;
        
    *)
        echo "❌ 無効な環境です: $ENV"
        echo "使用可能な環境: local, production"
        exit 1
        ;;
esac