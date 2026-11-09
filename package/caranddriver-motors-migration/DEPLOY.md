# Инструкция по развёртыванию caranddriver.ru на WordPress + Motors

Сайт: **https://caranddriver.ru** — автомобильный портал (1198 публикаций, 8121 медиафайл, 21 425 комментариев).
Старая среда: WordPress 4.6.1, тема ColorMag 1.3.5, MySQL 5.7, PHP 5.4.
Новая среда: WordPress 6.x (последняя), тема **Motors 5.6.95** + дочерняя тема **motors-child** (в пакете), PHP 8.1–8.3, MySQL/MariaDB 5.7+/8.

**Ключевой принцип: URL, слаги, структура постоянных ссылок и пути к картинкам НЕ меняются.** Папка `uploads/` загружается **последним шагом** — до этого картинки будут отдавать 404, это ожидаемо и проверяется отдельным шагом.

---

## Состав пакета

```
caranddriver-motors-migration/
├── DEPLOY.md                 ← этот документ
├── README.md                 — обзор продукта и критерии приёмки
├── migration-report.md       — таблица сравнения старое/новое + нерешённые вопросы
├── motors-child/             — готовая дочерняя тема (загружается как есть)
│   ├── style.css, functions.php, front-page.php, screenshot.jpg
│   ├── inc/cd-legacy-functions.php   — хелперы ColorMag (цвета рубрик и пр.)
│   ├── inc/cd-legacy-widgets.php     — области виджетов со СТАРЫМИ id (colormag_*)
│   ├── inc/widgets/…                 — 7 виджетов ColorMag (слайдер, избранные, баннеры)
│   └── assets/css/cd-legacy.css      — стили перенесённых блоков
└── server-scripts/
    ├── 01-backup.sh              — бэкап старого сервера (файлы + БД + uploads)
    ├── 02-deploy-wp.sh           — чистая установка WP последней версии
    ├── 03-import-db.sh           — импорт old_dump.sql + замена домена + ЧПУ
    ├── 04-setup-theme-plugins.sh — Motors + motors-child + плагины + меню
    ├── migrate-sidebars.php      — перенос виджетов правого сайдбара в Motors
    ├── 05-verify-content.php     — сверка контента с baseline (таблица сравнения)
    ├── 06-verify-images.php      — сверка медиатеки с реестром (после uploads!)
    ├── 07-redirects.php          — карта 301 (nginx/.htaccess)
    ├── 08-notify-telegram.sh     — уведомления (опционально)
    ├── baseline.json             — эталонные счётчики старого сайта
    ├── baseline-post-urls.tsv    — 1198 URL старых постов (для сверки 301)
    └── registries/               — реестры ссылок на картинки (CSV)
```

`database/old_dump.sql` берётся из репозитория `goland186/wp` (папка `database/`), демо-данные Motors **не используются**.

---

## Требования к серверу

