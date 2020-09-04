---
layout: default
title: Filter pip/enqueue/remove
parent: Hooks
nav_order: 5
---

## Filter `pip/enqueue/remove`

This filter allows you to remove auto add of Pilo'Press style.  
 
_Default value_

```php
add_filter( 'pip/enqueue/remove', function () {
    return false;
} );
```
