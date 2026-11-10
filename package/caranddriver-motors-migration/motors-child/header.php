<?php
/**
 * Шапка сайта — реплика ColorMag 1.3.5 для caranddriver.ru.
 *
 * Скопирована из старой темы без изменений разметки: лого (custom_logo 661),
 * строка новостей (дата + breaking news + соцсети), меню (location primary),
 * иконка поиска и случайный пост — как на старом сайте.
 * Все colormag_* функции объявлены в inc/cd-legacy-functions.php.
 *
 * @package motors-child
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11"/>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>"/>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php do_action( 'colormag_before' ); ?>

<div id="page" class="hfeed site">
	<?php do_action( 'colormag_before_header' ); ?>

	<?php
	// Тип отображения шапки (как в ColorMag).
	$main_total_header_option_layout_class = get_theme_mod( 'colormag_main_total_header_area_display_type', 'type_one' );

	$class_name = '';
	if ( $main_total_header_option_layout_class == 'type_two' ) {
		$class_name = 'colormag-header-clean';
	} else if ( $main_total_header_option_layout_class == 'type_three' ) {
		$class_name = 'colormag-header-classic';
	}
	?>

	<header id="masthead" class="site-header clearfix <?php echo esc_attr( $class_name ); ?>">
		<div id="header-text-nav-container" class="clearfix">

			<?php colormag_top_header_bar_display(); // Верхняя строка: дата + breaking news ?>

			<?php
			if ( get_theme_mod( 'colormag_header_image_position', 'position_two' ) == 'position_one' ) {
				colormag_render_header_image();
			}
			?>

			<?php colormag_middle_header_bar_display(); // Лого + баннер справа ?>

			<?php
			if ( get_theme_mod( 'colormag_header_image_position', 'position_two' ) == 'position_two' ) {
				colormag_render_header_image();
			}
			?>

			<?php colormag_below_header_bar_display(); // Главное меню ?>

		</div><!-- #header-text-nav-container -->

		<?php
		if ( get_theme_mod( 'colormag_header_image_position', 'position_two' ) == 'position_three' ) {
			colormag_render_header_image();
		}
		?>

	</header>

	<?php do_action( 'colormag_after_header' ); ?>
	<?php do_action( 'colormag_before_main' ); ?>

	<div id="main" class="clearfix">
		<div class="inner-wrap clearfix">
