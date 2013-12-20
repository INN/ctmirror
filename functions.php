<?php
/**
 * Connecticut Mirror functions and definitions
 */

define('CALLOUT_POSITION', 5);
/**
 * Inserts something interesting before the Nth paragraph, if present
 */
function ctmirror_callout( $content ) {

	$insertion = ctmirror_callout_insert();

	// only on single pages
	if ( !is_single() ) return $content;

	// see how many <p> tags we have, bail if not enough
	$num_grafs = substr_count( strtolower($content), '<p>' );
	if ( $num_grafs <= CALLOUT_POSITION ) return $content;

	//lowercase our tags
	$content = str_replace('<P>', '<p>', $content);

	//split apart
	$chunks = explode( '<p>', $content );

	//insert new
	$chunks[ CALLOUT_POSITION - 1 ] = $chunks[ CALLOUT_POSITION - 1 ] . $insertion;

	//return
	return implode('<p>', $chunks);

}
add_filter( 'the_content', 'ctmirror_callout', 20 );

/**
 * Returns what's to be inserted into the_content
 */
function ctmirror_callout_insert() {
	ob_start();	// why no get_the_widget() ?
	the_widget( 'largo_recent_posts_widget' );
	return ob_get_clean();
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
add_action( 'wp_enqueue_scripts', 'largo_child_enqueue' );