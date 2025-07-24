# CI/CD パイプライン設計書

## 概要

Laravel図書館管理システムの継続的な開発・デプロイメントを支援するCI/CDパイプラインの設計。GitHub Actions、AWS CodePipeline、Dockerを活用した現代的なDevOpsワークフローを構築する。

## アーキテクチャ

### CI/CDパイプライン全体図

```
開発者 → GitHub → GitHub Actions → AWS CodePipeline → EC2/ECS
  ↓         ↓           ↓              ↓              ↓
ローカル   コード管理   CI/テスト      CD/デプロイ     本番環境
  ↓         ↓           ↓              ↓              ↓
feature   PR作成    自動テスト      ステージング    本番サーバー
branch    レビュー   品質チェック    環境テスト      監視・ログ
```

### 環境構成

**開発環境 (Development)**:
- ローカル開発環境（Docker Compose）
- 個人用データベース
- ホットリロード対応

**ステージング環境 (Staging)**:
- 本番環境と同じ構成
- AWS EC2 t3.small
- テスト用データベース
- 自動デプロイ対象

**本番環境 (Production)**:
- AWS EC2 t3.medium以上
- RDS Multi-AZ
- 手動承認後デプロイ
- 監視・アラート完備

## コンポーネント設計

### 1. GitHub Actions ワークフロー

**CI ワークフロー (.github/workflows/ci.yml)**:
```yaml
name: CI Pipeline
on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: library_test
        ports:
          - 3306:3306
      redis:
        image: redis:7
        ports:
          - 6379:6379

    steps:
      - name: Checkout code
      - name: Setup PHP
      - name: Install dependencies
      - name: Run tests
      - name: Code quality checks
      - name: Security scan
```

**CD ワークフロー (.github/workflows/cd.yml)**:
```yaml
name: CD Pipeline
on:
  push:
    branches: [ main ]
  workflow_run:
    workflows: ["CI Pipeline"]
    types: [completed]

jobs:
  deploy-staging:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to staging
      - name: Run integration tests
      - name: Notify team

  deploy-production:
    needs: deploy-staging
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    environment: production
    steps:
      - name: Manual approval
      - name: Deploy to production
      - name: Health check
      - name: Rollback on failure
```

### 2. Docker化

**Dockerfile**:
```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev
RUN npm install && npm run production

# Set permissions
RUN chown -R www-data:www-data /var/www
RUN chmod -R 755 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]
```

**docker-compose.yml (開発環境)**:
```yaml
version: '3.8'
services:
  app:
    build: .
    volumes:
      - .:/var/www
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx:/etc/nginx/conf.d
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: library
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

volumes:
  mysql_data:
```

### 3. AWS CodePipeline設定

**buildspec.yml**:
```yaml
version: 0.2
phases:
  pre_build:
    commands:
      - echo Logging in to Amazon ECR...
      - aws ecr get-login-password --region $AWS_DEFAULT_REGION | docker login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$AWS_DEFAULT_REGION.amazonaws.com
  
  build:
    commands:
      - echo Build started on `date`
      - echo Building the Docker image...
      - docker build -t $IMAGE_REPO_NAME:$IMAGE_TAG .
      - docker tag $IMAGE_REPO_NAME:$IMAGE_TAG $AWS_ACCOUNT_ID.dkr.ecr.$AWS_DEFAULT_REGION.amazonaws.com/$IMAGE_REPO_NAME:$IMAGE_TAG
  
  post_build:
    commands:
      - echo Build completed on `date`
      - echo Pushing the Docker image...
      - docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_DEFAULT_REGION.amazonaws.com/$IMAGE_REPO_NAME:$IMAGE_TAG
      - echo Writing image definitions file...
      - printf '[{"name":"library-app","imageUri":"%s"}]' $AWS_ACCOUNT_ID.dkr.ecr.$AWS_DEFAULT_REGION.amazonaws.com/$IMAGE_REPO_NAME:$IMAGE_TAG > imagedefinitions.json

artifacts:
  files:
    - imagedefinitions.json
```

