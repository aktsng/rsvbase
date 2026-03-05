# RsvBase VPS構築・デプロイ手順書

このドキュメントでは、VPS上に本番環境を構築し、RsvBaseを公開するまでの手順を解説します。
OSは **Ubuntu 24.04 LTS** を想定しています。

> **⚠️ 同一VPSでの共存について**
> 本ガイドは、同一VPS上で **pitatto** が既に稼働している前提で記述しています。
> pitattoのサービスとポートが競合しないよう、rsvbase側のポートはすべてオフセット済みです。

## 1. 事前準備 (VPSの契約とドメイン)

1.  **VPSの契約**: メモリは最低でも **2GB** 推奨です（ビルド時にメモリを消費するため）。
    -   1GBプランの場合は、必ず **スワップファイル（Swap）** を作成してください（後述）。
2.  **ドメインの取得**: ドメインを取得し、DNS設定でVPSのIPアドレス（Aレコード）を登録しておきます。

## 2. サーバーの初期設定 (OSセットアップ)

> **Note:** pitattoで構築済みの場合、このセクションはスキップ可能です。

### 1. root ユーザーでログイン
```bash
ssh root@<VPS_IP_ADDRESS>
```

### 2. 作業用ユーザーの作成
```bash
adduser rsvbase
usermod -aG sudo rsvbase
```

### 3. SSH鍵の作成 (持っていない場合)
```bash
# ローカルPCで実行
ssh-keygen -t ed25519
```

### 4. SSH公開鍵の登録
```bash
# ローカルPCで実行
ssh-copy-id rsvbase@<VPS_IP_ADDRESS>
```

### 5. 一般ユーザーで再ログイン
```bash
ssh rsvbase@<VPS_IP_ADDRESS>
```

## 3. 環境の構築

> **Note:** pitattoで Docker / UFW / Fail2Ban / Swap 等が構築済みの場合、このセクションの多くはスキップ可能です。

### パッケージの更新と必要ツールのインストール
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y git make curl
```

### Dockerのインストール
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```
※ 一度ログアウトし、再ログインしてください。

### メモリ不足対策（スワップファイルの作成）
```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

### サーバーのセキュリティ設定 (UFW & Fail2Ban)

#### 1. UFW (ファイアウォール)
```bash
sudo ufw allow 22/tcp
sudo ufw enable
```

#### 2. Fail2Ban
```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

#### 3. 自動セキュリティアップデート
```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```

#### 4. SSHのさらなる保護（推奨）
1.  SSH設定ファイルを開きます。
    ```bash
    sudo nano /etc/ssh/sshd_config
    ```
2.  以下の項目を `no` に変更します。
    - `PasswordAuthentication no`
    - `PermitRootLogin no`
3.  設定を反映します。
    ```bash
    sudo systemctl restart ssh
    ```

### 時計の同期確認
```bash
timedatectl
```
`System clock synchronized: yes` になっていることを確認してください。

## 4. プロジェクトのセットアップ

### ソースコードの取得
```bash
git clone <REPOSITORY_URL> rsvbase
cd rsvbase
```

### 環境変数の設定
```bash
cp .env.example .env
nano .env
```

`.env`ファイル内の以下の項目を必ず修正してください。

```ini
APP_NAME=RsvBase
APP_ENV=production
APP_KEY=  # この後コマンドで生成します
APP_DEBUG=false
APP_URL=https://rsvbase.jp  # 取得したドメイン
APP_PORT=8082

APP_LOCALE=ja
APP_TIMEZONE=Asia/Tokyo

# ログ設定 (重要: ディスク溢れ防止のため daily に設定)
LOG_CHANNEL=daily
LOG_DAILY_DAYS=14

# データベース設定 (推測されにくいパスワードに変更してください)
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=rsvbase
DB_USERNAME=rsvbase
DB_PASSWORD=secret_password_change_me

# セッション / キャッシュ / キュー (Redis)
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

# メール (本番用: Resend等のSMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_xxxxx  # Resend APIキー
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@rsvbase.jp"
MAIL_FROM_NAME="${APP_NAME}"

# Stripe Connect
STRIPE_PUBLISHABLE_KEY=pk_live_xxxxx
STRIPE_SECRET_KEY=sk_live_xxxxx
STRIPE_CLIENT_ID=ca_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx

# Admin Seed
ADMIN_EMAIL=admin@rsvbase.jp
ADMIN_PASSWORD=secure_admin_password_change_me
```

