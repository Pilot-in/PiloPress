---
layout: default
title: Site template
nav_order: 9
---

# Site template

You can customize your header and your footer sections using layouts in `Pilo'Press > Site Template` menu.
First of all, you have to assign a layout to Header and/or Footer Site Template locations.  
Those sections are display by default inside `pip_content` functions.

You can deactivate auto-add and use the following functions in your template files:

```php
// Header Pattern
get_pip_header();

// Footer Pattern
get_pip_footer();
```

See [Hooks](/PiloPress/docs/hooks) for more details.
