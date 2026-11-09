<?php
/**
 * ШАГ 5. Проверка контента после миграции — ЭТАП 7 плана (таблица сравнения старое/новое).
 *
 * Запуск:  wp eval-file 05-verify-content.php [путь-к-baseline.json]
 * Результат: таблица в stdout + verify-content.json рядом с baseline.
 * Код выхода 1 при расхождениях — удобно для автоматизации.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
        echo "Запуск: wp eval-file 05-verify-content.php [baseline.json]\n";
        exit( 1 );
}

$baseline_path = isset( $args[0] ) ? $args[0] : __DIR__ . '/baseline.json';
$baseline      = json_decode( (string) file_get_contents( $baseline_path ), true );

if ( ! is_array( $baseline ) ) {
        WP_CLI::error( 'Не удалось прочитать baseline.json: ' . $baseline_path );
}

$c   = $baseline['counts'];
$rows = array();

/**
 * @param string $metric Название метрики
 * @param int    $actual Текущее значение
 * @param int    $expect Ожидаемое (старое) значение
 * @param bool   $hard   Критичное ли расхождение
 */
function cd_row( $metric, $actual, $expect, $hard = true ) {
        global $rows;
        $ok     = ( (int) $actual === (int) $expect );
        $status = $ok ? 'OK' : ( $hard ? 'FAIL' : 'WARN' );
        $rows[] = array( $metric, (string) $expect, (string) $actual, $status );
        return $ok;
}

$checks_ok = true;

// --- Данные (высший приоритет) ---
$posts_pub = (int) wp_count_posts( 'post' )->publish;
$checks_ok = cd_row( 'Постов (publish)', $posts_pub, $c['posts_publish'] ) && $checks_ok;

$pages_n = (int) wp_count_posts( 'page' )->publish;
$checks_ok = cd_row( 'Страниц (publish)', $pages_n, $c['pages'] ) && $checks_ok;

$attach_n = (int) wp_count_posts( 'attachment' )->inherit;
$checks_ok = cd_row( 'Вложений (медиатека)', $attach_n, $c['attachments'] ) && $checks_ok;

global $wpdb;
$comments = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved IN ('1','0')" );
$checks_ok = cd_row( 'Комментариев (одобр. + на модерации)', $comments, $c['comments_approved_all'] ) && $checks_ok;

$cats = (int) wp_count_terms( array( 'taxonomy' => 'category', 'hide_empty' => false ) );
$checks_ok = cd_row( 'Рубрик', $cats, $c['categories'] ) && $checks_ok;

$tags = (int) wp_count_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) );
$checks_ok = cd_row( 'Меток', $tags, $c['tags'] ) && $checks_ok;

$users_n = (int) count_users()['total_users'];
$checks_ok = cd_row( 'Пользователей', $users_n, $c['users'] ) && $checks_ok;

// --- Структура ---
$menus = (array) wp_get_nav_menus();
$checks_ok = cd_row( 'Меню', count( $menus ), $c['nav_menus'] ) && $checks_ok;

$menu_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy='nav_menu_item'" );
$checks_ok = cd_row( 'Элементов меню', $menu_items, $c['nav_menu_items'] ) && $checks_ok;

// --- Настройки ---
$checks_ok = cd_row( 'Структура постоянных ссылок', get_option( 'permalink_structure' ) === $baseline['permalink_structure'] ? 1 : 0, 1 ) && $checks_ok;
$checks_ok = cd_row( 'Главная = статическая страница (show_on_front)', get_option( 'show_on_front' ) === $baseline['show_on_front'] ? 1 : 0, 1 ) && $checks_ok;
$checks_ok = cd_row( 'page_on_front = ' . $baseline['page_on_front'] . ' («Автомобильный портал»)', (int) get_option( 'page_on_front' ), (int) $baseline['page_on_front'] ) && $checks_ok;
$checks_ok = cd_row( 'Название сайта', get_option( 'blogname' ) === $baseline['blogname'] ? 1 : 0, 1, false ) && $checks_ok;

// --- SEO (Yoast) ---
$yoast_title = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_yoast_wpseo_title'" );
$yoast_desc  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key='_yoast_wpseo_metadesc'" );
$checks_ok = cd_row( 'SEO-заголовки Yoast (постмета _yoast_wpseo_title)', $yoast_title, $baseline['yoast_titles'], false ) && $checks_ok;
$checks_ok = cd_row( 'SEO-описания Yoast (_yoast_wpseo_metadesc)', $yoast_desc, $baseline['yoast_desc'], false ) && $checks_ok;

// --- Виджеты главной (функционал дочерней темы) ---
$sw       = (array) get_option( 'sidebars_widgets', array() );
$fp_areas = array(
        'colormag_front_page_slider_area',
        'colormag_front_page_area_beside_slider',
        'colormag_front_page_content_top_section',
        'colormag_front_page_content_middle_left_section',
        'colormag_front_page_content_middle_right_section',
);
$fp_filled = 0;
foreach ( $fp_areas as $area ) {
        if ( ! empty( $sw[ $area ] ) ) {
                $fp_filled++;
        }
}
$checks_ok = cd_row( 'Заполненных областей главной (виджеты ColorMag)', $fp_filled, 5, false ) && $checks_ok;

// --- Тема ---
$theme = wp_get_theme();
WP_CLI::log( 'Активная тема: ' . $theme->get( 'Name' ) . ' v' . $theme->get( 'Version' ) . ' (родитель: ' . $theme->get( 'Template' ) . ')' );

// --- Вывод таблицы ---
WP_CLI::log( '' );
WP_CLI::log( sprintf( '%-58s %12s %12s %6s', 'Метрика', 'Старое', 'Новое', 'Статус' ) );
WP_CLI::log( str_repeat( '-', 92 ) );
foreach ( $rows as $r ) {
        WP_CLI::log( vsprintf( '%-58s %12s %12s %6s', $r ) );
}

// --- Сохранение JSON для отчёта ---
$out = array(
        'checked_at'     => gmdate( 'c' ),
        'active_theme'   => $theme->get_stylesheet(),
        'parent_theme'   => $theme->get( 'Template' ),
        'wp_version'     => get_bloginfo( 'version' ),
        'siteurl'        => get_option( 'siteurl' ),
        'all_ok'         => $checks_ok,
        'rows'           => array_map( function ( $r ) {
                return array( 'metric' => $r[0], 'old' => $r[1], 'new' => $r[2], 'status' => $r[3] );
        }, $rows ),
        'baseline_file'  => $baseline_path,
);
$out_path = dirname( $baseline_path ) . '/verify-content.json';
file_put_contents( $out_path, wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );

WP_CLI::log( '' );
WP_CLI::log( 'JSON: ' . $out_path );

if ( $checks_ok ) {
        WP_CLI::success( 'Все критичные метрики совпали со старым сайтом.' );
} else {
        WP_CLI::error( 'Есть расхождения с baseline — проверьте строки FAIL (контент/структура должны совпадать 1:1).' );
}
