<?php
/**
 * Виджетные колонки подвала — реплика ColorMag 1.3.5 (sidebar-footer.php).
 *
 * @package motors-child
 */

if ( ! is_active_sidebar( 'colormag_footer_sidebar_one' ) &&
	! is_active_sidebar( 'colormag_footer_sidebar_two' ) &&
	! is_active_sidebar( 'colormag_footer_sidebar_three' ) &&
	! is_active_sidebar( 'colormag_footer_sidebar_four' ) ) {
	return;
}
?>
<div class="footer-widgets-wrapper">
	<div class="inner-wrap">
		<div class="footer-widgets-area clearfix">
			<div class="tg-footer-main-widget">
				<div class="tg-first-footer-widget">
					<?php if ( ! dynamic_sidebar( 'colormag_footer_sidebar_one' ) ): endif; ?>
				</div>
			</div>
			<div class="tg-footer-other-widgets">
				<div class="tg-second-footer-widget">
					<?php if ( ! dynamic_sidebar( 'colormag_footer_sidebar_two' ) ): endif; ?>
				</div>
				<div class="tg-third-footer-widget">
					<?php if ( ! dynamic_sidebar( 'colormag_footer_sidebar_three' ) ): endif; ?>
				</div>
				<div class="tg-fourth-footer-widget">
					<?php if ( ! dynamic_sidebar( 'colormag_footer_sidebar_four' ) ): endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
