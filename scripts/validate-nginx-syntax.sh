#!/bin/bash

# Nginx設定の構文チェック（Docker環境外）
# upstreamの名前解決エラーを無視して構文のみをチェック

echo "🔧 Nginx設定の構文チェックを実行中..."

# 一時的な設定ファイルを作成（upstreamを削除）
temp_config=$(mktemp)
cat docker/nginx/default.prod.conf | sed '/upstream app_backend/,/}/d' | sed 's/http:\/\/app_backend/http:\/\/127.0.0.1:8000/g' > $temp_config

# 構文チェック実行
docker run --rm -v $temp_config:/etc/nginx/conf.d/default.conf nginx:alpine nginx -t

result=$?

# 一時ファイル削除
rm $temp_config

if [ $result -eq 0 ]; then
    echo "✅ Nginx設定の構文チェックが成功しました"
else
    echo "❌ Nginx設定に構文エラーがあります"
fi

exit $result