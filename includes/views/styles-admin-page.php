<?php
/**
 * @var string $page_title
 * @var string $post_id
 * @var array $pages
 * @var string $current_page
 * @var string $admin_url
 */
?>

<div class="wrap acf-settings-wrap">

    <h1><?php echo $page_title; ?></h1>

    <form id="post" method="post" name="post">

        <?php
        // render post data
        acf_form_data( array(
            'screen'  => 'options',
            'post_id' => $post_id,
        ) );

        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes( 'acf_options_page', 'side', null ); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">

                    <div class="nav-tab-wrapper">
                        <?php foreach ( $pages as $page ) : ?>
                            <a href="<?php echo add_query_arg( array( 'page' => $page['menu_slug'] ), admin_url( 'admin.php' ) ) ?>"
                               class="nav-tab <?php echo $current_page === $page['menu_slug'] ? 'nav-tab-active' : ''; ?>">
                                <?php echo $page['page_title'] ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php do_meta_boxes( 'acf_options_page', 'normal', null ); ?>
                </div>

            </div>
            <br class="clear">
        </div>
    </form>

</div>
