---
layout: default
title: Theme installation
nav_order: 2
---

# Theme installation

- In your theme, create a `pilopress` folder
- Within the `pilopress` folder, create a `layouts` subfolder and a `assets` subfolder.
- In the `index.php` file, add the following code after `<?php` tag:  


```php
// WordPress Header
get_header(); 

// Pilo'Press: Header
get_pip_header();

if( have_posts() ):
    while( have_posts() ): the_post();
        
        // Pilo'Press: Content
        the_pip_content();
        
    endwhile;
endif;

// Pilo'Press: Footer
get_pip_footer();

// WordPress: Footer
get_footer();
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
