<?php
/**
 * Car & Driver Russia — дочерняя тема Motors.
 *
 * Задача: сохранить функционал и порядок блоков старой темы ColorMag 1.3.5
 * при переходе на Motors. Кастомизации выполняются ТОЛЬКО здесь
 * (родительская тема Motors остаётся нетронутой).
 *
 * Состав:
 *  - inc/cd-legacy-functions.php  — хелперы ColorMag (цвета рубрик и пр.);
 *  - inc/cd-legacy-widgets.php    — те же области виджетов (colormag_*) и
 *                                   те же классы виджетов, что и на старом
 *                                   сайте: настройки виджетов подхватятся
 *                                   из БД автоматически;
 *  - front-page.php               — раскладка главной как в ColorMag
 *                                   (слайдер + блок у слайдера + 3 секции
 *                                   контента + текст статической страницы);
 *  - assets/css/cd-legacy.css     — стили перенесённых блоков.
 *
 * @package motors-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Подключение стилей родительской и дочерней темы.
 * (стиль Motors подключается самой темой под дескриптором stm-theme-style)
 */
add_action( 'wp_enqueue_scripts', 'stm_enqueue_parent_styles' );
function stm_enqueue_parent_styles() {
	wp_enqueue_style(
		'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'stm-theme-style' ),
		wp_get_theme()->get( 'Version' )
	);

	wp_enqueue_style(
		'cd-legacy-style',
		get_stylesheet_directory_uri() . '/assets/css/cd-legacy.css',
		array( 'child-style' ),
		'1.0.0'
	);
}

/**
 * Хелперы ColorMag (цвета рубрик, категория над заголовком, адаптивные видео).
 */
require_once get_stylesheet_directory() . '/inc/cd-legacy-functions.php';

/**
 * Области виджетов со СТАРЫМИ идентификаторами (colormag_*) —
 * благодаря этому все настройки виджетов из старой БД применяются сами.
 */
require_once get_stylesheet_directory() . '/inc/cd-legacy-widgets.php';

/**
 * Классы виджетов ColorMag (перенесены из старой темы без изменений логики).
 */
$cd_legacy_widgets = array(
	'colormag-featured-posts-slider-widget.php',
	'colormag-highlighted-posts-widget.php',
	'colormag-featured-posts-widget.php',
	'colormag-featured-posts-vertical-widget.php',
	'colormag-300x250-advertisement-widget.php',
	'colormag-728x90-advertisement-widget.php',
	'colormag-125x125-advertisement-widget.php',
);
foreach ( $cd_legacy_widgets as $cd_widget_file ) {
	require_once get_stylesheet_directory() . '/inc/widgets/' . $cd_widget_file;
}
