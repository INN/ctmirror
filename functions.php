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
 * Load Google font
 */
function ctmirror_head() {

	if ( !is_admin() ) :	?>
		<link href="//fonts.googleapis.com/css?family=Lora:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
	<?php
	endif;
}
add_action( 'wp_head', 'ctmirror_head', 9 );

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