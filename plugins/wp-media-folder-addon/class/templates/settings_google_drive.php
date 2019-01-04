<div class="content-box content-wpmf-google-drive">
    <?php
    if (isset($googleconfig['googleClientId']) && $googleconfig['googleClientId'] != ''
        && isset($googleconfig['googleClientSecret']) && $googleconfig['googleClientSecret'] != '') {
        if (!$googleDrive->checkAuth()) {
            $url = $googleDrive->getAuthorisationUrl();
            ?>
            <a id="ggconnect" class="button button-primary btndrive" href="#"
               onclick="window.location.assign('<?php echo $url; ?>','foo','width=600,height=600');return false;">
                <img class="wpmficon_gg_drop" src="<?php echo WPMFAD_URL . 'assets/images/drive-icon-colored.png'; ?>"
                     alt=""/>
                <?php _e('Connect Google Drive', 'wpmfAddon') ?></a>

            <?php
        } else {
            $url_logout = admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_gglogout');
            ?>
            <a id="gg_disconnect" class="button btndrive"
               href="<?php echo $url_logout ?>">
                <img class="wpmficon_gg_drop" src="<?php echo WPMFAD_URL . 'assets/images/drive-icon-colored.png'; ?>"
                     alt=""/>
                <?php _e('Disconnect Google Drive', 'wpmfAddon') ?></a>

            <?php
        }
    }
    ?>
    <table class="form-table" style="margin-left: 10px;">
        <tbody>
        <tr>
            <th scope="row"><label for="googleClientId"><?php _e('Google Client ID', 'wpmfAddon') ?></label></th>
            <td>
                <input name="googleClientId" type="text" class="regular-text"
                       value="<?php echo $googleconfig['googleClientId'] ?>">
                <p class="description" id="tagline-description">
                    <?php _e('The Client ID for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="googleClientSecret"><?php _e('Google Client Secret', 'wpmfAddon') ?></label>
            </th>
            <td>
                <input name="googleClientSecret" type="text" class="regular-text"
                       value="<?php echo $googleconfig['googleClientSecret'] ?>">
                <p class="description" id="tagline-description">
                    <?php _e('The Client secret for Web application available in your google Developers Console.
                     Click on documentation link below for more info', 'wpmfAddon') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="javaScript_origins"><?php _e('JavaScript origins', 'wpmfAddon') ?></label></th>
            <td>
                <input name="javaScript_origins" type="url" id="siteurl" readonly value="<?php echo site_url(); ?>"
                       class="regular-text">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="redirect_uris"><?php _e('Redirect URIs', 'wpmfAddon') ?></label></th>
            <td>
                <input name="redirect_uris"
                       type="url" id="home" readonly
                       value="<?php echo admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_authenticated') ?>"
                       class="regular-text code">
            </td>
        </tr>

        </tbody>
    </table>

    <a style="margin: 10px;" target="_blank" class="button"
       href="https://www.joomunited.com/documentation/wp-media-folder-addon-documentation">
        <?php _e('Read the online documentation', 'wpmfAddon') ?>
    </a>
</div>