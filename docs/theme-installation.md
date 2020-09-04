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
// Header
get_header(); 

// Pilo'Press: Content
the_pip_content();

// Footer
get_footer();
```