### 4. デプロイメント戦略

**Blue-Green デプロイメント**:
```bash
# 新バージョンを並行環境にデプロイ
deploy_new_version() {
    # Green環境にデプロイ
    aws ecs update-service --cluster library-cluster --service library-green --task-definition library-app:$NEW_VERSION
    
    # ヘルスチェック
    wait_for_healthy_service library-green
    
    # ロードバランサーのトラフィックを切り替え
    switch_traffic_to_green
    
    # Blue環境を停止
    aws ecs update-service --cluster library-cluster --service library-blue --desired-count 0
}

# ロールバック機能
rollback() {
    echo "Rolling back to previous version..."
    switch_traffic_to_blue
    aws ecs update-service --cluster library-cluster --service library-blue --desired-count 2
}
```

**ゼロダウンタイムデプロイメント**:
```bash
# ローリングアップデート
rolling_update() {
    # 新しいインスタンスを段階的に追加
    for i in {1..3}; do
        deploy_instance $i
        health_check_instance $i
        if [ $? -eq 0 ]; then
            remove_old_instance $i
        else
            rollback_instance $i
            exit 1
        fi
    done
}
```

## データベースマイグレーション管理

### マイグレーション戦略

**安全なマイグレーション実行**:
```php
// database/migrations/migration_runner.php
class SafeMigrationRunner
{
    public function runMigrations()
    {
        DB::beginTransaction();
        
        try {
            // バックアップ作成
            $this->createBackup();
            
            // マイグレーション実行
            Artisan::call('migrate', ['--force' => true]);
            
            // 整合性チェック
            if ($this->validateMigration()) {
                DB::commit();
                $this->notifySuccess();
            } else {
                throw new Exception('Migration validation failed');
            }
            
        } catch (Exception $e) {
            DB::rollback();
            $this->restoreBackup();
            $this->notifyFailure($e);
            throw $e;
        }
    }
}
```

**マイグレーション検証**:
```bash
# pre-deployment hook
validate_migrations() {
    # ステージング環境でマイグレーションテスト
    php artisan migrate --pretend > migration_plan.txt
    
    # 破壊的変更の検出
    if grep -q "drop\|delete" migration_plan.txt; then
        echo "Destructive migration detected. Manual approval required."
        exit 1
    fi
    
    # テストデータでの検証
    php artisan migrate --env=testing
    php artisan test --group=migration
}
```

## 監視とアラート設計

### アプリケーション監視

**ヘルスチェックエンドポイント**:
```php
// routes/web.php
Route::get('/health', function () {
    $checks = [
        'database' => DB::connection()->getPdo() ? 'ok' : 'error',
        'redis' => Redis::ping() ? 'ok' : 'error',
        'storage' => Storage::disk('local')->exists('test') ? 'ok' : 'error',
    ];
    
    $status = in_array('error', $checks) ? 500 : 200;
    
    return response()->json([
        'status' => $status === 200 ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now(),
        'version' => config('app.version')
    ], $status);
});
```

**CloudWatch メトリクス**:
```yaml
# cloudwatch-config.json
{
  "metrics": {
    "namespace": "Library/Application",
    "metrics_collected": {
      "cpu": {
        "measurement": ["cpu_usage_idle", "cpu_usage_iowait"],
        "metrics_collection_interval": 60
      },
      "disk": {
        "measurement": ["used_percent"],
        "metrics_collection_interval": 60,
        "resources": ["*"]
      },
      "mem": {
        "measurement": ["mem_used_percent"],
        "metrics_collection_interval": 60
      }
    }
  },
  "logs": {
    "logs_collected": {
      "files": {
        "collect_list": [
          {
            "file_path": "/var/www/storage/logs/laravel.log",
            "log_group_name": "library-app-logs",
            "log_stream_name": "{instance_id}/laravel"
          }
        ]
      }
    }
  }
}
```

### アラート設定

