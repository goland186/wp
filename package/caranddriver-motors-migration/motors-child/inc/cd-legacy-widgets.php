<?php
/**
 * Области виджетов со СТАРЫМИ идентификаторами ColorMag.
 *
 * Ключевой приём миграции: идентификаторы областей (colormag_right_sidebar,
 * colormag_front_page_slider_area и т.д.) и имена классов виджетов совпадают
 * со старым сайтом, поэтому настройки из таблицы wp_options
 * (sidebars_widgets, widget_colormag_*) применяются автоматически —
 * порядок блоков главной и состав сайдбаров сохраняются 1:1.
 *
 * Перенесено из inc/widgets/widgets.php ColorMag 1.3.5 (GPL).
 *
 * @package motors-child
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрация областей виджетов (идентификаторы идентичны ColorMag).
 */
if ( ! function_exists( 'cd_legacy_widgets_init' ) ) :
        function cd_legacy_widgets_init() {

                // Разметка области на фронт-странице (как в ColorMag front-page.php).
                $cd_front_defaults = array(
                        'before_widget' => '<section id="%1$s" class="widget %2$s clearfix">',
                        'after_widget'  => '</section>',
                        'before_title'  => '<h3 class="widget-title"><span>',
                        'after_title'   => '</span></h3>',
                );

                // Разметка обычных сайдбаров (как в ColorMag).
                $cd_sidebar_defaults = array(
                        'before_widget' => '<aside id="%1$s" class="widget %2$s clearfix">',
                        'after_widget'  => '</aside>',
                        'before_title'  => '<h3 class="widget-title"><span>',
                        'after_title'   => '</span></h3>',
                );

                // front => true: область рендерится на главной секциями <section>.
                $cd_sidebars = array(
                        'colormag_right_sidebar'                             => array(
                                'name'        => 'Right Sidebar',
                                'description' => 'Shows widgets at Right side.',
                                'front'       => false,
                        ),
                        'colormag_left_sidebar'                              => array(
                                'name'        => 'Left Sidebar',
                                'description' => 'Shows widgets at Left side.',
                                'front'       => false,
                        ),
                        'colormag_header_sidebar'                            => array(
                                'name'         => 'Header Sidebar',
                                'description'  => 'Shows widgets in header section just above the main navigation menu.',
                                'front'        => false,
                                'before_title' => '<h3 class="widget-title">',
                                'after_title'  => '</h3>',
                        ),
                        'colormag_front_page_slider_area'                    => array(
                                'name'        => 'Front Page: Slider Area',
                                'description' => 'Show widget just below menu. Suitable for TG: Featured Category Slider.',
                                'front'       => true,
                        ),
                        'colormag_front_page_area_beside_slider'             => array(
                                'name'        => 'Front Page: Area beside slider',
                                'description' => 'Show widget beside the slider. Suitable for TG: Highlighted Posts.',
                                'front'       => true,
                        ),
                        'colormag_front_page_content_top_section'            => array(
                                'name'        => 'Front Page: Content Top Section',
                                'description' => 'Content Top Section',
                                'front'       => true,
                        ),
                        'colormag_front_page_content_middle_left_section'    => array(
                                'name'        => 'Front Page: Content Middle Left Section',
                                'description' => 'Content Middle Left Section',
                                'front'       => true,
                        ),
                        'colormag_front_page_content_middle_right_section'   => array(
                                'name'        => 'Front Page: Content Middle Right Section',
                                'description' => 'Content Middle Right Section',
                                'front'       => true,
                        ),
                        'colormag_front_page_content_bottom_section'         => array(
                                'name'        => 'Front Page: Content Bottom Section',
                                'description' => 'Content Bottom Section',
                                'front'       => true,
                        ),
                        'colormag_contact_page_sidebar'                      => array(
                                'name'        => 'Contact Page Sidebar',
                                'description' => 'Shows widgets on Contact Page Template.',
                                'front'       => false,
                        ),
                        'colormag_error_404_page_sidebar'                    => array(
                                'name'        => 'Error 404 Page sidebar',
                                'description' => 'Shows widgets on Error 404 page.',
                                'front'       => false,
                        ),
                        'colormag_advertisement_above_the_footer_sidebar'    => array(
                                'name'        => 'Advertisement Above The Footer',
                                'description' => 'Shows widgets just above the footer, suitable for TG: 728x90 Advertisement widget.',
                                'front'       => false,
                        ),
                        'colormag_footer_sidebar_one'                        => array(
                                'name'        => 'Footer Sidebar One',
                                'description' => 'Shows widgets at footer sidebar one.',
                                'front'       => false,
                        ),
                        'colormag_footer_sidebar_two'                        => array(
                                'name'        => 'Footer Sidebar Two',
                                'description' => 'Shows widgets at footer sidebar two.',
                                'front'       => false,
                        ),
                        'colormag_footer_sidebar_three'                      => array(
                                'name'        => 'Footer Sidebar Three',
                                'description' => 'Shows widgets at footer sidebar three.',
                                'front'       => false,
                        ),
                        'colormag_footer_sidebar_four'                       => array(
                                'name'        => 'Footer Sidebar Four',
                                'description' => 'Shows widgets at footer sidebar four.',
                                'front'       => false,
                        ),
                );

                foreach ( $cd_sidebars as $cd_id => $cd_args ) {
                        $cd_is_front = ! empty( $cd_args['front'] );
                        unset( $cd_args['front'] );

                        register_sidebar( array_merge(
                                $cd_is_front ? $cd_front_defaults : $cd_sidebar_defaults,
                                $cd_args,
                                array( 'id' => $cd_id )
                        ) );
                }

                register_widget( 'colormag_featured_posts_slider_widget' );
                register_widget( 'colormag_highlighted_posts_widget' );
                register_widget( 'colormag_featured_posts_widget' );
                register_widget( 'colormag_featured_posts_vertical_widget' );
                register_widget( 'colormag_728x90_advertisement_widget' );
                register_widget( 'colormag_300x250_advertisement_widget' );
                register_widget( 'colormag_125x125_advertisement_widget' );
        }
endif;
add_action( 'widgets_init', 'cd_legacy_widgets_init' );
