<?php

// Register Custom Post Type
function ctmirror_sponsors() {
	$labels = array(
		'name'                => _x( 'Sponsors', 'Post Type General Name', 'ctmirror' ),
		'singular_name'       => _x( 'Sponsor', 'Post Type Singular Name', 'ctmirror' ),
		'menu_name'           => __( 'Sponsors', 'ctmirror' ),
		'parent_item_colon'   => __( 'Parent Sponsor:', 'ctmirror' ),
		'all_items'           => __( 'All Sponsors', 'ctmirror' ),
		'view_item'           => __( 'View Sponsor', 'ctmirror' ),
		'add_new_item'        => __( 'Add New Sponsor', 'ctmirror' ),
		'add_new'             => __( 'New Sponsor', 'ctmirror' ),
		'edit_item'           => __( 'Edit Sponsor', 'ctmirror' ),
		'update_item'         => __( 'Update Sponsor', 'ctmirror' ),
		'search_items'        => __( 'Search sponsors', 'ctmirror' ),
		'not_found'           => __( 'No sponsors found', 'ctmirror' ),
		'not_found_in_trash'  => __( 'No sponsors in Trash', 'ctmirror' ),
	);
	$args = array(
		'label'               => __( 'sponsor', 'ctmirror' ),
		'description'         => __( 'CT Mirror supporters', 'ctmirror' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 20,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'sponsor', $args );
}
add_action( 'init', 'ctmirror_sponsors', 0 );

/**
 * URL fields for sponsors
 */
// Add the Meta Box
function ctmirror_sponsor_box() {
  add_meta_box(
    'url', // $id
    'Sponsor Link', // $title
    'ctmirror_sponsor_url', // $callback
    'sponsor', // $page
    'normal', // $context
    'high' // $priority
  );
}
add_action('add_meta_boxes', 'ctmirror_sponsor_box');

function ctmirror_sponsor_url() {
	global $post;

	// Use nonce for verification
	wp_nonce_field('sponsor_edit','sponsor_url_nonce');

  // get value of this field if it exists for this post
  $value = get_post_meta($post->ID, 'sponsor_url', true);
	echo '<label for="sponsor_url">' . __('Sponsor URL', 'ctmirror') . '</label>';
	echo '<input type="text" name="sponsor_url" id="sponsor_url" value="' . esc_attr($value) . '" size="30" /><br />';
	echo '<span class="description">' . __('The URL this sponsor image should link to. Include http://', 'ctmirror') .'</span>';

}

// Save the Data
function save_sponsor_url($post_id) {

  // verify nonce
  if (!wp_verify_nonce($_POST['sponsor_url_nonce'], 'sponsor_edit'))
      return $post_id;
  // check autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
      return $post_id;
  // check permissions
  if ('sponsor' == $_POST['post_type']) {
      if (!current_user_can('edit_page', $post_id))
          return $post_id;
      } elseif (!current_user_can('edit_post', $post_id)) {
          return $post_id;
  }

  // loop through fields and save the data
	$old = get_post_meta($post_id, 'sponsor_url', true);
  $new = maybe_http( $_POST['sponsor_url'] );
  if ($new && $new != $old) {
  	update_post_meta($post_id, 'sponsor_url', $new);
  } elseif ('' == $new && $old) {
  	delete_post_meta($post_id, 'sponsor_url', $old);
  }

}
add_action('save_post', 'save_sponsor_url');

//make sure links always start with HTTP, users often forget this
function maybe_http( $url ) {
	$url = trim( $url );
	if (strpos( $url, "http://" ) === 0 || strpos( $url, "https://" ) === 0) return $url;
	return "http://" . $url;
}


/**
 * Implement the slideshow widget =====================================
 */
class ctmirror_sponsors extends WP_Widget {

	function ctmirror_sponsors() {
		$widget_ops = array(
			'classname' 	=> 'ctmirror-sponsors',
			'description' 	=> __('Shows a slideshow of sponsor logos and links', 'ctmirror')
		);
		$this->WP_Widget( 'ctmirror-sponsors', __('Sponsor carousel', 'ctmirror'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $post;
		extract( $args );

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Read Next', 'largo' ) : $instance['title'], $instance, $this->id_base);

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;

 		//get the sponsors
 		$sponsors = new WP_Query( array(
 			'post_type' => 'sponsor',
 			'nopaging' => 1,
 			'orderby' => $instance['orderby'],
 		) );

 		if ( $sponsors->have_posts() ) {

	 		echo '<ul class="sponsors-wrapper cycle-wrapper" data-cycle-timeout="'. $instance['delay'] * 1000 . '">';

	 		while ( $sponsors->have_posts() ) {
		 		$sponsors->the_post();
		 		echo '<li>';
		 		echo '<a href="' . get_post_meta( get_the_ID() , 'sponsor_url', true) . '" target="_blank">';
		 		the_post_thumbnail( $instance['img_size'] );
		 		echo '</a></li>';
	 		}

	 		echo "</ul>";
 		}
 		wp_reset_postdata();
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['delay'] = $new_instance['delay'];
		$instance['orderby'] = $new_instance['orderby'];
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => 'Our Sponsors',
				'delay' => 2,
				'orderby' => 'title',
				'img_size' => 'medium'
			)
		);
		extract($instance);
		$title = esc_attr( $title );
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'ctmirror' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p>
			<label for="<?php echo $this->get_field_id('delay'); ?>"><?php _e('Duration of each sponsor (in seconds):', 'largo'); ?></label>
			<select name="<?php echo $this->get_field_name('delay'); ?>" id="<?php echo $this->get_field_id('delay'); ?>">
			<?php
			for ($i = 1; $i <= 10; $i++) {
				echo '<option value="', $i, '"', selected($delay, $i, FALSE), '>', $i, '</option>';
			} ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order of sponsors', 'largo'); ?></label>
			<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>">
			<?php
			$order_opts = array('Alphabetical' => 'title', 'Date' => 'date', 'Custom' => 'menu_order', 'Random' => 'rand');
			foreach ( $order_opts as $label => $value ) {
				echo '<option value="', $value, '"', selected($orderby, $value, FALSE), '>', $label, '</option>';
			} ?>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('img_size'); ?>"><?php _e('Logo size', 'largo'); ?></label>
			<select name="<?php echo $this->get_field_name('img_size'); ?>" id="<?php echo $this->get_field_id('img_size'); ?>">
			<?php
			$size_opts = array('Small' => 'thumbnail', 'Medium' => 'medium', 'Large' => 'Large', 'Original' => 'full');
			foreach ( $size_opts as $label => $value ) {
				echo '<option value="', $value, '"', selected($img_size, $value, FALSE), '>', $label, '</option>';
			} ?>
			</select>
		</p>

	<?php
	}
}
function ctmirror_register_sponsor_widget() {
	register_widget( 'ctmirror_sponsors' );
}

add_action( 'widgets_init', 'ctmirror_register_sponsor_widget' );