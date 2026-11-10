<?php
/**
 * Перенос виджетов старого правого сайдбара ColorMag в сайдбар Motors.
 *
 * Запуск:  wp eval-file migrate-sidebars.php <target_sidebar_id> [--dry-run]
 * Список доступных сайдбаров:  wp sidebar list --fields=id,name
 *
 * Экземпляры виджетов (баннеры 300x250, TG: Featured Posts Vertical) уже
 * лежат в БД (widget_colormag_*); дочерняя тема регистрирует их классы,
 * поэтому достаточно перенести список назначений из colormag_right_sidebar
 * в выбранную область Motors — настройки сохранятся.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "Скрипт предназначен для запуска через wp-cli: wp eval-file migrate-sidebars.php <target_sidebar_id>\n";
	exit( 1 );
}

$target = isset( $args[0] ) ? trim( $args[0] ) : '';
$dry    = in_array( '--dry-run', (array) $args, true );

if ( '' === $target ) {
	WP_CLI::log( 'Доступные области виджетов:' );
	WP_CLI::runcommand( 'sidebar list --fields=id,name', array( 'return' => true, 'launch' => false ) );
	WP_CLI::error( 'Укажите целевую область: wp eval-file migrate-sidebars.php <target_sidebar_id>' );
}

$sidebars = (array) get_option( 'sidebars_widgets', array() );

if ( empty( $sidebars['colormag_right_sidebar'] ) ) {
	WP_CLI::warning( 'colormag_right_sidebar пуста — переносить нечего (возможно, уже перенесено).' );
	exit( 0 );
}

if ( ! isset( $sidebars[ $target ] ) ) {
	WP_CLI::error( "Область '$target' не найдена. Сначала зарегистрируйте её или выберите другую (wp sidebar list)." );
}

$moved  = (array) $sidebars['colormag_right_sidebar'];
$exists = (array) $sidebars[ $target ];

$to_append = array_values( array_diff( $moved, $exists ) );
if ( empty( $to_append ) ) {
	WP_CLI::success( 'Все виджеты уже есть в целевой области — перенос не требуется.' );
	exit( 0 );
}

WP_CLI::log( 'Перенос в "' . $target . '": ' . implode( ', ', $to_append ) );
if ( $dry ) {
	WP_CLI::log( 'DRY-RUN: ничего не изменено.' );
	exit( 0 );
}

$sidebars[ $target ] = array_merge( $exists, $to_append );
update_option( 'sidebars_widgets', $sidebars );

WP_CLI::success( 'Готово. Старые виджеты правого сайдбара добавлены в область Motors (настройки не изменялись).' );
