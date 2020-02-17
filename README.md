# Pilo'Press for the win!

## Points à améliorer

- Icônes de localisation dans le menu Flexible : depuis ACFE
- Changement de menu parent pour l'édition des layouts : enlever le JS
- Utiliser les fonctions WP pour créer les fichiers des layouts : _PIP_Field_Groups_Layouts::create_layout_dir()_
- Regarder et enlever les _PILO_TODO_


## Available filters/actions

- Path to bootstrap in Pilo'Press plugin (for @import to work in scss files)  
`add_filter( 'pip/layouts/bootstrap_path', 'path/to/bootstrap/' );`

- Locations where main flexible is visible  
`add_filter( 'pip/flexible/locations', array() );`

- Text format in TinyMCE dropdown  
`add_filter( 'pip/editor/block_formats', 'Paragraphe=p;Titre 1=h1' );`

- Font sizes in TinyMCE dropdown  
`add_filter( 'pip/editor/fontsize_formats', '1rem 1.5rem 2rem' );`