<?php
/*
 * Widget listing related posts
 * This is a backport of the Largo Related Posts Widget (simple), hacked to favor tags over categories (series still win)
 */
class largo_related_posts_widget extends WP_Widget {

	function largo_related_posts_widget() {
		$widget_ops = array(
			'classname' 	=> 'largo-related-posts',
			'description' 	=> __('Lists posts related to the current post', 'largo')
		);
		$this->WP_Widget( 'largo-related-posts-widget', __('Largo Related Posts (simple)', 'largo'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $post;
		extract( $args );

		// only useful on post pages
		if ( !is_single() ) return;

		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __( 'Read Next', 'largo' ) : $instance['title'], $instance, $this->id_base);

		echo $before_widget;

		if ( $title ) echo $before_title . $title . $after_title;
 		$related = new Largo_Related( $instance['qty'] );

 		//get the related posts
 		$rel_posts = new WP_Query( array(
 			'post__in' => $related->ids(),
 			'nopaging' => 1
 		) );

 		if ( $rel_posts->have_posts() ) {

	 		echo '<ul class="related">';

	 		while ( $rel_posts->have_posts() ) {
		 		$rel_posts->the_post();
		 		echo '<li>';
		 		get_template_part( 'content', 'tiny' );
		 		echo '</li>';
	 		}

	 		echo "</ul>";
 		}
 		wp_reset_postdata();
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['qty'] = $new_instance['qty'];
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Read Next', 'qty' => 1) );
		$title = esc_attr( $instance['title'] );
		$qty = $instance['qty'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'largo' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

		<p>
			<label for="<?php echo $this->get_field_id('qty'); ?>"><?php _e('Number of Posts to Display:', 'largo'); ?></label>
			<select name="<?php echo $this->get_field_name('qty'); ?>" id="<?php echo $this->get_field_id('qty'); ?>">
			<?php
			for ($i = 1; $i < 6; $i++) {
				echo '<option value="', $i, '"', selected($qty, $i, FALSE), '>', $i, '</option>';
			} ?>
			</select>
			<div class="description">It's best to keep this at just one.</div>
		</p>

	<?php
	}
}
function ctmirror_register_widgets() {
	register_widget( 'largo_related_posts_widget' );
}

add_action( 'widgets_init', 'ctmirror_register_widgets' );

/**
 * THE BRAINS BEHIND RELATING CONTENT
 */


/**
 * Returns (and optionally echoes) the 'top term' for a post, falling back to a category if one wasn't specified
 *
 * @param array|string $options Settings for post id, echo, link, use icon, wrapper and exclude
 */
function largo_top_term( $options = array() ) {

	global $wpdb;
	//print_r( $wpdb );

	$defaults = array(
		'post' => get_the_ID(),
		'echo' => TRUE,
		'link' => TRUE,
		'use_icon' => FALSE,
		'wrapper' => 'span',
		'exclude' => array(),	//only for compatibility with largo_categories_and_tags
	);

	$args = wp_parse_args( $options, $defaults );

	$term_id = get_post_meta( $args['post'], 'top_term', TRUE );
	$icon = ( $args['use_icon'] ) ?  '<i class="icon-white icon-tag"></i>' : '' ;	//this will probably change to a callback largo_term_icon() someday
	$link = ( $args['link'] ) ? array('<a href="%2$s" title="Read %3$s in the %4$s category">','</a>') : array('', '') ;
	if ( $term_id ) {
		//get the taxonomy slug
		$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $term_id) );
		// get the term object
		$term = get_term( $term_id, $taxonomy );
		$output = sprintf(
			'<%1$s class="post-category-link">'.$link[0].'%5$s%4$s'.$link[1].'</%1$s>',
			$args['wrapper'],
			get_term_link( $term ),
			of_get_option( 'posts_term_plural' ),
			$term->name,
			$icon
		);
	} else {
		$output = largo_categories_and_tags( 1, false, $args['link'], $args['use_icon'], '', $args['wrapper'], $args['exclude']);
		$output = ( is_array($output) ) ? $output[0] : '';
	}
	if ( $args['echo'] ) echo $output;
	return $output;
}

/**
 *
 */
function largo_filter_get_post_related_topics( $topics, $max ) {
    $post = get_post();

    if ( $post ) {
        $posts = preg_split( '#\s*,\s*#', get_post_meta( $post->ID, 'largo_custom_related_posts', true ) );

        if ( !empty( $posts ) ) {
            // Add a fake term with the ID of -90
            $top_posts = new stdClass();
            $top_posts->term_id = -90;
            $top_posts->name = __( 'Top Posts', 'largo' );
            array_unshift( $topics, $top_posts );
        }
    }

    return $topics;
}
add_filter( 'largo_get_post_related_topics', 'largo_filter_get_post_related_topics', 10, 2 );


