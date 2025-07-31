# 図書館管理システム

Laravel + Docker で構築された図書館管理システムです。

## 🏗️ プロジェクト構造

```
Library/
├── laravel-app/          # 本番環境用（ポート: 8000）
│   ├── docker-compose.yml
│   ├── Dockerfile.production
│   └── .env.production
├── docker-compose.yml    # 開発環境用（ポート: 8001）
├── Dockerfile           # 開発環境用
├── scripts/
│   ├── dev-setup.sh     # 開発環境セットアップ
│   └── prod-setup.sh    # 本番環境セットアップ
└── README.md
```

## 🚀 開発環境での作業

### 初回セットアップ

```bash
# Libraryディレクトリで実行
./scripts/dev-setup.sh
```

### 手動セットアップ

```bash
# 1. 開発用.envファイルを設定
cp laravel-app/.env.dev laravel-app/.env

# 2. コンテナ起動
docker compose up -d --build

# 3. マイグレーション実行
docker compose exec app php artisan migrate

# 4. シーダー実行
docker compose exec app php artisan db:seed
```

### アクセス情報（開発環境）

- **アプリケーション**: http://localhost:8001
- **メール確認（Mailpit）**: http://localhost:8026
- **MySQL**: localhost:3306
- **Redis**: localhost:6380

## 🏭 本番環境でのデプロイ

### 初回セットアップ

```bash
# laravel-appディレクトリで実行
cd laravel-app
../scripts/prod-setup.sh
```

### 手動セットアップ

```bash
cd laravel-app

# 1. 本番用設定ファイルを編集
nano .env.production

# 2. 本番用.envファイルを設定
cp .env.production .env

# 3. コンテナ起動
docker compose up -d --build

# 4. アプリケーションキー生成
docker compose exec app php artisan key:generate --force

# 5. マイグレーション実行
docker compose exec app php artisan migrate --force

# 6. 最適化
docker compose exec app php artisan optimize
```

### アクセス情報（本番環境）

- **アプリケーション**: http://localhost:8000

## 🔧 便利なコマンド

### 開発環境

```bash
# ログ確認
docker compose logs -f app

# コンテナ停止
docker compose down

# コンテナ再起動
docker compose restart

# Artisanコマンド実行
docker compose exec app php artisan [command]

# Composerコマンド実行
docker compose exec app composer [command]

# NPMコマンド実行
docker compose exec app npm [command]
```

### 本番環境

```bash
cd laravel-app

# キャッシュクリア
docker compose exec app php artisan cache:clear

# 設定キャッシュ
docker compose exec app php artisan config:cache

# ルートキャッシュ
docker compose exec app php artisan route:cache

# ビューキャッシュ
docker compose exec app php artisan view:cache
```

## 📋 機能一覧

- ✅ ユーザー認証・認可
- ✅ 書籍管理（CRUD）
- ✅ 貸出・返却管理
- ✅ 管理者機能
- ✅ 書籍検索
- ✅ Google Books API連携
- ✅ レスポンシブデザイン

## 🛠️ 技術スタック

- **Backend**: Laravel 11
- **Frontend**: Blade Templates + Alpine.js + Tailwind CSS
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Mail**: Mailpit（開発環境）
- **Container**: Docker + Docker Compose

## 📝 環境変数

### 開発環境（.env.dev）
- APP_ENV=local
- APP_DEBUG=true
- DB_HOST=db
- REDIS_HOST=redis

### 本番環境（.env.production）
- APP_ENV=production
- APP_DEBUG=false
- 外部データベース・Redis設定

## 🔒 セキュリティ

- CSRF保護
- XSS対策
- SQLインジェクション対策
- 管理者権限チェック
- セッション暗号化（本番環境）

## 📞 サポート

問題が発生した場合は、以下を確認してください：

1. Dockerコンテナの状態: `docker compose ps`
2. ログの確認: `docker compose logs -f app`
3. 環境変数の設定: `.env`ファイルの内容
4. ポートの競合: 他のサービスとのポート重複