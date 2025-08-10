#!/bin/bash

# vantanlib.com Nginx設定テストスクリプト
# このスクリプトはNginx設定の妥当性をテストします

set -e

# 色設定
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ログ関数
log() {
    echo -e "${GREEN}[$(date +'%H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[$(date +'%H:%M:%S')] ERROR: $1${NC}"
}

warning() {
    echo -e "${YELLOW}[$(date +'%H:%M:%S')] WARNING: $1${NC}"
}

info() {
    echo -e "${BLUE}[$(date +'%H:%M:%S')] INFO: $1${NC}"
}

log "🔍 vantanlib.com Nginx設定テストを開始します..."

# 設定ファイルの存在確認
if [ ! -f "docker/nginx/default.prod.conf" ]; then
    error "Nginx設定ファイルが見つかりません: docker/nginx/default.prod.conf"
    exit 1
fi

log "✅ Nginx設定ファイルが見つかりました"

# Docker Composeファイルの確認
if [ ! -f "laravel-app/docker-compose.prod.yml" ]; then
    error "Docker Compose設定ファイルが見つかりません: laravel-app/docker-compose.prod.yml"
    exit 1
fi

log "✅ Docker Compose設定ファイルが見つかりました"

# Nginx設定の構文チェック
log "🔧 Nginx設定の構文チェックを実行中..."

# 一時的なNginxコンテナで設定をテスト
docker run --rm -v $(pwd)/docker/nginx/default.prod.conf:/etc/nginx/conf.d/default.conf nginx:alpine nginx -t

if [ $? -eq 0 ]; then
    log "✅ Nginx設定の構文チェックが成功しました"
else
    error "❌ Nginx設定の構文エラーが検出されました"
    exit 1
fi

# 設定内容の確認
log "📋 設定内容の確認..."

# vantanlib.com設定の確認
if grep -q "vantanlib.com" docker/nginx/default.prod.conf; then
    log "✅ vantanlib.comドメイン設定が確認されました"
else
    error "❌ vantanlib.comドメイン設定が見つかりません"
    exit 1
fi

# HTTPS設定の確認
if grep -q "listen 443 ssl http2" docker/nginx/default.prod.conf; then
    log "✅ HTTPS + HTTP/2設定が確認されました"
else
    error "❌ HTTPS + HTTP/2設定が見つかりません"
    exit 1
fi

# SSL証明書パスの確認
if grep -q "/etc/letsencrypt/live/vantanlib.com" docker/nginx/default.prod.conf; then
    log "✅ Let's Encrypt証明書パス設定が確認されました"
else
    error "❌ Let's Encrypt証明書パス設定が見つかりません"
    exit 1
fi

# セキュリティヘッダーの確認
security_headers=(
    "Strict-Transport-Security"
    "X-Frame-Options"
    "X-Content-Type-Options"
    "Content-Security-Policy"
)

for header in "${security_headers[@]}"; do
    if grep -q "$header" docker/nginx/default.prod.conf; then
        log "✅ セキュリティヘッダー '$header' が設定されています"
    else
        warning "⚠️  セキュリティヘッダー '$header' が見つかりません"
    fi
done

# CSP設定でカメラ機能対応の確認
if grep -q "mediastream:" docker/nginx/default.prod.conf; then
    log "✅ カメラ機能対応のCSP設定が確認されました"
else
    warning "⚠️  カメラ機能対応のCSP設定が見つかりません"
fi

# Google Books API対応の確認
if grep -q "googleapis.com" docker/nginx/default.prod.conf; then
    log "✅ Google Books API対応のCSP設定が確認されました"
else
    warning "⚠️  Google Books API対応のCSP設定が見つかりません"
fi

# 静的ファイル最適化の確認
if grep -q "expires.*1y" docker/nginx/default.prod.conf; then
    log "✅ 静的ファイル最適化設定が確認されました"
else
    warning "⚠️  静的ファイル最適化設定が見つかりません"
fi

# Gzip圧縮設定の確認
if grep -q "gzip on" docker/nginx/default.prod.conf; then
    log "✅ Gzip圧縮設定が確認されました"
else
    warning "⚠️  Gzip圧縮設定が見つかりません"
fi

# レート制限設定の確認
if grep -q "limit_req_zone" docker/nginx/default.prod.conf; then
    log "✅ レート制限設定が確認されました"
else
    warning "⚠️  レート制限設定が見つかりません"
fi

# www -> non-www リダイレクトの確認
if grep -q "www.vantanlib.com.*301.*vantanlib.com" docker/nginx/default.prod.conf; then
    log "✅ www -> non-www リダイレクト設定が確認されました"
else
    warning "⚠️  www -> non-www リダイレクト設定が見つかりません"
fi

# HTTP -> HTTPS リダイレクトの確認
if grep -q "return 301 https://vantanlib.com" docker/nginx/default.prod.conf; then
    log "✅ HTTP -> HTTPS リダイレクト設定が確認されました"
else
    error "❌ HTTP -> HTTPS リダイレクト設定が見つかりません"
    exit 1
fi

log "🎉 vantanlib.com Nginx設定テストが完了しました！"

# 設定サマリーの表示
info "📊 設定サマリー:"
info "   ドメイン: vantanlib.com (www.vantanlib.com -> vantanlib.com)"
info "   プロトコル: HTTPS (HTTP/2対応)"
info "   SSL証明書: Let's Encrypt"
info "   セキュリティ: 強化されたヘッダー + CSP"
info "   最適化: Gzip圧縮 + 静的ファイルキャッシュ"
info "   機能対応: HTML5カメラ + Google Books API"

log "✅ 設定は本番環境デプロイの準備ができています！"