**Slack通知**:
```php
// app/Notifications/DeploymentNotification.php
class DeploymentNotification extends Notification
{
    public function via($notifiable)
    {
        return ['slack'];
    }
    
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->success()
            ->content('Deployment completed successfully!')
            ->attachment(function ($attachment) {
                $attachment->title('Library App v' . $this->version)
                          ->fields([
                              'Environment' => $this->environment,
                              'Deployed by' => $this->deployedBy,
                              'Duration' => $this->duration,
                          ]);
            });
    }
}
```

## セキュリティ設計

### コードセキュリティ

**静的解析設定**:
```yaml
# .github/workflows/security.yml
name: Security Scan
on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Run PHPStan
        run: ./vendor/bin/phpstan analyse
        
      - name: Run PHP CS Fixer
        run: ./vendor/bin/php-cs-fixer fix --dry-run --diff
        
      - name: Security Checker
        run: ./vendor/bin/security-checker security:check composer.lock
        
      - name: OWASP Dependency Check
        uses: dependency-check/Dependency-Check_Action@main
```

### 環境変数管理

**AWS Systems Manager Parameter Store**:
```bash
# 環境変数の安全な管理
store_secrets() {
    aws ssm put-parameter \
        --name "/library-app/production/db-password" \
        --value "$DB_PASSWORD" \
        --type "SecureString" \
        --key-id "alias/library-app-key"
}

# デプロイ時の環境変数取得
get_secrets() {
    DB_PASSWORD=$(aws ssm get-parameter \
        --name "/library-app/production/db-password" \
        --with-decryption \
        --query "Parameter.Value" \
        --output text)
}
```

## パフォーマンス最適化

### ビルド最適化

**マルチステージビルド**:
```dockerfile
# Build stage
FROM node:18-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

FROM composer:2 AS composer-builder
WORKDIR /app
COPY composer*.json ./
RUN composer install --no-dev --optimize-autoloader

# Production stage
FROM php:8.2-fpm-alpine
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/node_modules ./node_modules
```

**キャッシュ戦略**:
```yaml
# GitHub Actions キャッシュ
- name: Cache Composer dependencies
  uses: actions/cache@v3
  with:
    path: vendor
    key: composer-${{ hashFiles('composer.lock') }}

- name: Cache NPM dependencies
  uses: actions/cache@v3
  with:
    path: node_modules
    key: npm-${{ hashFiles('package-lock.json') }}
```

## 運用設計

### ログ管理

**集約ログシステム**:
```yaml
# docker-compose.logging.yml
version: '3.8'
services:
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.5.0
    environment:
      - discovery.type=single-node
      - xpack.security.enabled=false
    ports:
      - "9200:9200"

  logstash:
    image: docker.elastic.co/logstash/logstash:8.5.0
    volumes:
      - ./logstash.conf:/usr/share/logstash/pipeline/logstash.conf
    depends_on:
      - elasticsearch

  kibana:
    image: docker.elastic.co/kibana/kibana:8.5.0
    ports:
      - "5601:5601"
    depends_on:
      - elasticsearch
```

### バックアップ戦略

**自動バックアップスクリプト**:
```bash
#!/bin/bash
# backup.sh
backup_database() {
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_FILE="library_backup_${TIMESTAMP}.sql"
    
    # データベースバックアップ
    mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASSWORD $DB_NAME > $BACKUP_FILE
    
    # S3にアップロード
    aws s3 cp $BACKUP_FILE s3://library-backups/database/
    
    # ローカルファイル削除
    rm $BACKUP_FILE
    
    # 古いバックアップの削除（30日以上）
    aws s3 ls s3://library-backups/database/ | while read -r line; do
        createDate=$(echo $line | awk '{print $1" "$2}')
        createDate=$(date -d "$createDate" +%s)
        olderThan=$(date -d "30 days ago" +%s)
        if [[ $createDate -lt $olderThan ]]; then
            fileName=$(echo $line | awk '{print $4}')
            aws s3 rm s3://library-backups/database/$fileName
        fi
    done
}

# crontab設定
# 0 2 * * * /path/to/backup.sh
```

この設計により、安全で効率的な継続的デプロイメントが実現できます。