jQuery(document).ready(function($) {

    var media_file_selector = function() {
        $('.mmm_image_selector').not('.loaded').each(function() {

            $(this).addClass('loaded');

            var image_selector = $(this);
            var image_src = image_selector.attr('data-src');
            var input_field = image_selector.attr('data-field');
            var input = $('input#' + input_field);
            var image = $('<img>').attr('src', image_src);

            if (image_src.length > 0) {
                image_selector.addClass('has_image');
            }

            var delete_icon = $('<span class="dashicons dashicons-trash"></span>').on('click', function() {
                image.attr('src', '');
                input.attr('value', '0');
                image_selector.removeClass('has_image')
            });

            var choose_icon = $('<span class="dashicons dashicons-edit"></span>').on('click', function(e) {
                e.preventDefault();

                mm_choose_icon_frame = wp.media.frames.file_frame = wp.media({
                    title: megamenu_pro.file_frame_title,
                    library: {type: 'image'}
                });

                // When an image is selected, run a callback.
                mm_choose_icon_frame.on('select', function() {

                    var selection = mm_choose_icon_frame.state().get('selection');

                    selection.map( function(attachment) {
                        attachment = attachment.toJSON();
                        attachment_id = attachment.id;

                        if(attachment.sizes){
                            if (attachment.sizes.thumbnail) {
                                attachment_url = attachment.sizes.thumbnail.url;
                            } else {
                                attachment_url = attachment.sizes.full.url;
                            }
                        } else {
                            attachment_url = attachment.url;
                        }
                    });

                    image.attr('src', attachment_url);
                    input.attr('value', attachment_id);
                    image_selector.addClass('has_image')
                });

                mm_choose_icon_frame.open();
            });

            image_selector.append(image).append(delete_icon).append(choose_icon);
        });
    }

    media_file_selector();

	/** Media File Selector **/
    $(document).on('toggle_block_content_loaded', function() {
        media_file_selector();
    });

    $(document).on('megamenu_content_loaded', function() {
        media_file_selector();
    });

    $(document).on('megamenu_content_loaded', function() {
        if ($('#codemirror').length) {

            var codeMirror = CodeMirror.fromTextArea(document.getElementById('codemirror'), {
                tabMode: 'indent',
                lineNumbers: true,
                lineWrapping: true
            });

            codeMirror.on("change", function(cm, change) {
                $('#codemirror').text(cm.getValue());
            });

        }
    });


    // Refresh CodeMirror
    $('.replacements').live('click', function() {
        $('.CodeMirror').each(function(i, el){
            el.CodeMirror.refresh();
        });
    });

    $('#mega_replacement_type').live('change', function() {
        $('.CodeMirror').each(function(i, el){
           setTimeout(function(){
              el.CodeMirror.refresh();
            }, 50);
        });
    });


    $('select#mega_replacement_mode').live("change", function() {
        var select = $(this);
        var selected = $(this).val();
        select.next().children().hide();
        select.next().children('.' + selected).show();
    });

    $('select#mega_replacement_type').live("change", function() {
        var select = $(this);
        var selected = $(this).val();
        $(".replacements table tr").not(".type").hide();
        $(".replacements table tr." + selected).show();
    });

    $('.mm_tab.replacements').live('click', function() {

        $(".mm_colorpicker").spectrum({
            preferredFormat: "rgb",
            showInput: true,
            showAlpha: true,
            clickoutFiresChange: true,
            change: function(color) {
                if (color.getAlpha() === 0) {
                    $(this).siblings('div.chosen-color').html('transparent');
                } else {
                    $(this).siblings('div.chosen-color').html(color.toRgbString());
                }
            }
        });

    });

	/** Roles **/

    $('#mm_roles select').live('change', function() {

        var option = $(this);

        if (option.val() == 'by_role') {
            $('#mm_roles input[type=checkbox]').removeAttr('disabled');
        } else {
            $('#mm_roles input[type=checkbox]').attr('disabled', 'disabled');
        }

    });

    /** Style Overrides **/

    $('.override_toggle_enabled').live('change', function() {
        var checkbox = $(this);
        var checked = checkbox.is(":checked");

        var inputs = checkbox.parent().siblings().find('input, select');

        inputs.each(function() {

            var name = $(this).attr('name');

            if (checked) {
                name = name.replace('disabled', 'enabled');
            } else {
                name = name.replace('enabled', 'disabled');
            }

            $(this).attr('name', name);

        });

        var parent = checkbox.parent().parent();

        parent.toggleClass('mega-enabled', 'mega-disabled');

    });

    $('.mm_tab.styling').live('click', function() {

        $(".mm_colorpicker").spectrum({
            preferredFormat: "rgb",
            showInput: true,
            showAlpha: true,
            clickoutFiresChange: true,
            change: function(color) {
                if (color.getAlpha() === 0) {
                    $(this).siblings('div.chosen-color').html('transparent');
                } else {
                    $(this).siblings('div.chosen-color').html(color.toRgbString());
                }
            }
        });

    });

    $('.megamenu_orientation_select').on('change', function() {

        var accordion_behaviour_row = $(this).parent().parent().parent().children('tr.megamenu_accordion_behaviour');

        if ( $(this).val() == 'accordion' ) {
            accordion_behaviour_row.show();
        } else {
            accordion_behaviour_row.hide();
        }

    });

    $('.megamenu_sticky_enabled').on('change', function() {

        var sticky_behaviour_rows = $(this).parent().parent().parent().children('tr.megamenu_sticky_behaviour');

        if ( $(this).is(":checked") ) {
            sticky_behaviour_rows.show();
        } else {
            sticky_behaviour_rows.hide();
        }

    });


});