/**
 *
 */
function largo_filter_get_recent_posts_for_term_query_args( $query_args, $term, $max, $min, $post ) {

    if ( $term->term_id == -90 ) {
        $posts = preg_split( '#\s*,\s*#', get_post_meta( $post->ID, 'largo_custom_related_posts', true ) );
        $query_args = array(
            'showposts'             => $max,
            'orderby'               => 'post__in',
            'order'                 => 'ASC',
            'ignore_sticky_posts'   => 1,
            'post__in'              => $posts,
        );
    }

    return $query_args;
}
add_filter( 'largo_get_recent_posts_for_term_query_args', 'largo_filter_get_recent_posts_for_term_query_args', 10, 5 );


/**
 * The Largo Related class.
 * Used to dig through posts to find IDs related to the current post
 */
class Largo_Related {

	var $number;
	var $post_id;
	var $post_ids = array();

	/**
	 * Constructor.
	 * Sets up essential parameters for retrieving related posts
	 *
	 * @access public
	 *
	 * @param integer $number optional The number of post IDs to fetch. Defaults to 1
	 * @param integer $post_id optional The ID of the post to get related posts about. If not provided, defaults to global $post
	 * @return null
	 */
	function __construct( $number = 1, $post_id = '' ) {

		if ( ! empty( $number ) ) {
			$this->number = $number;
		}

		if ( ! empty( $post_id ) ) {
			$this->post_id = $post_id;
		} else {
			$this->post_id = get_the_ID();
		}
	}

	/**
	 * Array sorter for organizing terms by # of posts they have
	 *
	 * @param object $a First WP term object
	 * @param object $b Second WP term object
	 * @return integer
	 */
	function popularity_sort( $a, $b ) {
		if ( $a->count == $b->count ) return 0;
		return ( $a->count < $b->count ) ? -1 : 1;
	}

	/**
	 * Performs cleanup of IDs list prior to returning it. Also applies a filter.
	 *
	 * @access protected
	 *
	 * @return array The final array of related post IDs
	 */
	protected function cleanup_ids() {
		//make things unique just to be safe
		$ids = array_unique( $this->post_ids );

		//truncate to desired length
		$ids = array_slice( $ids, 0, $this->number - 1 );

		//run filters
		return apply_filters( 'largo_related_posts', $ids );
	}

	/**
	 * Fetches posts contained within the series(es) this post resides in. Feeds them into $this->post_ids array
	 *
	 * @access protected
	 */
	protected function get_series_posts() {
		//try to get posts by series, if this post is in a series
		$series = get_the_terms( $this->post_id, 'series' );
		if ( count($series) ) {

			//loop thru all the series this post belongs to
			foreach ( $series as $term ) {

				//start to build our query of posts in this series
				// get the posts in this series, ordered by rank or (if missing?) date
				$args = array(
					'post_type' => 'post',
					'posts_per_page' => 20,	//should usually be enough
					'taxonomy' 			=> 'series',
					'term' => $term->slug,
					'orderby' => 'date',
					'order' => 'DESC',
				);

				// see if there's a post that has the sort order info for this series
				$pq = new WP_Query( array(
					'post_type' => 'cftl-tax-landing',
					'series' => $term->slug,
					'posts_per_page' => 1
				));

				if ( $pq->have_posts() ) {
					$pq->next_post();
					$has_order = get_post_meta( $pq->post->ID, 'post_order', TRUE );
					if ( !empty($has_order) ) {
						switch ( $has_order ) {
							case 'ASC':
								$args['order'] = 'ASC';
								break;
							case 'custom':
								$args['orderby'] = 'series_custom';
								break;
							case 'featured, DESC':
							case 'featured, ASC':
								$args['orderby'] = $opt['post_order'];
								break;
						}
					}
				}

				// build the query with the sort defined
				$series_query = new WP_Query( $args );
				if ( $series_query->have_posts() ) {

					//flip our results
					//$series_query->posts = array_reverse($series_query->posts);
					//$series_query->rewind_posts();
					$this->add_from_query( $series_query );

				}
			}
		}
	}

