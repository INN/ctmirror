<?php
/**
 * Connecticut Mirror functions and definitions
 */

define('CALLOUT_POSITION', 5);

/**
 * Load up all of the goodies from the /inc directory
 */
$includes = array(
	'/inc/callout.php',	//adds a widget within the body of posts
	'/inc/largo-related.php',	// implements largo-related widget from newer version of Largo
	'/inc/sponsors.php',	// implements largo-related widget from newer version of Largo
	'/inc/post-types.php',	// the post types
	'/inc/fields.php',			// the fields
	'/inc/taxonomy.php',		// the custom taxonomies
);

// Perform load
foreach ( $includes as $include ) {
	require_once( get_stylesheet_directory() . $include );
}

/**
 * Enqueue JS
 */
function ctmirror_enqueue() {
	wp_enqueue_script(
		'ctmirror',
		get_stylesheet_directory_uri() . '/js/ctmirror.js',
		array('jquery'),
		'1.0',
		TRUE
	);
}
add_action( 'wp_enqueue_scripts', 'ctmirror_enqueue' );

/**
 * add elements to the header
 */
function ctmirror_head() {

	if ( !is_admin() ) :	?>
	
		<script type="text/javascript">
		    window._tpm = window._tpm || [];
		    window._tpm['paywallID'] = '87513365';
		    window._tpm['trackPageview'] = true;
		</script>
		<script type="text/javascript" src="//cdn.tinypass.com/tpl/d1/tpm.js"></script>
		
	<?php
	endif;
}
//add_action( 'wp_head', 'ctmirror_head', 9 );

/**
 * Eliminating relativistic times ("about 2 hours ago")
 */
function largo_time( $echo = true ) {

	$output = get_the_date();

	if ( $echo )
		echo $output;
	return $output;
}


/**
 * Outputs custom byline and link (if set), otherwise outputs author link and post date
 *
 * Here in case we need to hide author when "admin" or "staff"
 */
function largo_byline( $echo = true ) {
	global $post;

	$values = get_post_custom( $post->ID );
	$authors = ( function_exists( 'coauthors_posts_links' ) && !isset( $values['largo_byline_text'] ) ) ? coauthors_posts_links( null, null, null, null, false ) : largo_author_link( false );

	$format = '<span class="by-author"><span class="by">By:</span> <span class="author vcard" itemprop="author">%1$s</span></span><span class="sep"> | </span><time class="entry-date updated dtstamp pubdate" datetime="%2$s">%3$s</time>';

	if ( !isset( $values['largo_byline_text'] ) && $post->post_author == 1 )
		$format = '<time class="entry-date updated dtstamp pubdate" datetime="%2$s">%3$s</time>';

	$output = sprintf( '<span class="by-author"><span class="by">By:</span> <span class="author vcard" itemprop="author">%1$s</span></span><span class="sep"> | </span><time class="entry-date updated dtstamp pubdate" datetime="%2$s">%3$s</time>',
		$authors,
		esc_attr( get_the_date( 'c' ) ),
		largo_time( false )
	);



	if ( current_user_can( 'edit_post', $post->ID ) )
		$output .=  sprintf( ' | <span class="edit-link"><a href="%1$s">Edit This Post</a></span>', get_edit_post_link() );

 	if ( is_single() && of_get_option( 'clean_read' ) === 'byline' )
 		$output .=	__('<a href="#" class="clean-read">View as "Clean Read"</a>', 'largo');

	if ( $echo )
		echo $output;
	return $output;
}


/**
 * performance hack?
 * See http://hitchhackerguide.com/2011/11/01/reducing-postmeta-queries-with-update_meta_cache/
 */
add_filter( 'posts_results', 'cache_meta_data', 9999, 2 );
function cache_meta_data( $posts, $object ) {
    $posts_to_cache = array();
    // this usually makes only sense when we have a bunch of posts
    if ( empty( $posts ) || is_wp_error( $posts ) || is_single() || is_page() || count( $posts ) < 3 )
        return $posts;

    foreach( $posts as $post ) {
        if ( isset( $post->ID ) && isset( $post->post_type ) ) {
            $posts_to_cache[$post->ID] = 1;
        }
    }

    if ( empty( $posts_to_cache ) )
        return $posts;

    update_meta_cache( 'post', array_keys( $posts_to_cache ) );
    unset( $posts_to_cache );

    return $posts;
}

/**
 * New ad zone
 */
