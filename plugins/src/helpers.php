<?php

namespace Yoyo;

use Clickfwd\Yoyo\Yoyo;

if (! function_exists('Yoyo\yoyo_render')) 
{
    function yoyo_render($name, $variables = [], $attributes = []): string
    {
    	do_action('yoyo:on_render');

        $yoyo = new Yoyo();

        $vars = ['yoyo:resolver' => 'wordpress'];

		// Differentiate requests based on client (site or administrator)

		if (! wp_doing_ajax() && is_admin()) {
			$vars['yoyo:client'] = 'admin';

            if (! current_user_can('administrator')) {
                wp_die('',403);
            }
		}

        $variables = array_merge($vars, $variables);
    
        return $yoyo->mount($name, $variables, $attributes)->render();
    }
}