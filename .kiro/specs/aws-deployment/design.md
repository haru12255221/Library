# AWS EC2 Laravel図書館システム デプロイメント設計書

## 概要

Laravel図書館管理システムをAWS EC2上にデプロイし、インターネット経由でアクセス可能なWebアプリケーションとして公開する。

## アーキテクチャ

### システム構成図

```
インターネット
    ↓
[Route 53 DNS]
    ↓
[CloudFront CDN] (オプション)
    ↓
[Application Load Balancer] (オプション)
    ↓
[EC2 Instance]
├── Nginx (Webサーバー)
├── PHP-FPM (PHPプロセス管理)
├── Laravel Application
└── Redis (キャッシュ・セッション)
    ↓
[RDS MySQL] (データベース)
```

### ネットワーク設計

**VPC構成**:
- VPC: 10.0.0.0/16
- パブリックサブネット: 10.0.1.0/24 (EC2用)
- プライベートサブネット: 10.0.2.0/24 (RDS用)

**セキュリティグループ**:
- Web Server SG: HTTP(80), HTTPS(443), SSH(22)
- Database SG: MySQL(3306) - Web Server SGからのみ

## コンポーネント設計

### 1. EC2インスタンス

**インスタンスタイプ**: t3.micro (無料枠) または t3.small
**OS**: Ubuntu 22.04 LTS
**ストレージ**: 20GB gp3

**インストールするソフトウェア**:
```bash
- PHP 8.2
- Nginx
- Composer
- Node.js & NPM
- Redis
- Git
- Certbot (Let's Encrypt)
- UFW (ファイアウォール)
```

### 2. RDS MySQL

**エンジン**: MySQL 8.0
**インスタンスクラス**: db.t3.micro
**ストレージ**: 20GB gp2
**バックアップ**: 7日間保持
**Multi-AZ**: 本番環境では有効

### 3. Nginx設定

**主な機能**:
- リバースプロキシ
- SSL終端
- 静的ファイル配信
- Gzip圧縮
- セキュリティヘッダー

**設定ファイル構造**:
```
/etc/nginx/
├── nginx.conf (メイン設定)
├── sites-available/
│   └── library-app (アプリ設定)
└── sites-enabled/
    └── library-app -> ../sites-available/library-app
```

### 4. PHP-FPM設定

**プロセス管理**:
- プロセス数: 動的調整
- メモリ制限: 256MB
- 実行時間制限: 60秒

### 5. Laravel環境設定

**本番環境用.env**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=rds-endpoint
DB_PORT=3306
DB_DATABASE=library
DB_USERNAME=library_user
DB_PASSWORD=secure_password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## データモデル

### データベース設計

既存のLaravelマイグレーションを使用:
- users テーブル
- books テーブル
- loans テーブル

**インデックス最適化**:
```sql
-- 検索パフォーマンス向上
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_loans_user_id ON loans(user_id);
CREATE INDEX idx_loans_book_id ON loans(book_id);
```

## エラーハンドリング

### ログ管理

**ログファイル**:
- Nginx: `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- PHP: `/var/log/php8.2-fpm.log`
- Laravel: `/var/www/html/storage/logs/laravel.log`

**ログローテーション**:
```bash
# /etc/logrotate.d/laravel
/var/www/html/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### エラーページ

**カスタムエラーページ**:
- 404: 書籍が見つからない場合
- 500: サーバーエラー
- 503: メンテナンス中

## テスト戦略

### デプロイ前テスト

1. **ローカル環境での最終確認**
   - 全機能の動作確認
   - データベースマイグレーション確認
   - 依存関係の確認

2. **ステージング環境テスト**
   - 本番環境と同じ構成でテスト
   - パフォーマンステスト
   - セキュリティテスト

### 本番環境テスト

1. **デプロイ後確認**
   - アプリケーション起動確認
   - データベース接続確認
   - SSL証明書確認

2. **機能テスト**
   - ユーザー登録・ログイン
   - 書籍登録・検索
   - 貸出・返却機能

## セキュリティ設計

### サーバーセキュリティ

**SSH設定**:
```bash
# /etc/ssh/sshd_config
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
Port 22
```

**ファイアウォール設定**:
```bash
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 'Nginx Full'
ufw enable
```

### アプリケーションセキュリティ

**Laravel設定**:
- CSRF保護有効
- XSS保護有効
- SQL injection対策（Eloquent ORM使用）
- セッション暗号化

**Nginx セキュリティヘッダー**:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self'" always;
```

## パフォーマンス最適化

### キャッシュ戦略

**Laravel キャッシュ**:
- 設定キャッシュ: `php artisan config:cache`
- ルートキャッシュ: `php artisan route:cache`
- ビューキャッシュ: `php artisan view:cache`

**Redis キャッシュ**:
- セッション管理
- データベースクエリキャッシュ
- アプリケーションキャッシュ

### 静的ファイル最適化

**アセット最適化**:
```bash
npm run production  # CSS/JS圧縮
```

**Nginx 静的ファイル配信**:
```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## 監視とメンテナンス

### ヘルスチェック

**アプリケーション監視**:
- `/health` エンドポイント作成
- データベース接続確認
- Redis接続確認

### バックアップ戦略

**データベースバックアップ**:
- RDS自動バックアップ: 7日間
- 手動スナップショット: 月次

**アプリケーションバックアップ**:
- コードはGitで管理
- アップロードファイル: S3同期

### デプロイメント手順

**継続的デプロイ**:
1. Gitプッシュ
2. サーバーでプル
3. 依存関係更新
4. マイグレーション実行
5. キャッシュクリア
6. サービス再起動

## 運用設計

### ドメイン設定

**Route 53設定**:
- Aレコード: EC2 Elastic IP
- CNAMEレコード: www → apex
- MXレコード: メール設定（オプション）

### SSL証明書

**Let's Encrypt設定**:
```bash
certbot --nginx -d your-domain.com -d www.your-domain.com
```

**自動更新**:
```bash
# crontab
0 12 * * * /usr/bin/certbot renew --quiet
```

### メンテナンス

**定期メンテナンス**:
- システムアップデート: 月次
- ログクリーンアップ: 週次
- パフォーマンス監視: 日次

**緊急時対応**:
- サービス停止手順
- ロールバック手順
- 障害通知設定