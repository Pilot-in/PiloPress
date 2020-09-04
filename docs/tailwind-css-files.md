---
layout: default
title: Tailwind CSS files
nav_order: 4
---

# Tailwind CSS files

In the administration, under `Pilo'Press > Styles`, when you click on "Update & Compile", TailwindCSS will be compiled remotely using [TailwindAPI](https://www.tailwindapi.com/). Minified CSS files are then created under `/pilopress/assets/styles.min.css` and `/pilopress/assets/styles-admin.min.css`.

You can manually enqueue those files in your theme for the front-end & the back-end, see [Site template](/PiloPress/docs/site-template) for more details.

It is possible to manually retrieve the Tailwind PostCSS & JS fields of the administration if you want to build TailwindCSS locally. To do so, you can use the following code:

```php
<?php

$tailwind_css    = get_field( 'pip_tailwind_style', 'pip_styles_tailwind' );
$tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind' );
```

For more details, see [Tailwind CSS Documentation](https://tailwindcss.com/docs/installation/).
