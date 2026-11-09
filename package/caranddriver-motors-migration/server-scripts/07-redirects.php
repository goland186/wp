<?php
/**
 * ШАГ 7. Карта 301-редиректов — ЭТАП 8 плана (SEO/URL).
 *
 * Структура постоянных ссылок не менялась (/%year%/%monthnum%/%day%/%postname%/),
 * домен не менялся — для постов редиректы НЕ требуются. Скрипт:
 *  1) сверяет URL 1198 опубликованных постов с baseline-post-urls.tsv;
 *  2) генерирует 301 для реально изменившихся URL;
 *  3) отдаёт готовые блоки nginx / .htaccess (Apache).
 *
 * Запуск:  wp eval-file 07-redirects.php [baseline-post-urls.tsv]
 * Результат: redirects-map.csv + готовые конфиг-фрагменты redirects-nginx.conf,
 *            redirects-apache.conf рядом с baseline.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
        echo "Запуск: wp eval-file 07-redirects.php [baseline-post-urls.tsv]\n";
        exit( 1 );
}

$tsv_path = isset( $args[0] ) ? $args[0] : __DIR__ . '/baseline-post-urls.tsv';
$site_url = untrailingslashit( home_url() );
$out_dir  = dirname( $tsv_path );

// --- Текущие URL всех опубликованных постов ---
$current = array();
$posts   = get_posts( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
) );
foreach ( $posts as $pid ) {
        $permalink      = get_permalink( $pid );
        $current[ $pid ] = $permalink;
}

// --- Сверка с baseline ---
$map = array();  // old_url => new_url
if ( file_exists( $tsv_path ) ) {
        $fh = fopen( $tsv_path, 'r' );
        fgetcsv( $fh, 0, "\t", '"', '"' ); // заголовок
        while ( ( $row = fgetcsv( $fh, 0, "\t", '"', '"' ) ) !== false ) {
                $pid     = (int) $row[0];
                $old_url = trim( (string) $row[1] );

                if ( ! isset( $current[ $pid ] ) ) {
                        // Пост исчез (не должен, но на всякий случай) — редирект на главную.
                        $map[ $old_url ] = home_url( '/' );
                        continue;
                }

                $new_url = $current[ $pid ];

                // Приводим старый URL к текущей схеме (http/https) и сравниваем путь.
                $old_path = parse_url( str_replace( 'http://', 'https://', $old_url ), PHP_URL_PATH );
                $new_path = parse_url( $new_url, PHP_URL_PATH );

                if ( $old_path !== $new_path ) {
                        $map[ $old_url ] = $new_url;
                }
        }
        fclose( $fh );
} else {
        WP_CLI::warning( 'baseline-post-urls.tsv не найден — сравнение с baseline пропущено.' );
}

// --- Дополнительно: старые фиды отключённых плагинов (Турбо/Dzen) ---
// Плагины rss-for-yandex-turbo / rss-for-yandex-zen отдавали кастомные фиды;
// сервисы закрыты — направляем запросы к ним на стандартный /feed/.
$map['/feed/turbo'] = home_url( '/feed/' );
$map['/feed/zen']   = home_url( '/feed/' );

// --- CSV ---
$csv = $out_dir . '/redirects-map.csv';
$fh  = fopen( $csv, 'w' );
fputcsv( $fh, array( 'from', 'to', 'code' ) );
foreach ( $map as $from => $to ) {
        fputcsv( $fh, array( $from, $to, 301 ) );
}
fclose( $fh );

// --- nginx ---
$nginx = "# Карта 301-редиректов caranddriver.ru — сгенерировано 07-redirects.php\n";
$nginx .= "# Вставить в секцию server { } и выполнить nginx -t && systemctl reload nginx\n";
foreach ( $map as $from => $to ) {
        if ( 0 === strpos( $from, '/' ) ) {
                $nginx .= "  location = {$from} { return 301 {$to}; }\n";
        } else {
                $old_path = parse_url( $from, PHP_URL_PATH );
                $nginx .= "  location = {$old_path} { return 301 {$to}; }\n";
        }
}
file_put_contents( $out_dir . '/redirects-nginx.conf', $nginx );

// --- Apache (.htaccess) ---
$apache = "# Карта 301-редиректов caranddriver.ru — сгенерировано 07-redirects.php\n";
$apache .= "# Добавить в .htaccess ПЕРЕД блоком WordPress\n";
$apache .= "RewriteEngine On\n";
foreach ( $map as $from => $to ) {
        if ( 0 === strpos( $from, '/' ) ) {
                $apache .= "Redirect 301 {$from} {$to}\n";
        } else {
                $old_path = parse_url( $from, PHP_URL_PATH );
                $apache .= "Redirect 301 {$old_path} {$to}\n";
        }
}
file_put_contents( $out_dir . '/redirects-apache.conf', $apache );

// --- Сводка ---
WP_CLI::log( '' );
WP_CLI::log( 'Записей в карте редиректов: ' . count( $map ) );
WP_CLI::log( 'URL изменённых постов (нужен 301): ' . max( 0, count( $map ) - 2 ) );
WP_CLI::log( 'Отключённые фиды (turbo/zen -> /feed/): 2' );
WP_CLI::log( '' );
WP_CLI::log( 'Файлы:' );
WP_CLI::log( '  ' . $csv );
WP_CLI::log( '  ' . $out_dir . '/redirects-nginx.conf' );
WP_CLI::log( '  ' . $out_dir . '/redirects-apache.conf' );

if ( count( $map ) <= 2 ) {
        WP_CLI::success( 'Все старые URL постов совпадают с новыми — структурных 301 не требуется (только фиды).' );
} else {
        WP_CLI::warning( 'Обнаружены изменившиеся URL — добавьте сгенерированную карту в nginx/.htaccess.' );
}
