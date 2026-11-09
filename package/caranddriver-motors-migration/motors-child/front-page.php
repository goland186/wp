<?php
/**
 * Главная страница — раскладка старой темы ColorMag 1.3.5, перенесённая
 * в дочернюю тему Motors.
 *
 * Порядок блоков идентичен старому сайту:
 *  1. Слайдер избранных постов (colormag_front_page_slider_area);
 *  2. Блок у слайдера (colormag_front_page_area_beside_slider);
 *  3. Верхняя секция контента (colormag_front_page_content_top_section);
 *  4. Две средние колонки (middle_left / middle_right);
 *  5. Нижняя секция (colormag_front_page_content_bottom_section);
 *  6. Текст статической страницы «Автомобильный портал» (page_on_front).
 *
 * Шапка и подвал берутся из родительской темы Motors.
 *
 * @package motors-child
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="cd-front-page">

	<div class="front-page-top-section clearfix">
		<div class="widget_slider_area">
			<?php
			if ( is_active_sidebar( 'colormag_front_page_slider_area' ) ) {
				dynamic_sidebar( 'colormag_front_page_slider_area' );
			}
			?>
		</div>

		<div class="widget_beside_slider">
			<?php
			if ( is_active_sidebar( 'colormag_front_page_area_beside_slider' ) ) {
				dynamic_sidebar( 'colormag_front_page_area_beside_slider' );
			}
			?>
		</div>
	</div>

	<div class="main-content-section clearfix">
		<div id="primary">
			<div id="content" class="clearfix">

				<?php
				if ( is_active_sidebar( 'colormag_front_page_content_top_section' ) ) {
					dynamic_sidebar( 'colormag_front_page_content_top_section' );
				}

				if ( is_active_sidebar( 'colormag_front_page_content_middle_left_section' ) || is_active_sidebar( 'colormag_front_page_content_middle_right_section' ) ) {
					?>
					<div class="tg-one-half">
						<?php dynamic_sidebar( 'colormag_front_page_content_middle_left_section' ); ?>
					</div>

					<div class="tg-one-half tg-one-half-last">
						<?php dynamic_sidebar( 'colormag_front_page_content_middle_right_section' ); ?>
					</div>

					<div class="clearfix"></div>
					<?php
				}

				if ( is_active_sidebar( 'colormag_front_page_content_bottom_section' ) ) {
					dynamic_sidebar( 'colormag_front_page_content_bottom_section' );
				}
				?>

				<div class="article-container">
					<?php
					if ( have_posts() ) {
						while ( have_posts() ) :
							the_post();
							?>
							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cd-front-page-content' ); ?>>
								<?php if ( trim( get_the_title() ) !== '' ) : ?>
									<h1 class="entry-title cd-page-title"><?php the_title(); ?></h1>
								<?php endif; ?>
								<div class="entry-content clearfix">
									<?php the_content(); ?>
								</div>
							</article>
							<?php
						endwhile;
					}
					?>
				</div>

			</div>
		</div>

		<?php
		// Правый сайдбар старого сайта (баннеры + вертикальный блок постов).
		if ( is_active_sidebar( 'colormag_right_sidebar' ) ) {
			?>
			<div id="secondary" class="cd-front-secondary">
				<?php dynamic_sidebar( 'colormag_right_sidebar' ); ?>
			</div>
			<?php
		}
		?>
	</div>

</div>

<?php
get_footer();
