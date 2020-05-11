<?php
/**
 * @var $menu_items
 */
?>
<div class="pip-admin-navigation">
    <h2><img class="pip-tab-icon"
             src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCIgZmlsbD0iI2EwYTVhYSI+PHBhdGggZD0iTTEwIC4yQzQuNi4yLjMgNC42LjMgMTBzNC40IDkuOCA5LjcgOS44YzIuNiAwIDUuMS0xIDYuOS0yLjggMS44LTEuOCAyLjgtNC4zIDIuOC02LjkgMC01LjUtNC4zLTkuOS05LjctOS45em02LjQgMTYuM2MtMS43IDEuNy00IDIuNi02LjQgMi42LTUgMC05LTQuMS05LTkuMVM1IC45IDEwIC45IDE5IDUgMTkgMTBjMCAyLjUtLjkgNC43LTIuNiA2LjV6Ii8+PHBhdGggZD0iTTEwIDUuM2MtMi41IDAtNC42IDIuMS00LjYgNC43di41Yy4yIDEuOCAxLjQgMy4zIDMgMy45LjUuMiAxIC4zIDEuNS4zLjQgMCAuOS0uMSAxLjMtLjIuMSAwIC4xIDAgLjItLjEuMy0uMS41LS4yLjgtLjMgMCAwIC4xIDAgLjEtLjEgMCAwIC4xIDAgLjEtLjFoLjFzLjEgMCAuMS0uMWMwIDAgLjEgMCAuMS0uMS4yLS4yLjUtLjQuNy0uNmwuMy0uM2MuNi0uOCAxLTEuOSAxLTIuOSAwLTIuNS0yLjEtNC42LTQuNy00LjZ6bTMuMSA3LjNjMC0uMSAwLS4xIDAgMC0uNi0uNC0uNy0uOS0uNy0xLjR2LS40LS4xLS4zYzAtLjctLjItMS41LTEuNS0xLjYtLjUgMC0xLjMuMS0yLjMuNC0uMi0uMS0uNCAwLS42LjEtLjYuMi0xLjIuNC0yIC43IDAtMi4yIDEuOC00IDMuOS00IDEuNSAwIDIuOC44IDMuNSAyLjEuNC42LjYgMS4yLjYgMS45IDAgLjktLjMgMS44LS45IDIuNnoiLz48L3N2Zz4="
             alt="logo"> Pilo'Press</h2>
    <?php foreach ( $menu_items as $key => $menu_item ) : ?>
        <?php
        // Default tab class
        $item_class = 'pip-tab';

        // Get current page/post ID
        $post_id = acf_maybe_get_GET( 'post' );
        $page_id = acf_maybe_get_GET( 'page' );

        // Layouts category slug
        $layouts_cat = PIP_Layouts_Categories::$taxonomy;

        // Add "is-active" class
        if ( acf_get_current_url() === $menu_item['link']
             || ( strstr( $menu_item['link'], 'layouts=1' ) && PIP_Layouts::is_layout( $post_id ) )
             || ( strstr( $menu_item['link'], 'taxonomy=' . $layouts_cat ) && acf_maybe_get_GET( 'taxonomy' ) === $layouts_cat )
             || ( strstr( $menu_item['link'], 'post_type=' . PIP_Components::$post_type ) && PIP_Components::is_component( $post_id ) )
             || ( strstr( $menu_item['link'], 'page=pip-styles-' ) && PIP_Admin_Options_Page::is_style_page( $page_id ) ) ) {
            $item_class .= ' is-active';
        }

        // Get title
        $item_title = $key === 0 ? __( 'Dashboard', 'pilopress' ) : $menu_item['title'];
        ?>
        <a class="<?php echo $item_class ?>" href="<?php echo $menu_item['link'] ?>"><?php echo $item_title ?></a>
    <?php endforeach; ?>
</div>
