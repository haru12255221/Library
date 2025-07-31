# 環境分離ガイドライン

このドキュメントは、開発環境と本番環境を適切に分離するためのガイドラインです。

## 🎯 基本方針

### 環境の定義
- **開発環境（Development）**: 開発者がローカルで作業する環境
- **ステージング環境（Staging）**: 本番環境と同等の設定でテストする環境
- **本番環境（Production）**: 実際にユーザーが使用する環境

### 分離の原則
1. **設定の分離**: 環境ごとに異なる設定ファイルを使用
2. **データの分離**: 環境ごとに独立したデータベースを使用
3. **インフラの分離**: 環境ごとに独立したサーバー・コンテナを使用
4. **デプロイの分離**: 環境ごとに異なるデプロイプロセスを使用

## 🏗️ 実装方法

### 1. 環境変数による分離（推奨）

#### ファイル構成
```
project/
├── .env.local          # 開発環境用設定
├── .env.staging        # ステージング環境用設定
├── .env.production     # 本番環境用設定
├── .env.example        # 設定テンプレート
└── scripts/
    ├── switch-env.sh   # 環境切り替えスクリプト
    └── deploy.sh       # デプロイスクリプト
```

#### 環境別設定例

**開発環境（.env.local）**
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_HOST=localhost
DB_DATABASE=app_development
DB_USERNAME=dev_user
DB_PASSWORD=dev_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
```

**本番環境（.env.production）**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=prod-db-server
DB_DATABASE=app_production
DB_USERNAME=prod_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

MAIL_MAILER=smtp
```

### 2. Dockerによる分離

#### 開発環境用Docker設定
```yaml
# docker-compose.dev.yml
services:
  app:
    build:
      dockerfile: Dockerfile.dev
    ports:
      - "8001:8000"
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    volumes:
      - .:/app  # ホットリロード用
```

#### 本番環境用Docker設定
```yaml
# docker-compose.prod.yml
services:
  app:
    build:
      dockerfile: Dockerfile.prod
    ports:
      - "8000:8000"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    # volumesは最小限に
```

### 3. 設定管理のベストプラクティス

#### 環境切り替えスクリプト
```bash
#!/bin/bash
# scripts/switch-env.sh

ENV=$1
case $ENV in
  "local")
    cp .env.local .env
    echo "開発環境に切り替えました"
    ;;
  "production")
    cp .env.production .env
    echo "本番環境に切り替えました"
    ;;
  *)
    echo "使用方法: $0 [local|production]"
    exit 1
    ;;
esac
```

## 🔒 セキュリティ考慮事項

### 開発環境
- デバッグ情報の表示: 有効
- エラーログ: 詳細表示
- HTTPS: 不要
- 認証: 簡易設定

### 本番環境
- デバッグ情報の表示: 無効
- エラーログ: 最小限
- HTTPS: 必須
- 認証: 厳格な設定

## 📊 データベース分離

### 開発環境
```sql
-- 開発用データベース
CREATE DATABASE app_development;
CREATE USER 'dev_user'@'localhost' IDENTIFIED BY 'dev_password';
GRANT ALL PRIVILEGES ON app_development.* TO 'dev_user'@'localhost';
```

### 本番環境
```sql
-- 本番用データベース
CREATE DATABASE app_production;
CREATE USER 'prod_user'@'%' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON app_production.* TO 'prod_user'@'%';
```

## 🚀 デプロイ戦略

### 開発環境デプロイ
1. ローカルでの開発
2. 自動テスト実行
3. 開発サーバーへのデプロイ

### 本番環境デプロイ
1. ステージング環境でのテスト
2. コードレビュー
3. 本番環境へのデプロイ
4. ヘルスチェック

## 🛠️ 実装チェックリスト

### 初期設定
- [ ] 環境別設定ファイルの作成
- [ ] 環境切り替えスクリプトの作成
- [ ] Docker設定の分離
- [ ] データベースの分離

### セキュリティ
- [ ] 本番環境でのデバッグ無効化
- [ ] 環境変数の暗号化
- [ ] アクセス権限の設定
- [ ] ログ設定の最適化

### 運用
- [ ] デプロイスクリプトの作成
- [ ] バックアップ戦略の策定
- [ ] モニタリング設定
- [ ] エラー通知設定

## 🔄 継続的改善

### 定期的な見直し項目
1. セキュリティ設定の更新
2. パフォーマンス設定の最適化
3. 依存関係の更新
4. ログ設定の見直し

### メトリクス
- デプロイ頻度
- 障害発生率
- 復旧時間
- セキュリティインシデント数