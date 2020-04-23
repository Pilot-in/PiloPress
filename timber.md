---
layout: default
title: Timber compatibility
nav_order: 12
---

# Timber compatibility

:link: [Timber documentation](https://timber.github.io/docs/)

We will use the Timber [Starter Theme](https://github.com/timber/starter-theme) in this example. You will need [Timber plugin](https://fr.wordpress.org/plugins/timber-library/) to be activated.  
To make the starter theme Pilo'Press ready, you have to create a `pilopress` folder in your theme (as described in [Theme Structure](#theme-structure) part).  
You can enqueue Pilo'Press styles as described in [Instructions](#instructions) part.  
You have to add `'pilopress/layouts'` in the `Timber::$dirname` array in `functions.php` file.  
Finally, to display Pilo'Press content, you will have to add following code in your templates:
```html
// To display Header Pattern, in your base.twig or in header block
{{ function('get_pip_header') }}

// In your template files, like front-page.twig
{{ function('the_pip_content') }}

// To display Footer Pattern, in your footer.twig or in footer block
{{ function('get_pip_footer') }}
```

Regarding layouts files, you can use the PHP/Twig files duo perfectly.  

**Example**  
Let's say we have a layout named "Title" with a single ACF field (type text) named _title_.

- The PHP file will look like that ( after a `<?php` tag):  


```php
// Get Timber context
$context = Timber::context();

// If you need the post object, you will have to re-add it to the context
// $timber_post      = new Timber\Post();
// $context['post']  = $timber_post;

// Get the ACF field
$context['title'] = get_sub_field( 'title' );

// Render
Timber::render( 'title.twig', $context );
```


- The Twig file will look like that:  


```html
<h3>{{ title }}</h3>
```

So the theme structure will be almost the same, but with a `title.twig` file added:  
```
starter-theme/
└── pilopress/
    └── layouts/
        └── title/
            ├── title.js
            ├── title.php
            ├── title.twig
            ├── title.css
            └── group_123abcde.json
```
