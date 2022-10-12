=== Pilo'Press ===
Contributors: pilotin
Donate link: https://www.pilot-in.com
Tags: acf, page builder, tailwindcss
Requires at least: 4.9
Tested up to: 6.0
Requires PHP: 5.6
Stable tag: 0.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The most advanced WordPress Page Builder using Advanced Custom Fields & TailwindCSS.

== Description ==

Pilo'Press is a framework plugin for WordPress. Based on ACF and ACF Extended, it allows you to create layouts among other things and use the Flexible Content field as a page builder.

Pilo'Press uses Tailwind CSS for style templating which can be setup and build directly from the back-office.
Please note that Tailwind CSS is not mandatory, you can choose to use it or not.

== Requirements ==

This plugin requires [Advanced Custom Fields PRO](https://www.advancedcustomfields.com/pro/) and [Advanced Custom Fields: Extended](https://wordpress.org/plugins/acf-extended/) plugins in order to work correctly.

== Getting started ==

1. Activate Advanced Custom Fields Pro plugin
2. Activate ACF Extended plugin
3. Activate Pilo'Press plugin
4. In your theme, create a `pilopress` folder
5. Within the `pilopress` folder, create `layouts` subfolder
6. Within the `pilopress` folder, create `assets` subfolder
7. In the `index.php` file, add the following code:

`
    <?php

    get_header();

        the_pip_content();

    get_footer();
`

== Tailwind CSS ==

In the administration, under `Pilo'Press > Styles`, when you click on "Update & Compile", TailwindCSS will be compiled remotely using [TailwindAPI](https://www.tailwindapi.com/). Minified CSS files are then created under `/pilopress/assets/styles.min.css` and `/pilopress/assets/styles-admin.min.css`.

You can manually enqueue those files in your theme for the front-end & the back-end, but we recommend to use automatic enqueue code above.

It is possible to manually retrieve the Tailwind PostCSS & JS fields of the administration if you want to build TailwindCSS locally. To do so, you can use the following code:

`
$tailwind_css = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );
$tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind' );
`

For more details, see [Tailwind CSS Documentation](https://tailwindcss.com/docs/installation/).

== Customizing style ==

To customize default Tailwind CSS styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.

For more details about customization, see [Github Page](https://pilot-in.github.io/PiloPress/docs/customizing-styles/).

== Add new layout ==

- In the admin menu `Pilo'Press > Layouts`, add a new layout
- Configure the layouts fields
- Create PHP, CSS and JS files in your theme layout folder `/your-theme/pilopress/layouts/your-layout`
- You have to name those files the same way you did in back-office settings

Note: only PHP template file is require.

== Templating ==

To display the content of your post, you have to use the following function:
`
// Pilo'Press content (doesn't need 'echo')
the_pip_content();

// Pilo'Press content (needs 'echo')
echo get_pip_content();
`

== Components ==

See [GitHub Page](https://pilot-in.github.io/PiloPress/docs/components/) for complete example.

== Hooks ==

Available hooks are list and describe in [GitHub Page](https://pilot-in.github.io/PiloPress/docs/hooks/)

== Installation ==

= Plugin Install =

1. Activate Advanced Custom Fields Pro plugin
2. Activate ACF Extended plugin
3. Activate Pilo'Press plugin

= Theme Install =

1. In your theme, create a `pilopress` folder
2. Within the `pilopress` folder, create `layouts` subfolder
3. Within the `pilopress` folder, create `assets` subfolder

== Screenshots ==

1. Flexible Content Layout UI

== Changelog ==

= 0.4.3 - 08/07/2022 =

* Added: Filter `pip/locked_content/fields` to allow user add fields to locked content layout
* Added: Filter `pip/locked_content/html` to allow user alter HTML of locked content layout
* Added: Filter `pip/builder/hide_on_screen` to allow user to enable / disable the_content
* Added: Filter `pip/tailwind/config/prefix` to allow prefixed TailwindCSS generated classes
* Improved: "Set locked content (Post)" is displayed on 404 error page but it shouldn't
* Improved: Change edit locked content icon in admin bar
* Fixed: Title missing conditional in `[pip_title]` shortcode for home page.
* Fixed: Locked content - "Last posts" layout-like seems to trigger "locked content" set in another post-type
* Fixed: Default content - notice on multisite
* Fixed: PIP_Pattern doesn't retrieve correct `pattern_post_id` in other than default language when logged out
* Fixed: Fix component field when not in pip_flexible

= 0.4.2 - 07/03/2022 =

* Fixed: Location match for "Components" location condition
* Fixed: Remove Categories and Collections columns in layout sync screen to avoid columns to break
* Fixed: WYSIWYG Dark mode detection
* Fixed: Layout field group thumbnail not taking the whole space
* Fixed: AlpineJS enqueue through module
* Fixed: Layouts actions on newly added layout
* Fixed: Console log leftover in preview
* Fixed: Fix duplicated keys in pip_layout_var
* Fixed: deprecated acf.add_action to acf.addAction
* Improved: Add message when layout thumbnail already in folder
* Improved: Use `WP_Filesystem` instead of `file_get_contents()` or `file_put_contents()`
* Improved: Update TailwindCSS Native colors values according to TailwindCSS v2
* Improved: Add style around layout in admin when an error occurs to facilitate error location
* Improved: Rank Math compatibility for Breadcrumb + possibility to set a custom breadcrumb
* Improved: Export/import layouts style + wording
* Improved: Adding custom actions in Site Template aswell
* Added: Layout slug column in layout listing
* Added: Add option to show only button, font family and style checked as "Available in editor"
* Added: Preview for typography, colors, buttons and fonts in Pilo'Press > Styles
* Added: Import and Export tools for layouts
* Added: Search bar inside layouts selection modal
* Added: Edit layout, Move layout up and Move layout down actions
* Added: `pip/flexible/layouts/icons` filter
* Added: `pip/flexible/layouts/icons/hide` filter
* Added: `pip/shortcode/breadcrumb` filter
* Added: New module “**Patterns**” *(with Default Content & Locked Content per post-type / taxonomy)*

= 0.4.1.1 - 01/10/2021 =

* Improved: Change tailwindapi.com to api.pilopress.com
* Improved: Change way to get terms, improved performances in admin
* Improved: Change custom styles, colors and fonts wrapper argument to false, to allow user to apply different styles in
  same paragraph
* Added: Add `pip/shortcode/button_group/class` filter

= 0.4.1 - 02/08/2021 =
* Fixed: Pilo'Press navbar now displayed on new layout page
* Fixed: import for tailwind components position in code
* Fixed: PHP error on fresh install
* Fixed: PHP notice from wp_localize_script()
* Fixed: Hide actions on sync layouts page
* Fixed: Remove collection's badge from layout title when doing AJAX
* Fixed: Conditions to show layouts and Pilo'Press" flexible on options pages
* Fixed: Allow 3rd party buttons in TinyMCE toolbars
* Fixed: Infinite loop in specific cases for Components loop
* Improved: change button's "classes to apply" field from text to textarea
* Improved: Add "disabled" status in button states choices
* Improved: Allow HTML in button shortcode text
* Improved: Allow button shortcode to be a download link + add downloaded file name option
* Improved: Administration of fonts in Styles
* Improved: Pilo'Press' styles files version (from Pilo'Press version to `filemtime()`)
* Improved: Save all styles options when saving one page
* Improved: Thumbnail preview when selected size is Full
* Improved: Allow custom styles in TinyMCE to be applied on part of text instead of whole paragraph
* Added: Add option for a FontAwesome icon in `pip_button` shortcode
* Added: Add `pip/shortcode/button/icon_margin` filter
* Added: Add `pip/custom_font/url` filter to allow URL modification for custom fonts
* Added: AlpineJS module in administration panel. You can now enqueue easily AlpineJS with a toggle and specify its version (default to 3.0.6)
* Added: Add `pip/alpinejs` and `pip/alpinejs/version` filters
* Added: Add a notice in administration if Pilo'Press is active but no `pilopress` folder is found in active theme
* Added: Add spacings in Configuration tab
* Added: Add option to WYSIWYG fields to enable Dark mode by default
* Added: Fallback fonts option
* Added: CSS Vars for colors, screens, container paddings and fonts

= 0.4.0 - 04/02/2021 =
* Fixed: `pip_get_formatted_post_id()` function used in `get_pip_content()` and return nothing if used on custom post
  type archive when no posts
* Fixed: Render when multiple components on the same post
* Fixed: Sync link on layouts listing page
* Fixed: Use `pip/builder/parameters` filter for Header flexible and Footer flexible
* Fixed: Location match when location is set to Components > All
* Fixed: Redirection to ACF field groups when you duplicate a layout
* Fixed: Compatibility issue with ACF: Font Awesome plugin
* Added: Variables system to layouts
* Added: Allow user to compile locally, without TailwindAPI
* Added: `pip/tailwind_api` filter
* Added: `pip/tailwind/config_file_name` filter
* Added: `pip/tailwind/styles_file_name` filter
* Added: `pip/tailwind/css/after_base` filter
* Added: `pip/tailwind/css/after_fonts` filter
* Added: `pip/tailwind/css/after_components` filter
* Added: `pip/tailwind/css/after_utilities` filter
* Added: `pip/formatted_post_id` filter
* Added: `pip/tailwind_api` filter
* Added: `pip/tailwind/config_file_name` filter
* Added: `pip/tailwind/styles_file_name` filter
* Added: Filter choices in `pip_font_color` fields
* Added: Type of class returned by `pip_font_color` fields (text, background or border)
* Added: A lot of helpers to get data from back-office
* Added: TailwindCSS error message when compilation failed
* Added: Automatically create configuration file on layout insert or update
* Added: New "component like" location rule
* Improved: Make style back-office feature oriented
* Improved: Code quality
* Improved: Theme colors field type. Add return format choice and default value option
* Improved: Categories and collections filters on layouts listing page
* Improved: "pip_title" shortcode

= 0.3.2.11 - 02/11/2020 =
* Fixed: Reset h2 style inside TinyMCE
* Improved: When TailwindCSS compilation via TailwindAPI crashes, show an error message and don't replace CSS file content
* Improved: Allow main flexible in nav menu location
* Improved: Change version of generated CSS files to filemtime( FILE )

= 0.3.2.10 - 10/09/2020 =
* Fixed: Configuration file placeholder misleading
* Fixed: Component loop
* Fixed: PHP 7.4.9 warnings
* Fixed: Error if no layout slug
* Fixed: Top admin bar on plugin activation
* Fixed: Field groups colors in "Layouts" listing
* Fixed: Field groups in sync listing
* Improved: Add auto layout thumbnails for Site Template
* Added: Filter `pip/builder/parameters`

= 0.3.2.9 - 07/09/2020 =
* Fixed: Remove thumbnail and collection badge from layout title
* Fixed: Image inside layout folder used as thumbnail
* Improved: Add top nav menu as ACF did
* Improved: Add see more for components listing in Pilo'Press dashboard

= 0.3.2.8 - 22/07/2020 =
* Fixed: Collection badge style with automatic thumbnail

= 0.3.2.7 - 22/07/2020 =
* Added: `pip/layouts/always_show_collection` filter
* Added: Layout configuration file option
* Added: Allow png, jpeg, jpg file inside layout folder to be used as thumbnail. The file needs to be named as the layout slug.
* Added: Create layout folder and PHP file on layout creation
* Improved: Rename helpers functions
* Improved: Reset TinyMCE styles in a cleaner way
* Improved: Register conditions for Pilo'Press field types

= 0.3.2.6 - 16/07/2020 =
* Added: Create `pilopress/assets` and `pilopress/layouts` folders on plugin activation
* Improved: ACFE 0.8.6.7 Compatibility
* Improved: Group Pilo'Press field types under "Pilo'Press" category
* Improved: Use layout slug to autocomplete file names
* Improved: Use layout slug for location check, allow multiple layouts to have the same name
* Improved: Show collection badge only on layouts with the same name instead of on all layouts
* Fixed: Filters in layout modal when collections are used
* Fixed: Remove Collection meta box on field group pages
* Fixed: Translations

= 0.3.2.5 - 17/06/2020 =
* Improved: Collections name tag
* Improved: Layouts Configuration Modal setting now also display Local Field Groups
* Fixed: WYSIWYG Dark Mode not working in some specific cases
* Fixed: Readme URL

= 0.3.2.4 - 10/06/2020 =
* Improved: Collections name in layouts label
* Improved: Builder name is now displayed in the Builder Modal
* Fixed: Flexible Content PHP notice
* Fixed: Builder Mirror being displayed on pages

= 0.3.2.3 - 10/06/2020 =
* Added: `pip_maybe_get()` helper function
* Fixed: Fix WYSIWYG dark mode

= 0.3.2.2 - 09/06/2020 =
* Fixed: Fix WYSIWYG dark mode values and detection

= 0.3.2.1 - 08/06/2020 =
* Fixed: Fix WYSIWYG dark mode being required in specific case

= 0.3.2 - 08/06/2020 =
* Added: Dark mode for TinyMCE Editors
* Added: PHP Sync for layouts
* Added: Collection taxonomy for layouts, displayed before layout title. Example: "Collection: Layout title"
* Improved: `get_pip_header()` and `get_pip_footer()` are include in `the_pip_content()`
* Improved: Styles from Pilo'Press automatically enqueued
* Improved: Add layouts categories and collection in JSON and PHP files
* Improved: Hide category and collection columns if no term exist in layouts admin page

= 0.3.1 - 29/05/2020 =
* Improved: Translations
* Fixed: Save of builder field group

= 0.3 - 20/05/2020 =
* Improved: General Dashboard
* Fixed: Layouts Json Sync when the folder doesn't exists
* Removed: TailwindCSS PostCSS & JS file generation have been removed

= 0.2 - 19/05/2020 =
* Fixed: Layout path prefix field to correctly check theme path
* Fixed: Google Fonts are now enqueued using `wp_enqueue_style()`
* Fixed: TaildwindAPI now use native `wp_remote_post()` function instead of CURL

= 0.1 - 14/05/2020 =
* Initial commit
