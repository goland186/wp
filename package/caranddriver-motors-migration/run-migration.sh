#!/usr/bin/env bash
# ============================================================================
# ОДНОДЕЙСТВЕННОЕ РАЗВЁРТЫВАНИЕ caranddriver.ru (WordPress + Motors).
#
# ВСЕ файлы берутся из этого репозитория: ядро WordPress (../wordpress),
# дамп БД (../../database/old_dump.sql), тема Motors (../../new-theme/Motors),
# дочерняя тема, реестры картинок и wp-cli.phar (bin/).
#
# ДВА ЗАПУСКА:
#   bash run-migration.sh config.env            # 1) сайт целиком (без картинок)
#   bash run-migration.sh config.env uploads    # 2) копия папки uploads + финальная сверка
#
# Путь к папке uploads (каталог или архив .tar.gz) задаётся в config.env
# (UPLOADS_SRC) или третьим аргументом: bash run-migration.sh config.env uploads /path/uploads.tar.gz
#
# Повторный запуск безопасен: каждый шаг пропускается, если уже выполнен.
# Подробности: SHORT-DEPLOY.md (кратко) и DEPLOY.md (подробно).
# ============================================================================
set -euo pipefail

# ---------- Locate package/repo ----------
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"      # package/<pkg>/run-migration.sh -> repo root

WORDPRESS_SRC="$REPO_ROOT/wordpress"
DUMP_FILE="$REPO_ROOT/database/old_dump.sql"
MOTORS_ZIP="$REPO_ROOT/new-theme/Motors/motors-5.6.95.zip"

WP_PHAR="$SCRIPT_DIR/bin/wp-cli.phar"
PHPR="$(command -v php || echo php)"

wp() { "$PHPR" "$WP_PHAR" --allow-root "$@"; }

