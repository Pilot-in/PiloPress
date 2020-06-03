---
layout: default
title: Filter pip/header/remove
parent: Hooks
nav_order: 3
---

## Filter `pip/header/remove`

This filter allows you to remove auto add of Pilo'Press header which display header section of site template.  
 
_Default value_

```php
add_filter( 'pip/header/remove', function () {
    return false;
} );
```
