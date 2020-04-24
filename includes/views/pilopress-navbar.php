<?php
/**
 * @var $menu_items
 */
?>
<div class="pilopress_navbar">
    <nav>
        <?php foreach ( $menu_items as $menu_item ) : ?>
            <?php
            $item_class = '';
            // Is current item ?
            if ( acf_get_current_url() === $menu_item['link'] ) {
                $item_class = 'active';
            }
            ?>
            <a href="<?php echo $menu_item['link'] ?>" class="<?php echo $item_class ?>">
                <?php echo $menu_item['title'] ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>
