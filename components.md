---
layout: default
title: Components
nav_order: 9
---

# Components

Let's say we want to create a "Blue Button" Component.  
This component is going to display a button with dynamic text and link.  
To achieve that, we are going to follow those steps:

- Create a "Blue Button" component in `Pilo'Press > Components`.
- Create a "Buttons" field group in `Custom Fields > Field Groups` and assign it to the "Blue Button" component.  
For our example, we will need 2 fields: "Classes" and "Default text".
- Go back to the "Blue Button" component in `Pilo'Press > Components` and fill in the fields.
- Create a "Button" layout in `Pilo'Press > Layouts` with 4 fields: 
    - a component field (`button_type`)
    - an alignment field (`alignment`)
    - a text field (`text`)
    - a link field (`link`)
    
  We can restrict the component choices to the "Blue Button" component.  
  Assign that layout to Posts.
- Edit a post and add the "Button" layout.
- In the layout's PHP file, add the following code:
```markdown
<?php
// Get layout fields
$text      = get_sub_field( 'text' );
$link      = get_sub_field( 'link' );
$alignment = get_sub_field( 'alignment' );

// Component loop
while ( have_component( 'button_type' ) ): the_component(); ?>

    <?php
    // Get component fields
    $classes      = get_sub_field( 'classes' );
    $default_text = get_sub_field( 'default_text' );
    ?>

    <div class="<?php echo $alignment ?>">
        <a href="<?php echo $link['url'] ?>" class="<?php echo $classes ?>">
            <?php echo $text ? $text : $default_text ?>
        </a>
    </div>

<?php endwhile; // End component loop ?>
```

As you can see in the code, we have used the functions `have_component( 'your_field' )` and `the_component();`.  
Thanks to those functions, you can use ACF functions in the loop, in the exact same way of `have_rows()` and `the_row()`.
