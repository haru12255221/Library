# Laravel図書館管理システム 実装完了レポート

## 🎯 プロジェクト概要

**実際に完了したデプロイメント作業の記録**

Laravel + Docker + AWS EC2 を使用した図書館管理システムの構築とデプロイメントが完了しました。
このドキュメントは実際の作業手順、発生した問題、解決方法を記録したものです。

> **注意**: 計画段階のタスクは `.kiro/specs/` ディレクトリに、実装完了の記録はこのファイルに記載されています。

### 主要技術スタック
- **Backend**: Laravel 12.20.0 (PHP 8.4)
- **Frontend**: Vite + Tailwind CSS + Alpine.js
- **Database**: MySQL 8.3
- **Infrastructure**: AWS EC2 (Ubuntu 22.04 LTS)
- **Containerization**: Docker + Docker Compose
- **CI/CD**: GitHub Actions

---

## 🏗️ インフラストラクチャ構築

### 1. AWS EC2インスタンス作成

#### インスタンス仕様
```
- AMI: Ubuntu Server 22.04 LTS (HVM), SSD Volume Type
- インスタンスタイプ: t2.micro (無料利用枠)
- ストレージ: 8GB gp3 SSD
- キーペア: laravel-app-key.pem (RSA, .pem形式)
```

#### セキュリティグループ設定
```
インバウンドルール:
- SSH (22): 自分のIPアドレスのみ
- HTTP (80): 0.0.0.0/0
- HTTPS (443): 0.0.0.0/0
- カスタムTCP (8001): 0.0.0.0/0 (Laravel アプリ用)
```

#### SSH接続設定
```bash
# キーファイルの権限設定
chmod 400 ~/.ssh/laravel-app-key.pem

# SSH接続
ssh -i ~/.ssh/laravel-app-key.pem ubuntu@13.113.118.30
```

### 2. サーバー環境構築

#### システムアップデート
```bash
sudo apt update && sudo apt upgrade -y
```

#### Docker環境構築
```bash
# Dockerインストール
sudo apt install -y docker.io docker-compose

# Dockerサービス開始・自動起動設定
sudo systemctl start docker
sudo systemctl enable docker

# ユーザーをdockerグループに追加
sudo usermod -aG docker ubuntu
```

---

## 🐳 Docker環境構築

### 1. プロジェクト構造
```
Library/
├── .github/workflows/ci.yml
├── .kiro/
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
└── laravel-app/
    ├── app/
    ├── database/
    ├── resources/
    ├── public/
    ├── composer.json
    ├── package.json
    └── .env
```

### 2. Dockerfile設定
```dockerfile
FROM php:8.4
WORKDIR /workdir

# Composer設定
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME="/opt/composer"
ENV PATH="$PATH:/opt/composer/vendor/bin"

# 必要なパッケージインストール
RUN apt-get update && apt-get install -y zip git unzip

# PHP拡張インストール
RUN docker-php-ext-install pdo_mysql

# Node.js 18インストール
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get update && apt-get install -y nodejs

# アプリケーションファイルコピー
COPY . .
WORKDIR /workdir/laravel-app

# 依存関係インストール
RUN composer install
RUN npm install
RUN npm run build

# サーバー起動
CMD [ "php", "artisan", "serve", "--host", "0.0.0.0" ]
EXPOSE 8000
```

### 3. docker-compose.yml設定
```yaml
services:
  app:
    build: ./
    volumes:
      - .:/workdir
    ports:
      - "8001:8000"
      - "5174:5174"
    depends_on:
      - db

  db:
    image: mysql:8.3
    volumes:
      - ./laravel-app/mysql_data:/var/lib/mysql
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: library
      MYSQL_USER: user
      MYSQL_PASSWORD: password
    ports:
      - "3306:3306"
```

### 4. .dockerignore設定
```
laravel-app/mysql_data
laravel-app/node_modules
laravel-app/vendor
.git
.github
.kiro
```

---

## 🚀 アプリケーション設定

### 1. Laravel環境設定

#### .env設定
```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=library
DB_USERNAME=user
DB_PASSWORD=password

# Redis無効化（Redisエラー回避）
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

#### データベースマイグレーション
```bash
# コンテナ内で実行
php artisan migrate
```

### 2. Viteビルド設定

#### Node.js依存関係インストール
```bash
# コンテナ内で実行
npm install
npm run build
```

#### 生成されるファイル
```
public/build/
├── manifest.json
└── assets/
    ├── app-[hash].js
    └── app-[hash].css
```

---

## 👥 ユーザー管理システム

### 1. データベース構造

#### usersテーブル
```sql
- id: bigint (Primary Key)
- name: varchar(255)
- email: varchar(255) UNIQUE
- email_verified_at: timestamp
- password: varchar(255)
- role: tinyint (1=admin, 2=user, default=2)
- remember_token: varchar(100)
- created_at: timestamp
- updated_at: timestamp
```

### 2. テストユーザー作成

#### Laravel Tinkerで作成
```php
// 管理者ユーザー
$admin = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@library.com',
    'password' => bcrypt('password'),
    'role' => 1
]);

