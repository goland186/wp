#!/usr/bin/env bash
# ============================================================
# ШАГ 3. Импорт старой базы данных — ЭТАПЫ 3-4 плана (данные — высший приоритет).
# Запускается НА НОВОМ сервере ПОСЛЕ 02-deploy-wp.sh.
# Переменные окружения:
#   SITE_DIR   — корень сайта
#   DUMP_FILE  — путь к database/old_dump.sql (из репозитория goland186/wp)
#   OLD_DOMAIN — домен из дампа (по умолчанию http://caranddriver.ru и https://caranddriver.ru)
#   NEW_DOMAIN — целевой домен (по умолчанию тот же — замена не выполняется)
# Использование:
#   SITE_DIR=/var/www/caranddriver.ru DUMP_FILE=/root/old_dump.sql bash 03-import-db.sh
# ============================================================
set -euo pipefail

SITE_DIR="${SITE_DIR:?задайте SITE_DIR}"
DUMP_FILE="${DUMP_FILE:?задайте DUMP_FILE (путь к old_dump.sql)}"
OLD_DOMAIN_HTTP="${OLD_DOMAIN_HTTP:-http://caranddriver.ru}"
OLD_DOMAIN_HTTPS="${OLD_DOMAIN_HTTPS:-https://caranddriver.ru}"
NEW_DOMAIN="${NEW_DOMAIN:-}"

cd "$SITE_DIR"
command -v wp >/dev/null 2>&1 || { echo "Установите wp-cli"; exit 1; }

echo "[1/5] Импорт дампа в БД ($(du -h "$DUMP_FILE" | cut -f1)) — дамп 62 МБ, занимает 1-3 мин"
wp db import "$DUMP_FILE" --allow-root

echo "[2/5] Замена домена в контенте (если целевой домен изменился)"
if [ -n "$NEW_DOMAIN" ] && [ "$NEW_DOMAIN" != "caranddriver.ru" ]; then
    echo "  $OLD_DOMAIN_HTTPS -> $NEW_DOMAIN"
    wp search-replace "$OLD_DOMAIN_HTTPS" "$NEW_DOMAIN" --all-tables-with-prefix --report-changed-only --allow-root
    echo "  $OLD_DOMAIN_HTTP -> $NEW_DOMAIN"
    wp search-replace "$OLD_DOMAIN_HTTP" "$NEW_DOMAIN" --all-tables-with-prefix --report-changed-only --allow-root
else
    echo "  Домен не меняется — замена пропущена (все URL остаются https://caranddriver.ru/...)"
fi

echo "[3/5] Очистка кэшей и устаревших транзиентов от старых плагинов"
wp transient delete --all --allow-root || true
wp cache flush --allow-root || true

echo "[4/5] Структура постоянных ссылок (как на старом сайте — URL сохраняются 1:1)"
wp rewrite structure '/%year%/%monthnum%/%day%/%postname%/' --allow-root
wp rewrite flush --hard --allow-root

echo "[5/5] Обновление схемы БД под текущую версию WP"
wp core update-db --allow-root || true

echo "ГОТОВО. Проверка контента:"
echo -n "  постов (publish): "; wp post list --post_type=post --post_status=publish --format=count --allow-root
echo -n "  страниц: ";          wp post list --post_type=page --format=count --allow-root
echo -n "  вложений: ";        wp post list --post_type=attachment --format=count --allow-root
echo -n "  комментариев: ";    wp db query "SELECT COUNT(*) FROM wpla_comments" --skip-column-names --allow-root
