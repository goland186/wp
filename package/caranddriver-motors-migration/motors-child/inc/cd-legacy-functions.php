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

/* ============================================================================
 * Шапка и подвал ColorMag (header.php / footer.php дочерней темы).
 * Перенесено из ColorMag 1.3.5 (inc/functions.php, inc/header-functions.php)
 * практически без изменений — те же theme_mod, те же классы CSS.
 * ========================================================================== */

/**
 * Лого сайта (custom_logo из БД — вложение 661 на старом сайте).
 */
if ( ! function_exists( 'colormag_the_custom_logo' ) ) :
        function colormag_the_custom_logo() {
                if ( function_exists( 'the_custom_logo' ) ) {
                        the_custom_logo();
                }
        }
endif;

/**
 * Строка даты в верхней полосе.
 */
if ( ! function_exists( 'colormag_date_display' ) ) :
        function colormag_date_display() {
                if ( get_theme_mod( 'colormag_date_display', 0 ) == 0 ) {
                        return;
                } ?>
                <div class="date-in-header">
                        <?php
                        if ( get_theme_mod( 'colormag_date_display_type', 'theme_default' ) == 'theme_default' ) {
                                echo date_i18n( 'l, F j, Y' );
                        } elseif ( get_theme_mod( 'colormag_date_display_type', 'theme_default' ) == 'wordpress_date_setting' ) {
                                echo date_i18n( get_option( 'date_format' ) );
                        }
                        ?>
                </div>
                <?php
        }
endif;

/**
 * Бегущая строка последних новостей (newsTicker подключён в functions.php).
 */
if ( ! function_exists( 'colormag_breaking_news' ) ) :
        function colormag_breaking_news() {
                $post_status = 'publish';
                if ( get_option( 'fresh_site' ) == 1 ) {
                        $post_status = array( 'auto-draft', 'publish' );
                }

                $get_featured_posts = new WP_Query( array(
                        'posts_per_page'      => 5,
                        'post_type'           => 'post',
                        'ignore_sticky_posts' => true,
                        'post_status'         => $post_status,
                ) );
                ?>
                <div class="breaking-news">
                        <strong class="breaking-news-latest"><?php echo esc_html__( 'Latest:', 'colormag' ); ?></strong>
                        <ul class="newsticker">
                                <?php while ( $get_featured_posts->have_posts() ): $get_featured_posts->the_post(); ?>
                                        <li>
                                                <a href="<?php the_permalink(); ?>"
                                                   title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
                                        </li>
                                <?php endwhile; ?>
                        </ul>
                </div>
                <?php
                wp_reset_query();
        }
endif;

/**
 * Иконка «случайный пост» в меню.
 */
if ( ! function_exists( 'colormag_random_post' ) ) :
        function colormag_random_post() {
                if ( get_theme_mod( 'colormag_random_post_in_menu', 0 ) == 0 ) {
                        return;
                }

                $get_random_post = new WP_Query( array(
                        'posts_per_page'      => 1,
                        'post_type'           => 'post',
                        'ignore_sticky_posts' => true,
                        'orderby'             => 'rand',
                ) );
                ?>
                <div class="random-post">
                        <?php while ( $get_random_post->have_posts() ): $get_random_post->the_post(); ?>
                                <a href="<?php the_permalink(); ?>" title="<?php esc_attr_e( 'View a random post', 'colormag' ); ?>"><i
                                                        class="fa fa-random"></i></a>
                        <?php endwhile; ?>
                </div>
                <?php
                wp_reset_query();
        }
endif;

/**
 * Соцсети (иконки FontAwesome) — на старом сайте ссылки были пусты,
 * функция выводится только при заполненных theme_mod.
 */
