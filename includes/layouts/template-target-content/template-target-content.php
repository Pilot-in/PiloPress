<?php
global $is_preview;

// Show current post flexible content
if ( !$is_preview ) :
    echo '%%PIP_LOCKED_CONTENT%%';
else : ?>
    <div style="position: relative; padding: 40px 20px; text-align: center;">
        <div style="position: absolute; left: 20px; right: 20px; top: -2%; height: 25%; border: 2px dashed #aaa;"></div>
        <div style="border: 2px dashed #aaa; margin: 10px auto; padding: 20px;">
            <em style="color:#aaa; font-size: 16px"><?php _e( 'Target content', 'pilot-in' ); ?></em>
        </div>
        <div style="position: absolute; left: 20px; right: 20px; top: auto; bottom: -2%; height: 25%; border: 2px dashed #aaa;"></div>
    </div>
<?php endif;
