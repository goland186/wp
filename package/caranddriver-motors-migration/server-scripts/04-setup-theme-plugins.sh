#!/usr/bin/env bash
# ============================================================
# ШАГ 4. Темы и плагины — ЭТАПЫ 4-6 плана (Motors + дочерняя тема + сохранение функционала).
# Запускается НА НОВОМ сервере ПОСЛЕ 03-import-db.sh.
# Переменные окружения:
#   SITE_DIR        — корень сайта
#   MOTORS_ZIP      — путь к new-theme/Motors/motors-5.6.95.zip
#   CHILD_ZIP       — путь к сервер-scripts/../motors-child/ ИЛИ motors-child.zip
# ВАЖНО: демо-импорт Motors НЕ запускать — затрёт контент старого сайта!
# ============================================================
set -euo pipefail

SITE_DIR="${SITE_DIR:?задайте SITE_DIR}"
MOTORS_ZIP="${MOTORS_ZIP:?задайте MOTORS_ZIP (путь к motors-5.6.95.zip)}"

cd "$SITE_DIR"
command -v wp >/dev/null 2>&1 || { echo "Установите wp-cli"; exit 1; }

echo "[1/8] Установка родительской темы Motors (motors-5.6.95)"
wp theme install "$MOTORS_ZIP" --force --allow-root

echo "[2/8] Установка дочерней темы motors-child (кастомизации только в ней)"
CHILD_SRC="${CHILD_SRC:-$(dirname "$0")/../motors-child}"
wp theme install "$CHILD_SRC" --force --allow-root

echo "[3/8] Активация motors-child (родитель Motors остаётся нетронутым)"
wp theme activate motors-child --allow-root

echo "[4/8] Очистка 'хвостов' старого списка плагинов (missing-записи после импорта БД)"
wp plugin list --status=active --field=name --allow-root | while read -r p; do
    wp plugin deactivate "$p" --allow-root 2>/dev/null || true
done

echo "[5/8] Установка актуальных плагинов из старого набора"
# Yoast SEO — СЕО-мета всех постов (_yoast_wpseo_* в мета) подхватится сама
wp plugin install wordpress-seo --activate --allow-root
# Транслитерация (cyr3lat) — для новых постов/рубрик; старые slug уже транслитерированы
wp plugin install cyr-to-lat --activate --allow-root || echo "  cyr-to-lat недоступен — необязательно"
# Капча для форм (старый плагин captcha от BestWebSoft)
wp plugin install captcha --activate --allow-root || echo "  captcha недоступна — необязательно"

echo "[6/8] Плагины, ЗАКРЫТЫЕ сервисами (устанавливать НЕЛЬЗЯ, см. отчёт миграции):"
echo "  - rss-for-yandex-turbo  (Турбо-страницы отключены Яндексом в 2022)"
echo "  - rss-for-yandex-zen    (плагин устарел, сервис сменил API)"
echo "  - uptolike-share        (сервис UpToLike закрыт)"
echo "  - google-analytics-dashboard-for-wp (старый GA; рекомендация: Site Kit by Google)"
echo "  Решение по аналогам — см. migration-report.md, раздел 'Нерешённые вопросы'."

echo "[7/8] Основные настройки сайта"
wp option update timezone_string 'Europe/Moscow' --allow-root
wp option update blog_public 1 --allow-root
wp rewrite flush --hard --allow-root

echo "[8/8] Меню и сайдбары"
echo "-- доступные локации меню в Motors:"
wp menu location list --allow-root || true
# Автопривязка главного меню в первую подходящую локацию
LOC=$(wp menu location list --fields=location --format=csv --allow-root | tail -n +2 | grep -Ei '^(primary|main|stm-main|header)' | head -1 || true)
if [ -n "${LOC:-}" ]; then
    echo "-- привязка 'Main Menu' -> '$LOC'"
    wp menu location assign "Main Menu" "$LOC" --allow-root || true
else
    echo "!! Привяжите меню 'Main Menu' вручную: Внешний вид -> Меню -> Управление областями"
fi
echo "-- перенос виджетов правого сайдбара ColorMag в Motors:"
wp sidebar list --fields=id,name --allow-root || true
echo "   выберите целевую область и выполните:"
echo "   wp eval-file migrate-sidebars.php <target_sidebar_id>"

echo "ГОТОВО. Демо-контент Motors НЕ устанавливался; страницы и посты старого сайта сохранены."
