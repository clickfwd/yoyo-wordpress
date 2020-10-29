<?php

namespace Clickfwd\Yoyo\WordPress;

defined('ABSPATH') or die;

use Clickfwd\Yoyo\Yoyo;
use Clickfwd\Yoyo\Component;
use Clickfwd\Yoyo\AnonymousComponent;
use Clickfwd\Yoyo\ComponentResolver;
use Clickfwd\Yoyo\Interfaces\ViewProviderInterface;
use Clickfwd\Yoyo\Services\Configuration;
use Clickfwd\Yoyo\ViewProviders\YoyoViewProvider;
use Clickfwd\Yoyo\YoyoHelpers;
use Clickfwd\Yoyo\View;

class WordPressComponentResolver extends ComponentResolver
{
    public function resolveDynamic($registered): ?Component
    {
        if ($this->source()) {

            $class = $this->autoloadComponentClass();

            if ($class && is_subclass_of($class, Component::class)) {
                return new $class($this->id, $this->name, $this);
            }
        }

        return parent::resolveDynamic($registered);
    }

    public function resolveAnonymous($registered): ?Component
    {
        $view = $this->resolveViewProvider();

        if ($view->exists($this->name)) {
            return new AnonymousComponent($this->id, $this->name, $this);
        }

        return null;
    }    

    public function resolveViewProvider(): ViewProviderInterface
    {
        return new YoyoViewProvider(new View($this->getViewPath()));
    }

    protected function getViewPath()
    {
        $paths = [];

        $yoyoComponentName = $this->name;

        [$sourceType,$sourceName] = explode('.',$this->source().'.');

        switch($sourceType)
        {
            case 'plugin':
                if (self::clientIsAdmin()) {
                    $paths[] = WP_PLUGIN_DIR."/{$sourceName}/yoyo_admin/views";
                } else {
                    $paths[] = WP_PLUGIN_DIR."/{$sourceName}/yoyo/views";
                }
                break;  

            case 'theme':
                if (self::clientIsAdmin()) {
                    $paths[] = get_template_directory()."/yoyo_admin/views";
                    $paths[] = get_stylesheet_directory()."/yoyo_admin/views";
                } else {
                    $paths[] = get_template_directory()."/yoyo/views";
                    $paths[] = get_stylesheet_directory()."/yoyo/views";
                }
                break;  
        }

        foreach ($paths as $path) {
            if (is_dir($path)) {
                return $path;
            }
        }

        throw new \Exception("View path not found for Yoyo component [$yoyoComponentName] at [{$path}].");
    }

    protected function autoloadComponentClass()
    {
        $source = $this->source();

        $yoyoComponentName = $this->name;

        $paths = [];

        [$sourceType,$sourceName] = explode('.',$source.'.');

        $className = YoyoHelpers::studly($yoyoComponentName);

        switch($sourceType)
        {
            case 'plugin':
                if (self::clientIsAdmin()) {
                    $paths[] = WP_PLUGIN_DIR."/{$sourceName}/yoyo_admin/components/{$className}.php";
                } else {
                    $paths[] = WP_PLUGIN_DIR."/{$sourceName}/yoyo/components/{$className}.php";
                }
                
                $className = 'Yoyo\Plugins\\'.YoyoHelpers::studly($sourceName).'\\'.$className;
                
            break;  

            case 'theme':
                if (self::clientIsAdmin()) {
                    $paths[] = get_template_directory()."/yoyo_admin/components/{$className}.php";
                    $paths[] = get_stylesheet_directory()."/yoyo_admin/components/{$className}.php";
                } else {
                    $paths[] = get_template_directory()."/yoyo/components/{$className}.php";
                    $paths[] = get_stylesheet_directory()."/yoyo/components/{$className}.php";
                }

                $className = 'Yoyo\Themes\\'.$className;

            break;
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once($path);

                return $className;
            }
        }
    }

    protected static function clientIsAdmin()
    {
        return (! wp_doing_ajax() && is_admin()) || Yoyo::request()->get('yoyo:client') == 'admin';
    }
}