if ( ! function_exists( 'colormag_social_links' ) ) :
        function colormag_social_links() {
                if ( get_theme_mod( 'colormag_social_link_activate', 0 ) == 0 ) {
                        return;
                }

                $colormag_social_links = array(
                        'colormag_social_facebook'   => 'Facebook',
                        'colormag_social_twitter'    => 'Twitter',
                        'colormag_social_googleplus' => 'Google-Plus',
                        'colormag_social_instagram'  => 'Instagram',
                        'colormag_social_pinterest'  => 'Pinterest',
                        'colormag_social_youtube'    => 'YouTube',
                );
                ?>
                <div class="social-links clearfix">
                        <ul>
                                <?php
                                $colormag_links_output = '';
                                foreach ( $colormag_social_links as $key => $value ) {
                                        $link = get_theme_mod( $key, '' );
                                        if ( ! empty( $link ) ) {
                                                if ( get_theme_mod( $key . '_checkbox', 0 ) == 1 ) {
                                                        $new_tab = 'target="_blank"';
                                                } else {
                                                        $new_tab = '';
                                                }
                                                $colormag_links_output .= '<li><a href="' . esc_url( $link ) . '" ' . $new_tab . '><i class="fa fa-' . strtolower( $value ) . '"></i></a></li>';
                                        }
                                }
                                echo $colormag_links_output; // phpcs:ignore WordPress.Security.EscapeOutput
                                ?>
                        </ul>
                </div><!-- .social-links -->
                <?php
        }
endif;

/**
 * Баннер-изображение шапки (header_image; на старом сайте = remove-header).
 */
if ( ! function_exists( 'colormag_render_header_image' ) ) :
        function colormag_render_header_image() {
                $header_image = get_header_image();
                if ( ! empty( $header_image ) ) {
                        if ( get_theme_mod( 'colormag_header_image_link', 0 ) == 1 ) {
                                ?>
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                        <?php } ?>
                        <div class="header-image-wrap">
                                <img src="<?php echo esc_url( $header_image ); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>">
                        </div>
                        <?php if ( get_theme_mod( 'colormag_header_image_link', 0 ) == 1 ) { ?>
                                </a>
                                <?php
                        }
                }
        }
endif;

/**
 * Верхняя полоса шапки: дата + breaking news + соцсети.
 */
if ( ! function_exists( 'colormag_top_header_bar_display' ) ) :
        function colormag_top_header_bar_display() {
                if ( get_theme_mod( 'colormag_breaking_news', 0 ) == 1 || get_theme_mod( 'colormag_date_display', 0 ) == 1 || get_theme_mod( 'colormag_social_link_activate', 0 ) == 1 ) :
                        ?>
                        <div class="news-bar">
                                <div class="inner-wrap clearfix">
                                        <?php
                                        if ( get_theme_mod( 'colormag_date_display', 0 ) == 1 ) {
                                                colormag_date_display();
                                        }
                                        ?>

                                        <?php
                                        if ( get_theme_mod( 'colormag_breaking_news', 0 ) == 1 ) {
                                                colormag_breaking_news();
                                        }
                                        ?>

                                        <?php
                                        if ( ( get_theme_mod( 'colormag_social_link_activate', 0 ) == 1 ) && ( ( get_theme_mod( 'colormag_social_link_location_option', 'both' ) == 'both' ) || ( get_theme_mod( 'colormag_social_link_location_option', 'both' ) == 'header' ) ) ) {
                                                colormag_social_links();
                                        }
                                        ?>
                                </div>
                        </div>
                <?php
                endif;
        }
endif;

/**
 * Средняя полоса шапки: лого слева + рекламная область colormag_header_sidebar справа.
 */
if ( ! function_exists( 'colormag_middle_header_bar_display' ) ) :
        function colormag_middle_header_bar_display() {
                ?>

                <div class="inner-wrap">

                        <div id="header-text-nav-wrap" class="clearfix">
                                <div id="header-left-section">
                                        <?php
                                        if ( ( get_theme_mod( 'colormag_header_logo_placement', 'header_text_only' ) == 'show_both' || get_theme_mod( 'colormag_header_logo_placement', 'header_text_only' ) == 'header_logo_only' ) ) {
                                                ?>
                                                <div id="header-logo-image">
                                                        <?php
                                                        if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                                                                colormag_the_custom_logo();
                                                        }
                                                        ?>
                                                </div><!-- #header-logo-image -->
                                                <?php
                                        }
                                        $screen_reader = '';
                                        if ( get_theme_mod( 'colormag_header_logo_placement', 'header_text_only' ) == 'header_logo_only' || ( get_theme_mod( 'colormag_header_logo_placement', 'header_text_only' ) == 'disable' ) ) {
                                                $screen_reader = 'screen-reader-text';
                                        }
                                        ?>
                                        <div id="header-text" class="<?php echo $screen_reader; ?>">
                                                <?php if ( is_front_page() || is_home() ) : ?>
                                                        <h1 id="site-title">
                                                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                                                        </h1>
                                                <?php else : ?>
                                                        <h3 id="site-title">
                                                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
                                                        </h3>
                                                <?php endif; ?>
                                                <?php
                                                $description = get_bloginfo( 'description', 'display' );
                                                if ( $description || is_customize_preview() ) :
                                                        ?>
                                                        <p id="site-description"><?php echo $description; ?></p>
                                                <?php endif; ?><!-- #site-description -->
                                        </div><!-- #header-text -->
                                </div><!-- #header-left-section -->
                                <div id="header-right-section">
                                        <?php
                                        if ( is_active_sidebar( 'colormag_header_sidebar' ) ) {
                                                ?>
                                                <div id="header-right-sidebar" class="clearfix">
                                                        <?php
                                                        if ( ! dynamic_sidebar( 'colormag_header_sidebar' ) ):
                                                        endif;
                                                        ?>
                                                </div>
                                                <?php
                                        }
                                        ?>
                                </div><!-- #header-right-section -->

                        </div><!-- #header-text-nav-wrap -->

                </div><!-- .inner-wrap -->

                <?php
        }
