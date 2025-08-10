#!/bin/bash

# vantanlib.com SSL証明書自動更新スクリプト
# Cronで毎日午前2時に実行

set -e

LOG_FILE="/var/log/ssl-renewal.log"
EMAIL="admin@vantanlib.com"
COMPOSE_FILE="laravel-app/docker-compose.prod.yml"

echo "$(date): 🔄 vantanlib.com SSL証明書の更新を確認しています..." | tee -a $LOG_FILE

# 証明書更新チェック（dry-runで事前確認）
if docker compose -f $COMPOSE_FILE exec certbot certbot renew --dry-run --quiet; then
    echo "$(date): ✅ 証明書更新の事前チェックが成功しました" | tee -a $LOG_FILE
    
    # 実際の更新実行
    if docker compose -f $COMPOSE_FILE exec certbot certbot renew --quiet; then
        # Nginx設定リロード
        if docker compose -f $COMPOSE_FILE exec nginx nginx -s reload; then
            echo "$(date): ✅ vantanlib.com SSL証明書の更新が完了しました" | tee -a $LOG_FILE
            
            # 成功通知
            echo "vantanlib.com SSL証明書が正常に更新されました。" | mail -s "SSL証明書更新成功" $EMAIL
        else
            echo "$(date): ❌ Nginxリロードに失敗しました" | tee -a $LOG_FILE
        fi
    else
        echo "$(date): ❌ vantanlib.com SSL証明書の更新に失敗しました" | tee -a $LOG_FILE
        # 失敗通知
        echo "vantanlib.com SSL証明書の更新に失敗しました。ログを確認してください。" | mail -s "SSL証明書更新失敗" $EMAIL
    fi
else
    echo "$(date): ⚠️ vantanlib.com SSL証明書の更新前チェックに失敗しました" | tee -a $LOG_FILE
fi

# 証明書の有効期限確認
if [ -f "/etc/letsencrypt/live/vantanlib.com/fullchain.pem" ]; then
    CERT_EXPIRY=$(openssl x509 -enddate -noout -in /etc/letsencrypt/live/vantanlib.com/fullchain.pem | cut -d= -f2)
    echo "$(date): 📅 現在の証明書有効期限: $CERT_EXPIRY" | tee -a $LOG_FILE
fi