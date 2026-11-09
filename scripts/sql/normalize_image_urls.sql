-- =====================================================================
-- Нормализация ссылок на изображения (caranddriver.ru)
-- Выполнять НА КОПИИ базы перед импортом в новый WordPress.
-- Префикс таблиц: wpla_ (при импорте в новый сайт замените на актуальный).
-- Правило: http:// -> https:// для внутреннего домена; GUID не трогаем
-- (guid — постоянный идентификатор записи, WordPress его не использует
--  для рендеринга, менять его опасно для совместимости фидов).
-- =====================================================================

-- --- 0. Контрольные счётчики ДО (замер на дампе 2026-11-09) -----------
-- post_content http://caranddriver.ru  : 1084 строк (затронуто записей)
-- post_content http://www.             : 0
-- postmeta     http://                 : 4 строки
-- options      http://                 : 8 строк
-- termmeta     http://                 : 0
SELECT 'post_content http://' AS step, COUNT(*) AS n
FROM wpla_posts
WHERE post_content LIKE '%http://caranddriver.ru/wp-content/uploads/%';

SELECT 'post_content http://www' AS step, COUNT(*) AS n
FROM wpla_posts
WHERE post_content LIKE '%http://www.caranddriver.ru/wp-content/uploads/%';

SELECT 'postmeta http://' AS step, COUNT(*) AS n
FROM wpla_postmeta
WHERE meta_value LIKE '%http://caranddriver.ru/wp-content/uploads/%'
   OR meta_value LIKE '%http://www.caranddriver.ru/wp-content/uploads/%';

SELECT 'options http://' AS step, COUNT(*) AS n
FROM wpla_options
WHERE option_value LIKE '%http://caranddriver.ru/wp-content/uploads/%'
   OR option_value LIKE '%http://www.caranddriver.ru/wp-content/uploads/%';

-- --- 1. post_content: http -> https -----------------------------------
UPDATE wpla_posts
SET post_content = REPLACE(post_content, 'http://www.caranddriver.ru/', 'https://caranddriver.ru/')
WHERE post_content LIKE '%http://www.caranddriver.ru/%';

UPDATE wpla_posts
SET post_content = REPLACE(post_content, 'http://caranddriver.ru/', 'https://caranddriver.ru/')
WHERE post_content LIKE '%http://caranddriver.ru/%';

-- --- 2. postmeta: http -> https (Yoast, slideshow, произвольные поля) --
UPDATE wpla_postmeta
SET meta_value = REPLACE(meta_value, 'http://www.caranddriver.ru/', 'https://caranddriver.ru/')
WHERE meta_value LIKE '%http://www.caranddriver.ru/%';

UPDATE wpla_postmeta
SET meta_value = REPLACE(meta_value, 'http://caranddriver.ru/', 'https://caranddriver.ru/')
WHERE meta_value LIKE '%http://caranddriver.ru/%';

-- --- 2b. termmeta: http -> https (миниатюры рубрик, если появятся) ------
UPDATE wpla_termmeta
SET meta_value = REPLACE(meta_value, 'http://caranddriver.ru/', 'https://caranddriver.ru/')
WHERE meta_value LIKE '%http://caranddriver.ru/%';

-- --- 3. options: http -> https (theme_mods, виджеты-баннеры, логотипы) -
-- ВАЖНО: option_value часто сериализован (PHP serialize), длины строк
-- s:NN:"..." ломаются при REPLACE. Для wpla_options использовать wp-cli:
--   wp search-replace 'http://www.caranddriver.ru' 'https://caranddriver.ru' --all-tables --precise
--   wp search-replace 'http://caranddriver.ru' 'https://caranddriver.ru' --all-tables --precise
-- Сырой SQL ниже — только для справки и валидации.

-- --- 4. Контроль ПОСЛЕ (все COUNT должны быть 0) -----------------------
SELECT 'post_content leftover' AS step, COUNT(*) AS n
FROM wpla_posts WHERE post_content LIKE '%http://caranddriver.ru/%';

SELECT 'postmeta leftover' AS step, COUNT(*) AS n
FROM wpla_postmeta WHERE meta_value LIKE '%http://caranddriver.ru/%';

SELECT 'options leftover' AS step, COUNT(*) AS n
FROM wpla_options WHERE option_value LIKE '%http://caranddriver.ru/%';

-- --- 5. GUID не меняем (проверка состава — только информативно) -------
SELECT SUM(guid LIKE 'http://caranddriver%') AS guid_http,
       SUM(guid LIKE 'https://caranddriver%') AS guid_https
FROM wpla_posts WHERE post_type = 'attachment';
