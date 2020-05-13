# Pilo'Press

Pilo'Press is a framework plugin for WordPress. Based on popular plugins ACF and ACF Extended, it allows you to create layouts among other things and use the Flexible Content field as a page builder.  
Pilo'Press uses Tailwind CSS for style templating. You can customize the configuration directly from back-office.  
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
- Within the `pilopress` folder, create a `layouts` subfolder and a `tailwind` subfolder.
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

## Tailwind CSS files

All files under the `assets` folder are generate automatically.  
When you will save `Pilo'Press > Styles > Tailwind` options in back-office, two files will be generated: 
- `pip-styles.css` file will take the content of the "Tailwind CSS" option.  
- `tailwing.config.js` file will take the content of the "Tailwind Configuration" option.

If you click on "Update & Compile" and compile remotely thanks to [TailwindAPI](https://www.tailwindapi.com/), `pip-styles.min.css` and `pip-styles-admin.min.css` files will be generated.

For more details, see [Tailwind CSS Documentation](https://tailwindcss.com/docs/installation/).

## Customizing style

To customize default Tailwind CSS styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.  
For more details about customization, see [Github Page](https://pilot-in.github.io/PiloPress/docs/customizing-styles/).

## Add new layout

- In the admin menu `Pilo'Press > Layouts`, add a new layout
- Configure the layouts fields
- Create PHP, CSS and JS files in your theme layout folder `/your-theme/pilopress/layouts/your-layout`
- You have to name those files the same way you did in back-office settings. 

**NB:** only PHP file is require.

## Templating

To display the content of your post, you have to use the following function:  
```php
// Pilo'Press content (doesn't need 'echo')
the_pip_content();

// Pilo'Press content (needs 'echo')
echo get_pip_content();
```

## Components

See [GitHub Page](https://pilot-in.github.io/PiloPress/docs/components/) for complete example.

## Hooks

Available hooks are list and describe in [GitHub Page](https://pilot-in.github.io/PiloPress/docs/hooks/)
