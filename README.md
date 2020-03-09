# Pilo'Press

___

### Points à améliorer

- Icônes de localisation dans le menu Flexible : depuis ACFE
- Changement de menu parent pour l'édition des layouts : enlever le JS
- Utiliser les fonctions WP pour créer les fichiers des layouts : _PIP_Layouts::create_layout_dir()_
- Regarder et enlever les _PILO_TODO_

___

## Install

- Activate **Advanced Custom Fields Pro** plugin.
- Activate **ACF Extended** plugin.
- Create a `pilopress` directory with a `layouts` subdirectory in your theme.
- Activate **Pilo'Press** plugin.
- If you want to use **Pilo'Press'** styles, enqueue it in your theme like this :

```
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

### Add new layout

- Add new layout in back-office in `Pilo'Press > Layouts`.
- Create layout subdirectory in `layouts` in your theme. You have to name the files the same way you did in back-office.

### Customizing style

To customize default bootstrap styles, go to `Pilo'Press > Styles` from left navigation menu or top bar menu.  
When you will save, SCSS files for `style-pilopress*.css` files and layouts files will be compiled.  
To force compilation, you can use the top bar menu `Pilo'Press > Compile styles`.

### Templating

To display the content of your post, you have to use the following function : `the_flexible( PIP_Flexible::get_flexible_field_name() );`.

## Available filters/actions

- Path to bootstrap in Pilo'Press plugin (for @import to work in layout SCSS files)  
`add_filter( 'pip/layouts/bootstrap_path', 'path/to/bootstrap/' );`

- Locations where main flexible is visible  
`add_filter( 'pip/flexible/locations', array() );`

- Capability for Pilo'Press options pages  
`add_filter('pip/options/capability', 'your_capability');`