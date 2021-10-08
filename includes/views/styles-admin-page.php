<?php
/**
 * Variables available in this template
 *
 * @var string $page_title
 * @var string $post_id
 * @var array  $pages
 * @var string $current_page
 * @var string $admin_url
 */

?>

<div class="wrap acf-settings-wrap">

    <h1><?php echo $page_title; ?></h1>

    <form id="post" method="post" name="post">

        <?php
        // render post data
        acf_form_data(
            array(
                'screen'  => 'options',
                'post_id' => $post_id,
            )
        );

        // Get modules
        $modules = pip_get_modules();

        // Check if Tailwind configuration is overridden
        $tailwind_config = get_field( 'pip_tailwind_config', 'pip_styles_tailwind_module' );
        $override_config = acf_maybe_get( $tailwind_config, 'override_config' );

        if ( $override_config && ( $current_page === 'pip-styles-configuration' || $current_page === 'pip-styles-fonts' ) ) : ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <b><?php _e( 'TailwindCSS configuration is overridden.', 'pilopress' ); ?></b>
                </p>
                <p>
                    <?php _e( '<code>Breakpoints</code> and <code>Container</code> tabs are useless.', 'pilopress' ); ?>
                    <?php _e( '<code>Colors</code> tab is useful for TinyMCE module only.', 'pilopress' ); ?>
                    <?php _e( "Font families won't be added automatically.", 'pilopress' ); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        // If Tailwind module is deactivate
        if ( !acf_maybe_get( $modules, 'tailwind' ) ) : ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <b><?php _e( 'TailwindCSS module is disabled.', 'pilopress' ); ?></b>
                    <br>
                    <?php _e( "Stylesheets won't be generated automatically.", 'pilopress' ); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        // If TinyMCE module is deactivate
        if ( !acf_maybe_get( $modules, 'tinymce' ) ) : ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <b><?php _e( 'TinyMCE module is disabled.', 'pilopress' ); ?></b>
                    <br>
                    <?php _e( "Typography, colors, buttons and fonts won't be available in editor.", 'pilopress' ); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php
        wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
        wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
        $compile_error   = get_transient( 'pip_tailwind_api_compile_error' );
        $compile_success = get_transient( 'pip_tailwind_api_compile_success' );
        ?>

        <?php // Error message ?>
        <?php if ( acf_maybe_get_GET( 'error_compile' ) && ( $compile_error ) ) : ?>
            <?php
            $error_array   = json_decode( $compile_error, false );
            $error_message = false;

            if ( isset( $error_array[0] ) ) {
                $error_message = $error_array[0];
            }
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e( 'An error occurred while compiling.', 'pilopress' ); ?></p>

                <?php if ( $error_message ) : ?>
                    <pre style="margin-bottom:10px;"><?php echo $error_message; ?></pre>
                <?php endif; ?>

            </div>
            <?php delete_transient( 'pip_tailwind_api_compile_error' ); ?>
        <?php endif; ?>

        <?php // Success message ?>
        <?php if ( !acf_maybe_get_GET( 'error_compile' ) && ( $compile_success ) ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo $compile_success; ?></p>
            </div>
            <?php delete_transient( 'pip_tailwind_api_compile_success' ); ?>
        <?php endif; ?>

        <div id="poststuff">
            <div
                id="post-body"
                class="metabox-holder columns-<?php echo 1 === get_current_screen()->get_columns() ? '1' : '2'; ?>">

                <div id="postbox-container-1" class="postbox-container">
                    <?php do_meta_boxes( 'acf_options_page', 'side', null ); ?>
                </div>

                <div id="postbox-container-2" class="postbox-container">

                    <div class="nav-tab-wrapper">
                        <?php foreach ( $pages as $key => $menu_page ) : ?>
                            <?php

                            // If TailwindCSS module is not enable, skip
                            if ( !acf_maybe_get( $modules, 'tailwind' ) && $key === 'tailwind-module' ) {
                                continue;
                            }
                            ?>
                            <a
                                href="<?php echo add_query_arg( array( 'page' => $menu_page['menu_slug'] ), admin_url( 'admin.php' ) ); ?>"
                                class="nav-tab <?php echo $current_page === $menu_page['menu_slug'] ? 'nav-tab-active' : ''; ?>">
                                <?php echo $menu_page['page_title']; ?>
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
