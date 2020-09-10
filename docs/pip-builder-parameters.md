---
layout: default
title: Filter pip/builder/parameters
parent: Hooks
nav_order: 7
---

## Filter `pip/builder/parameters`

This filter allows you to change ACFE builder parameters.  
 
_Default value_

```php
add_filter( 'pip/builder/parameters', function () {
    return array(
       'acfe_permissions'                  => '',
       'acfe_flexible_stylised_button'     => 1,
       'acfe_flexible_layouts_thumbnails'  => 1,
       'acfe_flexible_layouts_settings'    => 1,
       'acfe_flexible_layouts_ajax'        => 1,
       'acfe_flexible_layouts_templates'   => 1,
       'acfe_flexible_layouts_placeholder' => 0,
       'acfe_flexible_disable_ajax_title'  => 1,
       'acfe_flexible_close_button'        => 1,
       'acfe_flexible_title_edition'       => 1,
       'acfe_flexible_clone'               => 1,
       'acfe_flexible_copy_paste'          => 1,
       'acfe_flexible_modal_edition'       => 0,
       'acfe_flexible_layouts_state'       => '',
       'acfe_flexible_hide_empty_message'  => 1,
       'acfe_flexible_empty_message'       => '',
       'acfe_flexible_layouts_previews'    => 1,
       'acfe_flexible_modal'               => array(
           'acfe_flexible_modal_enabled'    => '1',
           'acfe_flexible_modal_col'        => '6',
           'acfe_flexible_modal_categories' => '1',
       ),
   );
} );
```
