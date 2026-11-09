#!/usr/bin/env bash
# ============================================================
# ШАГ 2. Чистая установка WordPress последней версии — ЭТАП 3 плана.
# Запускается НА НОВОМ сервере. База данных на этом шаге НЕ импортируется.
# Переменные окружения (обязательны):
#   SITE_DIR  — корень сайта, напр. /var/www/caranddriver.ru
#   DB_NAME, DB_USER, DB_PASS, DB_HOST — новая пустая БД
#   DOMAIN    — домен, напр. caranddriver.ru
#   DB_PREFIX — префикс таблиц (обязателен 'wpla_', иначе дамп не подхватится)
# Использование:
#   SITE_DIR=/var/www/caranddriver.ru DOMAIN=caranddriver.ru \
#   DB_NAME=wp_cdr DB_USER=wp_cdr DB_PASS='***' bash 02-deploy-wp.sh
# ============================================================
set -euo pipefail

SITE_DIR="${SITE_DIR:?задайте SITE_DIR}"
DOMAIN="${DOMAIN:?задайте DOMAIN}"
DB_NAME="${DB_NAME:?задайте DB_NAME}"
DB_USER="${DB_USER:?задайте DB_USER}"
DB_PASS="${DB_PASS:?задайте DB_PASS}"
DB_HOST="${DB_HOST:-localhost}"
DB_PREFIX="${DB_PREFIX:-wpla_}"

command -v wp >/dev/null 2>&1 || { echo "Установите wp-cli: https://wp-cli.org/ru/installing/"; exit 1; }

mkdir -p "$SITE_DIR"
cd "$SITE_DIR"

echo "[1/3] Скачивание WordPress последней версии (ru_RU)"
wp core download --locale=ru_RU --force --allow-root

echo "[2/3] Создание wp-config.php (префикс таблиц: $DB_PREFIX — как в старом дампе)"
wp config create \
    --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" --dbhost="$DB_HOST" \
    --dbprefix="$DB_PREFIX" --locale=ru_RU --force --allow-root
wp config set WP_MEMORY_LIMIT 256M --allow-root
wp config set WP_AUTO_UPDATE_CORE minor --raw --allow-root

echo "[3/3] Проверка соединения с БД"
wp db check --allow-root || { echo "!! Проверьте права пользователя БД"; exit 1; }

echo "ГОТОВО. Ядро WP установлено в $SITE_DIR"
echo "ВАЖНО: не запускайте 'wp core install' — сайт наполняется импортом старой БД (шаг 3)."