### ポートの確認（pitatto共存時）

| サービス | pitatto | rsvbase |
|:--|:--|:--|
| Nginx (HTTP) | 8080 | **8082** |
| MariaDB | 3306 | **3308** |
| phpMyAdmin | 8081 | **8083** |
| Redis | — | **6380** |

## 5. アプリケーションの起動とビルド

付属の `Makefile` を使うと簡単にセットアップできます。

```bash
make setup
```

※ 初回は時間がかかります（5〜10分程度）。
※ 成功すると `✅ セットアップ完了!` のメッセージが表示されます。

### 確認
```bash
docker compose ps
```
`rsvbase-app`, `rsvbase-nginx`, `rsvbase-mariadb`, `rsvbase-redis` が `Up` になっていることを確認します。

## 6. 外部公開 (Cloudflare Tunnel)

pitattoと同じCloudflare Tunnelを利用してインターネットへ公開します。
pitattoのトンネルが既に稼働している場合、**Public Hostname を追加するだけ**で新しいドメインを公開できます。

### 1. 公開設定 (Public Hostname の追加)
1.  Cloudflare Dashboard の **Zero Trust** > **Networks** > **Tunnels** を開きます。
2.  pitattoで作成済みのトンネルの **Edit** を開き、**Public Hostname** タブを選択します。
3.  **Add a public hostname** をクリックし、以下のように設定します：
    -   **Subdomain**: (任意、空欄でトップレベルも可)
    -   **Domain**: rsvbase用のドメインを選択
    -   **Path**: (空欄)
    -   **Service**:
        -   Type: `HTTP`
        -   URL: `localhost:8082`
4.  **Save hostname** をクリックします。

### 2. Cloudflareの追加設定

#### キャッシュの除外設定 (Cache Rules)
**設定場所:** Cloudflareダッシュボード > Caching > Cache Rules
-   **名前:** `RsvBase - Disable Cache for Auth and Webhooks`
-   **条件 (If):**
    -   `URI Path` contains `/login`
    -   OR `URI Path` contains `/api/stripe/webhook`
    -   OR `URI Path` contains `/stripe/`
-   **アクション (Then):**
    -   `Cache eligibility`: `Bypass cache`

#### WAFのスキップ設定 (Stripe のブロック防止)
**設定場所:** Cloudflareダッシュボード > Security > WAF > Custom Rules
-   **名前:** `RsvBase - Allow Stripe and Auth`
-   **条件 (If):**
    -   `URI Path` contains `/login`
    -   OR `URI Path` contains `/api/stripe/webhook`
    -   OR `URI Path` contains `/stripe/`
    -   OR `URI Path` contains `/auth/`
-   **アクション (Then):**
    -   `Skip`: `All remaining Custom Rules`

#### HTTPSの強制設定
**設定場所:** Cloudflareダッシュボード > SSL/TLS > Edge Certificates
-   **Always Use HTTPS**: `On`

#### セキュリティ設定の強化
**設定場所:** Cloudflareダッシュボード > Security > Bots
-   **Bot Fight Mode**: `On`
-   **Block AI Scrapers and Crawlers**: `On`

**※重要:** WAFのスキップ設定が正しく設定されていることを確認してから有効にしてください。

## 7. サービスの永続化（自動起動）

