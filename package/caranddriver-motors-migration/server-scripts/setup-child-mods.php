<?php
/**
 * Перенос настроек старой темы в motors-child (запуск: wp eval-file setup-child-mods.php).
 *
 * Из option theme_mods_colormag копируются ВСЕ theme_mod в motors-child:
 *   - custom_logo (661 — лого сайта) и colormag_header_logo_placement;
 *   - nav_menu_locations: primary => 8 («Main Menu») — меню привязывается само;
 *   - colormag_breaking_news / date_display / home_icon / sticky / search_icon /
 *     random_post — полосы шапки как на старом сайте;
 *   - colormag_category_color_* — цвета рубрик;
 *   - colormag_favicon_upload — favicon старого сайта;
 *   - related_posts — блок похожих записей.
 *
 * Повторный запуск безопасен (значения перезаписываются теми же данными).
 *
 * @package motors-child (server-scripts)
 */

defined( 'ABSPATH' ) || exit;

$old = get_option( 'theme_mods_colormag', array() );

if ( empty( $old ) || ! is_array( $old ) ) {
        WP_CLI::warning( 'theme_mods_colormag пуст — настройки старой темы не найдены (дамп импортирован?)' );
        exit( 1 );
}

// Ключи-служебные, которые не переносим.
$skip = array( 0, 'custom_css_post_id', 'background_image', 'background_position_x',
        'background_position_y', 'background_preset', 'background_size',
        'background_repeat', 'background_attachment', 'header_image', 'header_image_data' );

$copied = array();
foreach ( $old as $key => $value ) {
        if ( in_array( $key, $skip, true ) ) {
                continue;
        }
        set_theme_mod( $key, $value );
        $copied[] = $key;
}

// Отдельные важные значения — выводим для контроля.
$logo     = get_theme_mod( 'custom_logo' );
$menus    = get_theme_mod( 'nav_menu_locations', array() );
$logo_pl  = get_theme_mod( 'colormag_header_logo_placement' );
$favicon  = get_theme_mod( 'colormag_favicon_upload' );

WP_CLI::success( 'Перенесено theme_mod: ' . count( $copied ) );
WP_CLI::log( 'custom_logo (ID вложения): ' . var_export( $logo, true ) );
WP_CLI::log( 'colormag_header_logo_placement: ' . var_export( $logo_pl, true ) );
WP_CLI::log( 'nav_menu_locations: ' . json_encode( $menus ) );
WP_CLI::log( 'favicon: ' . var_export( $favicon, true ) );
WP_CLI::log( 'цветов рубрик colormag_category_color_*: ' . count( preg_grep( '/^colormag_category_color_/', $copied ) ) );

// Контроль: лого должен существовать как вложение (картинка появится после копирования uploads).
if ( ! empty( $logo ) && null === get_post( $logo ) ) {
        WP_CLI::warning( 'Вложение лого #' . $logo . ' в БД не найдено! Проверьте импорт дампа.' );
}
