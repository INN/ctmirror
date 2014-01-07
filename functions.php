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
		<link href="//fonts.googleapis.com/css?family=Libre+Baskerville:400,700,400italic" rel="stylesheet" type="text/css" />
	<?php
	endif;
}
add_action( 'wp_head', 'ctmirror_head', 9 );