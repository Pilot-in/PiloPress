---
layout: default
title: Filter pip/options/capability
parent: Hooks
nav_order: 2
---

## Filter `pip/options/capability`

This filter allows you to manage the required capability to see Pilo'Press pages  
 
_Default value_

```php
add_filter( 'pip/options/capability', function () {
    return acf_get_setting( 'capability' );
} );
```