### Dockerコンテナの自動起動
`docker-compose.yml` に `restart: always` を記述済みです。
サーバー起動時にDockerデーモンが起動すると、自動的にコンテナも開始されます。

### Cloudflare Tunnelの自動起動
pitattoで `sudo cloudflared service install` 済みのため、追加設定不要です。

## 8. 運用・保守

### アプリケーションの更新
```bash
cd rsvbase
git pull origin main
make deploy
```

### ログの確認
```bash
docker compose logs -f --tail=100 app
```

### 画面が真っ白な場合 / CORSエラーが出る場合
1.  **環境変数の確認 (`.env`)**:
    -   `APP_ENV=production` になっているか
    -   `APP_URL=https://your-domain.com` になっているか
    -   `APP_DEBUG=false` になっているか
2.  **開発用一時ファイルの削除**:
    ```bash
    rm -f public/hot
    ```
3.  **キャッシュのクリア**:
    ```bash
    make cache-clear
    ```

### バックアップ
`.env` と `storage/app/public`（アップロード画像等）は重要です。
データベースのバックアップは以下のコマンドで手動取得可能です。

```bash
docker compose exec mariadb mysqldump -u rsvbase -psecret_password rsvbase > backup.sql
```

## 9. 自動バックアップとリストア

### 自動バックアップの設定 (cron)
`docker/backup.sh` をcronで定期実行します。

1.  スクリプトに実行権限を付与します。
    ```bash
    chmod +x docker/backup.sh
    ```

2.  cronを設定します。
    ```bash
    crontab -e
    ```

    以下の行を追加します（毎日午前3時30分に実行する場合）。
    ※ pitattoのバックアップ (AM 3:00) と時間をずらしています。

    ```cron
    30 3 * * * /home/rsvbase/rsvbase/docker/backup.sh >> /home/rsvbase/rsvbase/storage/logs/backup.log 2>&1
    ```

    バックアップファイルは `storage/backups` ディレクトリに7世代分保存されます。

### リストア（復元）手順
```bash
# バックアップファイルの確認
ls -l storage/backups/

# 復元（例: db_backup_20260301_033000.sql.gz）
zcat storage/backups/db_backup_20260301_033000.sql.gz | docker compose exec -T mariadb mysql -u rsvbase -psecret_password rsvbase
```

## 10. 自動デプロイ (GitHub Actions)

`.github/workflows/deploy.yml` を作成済みです。
GitHubのリポジトリ設定でSecretsを登録すると有効になります。

### 手順
1.  GitHubリポジトリの **Settings** > **Secrets and variables** > **Actions** を開きます。
2.  **New repository secret** をクリックし、以下を登録します。

| Secret名 | 内容 (設定例) |
| :--- | :--- |
| `VPS_HOST` | VPSのIPアドレス (例: `203.0.113.10`) |
| `VPS_USERNAME` | VPSのログインユーザー名 (例: `rsvbase`) |
| `VPS_SSH_KEY` | **SSH秘密鍵**の中身 (`id_ed25519.pub` ではなく **`id_ed25519`** の中身。`-----BEGIN ...` から `-----END ...` まで全てコピーして貼り付け) |
| `VPS_PORT` | SSHポート番号 (通常は `22`) |

> [!CAUTION]
> **SSH秘密鍵の注意点**:
> -   `.pub` が付いている方は「公開鍵」です。GitHub Secretsには **`.pub` が付いていない方** のファイルの中身を登録してください。
> -   `-----BEGIN OPENSSH PRIVATE KEY-----` などの最初の行から、最後の行まで正確にコピーしてください。

### 動作確認
-   `main` ブランチにコードをプッシュ（マージ）すると、Actionsが起動します。
-   VPSにSSH接続し、`git pull`、ビルド、マイグレーション、キャッシュクリア等が自動実行されます。

### GitHub Actions のデプロイでエラーが発生する場合

