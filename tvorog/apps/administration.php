<?php
	if ( !defined( 'ABSPATH' ) ) {
		die(); // Don't access directly
	}
?>	
<div class="wrap">
	<?php screen_icon(); ?>
	<h2>WP Twitter Plugin</h2>
	
	<?php if ( isset( $_REQUEST[ 'settings-updated' ] ) && false !== $_REQUEST[ 'settings-updated' ] ) : ?>
		<div id="setting-error-settings_updated" class="updated settings-error">
			<p><strong><?php _e( 'Options saved', $this->plugin_name ); ?></strong></p>
		</div>
	<?php endif; ?>
	
	<form method="post" action="../../wp-twitter-plugin/apps/options.php">
		<?php settings_fields( $this->plugin_name . '_options' ); ?>
		<?php $options = get_option( $this->plugin_name . '_plugin_options' ); ?> 
	
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Text on front page', $this->plugin_name ); ?></th>
				<td>
					<?php wp_editor( $options[ 'introtext' ], $this->plugin_name . '_plugin_options[introtext]' ); ?>
					<!--<textarea id="humblebrag_theme_options[introtext]" class="tinymce_data large-text" cols="50" rows="10" name="humblebrag_theme_options[introtext]"><?php echo esc_textarea( $options[ 'introtext' ] ); ?></textarea>-->
				</td>
			</tr>
		</table> 
	
		<p>
			<input type="submit" class="button button-primary" value="<?php _e( 'Save Options', $this->plugin_name ); ?>" />
		</p>
	</form>
</div>