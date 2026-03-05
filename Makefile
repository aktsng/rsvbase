# ============================================================
# RsvBase — Makefile
# ============================================================

DC := docker compose
APP := $(DC) exec app
ARTISAN := $(APP) php artisan

.PHONY: help build up up-all down restart logs shell shell-db \
	migrate migrate-force fresh seed cache-clear optimize test test-feature test-unit test-file test-filter \
	artisan composer composer-install \
	npm-install npm-dev npm-build \
	queue-work queue-restart \
	setup deploy \
	maint-on maint-off

# デフォルトターゲット
help:
	@echo "RsvBase - 開発・運用 Makeコマンド"
	@echo ""
	@echo "使用方法:"
	@echo "  make <command>"
	@echo ""
	@echo "Docker関連:"
	@echo "  build          Dockerイメージをビルド＆起動"
	@echo "  up             コンテナを起動（本番: mailpit除外）"
	@echo "  up-all         全コンテナ起動（開発用: mailpit含む）"
	@echo "  down           コンテナを停止"
	@echo "  restart        コンテナを再起動"
	@echo "  logs           ログを表示"
	@echo "  shell          appコンテナにシェルアクセス"
	@echo "  shell-db       dbコンテナにMySQLアクセス"
	@echo ""
	@echo "Laravel関連:"
	@echo "  migrate        マイグレーション実行"
	@echo "  migrate-force  マイグレーション実行（本番: --force）"
	@echo "  fresh          DB再構築（データ削除）+ シーダー"
	@echo "  seed           シーダー実行"
	@echo "  cache-clear    キャッシュクリア"
	@echo "  optimize       設定キャッシュ最適化"
	@echo "  test           PHPUnitテスト実行（全件）"
	@echo "  test-feature   Featureテストのみ実行"
	@echo "  test-unit      Unitテストのみ実行"
	@echo "  test-file      指定ファイルのみ (例: make test-file file=tests/Feature/XxxTest.php)"
	@echo "  test-filter    フィルタ指定 (例: make test-filter filter=some_test)"
	@echo "  artisan        artisanコマンド実行 (例: make artisan cmd='route:list')"
	@echo "  composer       composerコマンド実行 (例: make composer cmd='require xxx')"
	@echo "  maint-on       メンテナンスモード開始"
	@echo "  maint-off      メンテナンスモード解除"
	@echo ""
	@echo "フロントエンド関連:"
	@echo "  npm-install    npm install実行"
	@echo "  npm-dev        npm run dev実行（ホットリロード）"
	@echo "  npm-build      npm run build実行"
	@echo ""
	@echo "Queue関連:"
	@echo "  queue-work     Queueワーカー起動"
	@echo "  queue-restart  Queueワーカー再起動"
	@echo ""
	@echo "セットアップ / デプロイ:"
	@echo "  setup          初回セットアップ（build + composer + migrate + seed + npm-build）"
	@echo "  deploy         本番デプロイ（build + composer + migrate + npm-build + cache-clear）"

# ===================
# Docker関連
# ===================

build:
	$(DC) up -d --build

up:
	@if [ -f .env ] && grep -q "APP_ENV=production" .env; then \
		echo "🚀 Production environment detected. Starting app, nginx, mariadb, redis (skipping mailpit)..."; \
		$(DC) up -d app nginx mariadb redis; \
	else \
		echo "🛠️  Development environment. Starting all services including mailpit..."; \
		$(DC) --profile dev up -d; \
	fi

up-all:
	$(DC) --profile dev --profile tools up -d

down:
	$(DC) down

restart:
	$(DC) restart

logs:
	$(DC) logs -f

shell:
	$(APP) bash

shell-db:
	$(DC) exec mariadb mysql -u rsvbase -psecret rsvbase

# ===================
# Laravel関連
# ===================

migrate:
	$(ARTISAN) migrate $(args)

migrate-force:
	$(ARTISAN) migrate --force

fresh:
	$(ARTISAN) migrate:fresh --seed
	@echo "✅ DB再構築 + シーダー実行完了"

seed:
	$(ARTISAN) db:seed

cache-clear:
	$(ARTISAN) config:clear
	$(ARTISAN) cache:clear
	$(ARTISAN) route:clear
	$(ARTISAN) view:clear

optimize:
	$(ARTISAN) optimize

test:
	$(APP) php artisan test

test-feature:
	$(APP) php artisan test --testsuite=Feature

test-unit:
	$(APP) php artisan test --testsuite=Unit

test-file:
	@if [ -z "$(file)" ]; then echo "file= を指定してください"; exit 1; fi
	$(APP) php artisan test $(file)

test-filter:
	@if [ -z "$(filter)" ]; then echo "filter= を指定してください"; exit 1; fi
	$(APP) php artisan test --filter "$(filter)"

artisan:
	$(ARTISAN) $(cmd)

composer:
	$(APP) composer $(cmd)

composer-install:
	$(APP) composer install --no-dev --optimize-autoloader

# ===================
# フロントエンド関連
# ===================

npm-install:
	$(APP) npm install

npm-dev:
	$(APP) npm run dev

npm-build:
	$(APP) npm run build
	@rm -f public/hot

# ===================
# Queue関連
# ===================

queue-work:
	$(ARTISAN) queue:work redis --tries=3 --timeout=90

queue-restart:
	$(ARTISAN) queue:restart

# ===================
# セットアップ / デプロイ
# ===================

setup: build
	@echo "📦 Installing PHP dependencies..."
	$(APP) composer install
	@echo "🔑 Generating application key..."
	$(ARTISAN) key:generate
	@echo "⏳ Waiting for database..."
	@sleep 5
	$(ARTISAN) migrate
	$(ARTISAN) db:seed
	$(ARTISAN) storage:link
	@echo "🎨 Building frontend assets..."
	$(APP) npm install
	$(APP) npm run build
	@rm -f public/hot
	@echo ""
	@echo "✅ セットアップ完了!"
	@echo "アクセス: http://localhost:8082"

deploy:
	$(ARTISAN) down
	$(DC) build
	$(DC) up -d app nginx mariadb redis
	$(APP) composer install --no-dev --optimize-autoloader
	$(ARTISAN) migrate --force
	$(APP) npm ci
	$(APP) npm run build
	@rm -f public/hot
	$(ARTISAN) optimize
	$(ARTISAN) up
	@echo "✅ デプロイ完了"

# ===================
# メンテナンスモード
# ===================

maint-on:
	@echo "メンテナンスモードを開始します..."
	@SECRET=$$(openssl rand -hex 6); \
	$(ARTISAN) down --secret="$$SECRET"; \
	echo "✅ メンテナンスモード ON"; \
	echo "バイパスURL: http://localhost:8082/$$SECRET"

maint-off:
	@echo "メンテナンスモードを解除します..."
	$(ARTISAN) up
	@echo "✅ メンテナンスモード OFF"
