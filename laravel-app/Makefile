.PHONY: help build up down restart logs shell test migrate seed fresh install

# デフォルトターゲット
help: ## このヘルプメッセージを表示
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Dockerイメージをビルド
	docker-compose build

up: ## コンテナを起動
	docker-compose up -d

down: ## コンテナを停止・削除
	docker-compose down

restart: ## コンテナを再起動
	docker-compose restart

logs: ## ログを表示
	docker-compose logs -f

shell: ## アプリケーションコンテナにシェルでアクセス
	docker-compose exec app sh

mysql: ## MySQLコンテナにアクセス
	docker-compose exec mysql mysql -u library_user -p library

redis: ## Redisコンテナにアクセス
	docker-compose exec redis redis-cli

test: ## テストを実行
	docker-compose exec app php artisan test

migrate: ## データベースマイグレーションを実行
	docker-compose exec app php artisan migrate

seed: ## データベースシーダーを実行
	docker-compose exec app php artisan db:seed

fresh: ## データベースをリフレッシュしてシーダーを実行
	docker-compose exec app php artisan migrate:fresh --seed

install: ## 初回セットアップ
	docker-compose up -d
	docker-compose exec app composer install
	docker-compose exec app npm install
	docker-compose exec app cp .env.example .env
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --seed
	docker-compose exec app npm run build

clean: ## 全てのコンテナ、イメージ、ボリュームを削除
	docker-compose down -v --rmi all

status: ## コンテナの状態を確認
	docker-compose ps

# 本番環境用コマンド
prod-build: ## 本番用イメージをビルド
	docker-compose -f docker-compose.prod.yml build

prod-up: ## 本番環境を起動
	docker-compose -f docker-compose.prod.yml up -d

prod-down: ## 本番環境を停止
	docker-compose -f docker-compose.prod.yml down

prod-logs: ## 本番環境のログを表示
	docker-compose -f docker-compose.prod.yml logs -f