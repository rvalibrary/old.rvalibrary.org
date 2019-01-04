<div class="content-box content-wpmf-onedrive">
    <?php
    $page = isset($_GET["page"]) ? '?page=' . $_GET["page"] : '';
    $location = get_admin_url(null, 'admin.php' . $page);

    $appInfo = $onedriveDrive->getClient();
    if (is_wp_error($appInfo)) {
        echo "<div id='message' class='error'><p>" . $appInfo->get_error_message() . "</p></div>";
        return false;
    }

    $authUrl = $onedriveDrive->startWebAuth();
    $btnconnect = '';
    if (!is_wp_error($authUrl)) {
        $btnconnect = '<a class="button-primary btndrive wpmf_onedrive_login" href="#"
         onclick="window.location.assign(\'' . $authUrl . '\',\'foo\',\'width=600,height=600\');return false;">';
        $btnconnect .= '<img class="wpmficon_onedrive"
         src="' . WPMFAD_URL . 'assets/images/onedrive_white.png" alt="" />';
        $btnconnect .= __('Connect OneDrive', 'wpmfAddon');
        $btnconnect .= '</a>';
    }

    $btndisconnect = '<a class="button btndrive wpmf_onedrive_logout" href="#" >';
    $btndisconnect .= '<img class="wpmficon_onedrive" src="' . WPMFAD_URL . 'assets/images/onedrive.png" alt="" />';
    $btndisconnect .= __('Disconnect OneDrive', 'wpmfAddon');
    $btndisconnect .= '</a>';


    $hasToken = $onedriveDrive->loadToken();
    if (!empty($_GET['error']) && $_GET['error'] === 'access_denied') {
        $onedriveDrive->revokeToken();
        $hasToken = new WP_Error('broke', __("The plugin isn't yet authorized to use your OneDrive!
         Please (re)-authorize the plugin", 'wpmfAddon'));
    }
    $onedrive_config = get_option('_wpmfAddon_onedrive_config');
    if (isset($onedrive_config['connected']) && $onedrive_config['connected'] == 1) {
        $client = $onedriveDrive->startClient();
        $driveInfo = $onedriveDrive->getDriveInfo();
        if ($driveInfo === false) {
            echo $btnconnect;
        } elseif (is_wp_error($driveInfo)) {
            echo $btnconnect;
        } else {
            echo $btndisconnect;
        }
    } else {
        echo $btnconnect;
    }

    ?>
    <table class="form-table" style="margin-left: 10px;">
        <tbody>
        <tr>
            <th scope="row"><label for="onedriveClientId"><?php _e('OneDrive Client ID', 'wpmfAddon') ?></label></th>
            <td>
                <input name="OneDriveClientId" type="text" class="regular-text"
                       value="<?php echo $onedriveconfig['OneDriveClientId'] ?>">
                <p class="description" id="tagline-description">
                    <?php _e('Insert your OneDrive Application Id here.
                     You can find this Id in the OneDrive dev center', 'wpmfAddon') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="onedriveClientSecret"><?php _e('OneDrive Client Secret', 'wpmfAddon') ?></label>
            </th>
            <td>
                <input name="OneDriveClientSecret" type="text" class="regular-text"
                       value="<?php echo $onedriveconfig['OneDriveClientSecret'] ?>">
                <p class="description" id="tagline-description">
                    <?php _e('Insert your OneDrive Secret here.
                     You can find this secret in the OneDrive dev center', 'wpmfAddon') ?>
                </p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="redirect_uris"><?php _e('Redirect URIs', 'wpmfAddon') ?></label></th>
            <td>
                <input name="redirect_uris" type="url" id="home" readonly value="<?php echo admin_url(); ?>"
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

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.wpmf_onedrive_logout').click(function () {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpmf_onedrive_logout'
                },
                success: function (response) {
                    location.reload(true)
                }
            });
        });
    });
</script>