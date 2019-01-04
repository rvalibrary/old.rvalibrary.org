<div class="wpmfaddonfile entry folder" data-id="<?php echo $id ?>" data-name="<?php echo $name ?>">
    <div class="entry_block">
        <div class="entry_edit">
            <i data-id="<?php echo $id ?>" class="wpmfaddonicon-edit zmdi zmdi-edit"></i>
            <i data-id="<?php echo $id ?>" class="wpmfaddonicon-delete zmdi zmdi-delete"></i>
        </div>
        <div class="entry_thumbnail">
            <div class="entry_thumbnail-view-bottom">
                <div class="entry_thumbnail-view-center">
                    <a class="entry_link"><?php echo $thumbnail; ?></a>
                </div>
            </div>
        </div>
        <div class="entry_name">
            <a class="entry_link">
                <div class="entry-name-view">
                    <span class="wpmf_filename" data-ext="<?php echo $extension ?>"
                          data-name="<?php echo $infofile['filename'] ?>"><?php echo $name ?></span>
                </div>
            </a>
        </div>
    </div>
</div>