function ctmirror_ad_tags_ids( $tags ) {
	$tags[] = array(
		'tag'       => 'tall',
		'url_vars'  => array(
				'tag'       => '250x500',
				'sz'        => '250x500',
				'height'    => '500',
				'width'     => '250',
			),
		'enable_ui_mapping' => true,
	);
	$tags[] = array(
		'tag'       => 'wide',
		'url_vars'  => array(
				'tag'       => '640x80',
				'sz'        => '640x80',
				'height'    => '80',
				'width'     => '640',
			),
		'enable_ui_mapping' => true,
	);
	$tags[] = array(
		'tag'       => '250x250-2',
		'url_vars'  => array(
				'tag'       => '250x250-2',
				'sz'        => '250x250',
				'height'    => '250',
				'width'     => '250',
			),
		'enable_ui_mapping' => true,
	);
	$tags[] = array(
		'tag'       => '300x250-3',
		'url_vars'  => array(
				'tag'       => '300x250-3',
				'sz'        => '300x250',
				'height'    => '250',
				'width'     => '300',
			),
		'enable_ui_mapping' => true,
	);
	return $tags;
}
add_filter( 'acm_ad_tag_ids', 'ctmirror_ad_tags_ids', 11 );

// display ads as various actions
function ctmirror_rail_ads() {
	do_action( 'acm_tag', 'right-sidebar', 10, 3 );
	do_action( 'acm_tag', 'right-sidebar-tall' );
	do_action( 'acm_tag', 'right-sidebar-2' );
	do_action( 'acm_tag', 'adsense-rect' );
}
add_action( 'largo_after_sidebar_content', 'ctmirror_rail_ads', 11 );

// Add additional output tokens
function ctmirror_acm_output_tokens( $output_tokens, $tag_id, $code_to_display ) {
	// This is a quick example to show how to assign an output token to any value. Things like the zone1 value can be used to compute.
	$output_tokens['%tag_id%'] = $tag_id;
//	$output_tokens['%width%'] = $code_to_display['url_vars']['width'];	//can't get these to work, always NULL
//	$output_tokens['%height%'] = $code_to_display['url_vars']['height'];
	return $output_tokens;
}
// The low priority will not overwrite what's set up. Higher values will.
add_filter('acm_output_tokens', 'ctmirror_acm_output_tokens', 1, 3 );


/**
 * Iframe support as shortcode
 */
function ctmirror_iframe_shortcode( $atts ) {
	$attrs = array();
	foreach ( $atts as $k => $v ) {
		$attrs[] = $k.'="'.esc_attr($v).'"';
	}
	return '<iframe '.implode( ' ', $attrs). '></iframe>';
}
add_shortcode( 'iframe', 'ctmirror_iframe_shortcode' );

/**
 * Killing columns from 'All Posts' admin screen
 */
function ctmirror_admin_columns($columns) {
	//print_r($columns);
	$bad_columns = array ( 'taxonomy-health-topic', 'taxonomy-blog-type', 'taxonomy-campaign-issue', 'taxonomy-image-gallery', 'taxonomy-spotlight', 'comments', 'tags' );
	foreach ($bad_columns as $key) {
		unset( $columns[ $key ] );
	}
  return $columns;
}
add_filter('manage_posts_columns' , 'ctmirror_admin_columns', 11);


// Allow tags and attributes in TINYMCE

add_action( 'init', 'vipx_allow_contenteditable_on_divs' );
function vipx_allow_contenteditable_on_divs() {
    global $allowedposttags;
 
    $tags = array( 'div','style','script','link' );
    $new_attributes = array( 'contenteditable' => array() );
 
    foreach ( $tags as $tag ) {
        if ( isset( $allowedposttags[ $tag ] ) && is_array( $allowedposttags[ $tag ] ) )
            $allowedposttags[ $tag ] = array_merge( $allowedposttags[ $tag ], $new_attributes );
    }
}
add_filter('tiny_mce_before_init', 'vipx_filter_tiny_mce_before_init'); 
function vipx_filter_tiny_mce_before_init( $options ) { 
 
    if ( ! isset( $options['extended_valid_elements'] ) ) 
        $options['extended_valid_elements'] = ''; 
     
    $options['extended_valid_elements'] .= ',div[contenteditable|class|id|style|rel|num|word|text|related],span[contenteditable|class|id|style|rel|num|word|text|related]'; 
    return $options; 
}