#### 0. SSH認証エラー (handshake failed: unable to authenticate)
このエラーは、GitHub Actions が使用している秘密鍵が VPS 側に認可されていない場合に発生します。
1.  **公開鍵が VPS に登録されているか確認**: 
    VPSにログインし、以下のコマンドで登録済みの公開鍵を確認します。
    ```bash
    cat ~/.ssh/authorized_keys
    ```
    ここにある内容と、ローカルの `~/.ssh/id_ed25519.pub` の内容が一致しているか確認してください。一致していない場合は、以下を実行します（ローカルPCから）。
    ```bash
    ssh-copy-id rsvbase@<VPS_IP_ADDRESS>
    ```
2.  **GitHub Secrets の秘密鍵が正しいか確認**:
    登録した `VPS_SSH_KEY` が、上記で登録した公開鍵と対（ペア）になる秘密鍵ファイル（`id_ed25519`）の中身であることを再確認してください。

#### 1. Gitの認証エラー (Permission denied)
1. **公開鍵を表示**: `cat ~/.ssh/id_ed25519.pub`
2. **GitHub に登録**: [GitHub SSH Keys](https://github.com/settings/keys) へ登録。
3. **接続テスト**: `ssh -T git@github.com`

#### 2. ホストキーの検証エラー (Host key verification failed)
1. `ssh -T git@github.com` を実行。
2. `yes` と回答して信頼リストに追加。

#### 3. リモートURLが HTTPS のままになっている
```bash
git remote set-url origin git@github.com:aktsng/rsvbase.git
```

## 11. データベース管理 (phpMyAdmin)

phpMyAdminがポート8083で稼働可能（profilesで通常時停止）です。

### 接続手順 (SSHトンネル)

1. **VPS側でpma起動**
    ```bash
    docker compose --profile tools up -d phpmyadmin
    ```

2. **ローカルPCからSSHトンネル**
    ```bash
    ssh -L 8083:localhost:8083 rsvbase@<VPS_IP_ADDRESS>
    ```

3. ブラウザで http://localhost:8083 にアクセス

4. 作業完了後:
    ```bash
    docker compose stop phpmyadmin
    ```

## 12. Stripe Webhookの設定（本番環境）

1.  **Stripeダッシュボード設定**
    *   [Stripeダッシュボード](https://dashboard.stripe.com/)  > **「開発者」 > 「Webhooks」** を開く。
    *   **「エンドポイントを追加」** をクリック。
    *   **エンドポイントURL**: `https://rsvbase.jp/api/stripe/webhook`
    *   **リッスンするイベント**: `payment_intent.succeeded` を選択。
    *   保存してエンドポイントを作成。

2.  **署名シークレットの取得**
    *   「署名シークレット」の `whsec_` から始まる文字列をコピー。

3.  **環境変数の設定**
    *   `.env` の `STRIPE_WEBHOOK_SECRET` に貼り付ける。

## 13. タスクスケジューリングの設定 (Cron)

Laravelのスケジューラーを機能させるために、VPS側でcronを設定します。

### 設定手順

1.  **crontabの編集**
    ```bash
    crontab -e
    ```

2.  **設定の追加**
    ※ パスは実際の配置場所に合わせてください。

    ```cron
    # Laravel Scheduler (毎分実行)
    * * * * * cd /home/rsvbase/rsvbase && /usr/bin/docker compose exec -T app php artisan schedule:run >> /dev/null 2>&1

    # Database Backup (毎日 AM 3:30 — pitattoの3:00と時間をずらす)
    30 3 * * * cd /home/rsvbase/rsvbase && /bin/bash docker/backup.sh >> /home/rsvbase/rsvbase/storage/logs/backup.log 2>&1
    ```

### 注意点
-   cron実行時はPATHが通らないため、`/usr/bin/docker` のようにフルパス指定または `cd` でディレクトリ移動してから実行してください。
-   `schedule:run` が毎分動くことで、Laravel内の `routes/console.php` で定義されたタスクがトリガーされます。
