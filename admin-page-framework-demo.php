<?php
/* 
	Plugin Name: Admin Page Framework - Demo
	Plugin URI: http://en.michaeluno.jp/admin-page-framework
	Description: Demonstrates the features of the Admin Page Framework class.
	Author: Michael Uno
	Author URI: http://michaeluno.jp
	Version: 3.0.0b
	Requirements: PHP 5.2.4 or above, WordPress 3.3 or above.
*/ 

define( 'APFDEMO_FILE', __FILE__ );
define( 'APFDEMO_DIRNAME', dirname( APFDEMO_FILE ) );

/* Include the library */
if ( ! class_exists( 'AdminPageFramework' ) )
    include_once( APFDEMO_DIRNAME . '/class/admin-page-framework.php' );

/* Include the demo class that creates a custom post type. */
include_once( APFDEMO_DIRNAME . '/example/APF_PostType.php' );
new APF_PostType( 
	'apf_posts', 	// post type slug
	array(			// argument - for the array structure, refer to http://codex.wordpress.org/Function_Reference/register_post_type#Arguments
		'labels' => array(
			'name' => 'Admin Page Framework',
			'all_items' => __( 'Sample Posts', 'admin-page-framework-demo' ),
			'singular_name' => 'Admin Page Framework',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New APF Post',
			'edit' => 'Edit',
			'edit_item' => 'Edit APF Post',
			'new_item' => 'New APF Post',
			'view' => 'View',
			'view_item' => 'View APF Post',
			'search_items' => 'Search APF Post',
			'not_found' => 'No APF Post found',
			'not_found_in_trash' => 'No APF Post found in Trash',
			'parent' => 'Parent APF Post'
		),
		'public' => true,
		'menu_position' => 110,
		// 'supports' => array( 'title', 'editor', 'comments', 'thumbnail' ),	// 'custom-fields'
		'supports' => array( 'title' ),
		'taxonomies' => array( '' ),
		'has_archive' => true,
		'show_admin_column' => true,	// ( framework specific key ) this is for custom taxonomies to automatically add the column in the listing table.
		'menu_icon' => plugins_url( 'asset/image/wp-logo_16x16.png', APFDEMO_FILE ),
		// ( framework specific key ) this sets the screen icon for the post type.
		'screen_icon' => dirname( APFDEMO_FILE  ) . '/asset/image/wp-logo_32x32.png', // a file path can be passed instead of a url, plugins_url( 'asset/image/wp-logo_32x32.png', APFDEMO_FILE )
	)
);	// should not use "if ( is_admin() )" for the this class because posts of custom post type can be accessed from the front-end pages.
	
if ( is_admin() ) :
	
	/* Include the basic usage example that creates a root page and its sub-pages. */
	include_once( APFDEMO_DIRNAME . '/example/APF_BasicUsage.php' );
	new APF_BasicUsage;

	/* Instantiate the main framework class so that the pages and form fields will be created. */
	include_once( APFDEMO_DIRNAME . '/example/APF_Demo.php' );	// Include the demo class that creates various forms.
	new APF_Demo;

	/* Include the demo class that creates a meta box. */
	include_once( APFDEMO_DIRNAME . '/example/APF_MetaBox.php' );
	new APF_MetaBox(
		'sample_custom_meta_box',
		'My Custom Meta Box',
		array( 'apf_posts' ),	// post, page, etc.
		'normal',
		'default'
	);
	
endif;
	
/*
 * 
 * If you find this framework useful, include it in your project!
 * And please leave a nice comment in the review page, http://wordpress.org/support/view/plugin-reviews/admin-page-framework
 * 
 * If you have a suggestion, the GitHub repository is open to anybody so post an issue there.
 * https://github.com/michaeluno/admin-page-framework/issues
 * 
 * Happy coding!
 * 
 */