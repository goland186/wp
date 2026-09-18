<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly	

?>

<div class="wrap <?php echo $this -> pre; ?> slideshow">
	<h1><?php _e('Save Multiple Slides', 'slideshow-gallery'); ?></h1>
	
	<?php if (!empty($errors)) : ?>
		<div class="slideshow_error">
			<ul>
				<?php foreach ($errors as $error) : ?>
					<li><?php echo $error; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	
	<form action="" method="post">
		<?php wp_nonce_field($this -> sections -> slides . '_save-multiple'); ?>
		
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="Slide_mediaupload"><?php _e('Choose Images', 'slideshow-gallery'); ?></label></th>
					<td>
						<button class="button button-secondary" type="button" name="Slide_mediaupload" value="1" id="Slide_mediaupload">
							<i class="fa fa-image fa-fw"></i> <?php _e('Choose Images', 'slideshow-gallery'); ?>
						</button>
						<span class="howto"><?php _e('Upload/choose images from the media gallery. Ctrl/Shift + Click to choose multiple.', 'slideshow-gallery'); ?></span>
						
						<div id="Slide_mediaslides" style="display:<?php echo (!empty($_POST['Slide']['slides'])) ? 'block' : 'none'; ?>;">
							<!-- Slides go here -->
							<table class="form-table" id="Slide_mediaslides_table">
								<tbody>
									<?php if (!empty($_POST['Slide']['slides'])) : ?>
										<?php foreach ($_POST['Slide']['slides'] as $attachment_id => $slide) : ?>
											<tr id="Slide_mediaupload_row_<?php echo $slide['attachment_id']; ?>">
												<th style="width:100px; vertical-align:top;">
													<a href="" class="colorbox" onclick="jQuery.colorbox({href:'<?php echo esc_attr($slide['url']); ?>'}); return false;"><img style="width:100px;" class="img-rounded" src="<?php echo esc_attr($slide['url']); ?>" />
												</th>
												<td>
													<label><?php _e('Title:', 'slideshow-gallery'); ?> <input class="widefat" type="text" value="<?php echo esc_attr(stripslashes($slide['title'])); ?>" name="Slide[slides][<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>][title]" /></label>
													<label><?php _e('Description:', 'slideshow-gallery'); ?> <textarea class="widefat" rows="3" cols="100%" name="Slide[slides][<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>][description]"><?php echo esc_attr(stripslashes($slide['description'])); ?></textarea></label>
													<input class="widefat" readonly="readonly" type="text" value="<?php echo esc_attr(stripslashes($slide['url'])); ?>" name="Slide[slides][<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>][url]" />
													<input type="hidden" value="<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>" name="Slide[slides][<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>][attachment_id]" />
												</td>
												<td style="vertical-align:bottom;">
													<input onclick="if (confirm('<?php echo __('Are you sure you want to remove this slide?', 'slideshow-gallery'); ?>')) { jQuery('#Slide_mediaupload_row_<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>').remove(); } return false;" class="button button-secondary button-small" type="button" name="remove" value="<?php echo __('Remove', 'slideshow-gallery'); ?>" id="remove<?php echo esc_attr(stripslashes($slide['attachment_id'])); ?>" />
												</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</td>
				</tr>
				<tr>
					<th><label for=""><?php _e('Galleries', 'slideshow-gallery'); ?></label></th>
					<td>
						<?php if ($galleries = $this -> Gallery() -> select()) : ?>
							<label style="font-weight:bold"><input onclick="jqCheckAll(this,'','Slide[galleries]');" type="checkbox" name="checkboxall" value="checkboxall" id="checkboxall" /> <?php _e('Select All', 'slideshow-gallery'); ?></label><br/>
							<?php foreach ($galleries as $gallery_id => $gallery_title) : ?>
								<label><input <?php echo (!empty($_POST['Slide']['galleries']) && in_array($gallery_id, $_POST['Slide']['galleries'])) ? 'checked="checked"' : ''; ?> type="checkbox" name="Slide[galleries][]" value="<?php echo $gallery_id; ?>" id="Slide_galleries_<?php echo $gallery_id; ?>" /> <?php echo __($gallery_title); ?></label><br/>
							<?php endforeach; ?>
						<?php else : ?>
							<span class="error"><?php _e('No galleries are available.', 'slideshow-gallery'); ?></span>
						<?php endif; ?>
						<span class="howto"><?php _e('Choose the galleries to add these slides to.', 'slideshow-gallery'); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
	
		<p class="submit">
			<button type="submit" name="save" value="1" class="button button-primary">
				<i class="fa fa-check fa-fw"></i> <?php _e('Save Multiple Slides', 'slideshow-gallery'); ?>
			</button>
		</p>
	</form>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	var file_frame;
	
	jQuery('#Slide_mediaupload').on('click', function( event ){
		event.preventDefault();
		
		// If the media frame already exists, reopen it.
		if (file_frame) {
			file_frame.open();
			return;
		}
		
		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
			title: '<?php _e('Upload Slides', 'slideshow-gallery'); ?>',
			button: {
				text: '<?php _e('Select Images as Slides', 'slideshow-gallery'); ?>',
			},
			multiple: true  // Set to true to allow multiple files to be selected
		});
		
		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
		
			var selection = file_frame.state().get('selection');
			
			selection.map( function( attachment ) {
				attachment = attachment.toJSON();
				
				if (attachment.sizes.thumbnail) {
					var thumbnail_url = attachment.sizes.thumbnail.url;
				} else {
					var thumbnail_url = attachment.url;
				}
				
				var attachment_html = '<tr id="Slide_mediaupload_row_' + attachment.id + '">';
				attachment_html += '<th style="width:100px; vertical-align:top;"><a href="" class="colorbox" onclick="jQuery.colorbox({href:\'' + attachment.url + '\'}); return false;"><img style="width:100px;" class="img-rounded" src="' + thumbnail_url + '" /></th>';
				attachment_html += '<td>';
				attachment_html += '<label><?php _e('Title:', 'slideshow-gallery'); ?> <input class="widefat" type="text" value="' + attachment.title + '" name="Slide[slides][' + attachment.id + '][title]" /></label>';
				attachment_html += '<label><?php _e('Description:', 'slideshow-gallery'); ?> <textarea class="widefat" rows="3" cols="100%" name="Slide[slides][' + attachment.id + '][description]"></textarea></label>';
				attachment_html += '<input class="widefat" readonly="readonly" type="text" value="' + attachment.url + '" name="Slide[slides][' + attachment.id + '][url]" />';
				attachment_html += '<input type="hidden" value="' + attachment.id + '" name="Slide[slides][' + attachment.id + '][attachment_id]" />';
				attachment_html += '</td>';
				attachment_html += '<td style="vertical-align:bottom;"><input onclick="if (confirm(\'<?php echo __('Are you sure you want to remove this slide?', 'slideshow-gallery'); ?>\')) { jQuery(\'#Slide_mediaupload_row_' + attachment.id + '\').remove(); } return false;" class="button button-secondary button-small" type="button" name="remove" value="<?php echo __('Remove', 'slideshow-gallery'); ?>" id="remove' + attachment.id + '" /></td>';
				attachment_html += '</tr>';
				
				jQuery('#Slide_mediaslides').show();
				jQuery('#Slide_mediaslides_table tbody').append(attachment_html);
			});
		});
		
		// Finally, open the modal
		file_frame.open();
	});
});
</script>