endif;

/**
 * Полоса главного меню (location primary) + иконка home + случайный пост + поиск.
 */
if ( ! function_exists( 'colormag_below_header_bar_display' ) ) :
        function colormag_below_header_bar_display() {
                ?>

                <nav id="site-navigation" class="main-navigation clearfix" role="navigation">
                        <div class="inner-wrap clearfix">
                                <?php
                                if ( get_theme_mod( 'colormag_home_icon_display', 0 ) == 1 ) {
                                        if ( is_front_page() ) {
                                                $home_icon_class = 'home-icon front_page_on';
                                        } else {
                                                $home_icon_class = 'home-icon';
                                        }
                                        ?>

                                        <div class="<?php echo $home_icon_class; ?>">
                                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><i class="fa fa-home"></i></a>
                                        </div>

                                        <?php
                                }
                                ?>

                                <p class="menu-toggle"></p>
                                <?php
                                if ( has_nav_menu( 'primary' ) ) {
                                        wp_nav_menu( array(
                                                'theme_location'  => 'primary',
                                                'container_class' => 'menu-primary-container',
                                                'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
                                        ) );
                                } else {
                                        wp_page_menu();
                                }
                                ?>

                                <?php if ( get_theme_mod( 'colormag_random_post_in_menu', 0 ) == 1 ) { ?>
                                        <?php colormag_random_post(); ?>
                                <?php } ?>

                                <?php if ( get_theme_mod( 'colormag_search_icon_in_menu', 0 ) == 1 ) { ?>
                                        <i class="fa fa-search search-top"></i>
                                        <div class="search-form-top">
                                                <?php get_search_form(); ?>
                                        </div>
                                <?php } ?>
                        </div>
                </nav>

                <?php
        }
endif;

/**
 * Строка копирайта в подвале (русская версия строки ColorMag, как на старом сайте).
 */
if ( ! function_exists( 'colormag_footer_copyright' ) ) :
        function colormag_footer_copyright() {
                $site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';

                $default_footer_value = sprintf( __( 'Copyright &copy; %1$s %2$s. Все права защищены.', 'colormag' ), date_i18n( 'Y' ), $site_link );

                $colormag_footer_copyright = '<div class="copyright">' . $default_footer_value . '</div>';
                echo $colormag_footer_copyright; // phpcs:ignore WordPress.Security.EscapeOutput
        }
endif;
add_action( 'colormag_footer_copyright', 'colormag_footer_copyright', 10 );

/**
 * Favicon старого сайта (theme_mod colormag_favicon_upload) — пока не задан site_icon.
 */
if ( ! function_exists( 'colormag_favicon_link' ) ) :
        function colormag_favicon_link() {
                if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
                        return; // работает штатная иконка WordPress.
                }
                if ( get_theme_mod( 'colormag_favicon_show', 0 ) == 1 && get_theme_mod( 'colormag_favicon_upload', '' ) ) {
                        echo '<link rel="icon" href="' . esc_url( get_theme_mod( 'colormag_favicon_upload' ) ) . '" type="image/png" />' . "\n";
                }
        }
endif;
add_action( 'wp_head', 'colormag_favicon_link', 5 );
