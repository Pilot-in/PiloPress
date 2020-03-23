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

    <div id="dashboard-widgets" class="metabox-holder">

        <div id="postbox-container-1" class="postbox-container">
            <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                <div id="pilopress_configuration" class="postbox">
                    <div class="inside">
                        <p>
                            <strong>
                                <?php _e( 'Configuration status', 'pilopress' ) ?>
                            </strong>
                        </p>
                        <div class="main config-status">
                            <ul>
                                <?php foreach ( $configurations as $configuration ) : ?>
                                    <li>
                                        <?php echo $configuration['status'] ? $success_icon : $error_icon ?>
                                        <?php echo $configuration['label'] ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="postbox-container-2" class="postbox-container">
            <div id="side-sortables" class="meta-box-sortables ui-sortable">
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
    </div>

</div>
