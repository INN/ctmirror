<?php

function ctmirror_register_taxonomy() {
	$story_type_args = array(
		'hierarchical'       => true,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'labels'             => array(
			'name'          => 'Story Types',
			'singular_name' => 'Story Type',
		),
	);
	register_taxonomy( 'story-type', 'post', $story_type_args );


	$taxonomies = array(
		'health-topic'   => array( 'Health Topic', 'Health Topics' ),
		'blog-type'      => array( 'Blog Type', 'Blog Types' ),
		//'document-group' => array( 'Document Group', 'Document Groups' ),
		'campaign-issue' => array( 'Campaign Issue', 'Campaign Issues' ),
		'image-gallery'  => array( 'Image Gallery', 'Image Galleries' ),
		'spotlight'  => array( 'Spotlight', 'Spotlights' ),
	);

	foreach( $taxonomies as $taxonomy => $labels ) {
		$args = array(
			'hierarchical'       => true,
			'show_ui'            => true,
			'show_admin_column'  => true,
			'labels'             => array(
				'name'          => $labels[1],
				'singular_name' => $labels[0],
			),
		);

		register_taxonomy( $taxonomy, 'post', $args );
	}

	register_taxonomy( 'media-categories', 'attachment', array(
		'hierarchical'       => true,
		'show_ui'            => true,
		'show_admin_column'  => true,
		'labels'             => array(
			'name'          => 'Media Categories',
			'singular_name' => 'Media Category',
		),
	));
};

add_action( 'init', 'ctmirror_register_taxonomy' );