| Компонент | Требование |
|---|---|
| PHP | 8.1–8.3 (модули: mysql, curl, gd, imagick/pecl, mbstring, xml, zip) |
| MySQL/MariaDB | 5.7+ / 10.3+ |
| Диск | ≥ 15 ГБ (ядро + тема ~500 МБ; `uploads/` будет загружен отдельно, размер уточняется по бэкапу) |
| wp-cli | обязателен (`curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar` и т. д.) |
| Домен | caranddriver.ru с SSL (Let's Encrypt); DNS переключается после шага 9 |

---

## ЭТАП 1. Бэкап старого сервера (Шаг 1 плана миграции)

На **старом** сервере:

```bash
bash 01-backup.sh /var/www/caranddriver.ru /root/backups
```

Скрипт создаст: `site-files.tar.gz` (все файлы), `db-old_dump.sql` (БД), `uploads.tar.gz` (медиатека), `SHA256SUMS`.

> **Контрольная точка:** скопируйте папку бэкапа на внешнее хранилище. Только после этого продолжайте.

## ЭТАП 2. Подготовка нового сервера

1. Создайте пустую БД и пользователя:

```sql
CREATE DATABASE wp_cdr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp_cdr'@'localhost' IDENTIFIED BY 'СЛОЖНЫЙ_ПАРОЛЬ';
GRANT ALL PRIVILEGES ON wp_cdr.* TO 'wp_cdr'@'localhost';
FLUSH PRIVILEGES;
```

2. Проверьте версии: `php -v` (8.1+), `mysql --version` (5.7+), `wp --version`.

## ЭТАП 3. Чистая установка WordPress (Шаг 2 плана)

```bash
cd /root/migration
SITE_DIR=/var/www/caranddriver.ru DOMAIN=caranddriver.ru \
DB_NAME=wp_cdr DB_USER=wp_cdr DB_PASS='СЛОЖНЫЙ_ПАРОЛЬ' \
bash server-scripts/02-deploy-wp.sh
```

**Важно:** в `wp-config.php` будет задан префикс таблиц `wpla_` — **именно так префиксованы таблицы в старом дампе**; если поставить другой, импорт не подхватится. `wp core install` НЕ запускать — сайт наполняется импортом старой БД.

## ЭТАП 4. Импорт старой базы данных (Шаг 3 плана)

Загрузите `database/old_dump.sql` из репозитория на сервер (`/root/migration/old_dump.sql`), затем:

```bash
SITE_DIR=/var/www/caranddriver.ru DUMP_FILE=/root/migration/old_dump.sql \
bash server-scripts/03-import-db.sh
```

Скрипт: импортирует дамп (1–3 минуты), при смене домена выполнит `wp search-replace` (в нашем случае домен не меняется — замена пропускается), чистит транзиенты, задаёт ЧПУ `/%year%/%monthnum%/%day%/%postname%/` (как на старом сайте — все URL сохраняются 1:1) и обновляет схему БД под новую версию WP.

> **Контрольная точка:** в конце скрипт печатает счётчики: постов **1198**, страниц **2**, вложений **8121**, комментариев **21 425**. Числа должны совпасть.

## ЭТАП 5. Темы и плагины (Шаги 4–6 плана)

1. Загрузите на сервер папку `motors-child/` и архив `motors-5.6.95.zip` (из репозитория `new-theme/Motors/`).

2. Запустите:

```bash
SITE_DIR=/var/www/caranddriver.ru \
MOTORS_ZIP=/root/migration/motors-5.6.95.zip \
bash server-scripts/04-setup-theme-plugins.sh
```

Скрипт установит и активирует **motors-child** (родительский Motors останется нетронутым), установит актуальные плагины (Yoast SEO — SEO-мета постов подхватится из мета `_yoast_wpseo_*`, транслитерация, капча), очистит «хвосты» старого списка плагинов и выведет список локаций меню Motors.

3. **Демо-контент Motors НЕ импортировать.** Запуск Motors Demo Importer затрёт страницы и порядок блоков старого сайта — критерий приёмки нарушится.

4. Меню: если автопривязка не сработала, назначьте вручную: **Внешний вид → Меню → Управление областями** — меню «Main Menu» (9 пунктов) в главную локацию Motors.

5. Сайдбар: посмотрите доступные области (`wp sidebar list --fields=id,name`), выберите боковую область Motors для записей и перенесите старые виджеты (настройки сохранятся, классы виджетов зарегистрированы в motors-child):

```bash
wp eval-file server-scripts/migrate-sidebars.php <target_sidebar_id> --dry-run
wp eval-file server-scripts/migrate-sidebars.php <target_sidebar_id>
```

> **Контрольная точка:** `wp option get stylesheet` → `motors-child`; главная = страница ID 8 «Автомобильный портал» (show_on_front=page).

## ЭТАП 6. Проверка контента и структуры (Шаг 7 плана) — ДО загрузки uploads

```bash
wp eval-file server-scripts/05-verify-content.php
```

Скрипт сравнивает 16 метрик с `baseline.json` (посты, страницы, вложения, комментарии, рубрики, метки, пользователи, меню, элементы меню, ЧПУ, главная, Yoast-мета, заполненность областей главной) и печатает таблицу «Старое / Новое / Статус» + сохраняет `verify-content.json` для отчёта.

Ожидаемо на этом шаге: **картинки на сайте показываются как битые (404)** — это норма, файлы ещё не загружены. Критично, чтобы совпали счётчики контента и работали меню/структура.

> **Контрольная точка:** строка `FAIL` в таблице запрещает переход к следующему этапу — сначала устраните расхождение.

## ЭТАП 7. ЗАГРУЗКА ПАПКИ uploads (последний шаг по регламенту)

Медиатека — самый объёмный артефакт (8121 оригинал, с размерными вариантами — **67 769 файлов**). Структура `uploads/ГГГГ/ММ/имя-файла` сохраняется как есть, пути в БД не менялись.

**Вариант А — rsync со старого сервера (рекомендуется, докачивается при обрыве):**

```bash
rsync -avz --info=progress2 \
  /var/www/caranddriver.ru/wp-content/uploads/ \
  user@new-server:/var/www/caranddriver.ru/wp-content/uploads/
```

**Вариант Б — из архива бэкапа (uploads.tar.gz из ЭТАПА 1):**

```bash
tar -xzf /root/backups/migration-backup-*/uploads.tar.gz \
  -C /var/www/caranddriver.ru/wp-content/
chown -R www-data:www-data /var/www/caranddriver.ru/wp-content/uploads
find /var/www/caranddriver.ru/wp-content/uploads -type d -exec chmod 755 {} \;
find /var/www/caranddriver.ru/wp-content/uploads -type f -exec chmod 644 {} \;
```

**Вариант В — через панель/FTP:** загрузите содержимое старого `wp-content/uploads/` в такой же путь нового сервера. Не переименовывайте файлы и папки — реестр ссылок привязан к точным именам.

## ЭТАП 8. Сверка медиатеки и ссылок на картинки

```bash
wp eval-file server-scripts/06-verify-images.php
```

Скрипт сверяет **все** 8121 оригинал + 67 769 ожидаемых файлов (включая размерные варианты 130x90, 150x150, 390x205 и т. д.) с реестром `registries/`, а также все внутренние ссылки `/wp-content/uploads/...` из контента 1210 постов. Итог: сводка в stdout и `missing-uploads.csv` с точным списком недостающих файлов (если есть).

> **Контрольная точка:** «Все 8121 оригиналов вложений на месте». Если есть пропуски — докачайте файлы из бэкапа (имена менять нельзя) и повторите.

## ЭТАП 9. SEO, 301-редиректы, включение сайта (Шаг 8 плана)

1. **Карта 301:** структура ЧПУ и домен не менялись — редиректы для постов не нужны; скрипт это проверит по всем 1198 URL baseline и сгенерирует конфиги (включая редиректы закрытых фидов Турбо/Dzen на `/feed/`):

```bash
wp eval-file server-scripts/07-redirects.php
# при изменившихся URL: перенесите redirects-nginx.conf в конфиг nginx
#   (или redirects-apache.conf в .htaccess) и перезагрузите сервис
```

2. **Yoast SEO:** SEO → Общие → убедиться, что сайт не «закрыт от поисковиков»; XML-карта сайта: `https://caranddriver.ru/sitemap_index.xml`.

3. **robots.txt** (по умолчанию создаёт WordPress/Yoast) — убедитесь, что `Sitemap: https://caranddriver.ru/sitemap_index.xml` присутствует.

4. **Поисковые консоли:** Яндекс.Вебмастер и Google Search Console — добавить/подтвердить домен, отправить sitemap_index.xml, включить «Переезд сайта»/«изменение адреса» только если домен менялся (в нашем случае — нет).

5. Переключите DNS/веб-сервер на новую версию, снимите техобслуживание. Старый сервер держите выключенным, но с данными — минимум 2 недели (откат).

6. Уведомление (опционально): `TG_TOKEN=… TG_CHAT_ID=… MSG="Миграция caranddriver.ru завершена" bash 08-notify-telegram.sh`.

---

## Быстрая шпаргалка (весь конвейер)

| # | Действие | Где | Скрипт/команда |
|---|---|---|---|
| 1 | Бэкап старого сервера | старый сервер | `01-backup.sh` |
| 2 | Новая пустая БД | новый сервер | SQL из ЭТАПА 2 |
| 3 | Чистый WP 6.x (префикс `wpla_`) | новый сервер | `02-deploy-wp.sh` |
| 4 | Импорт `old_dump.sql` + ЧПУ | новый сервер | `03-import-db.sh` |
| 5 | Motors + motors-child + плагины | новый сервер | `04-setup-theme-plugins.sh` |
| 6 | Меню + перенос сайдбара | новый сервер | `migrate-sidebars.php` |
| 7 | Сверка контента с baseline | новый сервер | `05-verify-content.php` |
| **8** | **Загрузка папки uploads** | старый → новый | **rsync / tar (ЭТАП 7)** |
| 9 | Сверка медиатеки с реестром | новый сервер | `06-verify-images.php` |
| 10 | Карта 301 + sitemap + консоли | новый сервер | `07-redirects.php` |

## Откат

Если после включения сайта найдены критические проблемы: остановите веб-сервер нового сайта, верните DNS на старый сервер (данные старого сервера не удалялись — ЭТАП 1). Новая БД не затрагивалась. Решите проблему, повторите ЭТАПЫ 7–9.
