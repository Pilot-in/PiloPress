---
layout: default
title: Filter pip/enqueue/admin/remove
parent: Hooks
nav_order: 6
---

## Filter `pip/enqueue/admin/remove`

This filter allows you to remove auto add of Pilo'Press admin style.  
 
_Default value_

```php
add_filter( 'pip/enqueue/admin/remove', function () {
    return false;
} );
```
