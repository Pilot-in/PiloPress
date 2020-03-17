<?php
/**
 * @var array $layouts
 * @var string $add_new_link
 */

// To avoid change of field group key
acf_hidden_input( array(
    'name'  => 'acf_field_group[key]',
    'value' => PIP_Flexible_Mirror::get_flexible_mirror_group_key(),
) );
// To avoid change of field group label_placement
acf_hidden_input( array(
    'name'  => 'acf_field_group[label_placement]',
    'value' => 'left',
) );
// To avoid change of field group style
acf_hidden_input( array(
    'name'  => 'acf_field_group[style]',
    'value' => 'seamless',
) );
?>
<table id="pilopress-flexible-layouts-table" class="wp-list-table widefat fixed striped">
    <thead>
    <tr>
        <th scope="col" id="acf-layouts-category" class="column-acf-layouts-category"><?php _e( 'Categories', 'pilopress' ) ?></th>
        <th scope="col" id="title" class="column-title column-primary"><?php _e( 'Layout', 'pilopress' ) ?></th>
        <th scope="col" id="acf-fg-count" class="column-acf-fg-count"><?php _e( 'Fields', 'pilopress' ) ?></th>
        <th scope="col" id="acfe-locations" class="column-acfe-locations"><?php _e( 'Locations', 'pilopress' ) ?></th>
        <th scope="col" id="acfe-local" class="column-acfe-local"><?php _e( 'Load', 'pilopress' ) ?></th>
        <th scope="col" id="acfe-autosync-php" class="column-acfe-autosync-php"><?php _e( 'PHP sync', 'pilopress' ) ?></th>
        <th scope="col" id="acfe-autosync-json" class="column-acfe-autosync-json"><?php _e( 'JSON sync', 'pilopress' ) ?></th>
    </tr>
    </thead>
    <tbody id="the-list">
    <?php if ( $layouts ): ?>
        <?php foreach ( $layouts as $layout ) : ?>
            <tr class="iedit author-self level-0 type-acf-field-group hentry">
                <td class="acf-layouts-category column-acf-layouts-category"><?php echo $layout['terms']; ?></td>
                <td class="title column-title has-row-actions column-primary page-title">
                    <strong>
                        <a class="row-title" href="<?php echo $layout['edit_link'] ?>" aria-label="<?php echo $layout['title'] . ' (' . __( 'Edit', 'pilopress' ) . ')' ?>">
                            <?php echo $layout['title'] ?>
                        </a>
                    </strong>
                    <div class="row-actions">
                    <span class="edit">
                        <a href="<?php echo $layout['edit_link']; ?>" aria-label="<?php echo __( 'Edit', 'pilopress' ) . ' ' . $layout['title'] ?>">
                            <?php _e( 'Edit', 'pilopress' ); ?>
                        </a>
                    </span>
                    </div>
                </td>
                <td class="acf-fg-count column-acf-fg-count"><?php echo $layout['fields'] ?></td>
                <td class="acfe-locations column-acfe-locations"><?php echo $layout['locations']; ?></td>
                <td class="acfe-local column-acfe-local"><?php echo $layout['load']; ?></td>
                <td class="acfe-autosync-php column-acfe-autosync-php"><?php echo $layout['php']; ?></td>
                <td class="acfe-autosync-json column-acfe-autosync-json"><?php echo $layout['json']; ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    <tr>
        <td colspan="7" style="text-align:center;">

            <div style="padding: 40px 20px;border: #ccc dashed 2px;text-align: center;">

                <a class="button-secondary" href="<?php echo $add_new_link; ?>">
                    <?php _e( 'Add layout', 'pilopress' ) ?>
                </a>

            </div>

        </td>
    </tr>
    </tbody>
</table>
