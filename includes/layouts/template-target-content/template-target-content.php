<?php
global $is_preview;

// Show current post flexible content
if ( !$is_preview ) :
    echo apply_filters( 'pip/locked_content/html', '%%PIP_LOCKED_CONTENT%%' );
else : ?>
    <div style="position: relative; padding: 40px 0; text-align: center;">
        <div style="position: absolute; left: 0; right: 0; top: -2%; height: 25%; border: 2px solid #aaa;"></div>
        <div style="border: 2px solid #aaa; margin: 10px auto; padding: 20px;">
            <em style="color:#aaa; font-size: 16px"><?php _e( 'Layouts of target content', 'pilot-in' ); ?></em>
        </div>
        <div style="position: absolute; left: 0; right: 0; top: auto; bottom: -2%; height: 25%; border: 2px solid #aaa;"></div>
    </div>
<?php endif;