// 一般ユーザー
$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'user@library.com',
    'password' => bcrypt('password'),
    'role' => 2
]);

// メール認証完了
$admin->email_verified_at = now();
$admin->save();
$user->email_verified_at = now();
$user->save();
```

### 3. ログイン情報
```
管理者:
- Email: admin@library.com
- Password: password

一般ユーザー:
- Email: user@library.com
- Password: password
```

---

## 🔧 トラブルシューティング

### 1. 主要なエラーと解決方法

#### Redisエラー
```
エラー: Class "Redis" not found
解決: .envでRedis設定を無効化
```

#### Vite Manifestエラー
```
エラー: Vite manifest not found
解決: npm run buildでアセットビルド
```

#### データベース接続エラー
```
エラー: getaddrinfo for mysql failed
解決: .envのDB_HOSTを'db'に変更
```

#### 権限エラー
```
エラー: Permission denied mysql_data
解決: sudo rm -rf laravel-app/mysql_data
```

### 2. デバッグコマンド
```bash
# コンテナ状態確認
docker-compose ps

# ログ確認
docker-compose logs app

# コンテナ内アクセス
docker exec -it library_app_1 bash

# データベース接続テスト
php artisan tinker
\DB::connection()->getPdo()
```

---

## 🔄 CI/CD パイプライン

### 1. 現在のCI設定 (.github/workflows/ci.yml)

#### 自動実行内容
```yaml
- PHP 8.3環境セットアップ
- Composer依存関係インストール
- Node.js 18環境セットアップ
- npm依存関係インストール
- Viteアセットビルド
- データベースマイグレーション
- テスト実行
- 結果通知
```

#### トリガー条件
```yaml
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]
```

### 2. 自動デプロイ追加設定

#### GitHub Secrets設定
```
EC2_HOST: 13.113.118.30
EC2_SSH_KEY: (SSH秘密鍵の内容)
```

#### デプロイジョブ追加
```yaml
deploy:
  needs: test
  runs-on: ubuntu-latest
  if: github.ref == 'refs/heads/main' && needs.test.result == 'success'
  
  steps:
  - name: Deploy to EC2
    uses: appleboy/ssh-action@v0.1.5
    with:
      host: ${{ secrets.EC2_HOST }}
      username: ubuntu
      key: ${{ secrets.EC2_SSH_KEY }}
      script: |
        cd /home/ubuntu/Library
        git pull origin main
        docker-compose down
        docker-compose up -d --build
```

---

## 🌐 アクセス情報

### 1. アプリケーションURL
```
本番環境: http://13.113.118.30:8001
ログインページ: http://13.113.118.30:8001/login
```

### 2. サーバー接続
```bash
ssh -i ~/.ssh/laravel-app-key.pem ubuntu@13.113.118.30
```

### 3. Docker操作
```bash
# アプリケーション起動
docker-compose up -d

# アプリケーション停止
docker-compose down

# 再ビルド
docker-compose build --no-cache

# ログ確認
docker-compose logs -f app
```

---

## 📋 運用手順

### 1. 開発フロー
```bash
# 1. ローカル開発
git checkout -b feature/new-feature
# 開発作業
git add .
git commit -m "新機能追加"
git push origin feature/new-feature

# 2. プルリクエスト作成・マージ
# GitHub上でPR作成 → CI実行 → マージ

# 3. 本番デプロイ（自動）
# mainブランチへのマージで自動デプロイ実行
```

### 2. 手動デプロイ（緊急時）
```bash
# EC2にSSH接続
ssh -i ~/.ssh/laravel-app-key.pem ubuntu@13.113.118.30

# アプリケーション更新
cd Library
git pull origin main
docker-compose down
docker-compose up -d --build
```

---

## 🎯 今後の拡張予定

### 1. 機能追加
- [ ] 書籍検索機能強化
- [ ] 貸出期限管理
- [ ] メール通知機能
- [ ] レポート機能

### 2. インフラ改善
- [ ] SSL証明書設定
- [ ] ロードバランサー導入
- [ ] データベースバックアップ自動化
- [ ] モニタリング設定

### 3. セキュリティ強化
- [ ] WAF設定
- [ ] セキュリティヘッダー追加
- [ ] 脆弱性スキャン自動化

---

## 📞 サポート情報

### 重要なファイル
- `Dockerfile`: コンテナ設定
- `docker-compose.yml`: サービス構成
- `.env`: 環境変数
- `.github/workflows/ci.yml`: CI/CD設定

### 緊急時の連絡先
- EC2インスタンスID: i-xxxxxxxxx
- セキュリティグループID: sg-xxxxxxxxx
- キーペア名: laravel-app-key

---

**🎉 Laravel図書館管理システムのAWSデプロイメントが完了しました！**