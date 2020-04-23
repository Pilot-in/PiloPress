---
layout: default
title: Filter pip/builder/locations
parent: Hooks
nav_order: 1
---

## Filter `pip/builder/locations`

This filter allows you to manage where Pilo'Press builder is visible.

_Default value_
```php
add_filter( 'pip/builder/locations', function () {
    return array(
        array(
            array(
                'param'    => 'post_type',
                'operator' => '==',
                'value'    => 'all',
            ),
        ),
        array(
            array(
                'param'    => 'taxonomy',
                'operator' => '==',
                'value'    => 'all',
            ),
        ),
    );
} );
```
