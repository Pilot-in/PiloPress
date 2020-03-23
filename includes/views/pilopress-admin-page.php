<?php
/**
 * @var $success_icon
 * @var $error_icon
 * @var $configurations
 * @var $layouts
 */
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Pilo'Press</h1>

    <?php // Widgets area ?>
    <div id="dashboard-widgets" class="metabox-holder">

        <?php // Column 1 ?>
        <div id="postbox-container-1" class="postbox-container">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                <?php // Meta-boxes ?>
                <div id="pilopress_configuration" class="postbox">
                    <div class="inside">
                        <h4><strong><?php _e( 'Configuration status', 'pilopress' ) ?></strong></h4>
                        <div class="main config-status">
                            <ul>
                                <?php foreach ( $configurations as $configuration ) : ?>
                                    <li>
                                        <?php echo $configuration['status'] ? $success_icon : $error_icon ?>
                                        <?php echo $configuration['label'] ?>
                                        <?php if ( isset( $configuration['status_label'] ) ): ?>
                                            <?php echo $configuration['status_label'] ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php // Column 2 ?>
        <div id="postbox-container-2" class="postbox-container">
            <div id="side-sortables" class="meta-box-sortables ui-sortable">

                <?php // Meta-boxes ?>
                <div id="pilopress_layouts" class="postbox pilopress-layouts-table">
                    <table class="widefat">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th><strong><?php _e( 'Layout name', 'pilopress' ) ?></strong></th>
                            <th><strong><?php _e( 'Locations', 'pilopress' ) ?></strong></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $layouts as $key => $layout ) : ?>
                            <tr class="<?php echo $key % 2 ? 'alternate' : ''; ?>">
                                <td><?php echo $key + 1 ?></td>
                                <td>
                                    <a href="<?php echo $layout['edit_link'] ?>">
                                        <?php echo $layout['title'] ?>
                                    </a>
                                </td>
                                <td><?php echo $layout['location'] ?></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <?php // Column 3 ?>
        <div id="postbox-container-3" class="postbox-container">
            <div id="column3-sortables" class="meta-box-sortables ui-sortable">

                <?php // Meta-boxes ?>

            </div>
        </div>

        <?php // Column 4 ?>
        <div id="postbox-container-4" class="postbox-container">
            <div id="column4-sortables" class="meta-box-sortables ui-sortable">

                <?php // Meta-boxes ?>

            </div>
        </div>

    </div>

</div>
