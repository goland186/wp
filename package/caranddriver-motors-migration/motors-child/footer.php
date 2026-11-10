<?php
/**
 * Подвал сайта — реплика ColorMag 1.3.5 для caranddriver.ru.
 *
 * 4 колонки виджетов (colormag_footer_sidebar_*) + строка копирайта,
 * как на старом сайте (тогда колонки были пусты — выводилась только строка).
 *
 * @package motors-child
 */
?>

</div><!-- .inner-wrap -->
</div><!-- #main -->

<?php if ( is_active_sidebar( 'colormag_advertisement_above_the_footer_sidebar' ) ) { ?>
	<div class="advertisement_above_footer">
		<div class="inner-wrap">
			<?php dynamic_sidebar( 'colormag_advertisement_above_the_footer_sidebar' ); ?>
		</div>
	</div>
<?php } ?>

<?php do_action( 'colormag_before_footer' ); ?>

<?php
$main_total_footer_option_layout_class = get_theme_mod( 'colormag_main_footer_layout_display_type', 'type_one' );

$class_name = '';
if ( $main_total_footer_option_layout_class == 'type_two' ) {
	$class_name = 'colormag-footer--classic';
}
?>

<footer id="colophon" class="clearfix <?php echo esc_attr( $class_name ); ?>">
	<?php get_sidebar( 'footer' ); ?>
	<div class="footer-socket-wrapper clearfix">
		<div class="inner-wrap">
			<div class="footer-socket-area">
				<div class="footer-socket-right-section">
					<?php
					if ( ( get_theme_mod( 'colormag_social_link_activate', 0 ) == 1 ) && ( ( get_theme_mod( 'colormag_social_link_location_option', 'both' ) == 'both' ) || ( get_theme_mod( 'colormag_social_link_location_option', 'both' ) == 'footer' ) ) ) {
						colormag_social_links();
					}
					?>
				</div>

				<div class="footer-socket-left-section">
					<?php do_action( 'colormag_footer_copyright' ); ?>
				</div>
			</div>
		</div>
	</div>
</footer>

<a href="#masthead" id="scroll-up"><i class="fa fa-chevron-up"></i></a>

</div><!-- #page -->
<?php wp_footer(); ?>
</body>
</html>
