---
layout: default
title: Filter pip/footer/remove
parent: Hooks
nav_order: 4
---

## Filter `pip/footer/remove`

This filter allows you to remove auto add of Pilo'Press footer which display footer section of site template.  
 
_Default value_

```php
add_filter( 'pip/footer/remove', function () {
    return false;
} );
```
