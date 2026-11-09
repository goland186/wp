<?php
/**
 * Функции старой темы ColorMag 1.3.5, необходимые перенесённым виджетам.
 * Перенесено из inc/functions.php ColorMag (GPL) с сохранением имён —
 * именно эти функции вызывают классы виджетов.
 *
 * @package motors-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Цвет рубрики из модуля кастомизации ColorMag
 * (theme_mod colormag_category_color_{id}).
 *
 * Оригинальная реализация перебирала все рубрики в цикле — здесь
 * читается значение напрямую, поведение для виджетов не меняется.
 */
if ( ! function_exists( 'colormag_category_color' ) ) :
	function colormag_category_color( $wp_category_id ) {
		return get_theme_mod( 'colormag_category_color_' . $wp_category_id, '' );
	}
endif;

/**
 * Ссылка-«плашка» рубрики над заголовком записи (используется виджетами).
 */
if ( ! function_exists( 'colormag_colored_category' ) ) :
	function colormag_colored_category() {
		global $post;

		$categories = get_the_category();
		$separator  = '&nbsp;';
		$output     = '';

		if ( $categories ) {
			$output .= '<div class="above-entry-meta"><span class="cat-links">';
			foreach ( $categories as $category ) {
				$color_code = colormag_category_color( $category->term_id );
				if ( ! empty( $color_code ) ) {
					$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" style="background:' . esc_attr( $color_code ) . '" rel="category tag">' . esc_html( $category->cat_name ) . '</a>' . $separator;
				} else {
					$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" rel="category tag">' . esc_html( $category->cat_name ) . '</a>' . $separator;
				}
			}
			$output .= '</span></div>';
			echo trim( $output, $separator ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}
endif;

/**
 * Адаптивная обёртка для oEmbed-видео (YouTube и др., 221 встраивание в контенте).
 */
if ( ! function_exists( 'colormag_responsive_video' ) ) :
	function colormag_responsive_video( $html, $url, $attr, $post_ID ) {
		return '<div class="fitvids-video">' . $html . '</div>';
	}
	add_filter( 'embed_oembed_html', 'colormag_responsive_video', 10, 4 );
endif;
