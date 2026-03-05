#!/bin/bash
# ==============================================================================
# RsvBase — データベース自動バックアップスクリプト
# 毎日cronで実行し、7世代分を保持する
# ==============================================================================

set -euo pipefail

# プロジェクトルート（このスクリプトの1つ上のディレクトリ）
PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BACKUP_DIR="${PROJECT_DIR}/storage/backups"
RETENTION_DAYS=7

# .envからDB情報を読み取り
if [ -f "${PROJECT_DIR}/.env" ]; then
    DB_DATABASE=$(grep -E '^DB_DATABASE=' "${PROJECT_DIR}/.env" | cut -d'=' -f2)
    DB_USERNAME=$(grep -E '^DB_USERNAME=' "${PROJECT_DIR}/.env" | cut -d'=' -f2)
    DB_PASSWORD=$(grep -E '^DB_PASSWORD=' "${PROJECT_DIR}/.env" | cut -d'=' -f2)
else
    echo "[ERROR] .env file not found at ${PROJECT_DIR}/.env"
    exit 1
fi

# バックアップディレクトリ作成
mkdir -p "${BACKUP_DIR}"

# タイムスタンプ
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql.gz"

echo "[$(date)] Starting database backup..."

# ダンプ実行（docker compose経由）
cd "${PROJECT_DIR}"
docker compose exec -T mariadb mysqldump \
    -u "${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    "${DB_DATABASE}" \
    --single-transaction \
    --quick \
    | gzip > "${BACKUP_FILE}"

if [ $? -eq 0 ] && [ -s "${BACKUP_FILE}" ]; then
    FILE_SIZE=$(du -h "${BACKUP_FILE}" | cut -f1)
    echo "[$(date)] Backup succeeded: ${BACKUP_FILE} (${FILE_SIZE})"
else
    echo "[$(date)] [ERROR] Backup failed or file is empty!"
    rm -f "${BACKUP_FILE}"
    exit 1
fi

# 古いバックアップの削除（RETENTION_DAYS日以上前）
DELETED=$(find "${BACKUP_DIR}" -name "db_backup_*.sql.gz" -mtime +${RETENTION_DAYS} -print -delete | wc -l)
echo "[$(date)] Cleaned up ${DELETED} old backup(s)."

echo "[$(date)] Backup complete."
