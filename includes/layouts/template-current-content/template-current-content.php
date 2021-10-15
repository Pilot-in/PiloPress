<?php

// Show current post flexible content
if ( !is_admin() ) :
    echo '[pip_locked_content]';
else :
    ?>
    <div style="padding: 80px 20px;text-align: center;border: 2px dashed #aaa;">
        <em style="color:#aaa; font-size: 16px"><?php _e( 'Post content', 'pilot-in' ); ?></em>
    </div>
<?php endif; ?>
<?php
