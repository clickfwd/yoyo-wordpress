<?php
/* Plugin Name: Yoyo - Reactive PHP Framework
Plugin URI: https://getyoyo.dev
Description: WordPress implementation of Yoyo to simplify reactive component development
Version: 0.3.0
Author: ClickFWD LLC
Author URI: https://getyoyo.dev
License: GNU GPL version 3 or later
*/

defined('ABSPATH') or die;

use Clickfwd\Yoyo\Services\Configuration as YoyoConfig;
use Clickfwd\Yoyo\Services\Request as YoyoRequest;
use Clickfwd\Yoyo\WordPress\WordPressComponentResolver;
use Clickfwd\Yoyo\Yoyo;

class YoyoFramework 	
{
	private $yoyo;

	public function __construct()
	{
		// Initialize Yoyo

		add_action('init', [$this, 'yoyo_init'], 1);

		// Load Javavascript

		add_action('wp_enqueue_scripts', [$this, 'scripts']);
		add_action('admin_enqueue_scripts', [$this, 'scripts']);

		// Load CSS

		add_action('wp_head', [$this,'cssStyles']);
		add_action('admin_head', [$this,'cssStyles']);

		// Process Yoyo component updates
		
		add_action('wp_ajax_nopriv_yoyo', [$this, 'yoyo_update']);
		
		add_action('wp_ajax_yoyo', [$this, 'yoyo_update']);
	}

	public function yoyo_init()
	{
		define('YOYO_FRAMEWORK', 1);
		
		require_once __DIR__.'/helpers.php';
		require_once __DIR__.'/../vendor/autoload.php';
		require_once __DIR__.'/WordPressComponentResolver.php';

		$url = admin_url('admin-ajax.php?action=yoyo');

		$this->yoyo = new Yoyo();

		$this->yoyo->configure([
		  'url' => $url,
		  'scriptsPath' => plugin_dir_url(__FILE__).'/../vendor/clickfwd/yoyo/src/assets/js',
		  // Disabled until a better history caching solution can be implemented
		  'historyEnabled' => false,
		]);
		
		$this->yoyo->registerComponentResolver('wordpress', WordPressComponentResolver::class);

		do_action('yoyo:initialized', $this->yoyo);
	}

	public function scripts()
	{
		add_action('yoyo:on_render', function() {
			wp_enqueue_script('htmx', YoyoConfig::htmxSrc());
			wp_enqueue_script('yoyo-framework', YoyoConfig::yoyoSrc(), ['htmx']);
			wp_add_inline_script('yoyo-framework', YoyoConfig::javascriptInitCode(false) , 'after' );
		});		
	}

	public function cssStyles()
	{
		add_action('yoyo:on_render', function() {
			echo YoyoConfig::cssStyle();
		});
	}

	public function yoyo_update()
	{
		if ($this->yoyo->request()->get('yoyo:client') == 'admin' && ! current_user_can('administrator')) {
			wp_die('',403);
		}

		wp_die($this->yoyo->update());
	}
}

new YoyoFramework();