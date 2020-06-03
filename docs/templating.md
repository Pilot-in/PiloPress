---
layout: default
title: Templating
nav_order: 7
---

# Templating

To display the content of your post, you have to use one of the following functions:  
```php
// Pilo'Press content (doesn't need 'echo')
the_pip_content();
```

or

```php
// Pilo'Press content (needs 'echo')
echo get_pip_content();
```

**NB:** Site template sections "header" and "footer" are displayed inside `pip_content` functions.
