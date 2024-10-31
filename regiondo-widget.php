<?php
/* Widget creation */
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}
class regiondo_widget extends WP_Widget {
	function __construct() {
		$widget_ops = array( 'classname' => 'widget_regiondo_widget', 'description' => __( 'Regiondo Booking Widget', 'regiondo-booking-widget' ) );
		parent::__construct( 'regiondo_widget', __( 'Regiondo Widget', 'regiondo-booking-widget' ), $widget_ops );
	}

	function form( $instance ) {
		$regiondo_widget_id = '';
		if (isset($instance['regiondo_widget_id'])) {
			$regiondo_widget_id = esc_attr($instance['regiondo_widget_id']);
		};
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'regiondo_widget_id' ); ?>"> <?php echo __( 'Regiondo widget:', 'regiondo-booking-widget' ) ?>
				<select class="widefat" id="<?php echo $this->get_field_id( 'regiondo_widget_id' ); ?>" name="<?php echo $this->get_field_name( 'regiondo_widget_id' ); ?>">
				<?php
					$args = array( 'post_type' => 'regiondo_iframe', 'suppress_filters' => 0, 'numberposts' => -1, 'order' => 'ASC' );
					$regiondo_widget = get_posts($args);
					if ($regiondo_widget) {
						foreach($regiondo_widget as $regiondo_widget) : setup_postdata($regiondo_widget);
							echo '<option value="' . $regiondo_widget -> ID . '"';
							if( $regiondo_widget_id == $regiondo_widget -> ID ) {
								echo ' selected';
								$widgetExtraTitle = $regiondo_widget -> post_title;
							};
							echo '>' . $regiondo_widget -> post_title . '</option>';
						endforeach;
					} else {
						echo '<option value="">' . __( 'No widgets available', 'regiondo-booking-widget' ) . '</option>';
					};
				?>
				</select>
			</label>
		</p>

		<input type="hidden" id="<?php echo $this -> get_field_id( 'title' ); ?>" name="<?php echo $this -> get_field_name( 'title' ); ?>" value="<?php if ( !empty( $widgetExtraTitle ) ) { echo $widgetExtraTitle; } ?>" />

		<p>
			<?php
				echo '<a href="post.php?post=' . $regiondo_widget_id . '&action=edit">' . __( 'Edit Regiondo Widget', 'regiondo-booking-widget' ) . '</a>' ;
			?>
		</p>
        <?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['regiondo_widget_id'] = strip_tags( $new_instance['regiondo_widget_id'] );
		return $instance;
	}

	function widget($args, $instance) {
		extract($args);
		$regiondo_widget_id  = ( $instance['regiondo_widget_id'] != '' ) ? esc_attr($instance['regiondo_widget_id']) : __( 'Find', 'regiondo-booking-widget' );
		// Support for WPML Plugin.
		if ( function_exists( 'icl_object_id' ) ){
			$regiondo_widget_id = icl_object_id( $regiondo_widget_id, 'regiondo_iframe', true );
		}
		// Variables from the widget settings.
		$regiondo_widget = get_post( $regiondo_widget_id );
		$post_status = get_post_status( $regiondo_widget_id );
		$content = $regiondo_widget->post_content;
		if ( $post_status == 'publish' ) {
			// Display custom widget frontend
			if ( $located = locate_template( 'regiondo-widget.php' ) ) {
				require $located;
				return;
			}
			echo $before_widget;
			echo do_shortcode($content);
			echo $after_widget;
		}
	}
}

// Register the Regiondo Booking Widget custom type
function regiondo_bws_post_type_init() {
	$labels = array(
		'name' => _x( 'Regiondo widgets', 'post type general name', 'regiondo-booking-widget' ),
		'singular_name' => _x( 'Regiondo widget', 'post type singular name', 'regiondo-booking-widget' ),
		'plural_name' => _x( 'Regiondo widgets', 'post type plural name', 'regiondo-booking-widget' ),
		'add_new' => _x( 'Add new widget', 'block', 'regiondo-booking-widget' ),
		'add_new_item' => __( 'Add new widget', 'regiondo-booking-widget' ),
		'edit_item' => __( 'Edit Regiondo widget', 'regiondo-booking-widget' ),
		'new_item' => __( 'New Regiondo widget', 'regiondo-booking-widget' ),
		'view_item' => __( 'View Regiondo widget', 'regiondo-booking-widget' ),
		'search_items' => __( 'Search Regiondo widget', 'regiondo-booking-widget' ),
		'not_found' =>  __( 'No Regiondo widgets found', 'regiondo-booking-widget' ),
		'not_found_in_trash' => __( 'No Regiondo widgets found in Trash', 'regiondo-booking-widget' )
	);
	$options = array(
		'labels' => $labels,
		'public' => false,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_icon' => 'dashicons-cart',
		'supports' => array( 'title','editor','revisions','author' )
	);
	register_post_type('regiondo_iframe', $options);
}
add_action( 'init', 'regiondo_bws_post_type_init' );

// Add custom editor title
function regiondo_bws_editor_title() {
    global $post_type;

    if ( $post_type == 'regiondo_iframe' ) {
        echo '<h2 style="padding:2em 0 1em;">' . __("Code", "regiondo-booking-widget") . '</h2>';
    }
}
add_action('edit_form_after_title', 'regiondo_bws_editor_title' );

// Remove visual mode
function regiondo_bws_editor_settings( $settings ) {

    global $post_type;

    if ( $post_type == 'regiondo_iframe' ) {

        $settings['tinymce'] = false;
        $settings['quicktags'] = false;
        $settings['media_buttons'] = false;
        $settings['editor_height'] = '200px';
    }

    return $settings;
}

add_filter( 'wp_editor_settings', 'regiondo_bws_editor_settings' );

