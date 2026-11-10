<?php
/**
 * Car & Driver Russia — дочерняя тема Motors.
 *
 * Задача: сохранить функционал, шапку/подвал и порядок блоков старой темы
 * ColorMag 1.3.5 при переходе на Motors. Кастомизации выполняются ТОЛЬКО здесь
 * (родительская тема Motors остаётся нетронутой).
 *
 * Состав:
 *  - header.php / footer.php / sidebar-footer.php — реплика шапки и подвала
 *    ColorMag (лого, дата, breaking news, меню primary, копирайт);
 *  - assets/colormag/ — оригинальные CSS/JS и шрифты FontAwesome ColorMag;
 *  - inc/cd-legacy-functions.php  — функции ColorMag (цвета рубрик, шапка, подвал);
 *  - inc/cd-legacy-widgets.php    — те же области виджетов (colormag_*), что и на
 *    старом сайте: настройки виджетов подхватятся из БД автоматически;
 *  - inc/widgets/ — классы виджетов ColorMag (без изменений логики);
 *  - front-page.php               — раскладка главной как в ColorMag.
 *
 * @package motors-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Полифиллы функций из bundled-плагинов Motors (Car Dealer / Vehicle Listing).
 *
 * Шаблоны Motors 5.6.95 (inc/custom.php и др.) вызывают эти функции без
 * function_exists — без плагинов листингов страницы записей падают с фаталом
 * «Call to undefined function stm_is_dealer_two()». Для журнального сайта
 * дилерские режимы всегда отключены, поэтому функции просто возвращают false.
 */
if ( ! function_exists( 'stm_is_car_dealer' ) ) {
        function stm_is_car_dealer() {
                return false;
        }
}

if ( ! function_exists( 'stm_is_dealer_two' ) ) {
        function stm_is_dealer_two() {
                return false;
        }
}

if ( ! function_exists( 'stm_is_multilisting' ) ) {
        function stm_is_multilisting() {
                return false;
        }
}

if ( ! function_exists( 'stm_is_site_demo_mode' ) ) {
        function stm_is_site_demo_mode() {
                return false;
        }
}

/**
 * Меню: на старом сайте в ColorMag был один location — primary
 * (в него назначено «Main Menu», ID 8). Регистрируем то же имя,
 * чтобы привязка меню из theme_mods_colormag подхватилась сама.
 */
register_nav_menus( array(
        'primary' => esc_html__( 'Primary Menu', 'colormag' ),
) );

/**
 * Стили: оригинальный CSS ColorMag + шрифты FontAwesome + bxslider,
 * затем style.css дочерней темы и тонкая доводка cd-legacy.css.
 */
add_action( 'wp_enqueue_scripts', 'stm_enqueue_parent_styles' );
function stm_enqueue_parent_styles() {
        $cd = get_stylesheet_directory_uri();

        // FontAwesome (иконки home, search, random, chevron-up и т.д.).
        wp_enqueue_style( 'cd-fontawesome', $cd . '/assets/colormag/fontawesome/css/font-awesome.css', array(), '4.2.1' );

        // Оригинальный stylesheet ColorMag 1.3.5 — базовый вид шапки, меню, списков.
        wp_enqueue_style( 'cd-colormag-style', $cd . '/assets/colormag/colormag.css', array( 'cd-fontawesome' ), '1.3.5' );

        // Слайдер главной (виджет colormag_featured_posts_slider_widget).
        wp_enqueue_style( 'cd-bxslider', $cd . '/assets/colormag/css/jquery.bxslider.css', array( 'cd-colormag-style' ), '4.2.10' );

        // Стиль дочерней темы.
        wp_enqueue_style( 'child-style', $cd . '/style.css', array( 'cd-colormag-style' ), wp_get_theme()->get( 'Version' ) );

        // Доводка перенесённых блоков (после всех — приоритет за ней).
        wp_enqueue_style( 'cd-legacy-style', $cd . '/assets/css/cd-legacy.css', array( 'child-style' ), '1.0.0' );

        // Скрипты ColorMag (как в inc/functions.php старой темы).
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script( 'comment-reply' );
        }

        wp_enqueue_script( 'cd-bxslider', $cd . '/assets/colormag/js/jquery.bxslider.min.js', array( 'jquery' ), '4.2.10', true );
        wp_enqueue_script( 'cd-navigation', $cd . '/assets/colormag/js/navigation.js', array( 'jquery' ), '1.3.5', true );
        wp_enqueue_script( 'cd-custom', $cd . '/assets/colormag/js/colormag-custom.js', array( 'jquery' ), '1.3.5' );

        // Бегущая строка новостей (colormag_breaking_news = 1 на старом сайте).
        wp_enqueue_script( 'cd-news-ticker', $cd . '/assets/colormag/js/news-ticker/jquery.newsTicker.min.js', array( 'jquery' ), '1.0.0', true );

        // Липкое меню (colormag_primary_sticky_menu = 1 на старом сайте).
        wp_enqueue_script( 'cd-sticky', $cd . '/assets/colormag/js/sticky/jquery.sticky.js', array( 'jquery' ), '20150309', true );

        // Попап изображений (colormag_featured_image_popup = 1 на старом сайте).
        wp_enqueue_script( 'cd-magnific', $cd . '/assets/colormag/js/magnific-popup/jquery.magnific-popup.min.js', array( 'jquery' ), '20150310', true );
        wp_enqueue_style( 'cd-magnific', $cd . '/assets/colormag/js/magnific-popup/magnific-popup.css', array(), '20150310' );

        // Адаптивные видео (221 oEmbed-встраивание в контенте).
        wp_enqueue_script( 'cd-fitvids', $cd . '/assets/colormag/js/fitvids/jquery.fitvids.js', array( 'jquery' ), '20150311', true );
}

/**
 * Хелперы ColorMag (цвета рубрик, шапка, подвал, бегущая строка).
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
