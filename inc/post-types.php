<?php

function ctmirror_register_post_types() {
	register_post_type( 
		'affiliate', 
		array( 
			'label' => 'Affiliates', 
			'public' => true,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
		)
	);

	register_post_type( 
		'politician', 
		array( 
			'label' => 'Politician', 
			'public' => true,
			'supports' => array( 'title', 'editor', 'thumbnail' ),
		)
	);
};

add_action( 'init', 'ctmirror_register_post_types' );