<div id="WpmfGoogleDrive">
    <div class="WpmfGoogleDrive files uyd-grid" data-list="files" data-layout="grid">
        <div class="list-container" style="width:100%;max-width:100%;">
            <div class="nav-header">
                <a class="nav-home">
                    <?php
                    switch ($mediatype) {
                        case 'google':
                            echo '<i class="zmdi zmdi-google-drive"></i>';
                            break;
                        case 'dropbox':
                            echo '<i class="zmdi zmdi-dropbox"></i>';
                            break;
                        case 'onedrive':
                            echo '<i class="zmdi zmdi-google-drive"></i>';
                            break;
                    }
                    ?>
                </a>
                <a class="nav-refresh" title="<?php _e('Delete selected files', 'wpmfAddon') ?>">
                    <i class="wpmfaddondel_selected zmdi zmdi-delete"></i>
                </a>

                <a class="nav-refresh" title="<?php _e('Select all', 'wpmfAddon') ?>">
                    <i class="wpmfaddoncheckall zmdi zmdi-check-all"></i>
                </a>

                <a class="nav-refresh" title="<?php _e('Refresh', 'wpmfAddon') ?>">
                    <i class="wpmfaddonrefresh zmdi zmdi-refresh"></i>
                </a>

                <a class="nav-refresh" title="<?php _e('Sort (Descending)', 'wpmfAddon') ?>">
                    <i data-sort="desc" class="wpmfaddonsort zmdi zmdi-sort-desc"></i>
                </a>

                <a class="nav-refresh" title="<?php _e('Sort (Ascending)', 'wpmfAddon') ?>">
                    <i data-sort="asc" class="wpmfaddonsort zmdi zmdi-sort-asc active"></i>
                </a>

                <div class="wpmfaddon-search-div">
                    <input type="search" size="40" placeholder="<?php echo __('Search for files', 'wpmfAddon'); ?>"
                           class="wpmfaddon-search-input"/>
                </div>


                <div class="nav-title"><?php _e('Media', 'wpmfAddon') ?></div>
            </div>

            <div class="loading loading_<?php echo $mediatype ?>" style="opacity: 1; display: none;">&nbsp;</div>
            <div class="ajax-filelist" style="">
                <?php
                switch ($mediatype) {
                    case 'google':
                        echo '<div class="wpmf_ggbreadcrumb"></div>';
                        break;
                    case 'dropbox':
                        echo '<div class="wpmf_dbxbreadcrumb"></div>';
                        break;
                    case 'onedrive':
                        echo '<div class="wpmf_odvbreadcrumb"></div>';
                        break;
                }
                ?>

                <div class="files layout-grid" style="opacity: 1;">

                </div>
                <?php if ($mediatype == 'google') { ?>
                    <div id="wrap-librarytree" class="white-popup mfp-hide">
                        <div id="librarytree" class="librarytree"></div>

                        <div class="process_ggimport_full">
                            <span class="process_ggimport process_btnimport" data-w="0">0%</span>
                        </div>
                        <div class="message_import"></div>
                        <button type="button"
                                class="btnggimport wpmfbutton-primary"><?php _e('Import', 'wpmfAddon') ?></button>
                    </div>
                <?php } elseif ($mediatype == 'dropbox') { ?>
                    <div id="wrap-librarytree" class="white-popup mfp-hide">
                        <div id="librarytree" class="librarytree"></div>

                        <div class="process_dbximport_full"><span class="process_dbximport process_btnimport"
                                                                  data-w="0">0%</span></div>
                        <div class="message_import"></div>
                        <button type="button"
                                class="btndbximport wpmfbutton-primary"><?php _e('Import', 'wpmfAddon') ?></button>
                    </div>
                <?php } else { ?>
                    <div id="wrap-librarytree" class="white-popup mfp-hide">
                        <div id="librarytree" class="librarytree"></div>

                        <div class="process_odvimport_full"><span class="process_odvimport process_btnimport"
                                                                  data-w="0">0%</span></div>
                        <div class="message_import"></div>
                        <button type="button"
                                class="btnodvimport wpmfbutton-primary"><?php _e('Import', 'wpmfAddon') ?></button>
                    </div>
                <?php } ?>


                <?php if ($mediatype == 'google') { ?>
                    <a href="#wrap-librarytree"
                       class="wpmf-open-popup-media wpmf-open-popup-ggmedia wpmfbutton-primary">
                        <?php _e('Import files in media library', 'wpmfAddon') ?></a>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-gginsert wpmfbutton-primary"
                            onclick="if (window.parent) wpmfGoogleDriveModule.insertFile();">
                        <?php _e('Insert files in content', 'wpmfAddon') ?></button>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-ggembed wpmfbutton-primary"
                            onclick="if (window.parent) wpmfGoogleDriveModule.embedPdf();"><?php _e('Embed PDF', 'wpmfAddon') ?></button>
                <?php } elseif ($mediatype == 'dropbox') { ?>
                    <a href="#wrap-librarytree"
                       class="wpmf-open-popup-media wpmf-open-popup-dbxmedia wpmfbutton-primary">
                        <?php _e('Import files in media library', 'wpmfAddon') ?></a>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-dbxinsert wpmfbutton-primary"
                            onclick="if (window.parent) wpmfDropboxModule.insertFile();">
                        <?php _e('Insert files in content', 'wpmfAddon') ?></button>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-dbxembed wpmfbutton-primary"
                            onclick="if (window.parent) wpmfDropboxModule.embedPdf();"><?php _e('Embed PDF', 'wpmfAddon') ?></button>
                <?php } else { ?>
                    <a href="#wrap-librarytree"
                       class="wpmf-open-popup-media wpmf-open-popup-odvmedia wpmfbutton-primary">
                        <?php _e('Import files in media library', 'wpmfAddon') ?></a>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-odvinsert wpmfbutton-primary"
                            onclick="if (window.parent) wpmfOneDriveModule.insertFile();">
                        <?php _e('Insert files in content', 'wpmfAddon') ?></button>
                    <button type="button" class="wpmfmedia-button-insert wpmfmedia-button-odvembed wpmfbutton-primary"
                            onclick="if (window.parent) wpmfOneDriveModule.embedPdf();"><?php _e('Embed PDF', 'wpmfAddon') ?></button>
                <?php } ?>
            </div>

        </div>
        <div class="fileupload-container" style="width:100%;max-width:100%">
            <div>
                <div class="fileuploadform">
                    <input type="hidden" name="acceptfiletypes" value=".(.)$">
                    <div class="fileupload-drag-drop">
                        <div>
                            <i class="material-icons icon_file_upload">file_upload</i>
                            <p><?php _e('Drag your files here ...', 'wpmfAddon') ?></p>
                        </div>
                    </div>

                    <div class="fileupload-list">
                        <div role="presentation">
                            <div class="files"></div>
                        </div>
                        <input type="hidden" name="fileupload-filelist" id="fileupload-filelist"
                               class="fileupload-filelist" value="">
                    </div>
                    <div class="fileupload-buttonbar">
                        <div class="fileupload-buttonbar-text">
                            <?php
                            switch ($mediatype) {
                                case 'google':
                                    echo '<span>' . __('Browse and upload files 
                                    to Google Drive', 'wpmfAddon') . '</span>';
                                    break;
                                case 'dropbox':
                                    echo '<span>' . __('Browse and upload files to Dropbox', 'wpmfAddon') . '</span>';
                                    break;
                                case 'onedrive':
                                    echo '<span>' . __('Browse and upload files to OneDrive', 'wpmfAddon') . '</span>';
                                    break;
                            }
                            ?>
                        </div>
                        <div class="upload-btn-container upload-btn upload-btn-primary button button-primary">
                            <?php
                            switch ($mediatype) {
                                case 'google':
                                    echo '<span>' . __('Upload files to Google Drive', 'wpmfAddon') . '</span>';
                                    break;
                                case 'dropbox':
                                    echo '<span>' . __('Upload files to Dropbox', 'wpmfAddon') . '</span>';
                                    break;
                                case 'onedrive':
                                    echo '<span>' . __('Upload files to OneDrive', 'wpmfAddon') . '</span>';
                                    break;
                            }
                            ?>
                            <input type="file" name="files[]" multiple="multiple" class="upload-input-button">
                        </div>
                    </div>
                </div>
            </div>
            <div class="template-row">
                <div class="upload-thumbnail">
                    <img class="" src="">
                </div>

                <div class="upload-file-info">
                    <div class="upload-status-container"><i class="upload-status-icon fa fa-circle"></i> <span
                            class="upload-status"></span></div>
                    <div class="file-size"></div>
                    <div class="file-name"></div>
                    <div class="upload-progress">
                        <div
                            class="progress progress-striped active ui-progressbar
                             ui-widget ui-widget-content ui-corner-all"
                            role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <div class="ui-progressbar-value ui-widget-header ui-corner-left"
                                 style="display: none; width: 0;"></div>
                        </div>
                    </div>
                    <div class="upload-error"></div>
                </div>
            </div>
            <div class="fileupload-info-container">
                <?php $upload_max_size = @ini_get('upload_max_filesize'); ?>
                <?php _e('Max file size:', 'wpmfAddon') ?>
                <span class="max-file-size">
                    <?php
                    if (!empty($upload_max_size)) {
                        echo $upload_max_size;
                    } else {
                        echo '2MB';
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
</div>