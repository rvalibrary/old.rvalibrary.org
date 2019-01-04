<div id="robotsmessage" class="error">
    <p>
        <strong><?php echo $message; ?></strong>
        <a href="<?php echo $link_setting ?>" target="<?php echo ($open_new == true) ? "_blank" : "" ?>" class="button"
           style="background: #008ec2;border-color: #006799;color: #fff;"><?php _e('Configure now', 'wpmfAddon'); ?></a>
        <a href="<?php echo $link_document ?>" target="_blank"><?php _e('View documentation', 'wpmfAddon'); ?></a>
    </p>
</div>