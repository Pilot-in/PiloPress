# Pilo'Press

___

### Points à améliorer

- Icônes de localisation dans le menu Flexible : depuis ACFE
- Changement de menu parent pour l'édition des layouts : enlever le JS
- Utiliser les fonctions WP pour créer les fichiers des layouts : _PIP_Layouts::create_layout_dir()_
- Regarder et enlever les _PILO_TODO_

___

## Install

- In your theme, create a `pilopress` directory with a `layouts` subdirectory as you can see in _Theme structure_ part.
- Activate **Advanced Custom Fields Pro** plugin.
- Activate **ACF Extended** plugin.
- Activate **Pilo'Press** plugin.

### Optional (but recommended)

- If you want to use **Pilo'Press'** styles, enqueue it in your theme like this :

```php
// For front-office
add_action( 'wp_enqueue_scripts', 'enqueue_pilopress_styles' );
function enqueue_pilopress_styles() {
    wp_enqueue_style( 'style-pilopress', get_stylesheet_directory_uri() . '/pilopress/style-pilopress.css', false );
}
 
// For back-office
add_action( 'admin_enqueue_scripts', 'admin_enqueue_pilopress_styles' );
function admin_enqueue_pilopress_styles() {
    wp_enqueue_style( 'style-pilopress-admin', get_stylesheet_directory_uri() . '/pilopress/style-pilopress-admin.css', false );
}
```

### Theme structure

Ideal structure :

```
your-theme/
└── pilopress/
    ├── layouts/
    |   ├── layout-1/
    |   |      ├── layout-1.js
    |   |      ├── layout-1.php
    |   |      ├── layout-1.scss
    |   |      ├── layout-1.css
    |   |      ├── layout-1.css.map
    |   |      └── group_123abcde.json
    |   └── layout-2/
    |          ├── layout-2.js
    |          ├── layout-2.php
    |          ├── layout-2.scss
    |          ├── layout-2.css
    |          ├── layout-2.css.map
    |          └── group_123abcde.json
    ├── style-pilopress.css
    ├── style-pilopress.css.map
    ├── style-pilopress-admin.css
    └── style-pilopress-admin.css.map
```

### Add new layout

- Add new layout in back-office in `Pilo'Press > Layouts`.
- Create layout subdirectory in `pilopress/layouts/` in your theme. You have to name the files the same way you did in back-office options.

### Sync layout

- Add new directory in `pilopress/layouts/your-layout/` with your layout files (PHP, JS, SCSS, CSS, JSON) in it.
- Go to `Pilo'Press > Layouts > Sync available` and sync your layout field group.

### Customizing style

To customize default bootstrap styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.  
When you will save, SCSS files for `style-pilopress*.css` files and layouts files will be compiled.  
To force compilation, you can use the top bar menu `Pilo'Press > Compile styles`.

### Templating

To display the content of your post, you have to use the following function : `the_pip_content()` or `echo get_pip_content()` .

### Styles settings Import/Export

Go to `Custom Fields > Tools`, you have two new tools to import and export your styles settings.

## Available hooks

- Path to bootstrap in Pilo'Press plugin, from layout directory (for @import to work in layouts SCSS files)  
`add_filter( 'pip/layouts/bootstrap_path', 'path/to/bootstrap/' );`  
_Default value_  
`'../../../../../..' . parse_url( PIP_URL . 'assets/libs/bootstrap/scss/', PHP_URL_PATH )`

- Locations where main flexible is visible  
`add_filter( 'pip/flexible/locations', array() );`  
_Default value_  
```php
array(
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
```

- Capability for Pilo'Press options pages  
`add_filter('pip/options/capability', 'your_capability');`  
_Default value_  
`acf_get_setting('capability')`
