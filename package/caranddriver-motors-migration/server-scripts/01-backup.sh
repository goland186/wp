#!/usr/bin/env bash
# ============================================================
# ШАГ 1. Полный бэкап текущего сайта (файлы + БД) — ЭТАП 2 плана миграции.
# Запускается НА СТАРОМ сервере caranddriver.ru ДО любых изменений.
# Использование:  bash 01-backup.sh /var/www/caranddriver.ru  /root/backups
# ============================================================
set -euo pipefail

SITE_DIR="${1:?Укажите путь к корню сайта, напр. /var/www/caranddriver.ru}"
BACKUP_ROOT="${2:-/root/backups}"
STAMP="$(date +%Y-%m-%d_%H-%M)"
DEST="$BACKUP_ROOT/migration-backup-$STAMP"

mkdir -p "$DEST"

echo "[1/3] Бэкап файлов: $SITE_DIR"
tar -czf "$DEST/site-files.tar.gz" -C "$(dirname "$SITE_DIR")" "$(basename "$SITE_DIR")"

echo "[2/3] Бэкап базы данных"
if command -v wp >/dev/null 2>&1; then
    (cd "$SITE_DIR" && wp db export "$DEST/db-old_dump.sql" --add-drop-table --allow-root)
else
    # Без WP-CLI: параметры берём из wp-config.php вручную
    echo "  wp-cli не найден — возьмите креды из wp-config.php:"
    echo "  mysqldump -u <DB_USER> -p <DB_NAME> > $DEST/db-old_dump.sql"
    DB_NAME=$(grep -oP "define\(\s*'DB_NAME',\s*'\K[^']+" "$SITE_DIR/wp-config.php" || true)
    if [ -n "${DB_NAME:-}" ]; then
        mysqldump --add-drop-table "$DB_NAME" > "$DEST/db-old_dump.sql" 2>/dev/null || \
            echo "  !! mysqldump не удался — выполните дамп вручную"
    fi
fi

echo "[3/3] Бэкап uploads отдельно (для быстрой загрузки на новый сервер)"
if [ -d "$SITE_DIR/wp-content/uploads" ]; then
    tar -czf "$DEST/uploads.tar.gz" -C "$SITE_DIR/wp-content" uploads
fi

sha256sum "$DEST"/* > "$DEST/SHA256SUMS" 2>/dev/null || true
echo "ГОТОВО. Бэкап: $DEST"
echo "Сохраните $DEST вне сервера (облако/локальная машина) перед продолжением."
