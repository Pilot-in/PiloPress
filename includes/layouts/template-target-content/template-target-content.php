<?php
global $is_preview;

// Show current post flexible content
if ( !$is_preview ) :
    echo '%%PIP_LOCKED_CONTENT%%';
else :
    ?>
    <div style="padding: 80px 20px;text-align: center;border: 2px dashed #aaa;">
        <em style="color:#aaa; font-size: 16px"><?php _e( 'Target content (layouts)', 'pilot-in' ); ?></em>
    </div>
<?php endif; ?>
<?php
