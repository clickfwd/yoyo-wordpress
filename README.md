# Yoyo WordPress

Yoyo WordPress is an implementation of the [Yoyo Reactive PHP Framework](https://github.com/clickfwd/yoyo) so you can easily incorporate reactive Yoyo components on WordPress sites.

The Yoyo Plugin automatically loads the necessary Javascript and CSS files as needed, and it works in the site's front-end and administration.

In addition to the Yoyo plugin, you will also find a Yoyo Demo Widget plugin with a few example components.

## Instalation

Download the zip file, install using the WordPress plugin installer and enable the plugin.

If you also install the Yoyo Demo Widget plugin, remember to publish the Yoyo Demo Widget to one of your theme's existing widget positions.

## Developing with Yoyo in WordPress

Refer to the [Yoyo documentation](https://github.com/clickfwd/yoyo). You can add Yoyo reactive components in WordPress plugins, and themes in both front-end and administration.

### WordPress Plugins

Create a `yoyo` directory in the plugin root. In this example `foo_bar` is the plugin name.

```files
foo_bar
`-- \yoyo
    |-- \components
    `-- \views
```

Use the following namespace convention for Yoyo classes in WordPress plugins:

```php
<?php

namespace Yoyo\Plugins\FooBar;

use Clickfwd\Yoyo\Component;

class Counter extends Component {
    // ...
}
```

To render the Yoyo component in templates use:

```php
<?php echo Yoyo\yoyo_render('counter',[
    'yoyo:source' => 'plugin.foo_bar'
]); ?>
```

### Themes

Create a `yoyo` directory in the WordPress theme or child-theme directory root.

```files
theme_name
`-- \yoyo
    |-- \components
    `-- \views
```

Use the following namespace convention for Yoyo classes in WordPress themes:

```php
<?php

namespace Yoyo\Themes;

use Clickfwd\Yoyo\Component;

class Counter extends Component {
    // ...
}
```

To render the Yoyo component in templates use:

```php
<?php echo Yoyo\yoyo_render('counter',[
    'yoyo:source' => 'theme'
]); ?>
```

There's no need to specify the theme name because Yoyo automatically resolves the component for the current theme.

## Front-end vs. Administration

To differentiate between administrator and site components, when using Yoyo for the administrator, place the components and views inside the `yoyo_admin` directory. Yoyo will automatically resolve Yoyo components to the right directories.

For example if you have a plugin with both front-end and administrator Yoyo components, this is the directory structure:

```files
foo_bar
`-- \yoyo
    |-- \components
    `-- \views
`-- \yoyo_admin
    |-- \components
    `-- \views
```

If a front-end request is made, Yoyo will resolve its components only from front-end `yoyo` directory.

If an administration request is made, Yoyo will resolve its components only from the `yoyo_admin` directory.

## Rendering Yoyo components

You can render any Yoyo component from anywhere on the site as long as the functionality is self contained and loads all the necessary classes. So if you have a Yoyo component in a WordPress plugin, you can call it from within other plugins or theme templates, just by referencing the right `yoyo:source`:

```php
<?php echo Yoyo\yoyo_render('cart',[
    'yoyo:source' => 'plugin.foo_bar'
]); ?>
```

## Creating Custom Resolvers

The Yoyo Plugin triggers a `yoyo:initialized` action hook, allowing you to extend some of the plugin functionality, like adding your own Yoyo component resolvers.

For example, you could use this to allow Yoyo to load Yoyo component class and themes files from any directory on your site.

To tell Yoyo to use a different resolver when rendering a Yoyo component, use the `yoyo:resolver` variable. The example below uses a `custom` resolver instead of the default `wordpress` resolver, to load Yoyo components from the `yoyo` directory in the root of the site. The class namespace used for all Yoyo components is `Yoyo\Custom`.

```php
<?php 
echo Yoyo\yoyo_render($component['name'],[
    'yoyo:resolver' => 'custom',
]); 
?>
```

To create a custom resolver, use the `yoyo:initialized` action which receives a `$yoyo` instance that can be used to register a new resolver.

Below you can see some sample code for a custom resolver that loads the files from a `yoyo` directory in the root of the site. You can add this code to your theme's function.php, or create a new WordPress plugin.
    
```php
add_action('yoyo:initialized', function($yoyo) {
    require_once 'CustomResolver.php';

    $yoyo->registerComponentResolver('custom', Clickfwd\Yoyo\WordPress\CustomResolver::class);
});
```

    CustomResolver.php

```php
<?php

namespace Clickfwd\Yoyo\WordPress;

use Clickfwd\Yoyo\ComponentResolver;
use Clickfwd\Yoyo\YoyoHelpers;

class CustomResolver extends WordPressComponentResolver
{
    protected function getViewPath()
    {
        $yoyoComponentName = $this->name;

        if (self::clientIsAdmin()) {
            $path = ABSPATH."/yoyo/views";
        } else {
            $path = ABSPATH."/yoyo_admin/views";
        }

        if (! is_dir($path)) {
            throw new \Exception("View path not found for Yoyo component [$yoyoComponentName] at [{$path}].");
        }

        return $path;
    }

    protected function autoloadComponentClass()
    {
        $yoyoComponentName = $this->name;
        
        if (self::clientIsAdmin()) {
            $path = ABSPATH."/yoyo/components/{$yoyoComponentName}.php";
        } else {
            $path = ABSPATH."/yoyo_admin/components/{$yoyoComponentName}.php";
        }

        $className = YoyoHelpers::studly($yoyoComponentName);

        if (file_exists($path)) {
            require_once($path);

            return 'Yoyo\Custom\\'.YoyoHelpers::studly($className);
        }
    }
}
```