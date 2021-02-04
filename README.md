# Pilo'Press

The most advanced WordPress Page Builder using Advanced Custom Fields & TailwindCSS.

Pilo'Press is a framework plugin for WordPress. Based on ACF and ACF Extended, it allows you to create layouts among other things and use the Flexible Content field as a page builder.

Pilo'Press uses Tailwind CSS for style templating which can be setup and build directly from the back-office.
Please note that Tailwind CSS is not mandatory, you can choose to use it or not. 

**All features are describe in details _(with screens & videos)_, in our [GitHub Page](https://pilot-in.github.io/PiloPress/).**

## Table of Contents

- [Requirements](#requirements)
- [Plugin installation](#plugin-installation)
- [Theme installation](#theme-installation)
- [Tailwind CSS files](#tailwindcss)
- [Customizing style](#customizing-tailwindcss)
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

// Header
get_header(); 

// Pilo'Press: Content
the_pip_content();

// Footer
get_footer();

```

## TailwindCSS

In the administration, under `Pilo'Press > Styles`, when you click on "Update & Compile", TailwindCSS will be compiled remotely using [TailwindAPI](https://www.tailwindapi.com/). Minified CSS files are then created under `/pilopress/assets/styles.min.css` and `/pilopress/assets/styles-admin.min.css`.

You can manually enqueue those files in your theme for the front-end & the back-end, but we recommend to use automatic enqueue code above.

It is possible to manually retrieve the Tailwind PostCSS & JS fields of the administration if you want to build TailwindCSS locally. To do so, you can use the following code:

```php
$tailwind_css    = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );
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

### 0.3.2.11 - 02/11/2020
* Fixed: Reset h2 style inside TinyMCE
* Improved: When TailwindCSS compilation via TailwindAPI crashes, show an error message and don't replace CSS file content
* Improved: Allow main flexible in nav menu location
* Improved: Change version of generated CSS files to filemtime( FILE )

### 0.3.2.10 - 10/09/2020
* Fixed: Configuration file placeholder misleading
* Fixed: Component loop
* Fixed: PHP 7.4.9 warnings
* Fixed: Error if no layout slug
* Fixed: Top admin bar on plugin activation
* Fixed: Field groups colors in "Layouts" listing
* Fixed: Field groups in sync listing
* Improved: Add auto layout thumbnails for Site Template
* Added: Filter `pip/builder/parameters`

### 0.3.2.9 - 07/09/2020
* Fixed: Remove thumbnail and collection badge from layout title
* Fixed: Image inside layout folder used as thumbnail
* Improved: Add top nav menu as ACF did
* Improved: Add see more for components listing in Pilo'Press dashboard

### 0.3.2.8 - 22/07/2020
* Fixed: Collection badge style with automatic thumbnail

### 0.3.2.7 - 22/07/2020
* Added: `pip/layouts/always_show_collection` filter
* Added: Layout configuration file option
* Added: Allow png, jpeg, jpg file inside layout folder to be used as thumbnail. The file needs to be named as the layout slug.
* Added: Create layout folder and PHP file on layout creation
* Improved: Rename helpers functions
* Improved: Reset TinyMCE styles in a cleaner way
* Improved: Register conditions for Pilo'Press field types

### 0.3.2.6 - 16/07/2020
* Added: Create `pilopress/assets` and `pilopress/layouts` folders on plugin activation
* Improved: ACFE 0.8.6.7 Compatibility
* Improved: Group Pilo'Press field types under "Pilo'Press" category
* Improved: Use layout slug to autocomplete file names
* Improved: Use layout slug for location check, allow multiple layouts to have the same name
* Improved: Show collection badge only on layouts with the same name instead of on all layouts
* Fixed: Filters in layout modal when collections are used
* Fixed: Remove Collection meta box on field group pages
* Fixed: Translations

### 0.3.2.5 - 17/06/2020
* Improved: Collections name tag
* Improved: Layouts Configuration Modal setting now also display Local Field Groups
* Fixed: WYSIWYG Dark Mode not working in some specific cases
* Fixed: Readme URL

### 0.3.2.4 - 10/06/2020
* Improved: Collections name in layouts label
* Improved: Builder name is now displayed in the Builder Modal
* Fixed: Flexible Content PHP notice
* Fixed: Builder Mirror being displayed on pages

### 0.3.2.3 - 10/06/2020
* Added: `pip_maybe_get()` helper function
* Fixed: Fix WYSIWYG dark mode

### 0.3.2.2 - 09/06/2020
* Fixed: Fix WYSIWYG dark mode values and detection
### 0.3.2.1 - 08/06/2020
 Fixed: Fix WYSIWYG dark mode being required in specific case

### 0.3.2 - 08/06/2020
* Added: Dark mode for TinyMCE Editors
* Added: PHP Sync for layouts
* Added: Collection taxonomy for layouts, displayed before layout title. Example: "Collection: Layout title"
* Improved: `get_pip_header()` and `get_pip_footer()` are include in `the_pip_content()`
* Improved: Styles from Pilo'Press automatically enqueued
* Improved: Add layouts categories and collection in JSON and PHP files
* Improved: Hide category and collection columns if no term exist in layouts admin page

### 0.3.1 - 29/05/2020
* Improved: Translations
* Fixed: Save of builder field group

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
