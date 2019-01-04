<div class="content-box content-wpmf-dropbox">
    <?php
    if (isset($dropboxconfig['dropboxKey']) && $dropboxconfig['dropboxKey'] != ''
        && isset($dropboxconfig['dropboxSecret']) && $dropboxconfig['dropboxSecret'] != '') {
        if ($Dropbox->checkAuth()) {
            ?>
            <a class="button btndrive" href="#"
               onclick="window.open('<?php echo $url; ?>','foo','width=600,height=600');return false;">
                <img class="wpmficon_gg_drop" src="<?php echo WPMFAD_URL . 'assets/images/dropbox_icon_colored.png'; ?>"
                     alt=""/>
                <?php _e('Connect Dropbox', 'wpmfAddon') ?></a>

            <?php
        } else { ?>
            <a class="button btndrive"
               href="<?php echo admin_url('options-general.php?
               page=option-folder&task=wpmf&function=wpmf_dropboxlogout') ?>">
                <img class="wpmficon_gg_drop" src="<?php echo WPMFAD_URL . 'assets/images/dropbox_icon_colored.png'; ?>"
                     alt=""/>
                <?php _e('Disconnect Dropbox', 'wpmfAddon') ?></a>

            <?php
        }
    }
    ?>
    <table class="form-table" style="margin-left: 10px;">
        <tbody>
        <tr>
            <th scope="row"><label for="dropboxKey"><?php _e('App Key', 'wpmfAddon') ?></label></th>
            <td>
                <input name="dropboxKey" type="text" class="regular-text"
                       value="<?php echo $dropboxconfig['dropboxKey'] ?>">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="dropboxSecret"><?php _e('App Secret', 'wpmfAddon') ?></label></th>
            <td>
                <input name="dropboxSecret" type="text" class="regular-text"
                       value="<?php echo $dropboxconfig['dropboxSecret'] ?>">
            </td>
        </tr>
        <?php if ($Dropbox->checkAuth()) { ?>
            <tr>
                <th scope="row"><label for="dropboxAuthor"><?php _e('Authorization Code', 'wpmfAddon') ?></label></th>
                <td>
                    <input name="dropboxAuthor" type="text" value="" class="regular-text">
                </td>
            </tr>
        <?php } else { ?>
            <tr style="display: none;">
                <th scope="row"><label for="dropboxAuthor"><?php _e('Authorization Code', 'wpmfAddon') ?></label></th>
                <td>
                    <input name="dropboxAuthor" type="text" value="" class="regular-text">
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <a style="margin: 10px;" target="_blank" class="button"
       href="https://www.joomunited.com/documentation/wp-media-folder-addon-documentation">
        <?php _e('Read the online documentation', 'wpmfAddon') ?>
    </a>
</div>