	/**
	 * Fetches posts contained within the tags this post has. Feeds them into $this->post_ids array
	 *
	 * @access protected
	 */
	protected function get_tag_posts() {

		//we've gone back and forth through all the post's series, now let's try traditional taxonomies, starting with tags
		$taxonomies = get_the_terms( $this->post_id, array('post_tag' ) );

		//loop thru taxonomies, much like series, and get posts
		if ( count($taxonomies) ) {
			//sort by popularity, least popular first (considered more precise)
			usort( $taxonomies, array(__CLASS__, 'popularity_sort' ) );

			foreach ( $taxonomies as $term ) {
				$args = array(
					'post_type' => 'post',
					'posts_per_page' => 20,	//should usually be enough
					'taxonomy' 			=> $term->taxonomy,
					'term' => $term->slug,
					'orderby' => 'date',
					'order' => 'DESC',
				);
			}
			// run the query
			$term_query = new WP_Query( $args );

			if ( $term_query->have_posts() ) {
				$this->add_from_query( $term_query );
			}
		}
	}

	/**
	 * Fetches posts contained within the categories and tags this post has. Feeds them into $this->post_ids array
	 *
	 * @access protected
	 */
	protected function get_category_posts() {

		//we've gone back and forth through all the post's series, now let's try traditional taxonomies, starting with tags
		$taxonomies = get_the_terms( $this->post_id, array('category') );

		//loop thru taxonomies, much like series, and get posts
		if ( count($taxonomies) ) {
			//sort by popularity, least popular first (considered more precise)
			usort( $taxonomies, array(__CLASS__, 'popularity_sort' ) );

			foreach ( $taxonomies as $term ) {
				$args = array(
					'post_type' => 'post',
					'posts_per_page' => 20,	//should usually be enough
					'taxonomy' 			=> $term->taxonomy,
					'term' => $term->slug,
					'orderby' => 'date',
					'order' => 'DESC',
				);
			}
			// run the query
			$term_query = new WP_Query( $args );

			if ( $term_query->have_posts() ) {
				$this->add_from_query( $term_query );
			}
		}
	}

	/**
	 * Fetches recent posts. Used as a fallback when other methods have failed to fill post_ids to requested length
	 *
	 * @access protected
	 */
	protected function get_recent_posts() {

		$args = array(
			'post_type' => 'post',
			'posts_per_page' => $this->number,
			'post__not_in' => array( $this->post_id ),
		);

		$posts_query = new WP_Query( $args );

		if ( $posts_query->have_posts() ) {
			while ( $posts_query->next_post() ) {
				if ( !in_array($posts_query->post->ID, $this->post_ids) ) $this->post_ids[] = $posts_query->post->ID;
			}
		}
	}

	/**
	 * Loops through series, terms and recent to fill array of related post IDs. Primary means of using this class.
	 *
	 * @access public
	 *
	 * @return array An array of post ids related to the given post
	 */
	public function ids() {

		//see if this post has manually set related posts
		$post_ids = get_post_meta( $this->post_id, '_largo_custom_related_posts', true );
		if ( ! empty( $post_ids ) ) {
			$this->post_ids = explode( ",", $post_ids );
			if ( count( $this->post_ids ) >= $number ) {
				return $this->cleanup_ids();
			}
		}

		$this->get_series_posts();

		//are we done yet?
		if ( count($this->post_ids) == $this->number ) return $this->cleanup_ids();
		$this->get_tag_posts();

		//are we done yet?
		if ( count($this->post_ids) == $this->number ) return $this->cleanup_ids();
		$this->get_category_posts();

		//are we done yet?
		if ( count($this->post_ids) == $this->number ) return $this->cleanup_ids();

		$this->get_recent_posts();
		return $this->cleanup_ids();
	}

	/**
	 * Takes a WP_Query result and adds the IDs to $this->post_ids
	 *
	 * @access protected
	 *
	 * @param object a WP_Query object
	 * @param boolean optional whether the query post order has been reversed yet. If not, this will loop through in both directions.
	 */
	protected function add_from_query( $q, $reversed = FALSE ) {
		// don't pick up anything until we're past our own post
		$found_ours = FALSE;

		while ( $q->have_posts() ) {
			$q->next_post();
			//don't show our post, but record that we've found it
			if ( $q->post->ID == $this->post_id ) {
				$found_ours = TRUE;
				continue;
			// don't add any posts until we're adding posts newer than the one being displayed
			} else if ( ! $found_ours ) {
				continue;
			// add this post if it's new
			} else if ( ! in_array( $q->post->ID, $this->post_ids ) ) {	// only add it if it wasn't already there
				$this->post_ids[] = $q->post->ID;
				// stop if we have enough
				if ( count( $this->post_ids ) == $this->number ) return;
			}
		}

		//still here? reverse and try again
		if ( ! $reversed ) {
			$q->posts = array_reverse($q->posts);
			$q->rewind_posts();
			$this->add_from_query( $q, TRUE );
		}
	}
}
