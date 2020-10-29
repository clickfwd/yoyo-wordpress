<?php
/* Plugin Name: Yoyo Demo Widget
Plugin URI: https://getyoyo.dev
Description: Widget with a few demo Yoyo components
Version: 0.4.0
Author: ClickFWD LLC
Author URI: https://getyoyo.dev
License: GNU GPL version 3 or later
*/

defined('ABSPATH') or die;

class Yoyo_Demo_Widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
			'yoyo_demo_widget',
			__( 'Yoyo Demo Widget', 'yoyo_demo_widget' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}

	public function form( $instance ) {	
		/* ... */
	}

	public function update( $new_instance, $old_instance ) {
		/* ... */
	}

	public function widget( $args, $instance ) 
	{
		if (!function_exists('Yoyo\yoyo_render')) {
			return;
		}	

		include __DIR__.'/../template/default.php';
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Yoyo_Demo_Widget' );
});