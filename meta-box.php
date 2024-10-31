<?php
function regiondo_bws_add_meta_boxes() {
	add_meta_box('regiondo_bws_shortcode', __( 'Shortcode', 'regiondo-widget' ), 'regiondo_bws_shortcode_meta_box', 'regiondo_iframe', 'side', 'high');
}
add_action('add_meta_boxes_regiondo_iframe', 'regiondo_bws_add_meta_boxes');

function regiondo_bws_shortcode_meta_box($post) { ?>
    <p><?php _e('You can place this shortcode to your posts and pages:','regiondo-widget'); ?></p>
	<span class="rws-code" id="rws-shortcode"><?php echo '[regiondo_widget slug=' . $post -> post_name . ']'; ?></span>
<?php
}

function regiondo_bws_save_postdata($post_id) {
	if (!isset( $_POST['regiondo_bws_info_meta_box_nonce']))
		return $post_id;

	$nonce = $_POST['regiondo_bws_info_meta_box_nonce'];

	if (!wp_verify_nonce( $nonce, 'regiondo_bws_info_meta_box'))
		return $post_id;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;

	if ('regiondo_iframe' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id))
			return $post_id;
		} else {
			if (!current_user_can( 'edit_post', $post_id))
				return $post_id;
	}

	$regiondo_bws_custom_info = sanitize_text_field($_POST['regiondo_widget_info']);
	update_post_meta($post_id, 'regiondo_bws_info', $regiondo_bws_custom_info);
}
add_action('save_post', 'regiondo_bws_save_postdata');
