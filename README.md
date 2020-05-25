# Pilo'Press

The most advanced WordPress Page Builder using Advanced Custom Fields & TailwindCSS.

Pilo'Press is a framework plugin for WordPress. Based on ACF and ACF Extended, it allows you to create layouts among other things and use the Flexible Content field as a page builder.

Pilo'Press uses Tailwind CSS for style templating which can be setup and build directly from the back-office.
Please note that Tailwind CSS is not mandatory, you can choose to use it or not. 

**All features are describe in details, in our [GitHub Page](https://pilot-in.github.io/PiloPress/).**

## Table of Contents

- [Requirements](#requirements)
- [Plugin installation](#plugin-installation)
- [Theme installation](#theme-installation)
- [Tailwind CSS files](#tailwind-css-files)
- [Customizing style](#customizing-style)
- [Add new layout](#add-new-layout)
- [Templating](#templating)
- [Components](#components)
- [Hooks](#hooks)

## Requirements

This plugin requires [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) and [Advanced Custom Fields: Extended](https://wordpress.org/plugins/acf-extended/) plugins in order to work correctly.

## Plugin installation

- Activate **Advanced Custom Fields Pro** plugin.
- Activate **ACF Extended** plugin.
- Activate **Pilo'Press** plugin.

## Theme installation

- In your theme, create a `pilopress` folder
- Within the `pilopress` folder, create `layouts` subfolder
- Within the `pilopress` folder, create `assets` subfolder
- In the `index.php` file, add the following code:
```php
<?php 

// WordPress Header
get_header(); 

// Pilo'Press: Header
get_pip_header();

?>

<?php if( have_posts() ): ?>
    <?php while( have_posts() ): the_post(); ?>
        
        <?php 
        
        // Pilo'Press: Content
        the_pip_content();
        
        ?>
    
    <?php endwhile; ?>
<?php endif; ?>
    
<?php 

// Pilo'Press: Footer
get_pip_footer();

// WordPress: Footer
get_footer();

?>
```

- In the `functions.php` file, add the following code:

```php
<?php

// Pilo'Press: Front-end
add_action( 'wp_enqueue_scripts', 'enqueue_pilopress_styles' );
function enqueue_pilopress_styles() {
    pip_enqueue();
}
 
// Pilo'Press: Back-end
add_action( 'admin_enqueue_scripts', 'admin_enqueue_pilopress_styles' );
function admin_enqueue_pilopress_styles() {
    pip_enqueue_admin();
}
```

## TailwindCSS

In the administration, under `Pilo'Press > Styles`, when you click on "Update & Compile", TailwindCSS will be compiled remotely using [TailwindAPI](https://www.tailwindapi.com/). Minified CSS files are then created under `/pilopress/assets/styles.min.css` and `/pilopress/assets/styles-admin.min.css`.

You can manually enqueue those files in your theme for the front-end & the back-end, but we recommend to use automatic enqueue code above.

It is possible to manually retrieve the Tailwind PostCSS & JS fields of the administration if you want to build TailwindCSS locally. To do so, you can use the following code:

```php
<?php

$tailwind_css = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );
$tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind' );
```

For more details, see [Tailwind CSS Documentation](https://tailwindcss.com/docs/installation/).

## Customizing TailwindCSS

To customize default Tailwind CSS styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.
 
For more details about customization, see [Github Page](https://pilot-in.github.io/PiloPress/docs/customizing-styles/).

## Add new layout

- In the admin menu `Pilo'Press > Layouts`, add a new layout
- Configure the layouts fields
- Create PHP, CSS and JS files in your theme layout folder `/your-theme/pilopress/layouts/your-layout`
- You have to name those files the same way you did in back-office settings

**Note:** only PHP template file is require.

## Templating

To display the content of your post, you have to use the following function:
```php
<?php

// Pilo'Press content (doesn't need 'echo')
the_pip_content();

// Pilo'Press content (needs 'echo')
echo get_pip_content();
```

## Components

See [GitHub Page](https://pilot-in.github.io/PiloPress/docs/components/) for complete example.

## Hooks

Available hooks are list and describe in [GitHub Page](https://pilot-in.github.io/PiloPress/docs/hooks/)

## Changelog

### 0.3 - 20/05/2020
* Improved: General Dashboard
* Fixed: Layouts Json Sync when the folder doesn't exists
* Removed: TailwindCSS PostCSS & JS file generation have been removed

### 0.2 - 19/05/2020
* Fixed: Layout path prefix field to correctly check theme path
* Fixed: Google Fonts are now enqueued using `wp_enqueue_style()`
* Fixed: TaildwindAPI now use native `wp_remote_post()` function instead of CURL

### 0.1 - 14/05/2020
* Initial commit
