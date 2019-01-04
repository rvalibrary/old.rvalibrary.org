<?php
if (!isset($checktype)) {
    $checktype = '';
}
?>
<div class="wpmfaddonfile entry file <?php echo $checktype ?>" data-id="<?php echo $id ?>"
     data-name="<?php echo $name ?>">
    <div class="entry_block">
        <div class="entry_edit">
            <i data-id="<?php echo $id ?>" class="wpmfaddonicon-edit zmdi zmdi-edit"></i>
            <i data-id="<?php echo $id ?>" class="wpmfaddonicon-delete zmdi zmdi-delete"></i>

            <a href="<?php echo $downloadlink ?>" title="<?php _e('Download file', 'wpmfAddon') ?>"><i
                    data-id="<?php echo $id ?>" class="wpmfaddonicon-download zmdi zmdi-cloud-download"></i></a>

            <?php if (!empty($display_preview)) : ?>
                <a class="wpmflinkfile <?php echo $type ?>" title="<?php echo $name ?>"
                   data-link="<?php echo $downloadlink ?>" data-id="<?php echo $id ?>"
                   href="<?php echo $lightboxlink ?>">
                    <i title="<?php _e('Preview file', 'wpmfAddon') ?>" class="wpmfaddonicon-preview zmdi zmdi-eye"></i>
                </a>
            <?php endif; ?>
        </div>
        <div class="entry_checkfile">
            <input class="wpmf_checkfile" type="checkbox" data-type="<?php echo $mimeType ?>" value="<?php echo $id ?>">
        </div>
        <a class="entry_link" title="<?php echo $name ?>" data-filename="<?php echo $name ?>">
            <div class="entry_thumbnail">
                <div class="entry_thumbnail-view-bottom">
                    <div class="entry_thumbnail-view-center">
                        <a href="#">
                            <img class="imgthumb imgloading" data-id="<?php echo $id ?>"
                                 src="<?php echo WPMFAD_PLUGIN_URL . '/assets/images/Loading_icon.gif' ?>">
                            <img class="imgthumb imgfile" data-id="<?php echo $id ?>" src="<?php echo $thumbnail ?>">
                        </a>
                    </div>
                </div>
            </div>
            <div class="entry_name">
                <div class="entry-name-view">
                    <span class="wpmf_filename" data-ext="<?php echo $infofile['extension'] ?>"
                          data-name="<?php echo $infofile['filename'] ?>"><?php echo $name ?></span>
                </div>
            </div>
        </a>
    </div>
</div>