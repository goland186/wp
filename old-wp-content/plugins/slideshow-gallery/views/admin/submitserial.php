<h3><?php _e('Slideshow Gallery Serial Key', 'slideshow-gallery'); ?></h3>

<?php if (empty($success) || $success == false) : ?>
	<?php if (!$this -> ci_serial_valid()) : ?>
        <p style="width:400px;">
        	<?php _e('You are running Slideshow Gallery LITE.', 'slideshow-gallery'); ?>
        	<?php echo sprintf(__('To remove limits, you can submit a serial key or %s.'), '<a href="' . admin_url('admin.php?page=' . $this -> sections -> lite_upgrade) . '">' . __('Upgrade to PRO', 'slideshow-gallery') . '</a>'); ?>
        </p>
        <p style="width:400px;">
	        <?php _e('Please obtain a serial key from the downloads section in your Tribulant Software account.', 'slideshow-gallery'); ?>
	        <?php _e('Once in the downloads section, click the KEY icon to request a serial key.', 'slideshow-gallery'); ?>
	        <a href="https://tribulant.com/downloads/" title="Tribulant Software Downloads" target="_blank"><?php _e('Downloads Section', 'slideshow-gallery'); ?></a>
        </p>
    
        <div class="slideshow_error">
            <?php $this -> render('error', array('errors' => $errors), true, 'admin'); ?>
        </div>
        
        <form onsubmit="slideshow_submitserial(this); return false;" action="<?php echo admin_url('admin.php?page=' . $this -> sections -> submitserial); ?>" method="post">
	        <?php wp_nonce_field($this -> sections -> submitserial); ?>
            <p>
	            <input type="text" class="widefat" style="width:400px;" name="serialkey" value="<?php echo esc_attr(stripslashes($_POST['serialkey'])); ?>" /><br/>
            </p>
            <p class="submit">
            	<input type="button" class="button-secondary" name="close" onclick="jQuery.colorbox.close();" value="<?php _e('Cancel', 'slideshow-gallery'); ?>" />
            	<input id="slideshow_submitserial_button" type="submit" class="button-primary" name="submit" value="<?php _e('Submit Serial Key', 'slideshow-gallery'); ?>" />
            	<span style="display:none;" id="slideshow_submitserial_loading"><i class="fa fa-refresh fa-spin fa-fw"></i></span>
            </p>
        </form>        
    <?php else : ?>
        <p><?php _e('Serial Key:', 'slideshow-gallery'); ?> <strong><?php echo $this -> get_option('serialkey'); ?></strong></p>
        <p><?php _e('Your current serial is valid and working.', 'slideshow-gallery'); ?></p>
        <p>
        	<input type="button" onclick="jQuery.colorbox.close();" name="close" class="button-primary" value="<?php _e('Close', 'slideshow-gallery'); ?>" />
        	<input id="slideshow_deleteserial_button" type="button" onclick="if (confirm('<?php _e('Are you sure you want to delete your serial key?', 'slideshow-gallery'); ?>')) { slideshow_deleteserial(); } return false;" name="delete" class="button-secondary" value="<?php _e('Delete Serial', 'slideshow-gallery'); ?>" />
        	<span style="display:none;" id="slideshow_submitserial_loading"><i class="fa fa-refresh fa-spin fa-fw"></i></span>
        </p>
    <?php endif; ?>
<?php else : ?>
    <p><?php _e('The serial key is valid and you can now continue using the Slideshow Gallery plugin. Thank you for your business and support!', 'slideshow-gallery'); ?></p>
    <p><input type="button" onclick="jQuery.colorbox.close(); parent.location = '<?php echo rtrim(get_admin_url(), '/'); ?>/admin.php?page=<?php echo $this -> sections -> slides; ?>';" class="button-primary" name="close" value="<?php _e('Apply Serial and Close Window', 'slideshow-gallery'); ?>" /></p>
<?php endif; ?>