// Add a shortcode
function regiondo_booking_widget_shortcode( $atts ) {
	extract( shortcode_atts( array(
		'id' => '',
		'slug' => ''
	), $atts ) );

	if ( $slug ) {
		$block = get_page_by_path( $slug, OBJECT, 'regiondo_iframe' );
		if ( $block ) {
			$id = $block->ID;
		}
	}

	$content = "";

	if( $id != "" ) {
		$args = array(
			'post__in' => array( $id ),
			'post_type' => 'regiondo_iframe',
		);

		$content_post = get_posts( $args );

		foreach( $content_post as $post ) :
            $content .= $post->post_content;
		endforeach;
	}

	return $content;
}
add_shortcode( 'regiondo_widget', 'regiondo_booking_widget_shortcode' );

// Add button
function regiondo_bws_add_button() {
	global $current_screen;
    if ( ( 'regiondo_iframe' != $current_screen -> post_type ) && ( 'toplevel_page_revslider' != $current_screen -> id ) ) {
		add_action( 'media_buttons', 'add_regiondo_bws_button' );
		add_action( 'admin_footer', 'add_regiondo_bws_popup' );
	}
}
add_action( 'admin_head', 'regiondo_bws_add_button' );

function add_regiondo_bws_button() {
    echo '<a style="padding-left:10px;" class="button thickbox rw-button" title="' . __("Add Regiondo Widget", 'regiondo-booking-widget' ) . '" href="#TB_inline?width=600&height=550&inlineId=regiondo-widget-form">' . __("Add Regiondo Widget", "regiondo-widget") . '</a>';
}

function add_regiondo_bws_popup() { ?>
    <script>
        function getId(item) {
            selectedId = item.options[item.selectedIndex].value;
            slectedSlug = item.options[item.selectedIndex].getAttribute("data-slug");
        }
        function insertRegiondoWidgetShortcode() {
            if (typeof selectedId === 'undefined') {
                alert( "<?php _e( 'Please select a Regiondo Booking Widget', 'regiondo-booking-widget' ); ?>" );
                return false;
            }
            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor( "[regiondo_widget slug=" + slectedSlug + "]" );
        }
    </script>
    <div id="regiondo-widget-form" style="display: none;">
        <h3>
            <?php _e('Insert Regiondo Booking Widget', 'regiondo-booking-widget'); ?>
        </h3>
        <p>
            <?php _e('Please select a Regiondo Booking Widget below to add it to your post or page.', 'regiondo-booking-widget'); ?>
        </p>
        <p>
            <select onchange="getId(this)">
                <option value="">
                    <?php _e( 'Select a Regiondo Booking Widget', 'regiondo-booking-widget' ); ?>
                </option>
                <?php
                $args = array( 'post_type' => 'regiondo_iframe', 'suppress_filters' => 0, 'numberposts' => -1, 'order' => 'ASC' );
                $regiondoWidget = get_posts( $args );
                if ( $regiondoWidget ) {
                    foreach( $regiondoWidget as $regiondoWidget ) : setup_postdata( $regiondoWidget );
                        echo '<option value="' . $regiondoWidget -> ID . '" data-slug="' . $regiondoWidget -> post_name . '">' . esc_html( $regiondoWidget -> post_title ) . '</option>';
                    endforeach;
                } else {
                    echo '<option value="">' . __( 'No Regiondo Booking widgets found', 'regiondo-booking-widget' ) . '</option>';
                };
                ?>
            </select>
        </p>
        <p>
            <input type="button" class="button-primary" value="<?php _e( 'Insert Regiondo Booking Widget', 'regiondo-booking-widget' ) ?>" onclick="insertRegiondoWidgetShortcode();"/>
        </p>
    </div>

<?php }
// Regindo Widget messages
function regiondo_widget_messages( $messages ) {
    $messages['regiondo_iframe'] = array(
        0 => '',
        1 => current_user_can( 'edit_theme_options' ) ? sprintf( __( 'Regiondo Widget updated. <a href="%s">Manage Widgets</a>', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ) : sprintf( __( 'Regiondo Widget updated.', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ),
        2 => __( 'Regiondo Widget updated.', 'regiondo-booking-widget' ),
        3 => __( 'Regiondo Widget deleted.', 'regiondo-booking-widget' ),
        4 => __( 'Regiondo Widget updated.', 'regiondo-booking-widget' ),
        5 => isset($_GET['revision']) ? sprintf( __( 'Regiondo Widget restored to revision from %s', 'regiondo-booking-widget' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => current_user_can( 'edit_theme_options' ) ? sprintf( __( 'Regiondo Widget published. <a href="%s">Manage Widgets</a>', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ) : sprintf( __( 'Regiondo Widget published.', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ),
        7 => __( 'Block saved.', 'regiondo-booking-widget' ),
        8 => current_user_can( 'edit_theme_options' ) ? sprintf( __( 'Regiondo Widget submitted. <a href="%s">Manage Widgets</a>', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ) : sprintf( __( 'Regiondo Widget submitted.', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ),
        9 => sprintf( __( 'Regiondo Widget scheduled for: <strong>%1$s</strong>.', 'regiondo-booking-widget' ), date_i18n( __( 'M j, Y @ G:i' , 'regiondo-booking-widget' ), strtotime(isset($post->post_date) ? $post->post_date : '') ), esc_url( 'widgets.php' ) ),
        10 => current_user_can( 'edit_theme_options' ) ? sprintf( __( 'Regiondo Widget draft updated. <a href="%s">Manage Widgets</a>', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ) : sprintf( __( 'Regiondo Widget draft updated.', 'regiondo-booking-widget' ), esc_url( 'widgets.php' ) ),
    );
    return $messages;
}
add_filter( 'post_updated_messages', 'regiondo_widget_messages' );