log()  { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m  OK: %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m  !! %s\033[0m\n' "$*"; }

# ---------- Config ----------
CONF="${1:-}"
STAGE="${2:-deploy}"
UPLOADS_ARG="${3:-}"

if [ -z "$CONF" ]; then
    echo "Использование:"
    echo "  bash run-migration.sh config.env            # 1) развернуть сайт"
    echo "  bash run-migration.sh config.env uploads    # 2) uploads + финальная сверка"
    echo "  bash run-migration.sh config.env uploads /path/uploads.tar.gz"
    exit 1
fi
if [ ! -f "$CONF" ]; then
    echo "Файл конфигурации не найден: $CONF (скопируйте config.env.sample -> config.env и заполните)"
    exit 1
fi
# shellcheck disable=SC1090
source "$CONF"

: "${SITE_DIR:?задайте SITE_DIR в $CONF}"
: "${DOMAIN:=caranddriver.ru}"
: "${NEW_DOMAIN:=}"                # заполнять только если домен меняется
: "${DB_PREFIX:=wpla_}"
: "${UPLOADS_SRC:=$UPLOADS_ARG}"   # третий аргумент имеет приоритет
: "${TG_TOKEN:=}"
: "${TG_CHAT_ID:=}"

notify() { # Telegram-уведомление (необязательно)
    [ -n "$TG_TOKEN" ] && [ -n "$TG_CHAT_ID" ] || return 0
    curl -s -o /dev/null -X POST "https://api.telegram.org/bot${TG_TOKEN}/sendMessage" \
        -d chat_id="${TG_CHAT_ID}" -d text="$1" || true
}

# ============================================================================
if [ "$STAGE" = "deploy" ]; then
log "ПРЕДВАРИТЕЛЬНАЯ ПРОВЕРКА"
    command -v php >/dev/null || { echo "Нет php в PATH"; exit 1; }
    command -v mysql >/dev/null || { echo "Нет mysql-клиента (нужен для wp db create/import): apt install default-mysql-client"; exit 1; }
    [ -d "$WORDPRESS_SRC" ] || { echo "Нет $WORDPRESS_SRC — клонируйте репозиторий целиком"; exit 1; }
    [ -f "$DUMP_FILE" ]     || { echo "Нет $DUMP_FILE"; exit 1; }
    [ -f "$MOTORS_ZIP" ]    || { echo "Нет $MOTORS_ZIP"; exit 1; }
    ok "wp-cli: $($PHPR $WP_PHAR --version 2>/dev/null | head -1)"
    ok "PHP: $("$PHPR" -r 'echo PHP_VERSION;')"

# ---------- 1. Файлы WordPress ----------
log "ШАГ 1/9. Файлы WordPress из репозитория -> $SITE_DIR"
    if [ -f "$SITE_DIR/wp-settings.php" ]; then
        ok "ядро уже развёрнуто — пропускаю"
    else
        mkdir -p "$SITE_DIR"
        if command -v rsync >/dev/null 2>&1; then
            rsync -a "$WORDPRESS_SRC/" "$SITE_DIR/"
        else
            cp -a "$WORDPRESS_SRC/." "$SITE_DIR/"
        fi
        ok "скопировано ядро WordPress"
    fi
    cd "$SITE_DIR"

# ---------- 2. wp-config ----------
log "ШАГ 2/9. wp-config.php (префикс таблиц ${DB_PREFIX} — как в старом дампе)"
    if [ -f wp-config.php ]; then
        ok "wp-config.php уже есть — пропускаю"
    else
        wp config create --dbname="$DB_NAME" --dbuser="$DB_USER" --dbpass="$DB_PASS" \
            --dbhost="${DB_HOST:-localhost}" --dbprefix="$DB_PREFIX" --locale=ru_RU --force
        wp config set WP_MEMORY_LIMIT 256M
        ok "создан wp-config.php"
    fi

# ---------- 3. БД ----------
log "ШАГ 3/9. Создание БД (если отсутствует)"
    wp db create 2>/dev/null && ok "БД создана" || ok "БД уже существует"

# ---------- 4. Импорт дампа ----------
log "ШАГ 4/9. Импорт старой БД $(du -h "$DUMP_FILE" | cut -f1) (1-3 минуты)"
    POSTS_NOW=$(wp post list --post_type=post --post_status=publish --format=count 2>/dev/null || echo 0)
    if [ "${POSTS_NOW:-0}" -gt 100 ]; then
        ok "дамп уже импортирован (постов: $POSTS_NOW) — пропускаю"
    else
        wp db import "$DUMP_FILE"
        ok "дамп импортирован"
    fi

# ---------- 5. Домен ----------
log "ШАГ 5/9. Домен"
    if [ -n "$NEW_DOMAIN" ] && [ "$NEW_DOMAIN" != "$DOMAIN" ]; then
        wp search-replace "https://$DOMAIN" "$NEW_DOMAIN" --all-tables-with-prefix --report-changed-only | tail -1
        wp search-replace "http://$DOMAIN" "$NEW_DOMAIN" --all-tables-with-prefix --report-changed-only | tail -1
        ok "замена $DOMAIN -> $NEW_DOMAIN выполнена"
    else
        ok "домен не меняется — URL остаются https://$DOMAIN"
    fi
    wp transient delete --all >/dev/null 2>&1 || true
    wp cache flush >/dev/null 2>&1 || true
    wp core update-db >/dev/null 2>&1 || true

# ---------- 6. ЧПУ ----------
log "ШАГ 6/9. Постоянные ссылки (как на старом сайте — URL сохраняются 1:1)"
    wp rewrite structure '/%year%/%monthnum%/%day%/%postname%/'
    wp rewrite flush --hard >/dev/null 2>&1 || wp rewrite flush
    wp option update timezone_string 'Europe/Moscow'
    wp option update blog_public 1
    ok "ЧПУ /%year%/%monthnum%/%day%/%postname%/"

# ---------- 7. Темы ----------
log "ШАГ 7/9. Темы Motors + motors-child (демо-контент НЕ импортируется)"
    wp theme is-installed motors 2>/dev/null && ok "Motors уже установлен" || { wp theme install "$MOTORS_ZIP" --force; ok "Motors установлен"; }
    if [ -d "$SITE_DIR/wp-content/themes/motors-child" ]; then
        ok "motors-child уже установлен"
    else
        cp -a "$SCRIPT_DIR/motors-child" "$SITE_DIR/wp-content/themes/motors-child"
        ok "motors-child скопирован в wp-content/themes"
    fi
    wp theme activate motors-child
    ok "активная тема: motors-child (родитель motors не правится)"

    # Демо-импорт Motors использовать ЗАПРЕЩЕНО — затрёт контент старого сайта.

# ---------- 8. Плагины ----------
log "ШАГ 8/9. Плагины (yoast-seo — SEO-мета старых постов подхватится сама)"
    # Чистим "хвосты" старого списка активных плагинов (после импорта дампа в нём ссылки на отсутствующие файлы)
    wp option update active_plugins '[]' >/dev/null 2>&1 || true
    for PLUG in wordpress-seo cyr-to-lat captcha; do
        if wp plugin is-installed "$PLUG" 2>/dev/null; then
            wp plugin activate "$PLUG" >/dev/null 2>&1 && ok "$PLUG активирован" || warn "$PLUG не активирован"
        elif wp plugin install "$PLUG" --activate >/dev/null 2>&1; then
            ok "$PLUG установлен и активирован"
        else
            warn "$PLUG недоступен (нет сети?) — установите вручную, остальное не пострадает"
        fi
    done

# ---------- 9. Меню и сайдбар ----------
log "ШАГ 9/9. Меню (Main Menu -> primary) и перенос сайдбара (colormag_right_sidebar -> default)"
    wp menu location assign "Main Menu" primary 2>/dev/null && ok "меню привязано" \
        || warn "привяжите меню вручную: Внешний вид -> Меню"
    wp eval-file "$SCRIPT_DIR/server-scripts/migrate-sidebars.php" default && ok "виджеты правого сайдбара перенесены (настройки сохранены)"

# ---------- Контроль контента ----------
log "КОНТРОЛЬ: сверка контента со старым сайтом (baseline)"
    set +e
    wp eval-file "$SCRIPT_DIR/server-scripts/05-verify-content.php" "$SCRIPT_DIR/server-scripts/baseline.json"
    VERIFY=$?
    set -e
    if [ "$VERIFY" -eq 0 ]; then
        ok "все критичные метрики совпали"
    else
        warn "есть расхождения (строки FAIL) — проверьте до включения сайта"
    fi

notify "caranddriver.ru: развёртывание выполнено (без uploads). Осталось скопировать uploads и запустить stage 'uploads'."

echo
echo "======================================================================"
echo " САЙТ РАЗВЁРНУТ. Картинки пока 404 — так и задумано."
echo
echo " ПОСЛЕДНИЙ ШАГ — папка uploads. Затем финальная сверка:"
echo "   bash run-migration.sh $CONF uploads"
echo
echo " (путь к папке/архиву uploads — в $CONF -> UPLOADS_SRC"
echo "  или третьим аргументом этой команды)"
echo "======================================================================"
exit 0
fi

# ============================================================================
if [ "$STAGE" = "uploads" ]; then
log "ЗАГРУЗКА ПАПКИ uploads (последний шаг)"
    [ -n "$UPLOADS_SRC" ] || { echo "Укажите UPLOADS_SRC в $CONF или третьим аргументом"; exit 1; }
    cd "$SITE_DIR"

    mkdir -p wp-content/uploads
    if [ -d "$UPLOADS_SRC" ]; then
        log "Копирование из каталога $UPLOADS_SRC"
        if command -v rsync >/dev/null 2>&1; then
            rsync -a --info=progress2 "$UPLOADS_SRC/" wp-content/uploads/
        else
            cp -a "$UPLOADS_SRC/." wp-content/uploads/
        fi
    elif [ -f "$UPLOADS_SRC" ]; then
        log "Распаковка архива $UPLOADS_SRC ($(du -h "$UPLOADS_SRC" | cut -f1))"
        EXTRACT_TMP="$(mktemp -d)"
        case "$UPLOADS_SRC" in
            *.tar.gz|*.tgz) tar -xzf "$UPLOADS_SRC" -C "$EXTRACT_TMP" ;;
            *.tar)          tar -xf  "$UPLOADS_SRC" -C "$EXTRACT_TMP" ;;
            *.zip)          command -v unzip >/dev/null || { echo "Для .zip нужен unzip (или укажите .tar.gz)"; exit 1; }
                            unzip -oq "$UPLOADS_SRC" -d "$EXTRACT_TMP" ;;
            *) echo "Неизвестный формат архива: $UPLOADS_SRC"; exit 1 ;;
        esac
        if [ -d "$EXTRACT_TMP/uploads" ]; then
            rsync -a "$EXTRACT_TMP/uploads/" wp-content/uploads/
        else
            rsync -a "$EXTRACT_TMP/" wp-content/uploads/
        fi
        rm -rf "$EXTRACT_TMP"
    else
        echo "Не найдено: $UPLOADS_SRC"; exit 1
    fi

    if [ "$(id -u)" = "0" ] && id www-data >/dev/null 2>&1; then
        chown -R www-data:www-data wp-content/uploads
        find wp-content/uploads -type d -exec chmod 755 {} \;
        find wp-content/uploads -type f -exec chmod 644 {} \;
        ok "права 755/644, владелец www-data"
    fi

    log "СВЕРКА МЕДИАТЕКИ С РЕЕСТРОМ (8121 оригинал, 67769 файлов)"
    set +e
    wp eval-file "$SCRIPT_DIR/server-scripts/06-verify-images.php" "$SCRIPT_DIR/server-scripts/registries"
    set -e

    log "КАРТА 301 + финальная проверка URL"
    wp eval-file "$SCRIPT_DIR/server-scripts/07-redirects.php" "$SCRIPT_DIR/server-scripts/baseline-post-urls.tsv" || true
    wp rewrite flush >/dev/null 2>&1 || true

    notify "caranddriver.ru: миграция завершена — uploads загружены, сверка выполнена."

    echo
    echo "======================================================================"
    echo " ГОТОВО. Проверьте: главную (блоки как на старом сайте),"
    echo " любую статью (URL вида /ГГГГ/ММ/ДД/слаг/), картинки, меню."
    echo " sitemap: https://$DOMAIN/sitemap_index.xml -> Яндекс.Вебмастер + Search Console"
    echo " Подробности и откат: DEPLOY.md, ЭТАП 9."
    echo "======================================================================"
    exit 0
fi

echo "Неизвестный этап: $STAGE (допустимо: deploy, uploads)"
exit 1
