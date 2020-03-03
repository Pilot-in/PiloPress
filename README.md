# Pilo'Press

## Points à améliorer

- Icônes de localisation dans le menu Flexible : depuis ACFE
- Changement de menu parent pour l'édition des layouts : enlever le JS
- Utiliser les fonctions WP pour créer les fichiers des layouts : _PIP_Field_Groups_Layouts::create_layout_dir()_
- Regarder et enlever les _PILO_TODO_


## Available filters/actions

- Path to bootstrap in Pilo'Press plugin (for @import to work in layout SCSS files)  
`add_filter( 'pip/layouts/bootstrap_path', 'path/to/bootstrap/' );`

- Locations where main flexible is visible  
`add_filter( 'pip/flexible/locations', array() );`