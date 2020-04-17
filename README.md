# Pilo'Press

# Table of Contents

- [Requirements](#requirements)
- [Plugin installation](#plugin-installation)
- [Theme installation](#theme-installation)
    - [Instructions](#instructions)
    - [Theme structure](#theme-structure)
    - [Tailwind CSS files](#tailwind-css-files)
    - [Customizing style](#customizing-style)
        - [Add fonts](#add-fonts)
            - [\#1 - Custom class](#1---custom-class)
            - [\#2 - Tailwind configuration file](#2---tailwind-configuration-file)
        - [Customize image sizes](#customize-image-sizes)
        - [TinyMCE custom styles](#tinymce-custom-styles)
    - [Add new layout](#add-new-layout)
    - [Sync layout](#sync-layout)
    - [Templating](#templating)
    - [Pattern](#pattern)
    - [Components](#components)
    - [Styles settings Import/Export](#styles-settings-importexport)
- [Hooks](#hooks)
    - [Filter `pip/builder/locations`](#filter-pipbuilderlocations)
    - [Filter `pip/options/capability`](#filter-pipoptionscapability)
- [Timber compatibility](#timber-compatibility)

# Requirements

This plugin requires [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) and [Advanced Custom Fields: Extended](https://wordpress.org/plugins/acf-extended/) plugins in order to work correctly.

# Plugin installation

- Activate **Advanced Custom Fields Pro** plugin.
- Activate **ACF Extended** plugin.
- Activate **Pilo'Press** plugin.

# Theme installation

## Instructions
- In your theme, create a `pilopress` folder
- Within the `pilopress` folder, create a `layouts` subfolder and a `tailwind` subfolder as you can see in [Theme structure](#theme-structure) part.
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

## Theme structure
```text
your-theme/
└── pilopress/
    ├── layouts/
    |   ├── layout-1/
    |   |      ├── layout-1.js
    |   |      ├── layout-1.php
    |   |      ├── layout-1.css
    |   |      └── group_123abcde.json
    |   └── layout-2/
    |          ├── layout-2.js
    |          ├── layout-2.php
    |          ├── layout-2.css
    |          └── group_123abcde.json
    └── tailwind/
        ├── tailwind.config.js
        ├── tailwind.css
        ├── tailwind.min.css
        └── tailwind-admin.min.css
```

## Tailwind CSS files

All files under the `tailwind` folder are generated automatically.  
When you will save `Pilo'Press > Styles > Tailwind` options in back-office, two files will be generated: 
- `tailwind.css` file will take the content of the "Tailwind CSS" option.  
- `tailwing.config.js` file will take the content of the "Tailwind Configuration" option.

If you click on "Update & Compile" and compile remotely thank to [TailwindAPI](https://www.tailwindapi.com/), `tailwind.min.css` and `tailwind-admin.min.css` files will be generated.

For more details, see [Tailwind CSS Documentation](https://tailwindcss.com/docs/installation/).

## Customizing style

To customize default Tailwind styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.  
You can add fonts, customize image sizes and add custom styles for TinyMCE editor.

### Add fonts
First step, we will go to `Pilo'Press > Styles > Fonts` and add a font.  
You have 2 choices : Google Font or Custom font.  

<details>
<summary>**Example: Google Font**</summary>

Let's say we want to add Google's Roboto Font.  
We have to fill the fields as following:  
```text
Name:            Roboto
URL:             https://fonts.googleapis.com/css2?family=Roboto&display=swap
Auto-enqueue:    true
```
**NB:** The `Auto-enqueue` option will automatically add the `<link>` tag if set to `true`.
</details>

<details>
<summary>Example: Custom font</summary>

Let's say we want to add a font named _Homework_.  
_Be careful with your font formats, because of [browser compatibility](https://www.w3schools.com/css/css3_fonts.asp)._  

We have to fill the fields as following:  
```text
Name:      Homework
Files:     <Your files>
Weight:    normal         // Depends on your font
Style:     normal         // Depends on your font
```
When you will save, the `@font-face` code will be added automatically.  
</details>

Then, to use those fonts, we have 2 different ways.

#### #1 - Custom class
We can add a custom class in `Pilo'Press > Styles > Tailwind`, in CSS field.  
Something like that:
```css
.font-roboto {
    font-family: "Roboto", sans-serif;
}

.font-homework {
    font-family: "Homework", sans-serif;
}
```
After re-building styles, we will be able to use those classes everywhere.

#### #2 - Tailwind configuration file
As explain in [Tailwind Documentation](https://tailwindcss.com/docs/font-family/#font-families), you can define custom fonts and modify the default ones.  
Let's say we want to add our custom fonts without removing default ones, so we can write something like that:
```js
module.exports = {
    theme: {
        extend: {
            fontFamily: {
                roboto: ['Roboto', 'sans-serif'],
                homework: ['Homework', 'sans-serif'],
            },
        },
    },
};
```
Tailwind will generate the following classes: `font-roboto` and `font-homework`.

### Customize image sizes

You can customize default WordPress image sizes and add new ones in `Pilo'Press > Styles > Images`.

### TinyMCE custom styles

In `Pilo'Press > Styles > TinyMCE`, you will be able to add font style, font family, font color and buttons styles which will be available in TinyMCE Editor.

## Add new layout

- In the admin menu `Pilo'Press > Layouts`, add a new layout
- Configure the layouts fields
- Configure the layouts settings to match your theme `/your-theme/pilopress/layouts/` folder structure
- You have to name the files the same way you did in back-office settings

## Sync layout

- Add folder `pilopress/layouts/your-layout/` with your layout files in it (PHP, JS, CSS, JSON).
- Go to `Pilo'Press > Layouts > Sync available` and sync your layout field group.


## Templating

To display the content of your post, you have to use the following function:  
```php
// Pilo'Press content (doesn't need 'echo')
the_pip_content();

// Pilo'Press content (needs 'echo')
echo get_pip_content();
```

## Pattern

You can customize your header and your footer sections using layouts in `Pilo'Press > Pattern` menu.  
First of all, you have to assign a layout to Header Pattern and/or Footer Pattern.  
To display those sections, you have to use the following functions in your template files:
```php
// Header Pattern
get_pip_header();

// Footer Pattern
get_pip_footer();
```

See [Instructions](#instructions) part for example.

## Components

Let's say we want to create a "View more" Component. This component is going to display a given number of posts from a given post type.  
To achieve that, we are going to follow those steps:

- Create a "View more" component in `Pilo'Press > Components`.
- Create a "View more (Component fields)" field group in `Custom Fields > Field Groups` and assign it to the "View more" component.  
For our example, we will need 2 fields: "Posts per page" and "Post type".
- Go back to the "View more" component in `Pilo'Press > Components` and fill in the fields.
- Create a layout in `Pilo'Press > Layouts` with a component field.  
We can restrict the field choices to our component.  
Assign that layout to Posts.
- Edit a post and add the layout with the component field.
- In the layout's PHP file, add the following code:
```php
<?php
// Component loop
while ( have_component( 'view_more' ) ): the_component(); ?>

    <?php
    // Custom query to display posts
    $query = new WP_Query( array(
        'post_type'      => get_sub_field( 'post_type' ),
        'posts_per_page' => get_sub_field( 'posts_per_page' ),
    ) );
    ?>

    <div class="text-center">

        <p class="text-xl font-bold">View more</p>

        <?php while ( $query->have_posts() ): // The Loop ?>

            <?php $query->the_post(); ?>

            <a class="inline-block text-lg font-semibold py-2 px-3 m-3 border-2 rounded"
               href="<?php the_permalink(); ?>">
                <?php the_title() ?>
            </a>

        <?php endwhile; // End of the loop ?>

    </div>

    <?php wp_reset_query(); // Reset WP Query ?>

<?php endwhile; // End component loop ?>
```

As you can see in the code, we have used the functions `have_component( 'your_field' )` and `the_component();`.  
Thanks to those functions, you can use ACF functions in the loop, in the exact same way of `have_rows()` and `the_row()`.

## Styles settings Import/Export

Go to `Custom Fields > Tools`, you have two new tools to import and export your styles settings.

# Hooks

## Filter `pip/builder/locations`

This filter allows you to manage where Pilo'Press builder is visible.

_Default value_
```php
add_filter( 'pip/builder/locations', function () {
    return array(
        array(
            array(
                'param'    => 'post_type',
                'operator' => '==',
                'value'    => 'all',
            ),
        ),
        array(
            array(
                'param'    => 'taxonomy',
                'operator' => '==',
                'value'    => 'all',
            ),
        ),
    );
} );
```

## Filter `pip/options/capability`

This filter allows you to manage the required capability to see Pilo'Press pages  
 
_Default value_

```php
add_filter( 'pip/options/capability', function () {
    return acf_get_setting( 'capability' );
} );
```

# Timber compatibility

:link: [Timber documentation](https://timber.github.io/docs/)

We will use the Timber [Starter Theme](https://github.com/timber/starter-theme) in this example. You will need [Timber plugin](https://fr.wordpress.org/plugins/timber-library/) to be activated.  
To make the starter theme Pilo'Press ready, you have to create a `pilopress` folder in your theme (as described in [Theme Structure](#theme-structure) part).  
You can enqueue Pilo'Press styles as described in [Instructions](#instructions) part.  
You have to add `'pilopress/layouts'` in the `Timber::$dirname` array in `functions.php` file.  
Finally, to display Pilo'Press content, you will have to add following code in your templates:
```twig
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

- The PHP file will look like that:
```php
<?php

// Get Timber context
$context = Timber::context();

// If you need the post object, you will have to re-add it to the context
// $timber_post      = new Timber\Post();
// $context['post']  = $timber_post;

// Get the ACF field
$context['title'] = get_sub_field( 'title' );

// Render
Timber::render( 'title.twig', $context );
?>
```
- The Twig file will look like that:
```twig
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
