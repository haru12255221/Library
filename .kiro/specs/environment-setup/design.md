# 環境分離システム 設計書

## 概要

図書館管理システムの開発効率向上と本番環境の安全性確保のため、環境分離システムを設計します。開発環境（Library/）と本番環境（laravel-app/）を明確に分離し、それぞれに最適化された設定とインフラを提供します。

## アーキテクチャ

### システム構成
```
Library/                          # 開発環境ルート
├── laravel-app/                  # 本番環境ルート
│   ├── docker-compose.yml       # 本番用Docker設定
│   ├── Dockerfile.production     # 本番用Dockerfile
│   └── .env.production          # 本番用環境変数
├── docker-compose.yml            # 開発用Docker設定
├── Dockerfile                    # 開発用Dockerfile
├── scripts/
│   ├── dev-setup.sh             # 開発環境セットアップ
│   ├── prod-setup.sh            # 本番環境セットアップ
│   └── switch-env.sh            # 環境切り替え
└── .kiro/steering/
    └── environment-separation.md # 環境分離ガイドライン
```

### 環境分離戦略

#### 1. ディレクトリベース分離
- **開発環境**: `Library/` ディレクトリから実行
- **本番環境**: `Library/laravel-app/` ディレクトリから実行

#### 2. ポートベース分離
- **開発環境**: 8001番ポート
- **本番環境**: 8000番ポート

#### 3. 設定ファイル分離
- **開発環境**: `.env.dev`
- **本番環境**: `.env.production`

## コンポーネント設計

### 1. 開発環境コンポーネント

#### Docker構成
```yaml
services:
  app:
    build: ./                     # 開発用Dockerfile使用
    ports:
      - "8001:8000"              # 開発環境ポート
      - "5174:5174"              # Vite開発サーバー
    volumes:
      - .:/workdir               # ホットリロード用
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
```

#### 特徴
- ホットリロード機能
- デバッグツール（Xdebug）
- 開発用依存関係
- ローカルファイル同期

### 2. 本番環境コンポーネント

#### Docker構成
```yaml
services:
  app:
    build:
      dockerfile: Dockerfile.production
    ports:
      - "8000:80"                # 本番環境ポート
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
```

#### 特徴
- 最適化されたイメージ
- セキュリティ強化
- パフォーマンス最適化
- 最小限の依存関係

### 3. 自動化スクリプト

#### 開発環境セットアップ（dev-setup.sh）
```bash
#!/bin/bash
# 1. 開発用.envファイル設定
# 2. Dockerコンテナ起動
# 3. データベースマイグレーション
# 4. シーダー実行
# 5. 成功メッセージ表示
```

#### 本番環境セットアップ（prod-setup.sh）
```bash
#!/bin/bash
# 1. 本番用.envファイル設定
# 2. Dockerコンテナ起動
# 3. アプリケーションキー生成
# 4. データベースマイグレーション
# 5. キャッシュ最適化
# 6. セキュリティチェック
```

## データモデル

### 環境設定データ

#### 開発環境設定
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001
DB_HOST=db
DB_DATABASE=library
CACHE_DRIVER=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp
MAIL_HOST=mailpit
```

#### 本番環境設定
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_HOST=prod-db-server
DB_DATABASE=library_production
CACHE_DRIVER=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-domain.com
```

## セキュリティ設計

### 開発環境セキュリティ
- デバッグ情報表示: 有効
- エラーログ: 詳細表示
- HTTPS: 不要
- 認証: 簡易設定

### 本番環境セキュリティ
- デバッグ情報表示: 無効
- エラーログ: 最小限
- HTTPS: 必須
- 認証: 厳格な設定
- セッション暗号化: 有効

## パフォーマンス設計

### 開発環境
- ホットリロード優先
- デバッグツール有効
- キャッシュ無効化

### 本番環境
- 応答速度優先
- キャッシュ最適化
- 静的ファイル圧縮

## エラーハンドリング

### 環境切り替えエラー
- 設定ファイル不存在
- ポート競合
- Docker起動失敗

### データベース接続エラー
- 接続情報不正
- データベース不存在
- 権限不足

### セットアップエラー
- 依存関係不足
- 権限問題
- ネットワーク問題

## テスト戦略

### 環境分離テスト
1. 開発環境起動テスト
2. 本番環境起動テスト
3. 環境切り替えテスト
4. ポート分離テスト

### 設定テスト
1. 環境変数読み込みテスト
2. データベース接続テスト
3. キャッシュ動作テスト
4. メール送信テスト

### セキュリティテスト
1. デバッグ情報漏洩テスト
2. エラー情報隠蔽テスト
3. 認証・認可テスト
4. セッション暗号化テスト

## 運用設計

### デプロイフロー

#### 開発環境
```
コード変更 → 自動リロード → テスト → コミット
```

#### 本番環境
```
コミット → CI/CD → ステージング → 本番デプロイ
```

### モニタリング

#### 開発環境
- アプリケーションログ
- デバッグ情報
- パフォーマンス情報

#### 本番環境
- エラーログ
- アクセスログ
- システムメトリクス
- セキュリティログ

## 拡張性設計

### 新環境追加
- ステージング環境
- テスト環境
- デモ環境

### 設定管理
- 環境変数の暗号化
- 設定の外部化
- 動的設定変更