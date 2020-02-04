<?php
/**
 * @var array $layouts
 * @var string $add_new_link
 */
?>
<table class="wp-list-table widefat fixed striped" style="border: 0">
    <thead>
    <tr>
        <th scope="col">Layout</th>
        <th scope="col">Locations</th>
    </tr>
    </thead>
    <tbody>
	<?php foreach ( $layouts as $layout ) : ?>
        <tr>
            <td class="title column-title has-row-actions">
                <strong>
                    <a class="row-title" href="<?php echo $layout['edit_link'] ?>" aria-label="<?php echo $layout['title'] . ' (' . __( 'Modifier', 'pilopress' ) . ')' ?>">
						<?php echo $layout['title'] ?>
                    </a>
                </strong>
                <div class="row-actions">
                    <span class="edit">
                        <a href="<?php echo $layout['edit_link']; ?>" aria-label="<?php echo __( 'Modifier', 'pilopress' ) . ' ' . $layout['title'] ?>">
                            <?php _e( 'Modifier', 'pilopress' ); ?>
                        </a>
                    </span>
                </div>
            </td>
            <td><?php echo $layout['locations']; ?></td>
        </tr>
	<?php endforeach; ?>
    <tr>
        <td colspan="2">
            <a class="button-secondary" href="<?php echo $add_new_link; ?>" target="_blank">
				<?php _e( 'Ajouter un layout', 'pilopress' ) ?>
            </a>
        </td>
    </tr>
    </tbody>
    <tfoot>
    <tr>
        <th scope="col">Layout</th>
        <th scope="col">Locations</th>
    </tr>
    </tfoot>
</table>