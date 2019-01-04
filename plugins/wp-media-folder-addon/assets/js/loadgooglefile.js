var wpmfGoogleDriveModule;
var wpmfGoogleTreeModule;
(function ($) {
    wpmfGoogleDriveModule = {
        /**
         * root folder
         * @type {string}
         */
        googlecurrentfolderID: wpmfaddonparams.googleBaseFolder,
        /**
         * search string
         * @type {string}
         */
        searchfilename: '',
        /**
         * order
         * @type {string}
         */
        sortfilename: 'asc',
        /**
         * list files in current folder
         * @type {{}}
         */
        listFilesCurrentFolder: {},
        /**
         * current folder id
         * @type {number}
         */
        wpmfcurrentFolderId: 0,
        init: function () {
            /* Call ajax get google files */
            wpmfGoogleDriveModule.getDriveFileList();

            /* Check all file */
            $('.wpmfaddoncheckall').on('click', function () {
                if ($(this).hasClass('active')) {
                    $('.wpmf_checkfile').prop('checked', false);
                    $('.wpmfaddonfile.file').removeClass('selected');
                    $(this).removeClass('active');
                } else {
                    $('.wpmf_checkfile').prop('checked', true);
                    $('.wpmfaddonfile.file').addClass('selected');
                    $(this).addClass('active');
                }

                wpmfGoogleDriveModule.displayButton();
            });

            /* delete selected files */
            $('.wpmfaddondel_selected').on('click', function () {
                var filesselected = [];
                $('.wpmf_checkfile:checked').each(function (i, v) {
                    filesselected.push($(v).val());
                });
                var ids = filesselected.join();
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.deletefolder + "'><p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span>" + wpmfaddonparams.str_message_delete + "</p></div>");
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.delete] = function () {
                    var data = {
                        action: 'wpmf-google-deletefolder',
                        parentId: wpmfGoogleDriveModule.googlecurrentfolderID,
                        ids: ids
                    };
                    wpmfGoogleDriveModule.ajaxGoogle(data);
                    $(this).dialog("destroy");
                };

                l18nButtons[wpmfaddonparams.cancelfolder] = function () {
                    $(this).dialog("destroy");
                };

                dialog_html.dialog({
                    dialogClass: 'Wpmfdialogaddfolder', resizable: false,
                    height: 200,
                    width: 400,
                    modal: true,
                    buttons: l18nButtons,
                    create: function () {
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:first").removeClass('ui-state-default').addClass("button-primary");
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:last").removeClass('ui-state-default').addClass("button-secondary");
                    },
                    close: function () {

                    }
                });
            });

            /* Sort folders and files */
            $('.wpmfaddonsort').on('click', function () {
                wpmfGoogleDriveModule.sortfilename = $(this).data('sort');
                $('.wpmfaddonsort').removeClass('active');
                $(this).addClass('active');
                wpmfGoogleDriveModule.getDriveFileList();
            });

            /* Search folders and files */
            $('.wpmfaddon-search-input').on('change', function () {
                wpmfGoogleDriveModule.searchfilename = $(this).val();
                wpmfGoogleDriveModule.getDriveFileList();
            });

            /* Refesh the results */
            $('.wpmfaddonrefresh').on('click', function () {
                wpmfGoogleDriveModule.getDriveFileList();
            });

            /* popup folder tree */
            $('.wpmf-open-popup-ggmedia').magnificPopup({
                type: 'inline',
                midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
            });

            /* click import button */
            $('.btnggimport').on('click', function () {
                var $this = $(this);
                $this.hide();
                var filesselected = [];
                $('.wpmf_checkfile:checked').each(function (i, v) {
                    filesselected.push($(v).val());
                });
                var ids = filesselected.join();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpmf_ggimport_file',
                        ids: ids,
                        wpmfcurrentFolderId: wpmfGoogleDriveModule.wpmfcurrentFolderId
                    }, beforeSend: function () {
                        $('.process_ggimport_full').show();
                    },
                    complete: function () {

                    }, success: function (res) {
                        var w = $('.process_ggimport').data('w');
                        if (typeof res.status !== "undefined" && res.status === 'error time') {
                            if (typeof res.percent !== "undefined") {
                                var percent = parseFloat(w) + parseFloat(res.percent);
                                if (percent > 100)
                                    percent = 100;
                                $('.process_ggimport').data('w', percent);
                                $('.process_ggimport').css('width', percent + '%');
                                $('.process_ggimport').html(percent + '%');
                            }
                            $this.click();
                        } else {
                            $('.process_ggimport_full').hide();
                            $('.process_ggimport').css('width', '0%').html('0%').data('w', 0);
                            $('.message_import').fadeIn(500).fadeOut(1500).html(wpmfaddonparams.message_import);
                            setTimeout(function () {
                                $('.wpmf-open-popup-ggmedia').magnificPopup('close');
                            }, 2000);
                            $this.show();
                        }

                    },
                    dataType: 'json'
                });
            });

            /* when drag over to upload file */
            $(document).bind('dragover', function (e) {
                var dropZone = $('.WpmfGoogleDrive .fileuploadform').closest('.WpmfGoogleDrive'),
                    timeout = window.dropZoneTimeout;
                if (!timeout) {
                    dropZone.addClass('in');
                } else {
                    clearTimeout(timeout);
                }
                var found = false,
                    node = e.target;
                do {
                    if ($(node).is(dropZone)) {
                        found = true;
                        break;
                    }
                    node = node.parentNode;
                } while (node !== null);
                if (found) {
                    $(node).addClass('hover');
                } else {
                    dropZone.removeClass('hover');
                }
                window.dropZoneTimeout = setTimeout(function () {
                    window.dropZoneTimeout = null;
                    dropZone.removeClass('in hover');
                }, 100);
            });

            /* Upload files */
            $('.WpmfGoogleDrive .fileuploadform').each(function () {
                $(this).fileupload({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    autoUpload: true,
                    maxFileSize: wpmfaddonparams.maxsize,
                    acceptFileTypes: new RegExp($(this).find('input[name="acceptfiletypes"]').val(), "i"),
                    dropZone: $(this).closest('.WpmfGoogleDrive'),
                    messages: {
                        maxNumberOfFiles: wpmfaddonparams.maxNumberOfFiles,
                        acceptFileTypes: wpmfaddonparams.acceptFileTypes,
                        maxFileSize: wpmfaddonparams.maxFileSize,
                        minFileSize: wpmfaddonparams.minFileSize
                    },
                    limitConcurrentUploads: 3,
                    disableImageLoad: true,
                    disableImageResize: true,
                    disableImagePreview: true,
                    disableAudioPreview: true,
                    disableVideoPreview: true,
                    uploadTemplateId: null,
                    downloadTemplateId: null,
                    add: function (e, data) {
                        var listtoken = $(this).attr('data-token');

                        $.each(data.files, function (index, file) {
                            file.hash = file.name.hashCode() + '_' + Math.floor(Math.random() * 1000000);
                            file = wpmfGoogleDriveModule.validateFile(file);
                            var row = wpmfGoogleDriveModule.renderFileUploadRow(file);
                            if (file.error !== false) {
                                data.files.splice(index, 1);
                                row.find('.upload-status').removeClass().addClass('upload-status error').text(file.error);
                            }
                        });

                        if (data.files.length > 0) {
                            data.process().done(function () {
                                data.submit();
                            });
                        }

                    }
                }).on('fileuploadsubmit', function (e, data) {
                    var datatoken = $(this).attr('data-token');
                    $(".WpmfGoogleDrive .loading").addClass('upload');
                    $(".WpmfGoogleDrive .loading").fadeTo(400, 1);

                    $.each(data.files, function (index, file) {
                        wpmfGoogleDriveModule.uploadStart(file);
                    });

                    var filehash = '';
                    $.each(data.files, function (index, file) {
                        wpmfGoogleDriveModule.uploadStart(file);
                        filehash = file.hash;
                    });

                    $('.gform_button:submit').prop("disabled", false).fadeTo(400, 0.3);

                    data.formData = {
                        action: 'wpmf-upload-file',
                        type: 'do-upload',
                        hash: filehash,
                        parentID: wpmfGoogleDriveModule.googlecurrentfolderID,
                        _ajax_nonce: wpmfaddonparams.upload_nonce
                    };

                }).on('fileuploadprogress', function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10) / 2;
                    $.each(data.files, function (index, file) {
                        wpmfGoogleDriveModule.uploadProgress(file, {percentage: 100, progress: 'uploading_to_cloud'});
                    });

                }).on('fileuploadstopped', function () {
                }).on('fileuploaddone', function (e, data) {
                    wpmfGoogleDriveModule.uploadFinished(data.files[0]);
                }).on('fileuploadalways', function (e, data) {
                }).on('fileuploaddrop', function (e, data) {
                    var uploadcontainer = $(this);
                    $('html, body').animate({
                        scrollTop: uploadcontainer.offset().top
                    }, 1500);
                });
            });
        },
        /**
         * get google drive files
         */
        getDriveFileList: function () {
            $.ajax({
                method: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: 'wpmf-get-filelist',
                    googleBaseFolder: wpmfGoogleDriveModule.googlecurrentfolderID,
                    searchfilename: wpmfGoogleDriveModule.searchfilename,
                    sortfilename: wpmfGoogleDriveModule.sortfilename
                }, beforeSend: function () {
                    $(".WpmfGoogleDrive .loading").removeClass('initialize upload error');
                    $(".WpmfGoogleDrive .loading").fadeTo(400, 1);
                },
                complete: function () {
                    $(".WpmfGoogleDrive .loading").removeClass('search').hide();
                },
                success: function (response) {
                    /* Set list files in current folder */
                    wpmfGoogleDriveModule.listFilesCurrentFolder = response.files;
                    /* Set breadcrumb */
                    $('.wpmf_ggbreadcrumb').html(response.breadcrumb);
                    $('.wpmfaddoncheckall').removeClass('active');
                    $('#WpmfGoogleDrive .ajax-filelist .files').html(null);
                    $('#WpmfGoogleDrive .ajax-filelist .files').prepend(response.message);
                    /* Init event */
                    wpmfGoogleDriveModule.displayButton();
                    wpmfGoogleDriveModule.bindEvent();
                    wpmfGoogleDriveModule.loadPopup();
                    wpmfGoogleDriveModule.initFileDragDropable();
                }
            });
        },

        /**
         * Init event
         */
        bindEvent: function () {
            /* check/uncheck file */
            $('.wpmfaddonfile').on('click', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    $(this).find('.wpmf_checkfile').prop('checked', false);
                } else {
                    $(this).addClass('selected');
                    $(this).find('.wpmf_checkfile').prop('checked', true);
                }

                wpmfGoogleDriveModule.displayButton();
            });

            /* click breadcrumb */
            $('.wpmf_breadcrumb_folder').on('click', function () {
                wpmfGoogleDriveModule.googlecurrentfolderID = $(this).data('id');
                wpmfGoogleDriveModule.getDriveFileList();
            });

            /* click open folder */
            $('.entry.folder').not('.newfolder').on('click', function () {
                if (!$(this).hasClass('wpmfunclick')) {
                    wpmfGoogleDriveModule.googlecurrentfolderID = $(this).data('id');
                    wpmfGoogleDriveModule.getDriveFileList();
                }
            });

            /* click edit file */
            $('.wpmfaddonicon-edit').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var id = $(this).data('id');
                var name = $(this).closest('.wpmfaddonfile').find('.entry-name-view span.wpmf_filename').data('name');
                var ext = $(this).closest('.wpmfaddonfile').find('.entry-name-view span.wpmf_filename').data('ext');
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.editfolder + "'><p>" +
                    '<input type="text" name="editfolder" id="editfolder" value="' + name + '" class="text ui-widget-content ui-corner-all" style=" width: 90%; "/></p></div>');
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.save] = function () {
                    if ($('#editfolder').val() !== '') {
                        if (typeof ext !== "undefined" && ext !== '') {
                            var filename = encodeURIComponent($('#editfolder').val() + '.' + ext);
                        } else {
                            filename = encodeURIComponent($('#editfolder').val());
                        }
                        var data = {
                            action: 'wpmf-google-editfolder',
                            filename: filename,
                            id: id
                        };
                        wpmfGoogleDriveModule.ajaxGoogle(data);
                        $(this).dialog("destroy");
                    } else {
                        $('#editfolder').focus();
                    }
                };

                l18nButtons[wpmfaddonparams.cancelfolder] = function () {
                    $(this).dialog("destroy");
                };

                dialog_html.dialog({
                    dialogClass: 'Wpmfdialogaddfolder', resizable: false,
                    height: 200,
                    width: 400,
                    modal: true,
                    buttons: l18nButtons,
                    create: function () {
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:first").removeClass('ui-state-default').addClass("button-primary");
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:last").removeClass('ui-state-default').addClass("button-secondary");

                    },
                    close: function () {
                        $(this).dialog("destroy");
                    }
                });
            });

            /* click delete file */
            $('.wpmfaddonicon-delete').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var id = $(this).data('id');
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.deletefolder + "'><p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p></div>");
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.delete] = function () {
                    var data = {
                        action: 'wpmf-google-deletefolder',
                        parentId: wpmfGoogleDriveModule.googlecurrentfolderID,
                        ids: id
                    };
                    wpmfGoogleDriveModule.ajaxGoogle(data);
                    $(this).dialog("destroy");
                };

                l18nButtons[wpmfaddonparams.cancelfolder] = function () {
                    $(this).dialog("destroy");
                };

                dialog_html.dialog({
                    dialogClass: 'Wpmfdialogaddfolder', resizable: false,
                    height: 200,
                    width: 400,
                    modal: true,
                    buttons: l18nButtons,
                    create: function () {
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:first").removeClass('ui-state-default').addClass("button-primary");
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:last").removeClass('ui-state-default').addClass("button-secondary");
                    },
                    close: function () {
                        $(this).dialog("destroy");
                    }
                });
            });

            /* click add new folder */
            $(".WpmfGoogleDrive .newfolder").click(function () {
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.addfolder + "'><p>" +
                    '<input type="text" name="newfolder" id="newfolder" placeholder="' + wpmfaddonparams.newfolder + '" value="" class="text ui-widget-content ui-corner-all" style=" width: 90%; "/></p></div>');
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.addfolder] = function () {
                    var data = {
                        action: 'wpmf-google-addfolder',
                        title: encodeURIComponent($('#newfolder').val()),
                        parentId: wpmfGoogleDriveModule.googlecurrentfolderID
                    };
                    wpmfGoogleDriveModule.ajaxGoogle(data);
                    $(this).dialog("destroy");
                };

                l18nButtons[wpmfaddonparams.cancelfolder] = function () {
                    $(this).dialog("destroy");
                };

                dialog_html.dialog({
                    dialogClass: 'Wpmfdialogaddfolder', resizable: false,
                    height: 200,
                    width: 400,
                    modal: true,
                    buttons: l18nButtons,
                    create: function () {
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:first").removeClass('ui-state-default').addClass("button-primary");
                        $(this).closest('.ui-dialog').find(".ui-dialog-buttonset .ui-button:last").removeClass('ui-state-default').addClass("button-secondary");
                    },
                    close: function () {
                        $(this).dialog("destroy");
                    }
                });

                return false;
            });
        },

        /**
         * call ajax by data
         * @param data
         */
        ajaxGoogle: function (data) {
            var listtoken = data.listtoken;
            $.ajax({
                type: "POST",
                url: ajaxurl,
                method: 'POST',
                data: data, beforeSend: function () {
                    $(".WpmfGoogleDrive .loading").fadeTo(400, 1);
                },
                complete: function () {

                }, success: function (response) {
                    wpmfGoogleDriveModule.getDriveFileList();
                },
                dataType: 'json'
            });
        },

        /**
         * open popup when preview file
         */
        loadPopup: function () {
            if (jQuery().magnificPopup) {
                $('.wpmflinkfile.image').magnificPopup({
                    disableOn: 700,
                    mainClass: 'wpmfifame',
                    gallery: {
                        enabled: true,
                        tCounter: '<span class="mfp-counter">%curr% / %total%</span>',
                        arrowMarkup: '<button title="%title%" type="button" class="zmdi zmdi-chevron-%dir%"></button>' // markup of an arrow button
                    },
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false,
                    type: 'image'
                });

                $('.wpmflinkfile.iframe').magnificPopup({
                    disableOn: 700,
                    mainClass: 'wpmfifame',
                    gallery: {
                        enabled: true,
                        tCounter: '<span class="mfp-counter">%curr% / %total%</span>',
                        arrowMarkup: '<button title="%title%" type="button" class="zmdi zmdi-chevron-%dir%"></button>' // markup of an arrow button
                    },
                    removalDelay: 160,
                    preloader: false,
                    fixedContentPos: false,
                    type: 'iframe'
                });
            }
        },

        /**
         * init draggable and droppable
         */
        initFileDragDropable: function () {
            /* init droppable for folder */
            $('.wpmfaddonfile.folder').droppable({
                hoverClass: "wpmfui-hoverClass",
                drop: function (event, ui) {
                    if ($(ui.draggable).hasClass('folder')) {
                        var fileIds = ui.helper.data('id');
                        var newParentId = $(this).data('id');
                        $('.wpmfaddonfile.folder[data-id="' + fileIds + '"]').hide();
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "wpmfaddon_move_file",
                                fileIds: fileIds,
                                newParentId: newParentId
                            },
                            success: function (response) {
                                wpmfGoogleDriveModule.getDriveFileList();
                            }
                        });
                    } else {
                        fileIds = ui.helper.data('wpmffileIds');
                        newParentId = $(this).data('id');
                        if (typeof fileIds !== "undefined") {
                            $(fileIds.split(',')).each(function () {
                                $('.wpmfaddonfile.file[data-id="' + this + '"]:not(.ui-draggable-dragging)').hide();
                            });
                        }
                        var wpmf_time = 500;
                        setTimeout(function () {
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "wpmfaddon_move_file",
                                    fileIds: fileIds,
                                    newParentId: newParentId
                                },
                                success: function (response) {
                                    wpmfGoogleDriveModule.getDriveFileList();
                                }
                            });
                        }, wpmf_time);
                    }
                }
            });

            /* init draggable for folder */
            $('.wpmfaddonfile.folder:not(.parentfolder,.newfolder)').draggable({
                revert: true,
                distance: 10,
                drag: function () {

                },
                start: function (event, ui) {
                    var id = ui.helper.data('id');
                    $('.wpmfaddonfile.folder[data-id="' + id + '"]').addClass('wpmfunclick');
                },
                stop: function (event, ui) {
                    var id = ui.helper.data('id');
                    $('.wpmfaddonfile.folder[data-id="' + id + '"]').removeClass('wpmfunclick');
                }
            });

            /* init draggable for file */
            $('.wpmfaddonfile.file').draggable({
                revert: true,
                appendTo: ".files.layout-grid",
                helper: function (e) {
                    var fileIds = [];
                    var elements = $.merge($(this), $('.files.layout-grid .wpmfaddonfile.file.selected').not(this));

                    //attach selected elements data-id to the helper
                    elements.each(function () {
                        fileIds.push($(this).data('id'));
                    });
                    helper = $(this).clone();
                    helper.append('<span class="dragNumber">' + elements.length + '</span>');
                    helper.data('wpmffileIds', fileIds.join());
                    return helper;
                },
                drag: function () {

                },
                start: function (event, ui) {
                    var fileIds = ui.helper.data('wpmffileIds').split(',');
                    $(fileIds).each(function (index, value) {
                        $('.files.layout-grid .wpmfaddonfile.file[data-id="' + value + '"]:not(.ui-draggable-dragging)').css('visibility', 'hidden');
                    });
                },
                stop: function (event, ui) {
                    var fileIds = ui.helper.data('wpmffileIds').split(',');
                    $(fileIds).each(function (index, value) {
                        $('.files.layout-grid .wpmfaddonfile.file[data-id="' + value + '"]:not(.ui-draggable-dragging)').css('visibility', 'visible');
                    });
                }
            });
        },

        /**
         * Start upload file
         * @param file
         */
        uploadStart: function (file) {
            var row = $(".WpmfGoogleDrive .fileupload-list [data-id='" + file.hash + "']");
            row.find('.upload-status').removeClass().addClass('upload-status inqueue').text(wpmfaddonparams.str_inqueue);
            row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle-o-notch fa-spin');
            row.find('.upload-progress').slideDown();
            $('input[type="submit"]').prop('disabled', true)
        },

        /**
         * Helper functions
         * @param bytes
         * @param si
         * @returns {string}
         */
        humanFileSize: function (bytes, si) {
            var thresh = si ? 1000 : 1024;
            if (Math.abs(bytes) < thresh) {
                return bytes + ' B';
            }
            var units = si
                ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
                : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
            var u = -1;
            do {
                bytes /= thresh;
                ++u;
            } while (Math.abs(bytes) >= thresh && u < units.length - 1);
            return bytes.toFixed(1) + ' ' + units[u];
        },

        /**
         * Validate File for Upload
         * @param file
         * @returns {*}
         */
        validateFile: function (file) {
            var acceptFileType = new RegExp($(".WpmfGoogleDrive").find('input[name="acceptfiletypes"]').val(), "i");
            file.error = false;
            if (file.name.length && !acceptFileType.test(file.name)) {
                file.error = wpmfaddonparams.acceptFileTypes;
            }

            if (wpmfaddonparams.maxsize !== '' && file.size > 0 && file.size > wpmfaddonparams.maxsize) {
                file.error = wpmfaddonparams.maxFileSize;
            }
            return file;
        },

        /**
         * Get thumbnail for local and cloud files
         * @param file
         * @returns {*}
         */
        getThumbnail: function (file) {
            if (typeof file.thumbnail === 'undefined' || file.thumbnail === null || file.thumbnail === '') {
                var icon;

                if (typeof file.type === 'undefined' || file.type === null) {
                    icon = 'generic';
                } else if (file.type.indexOf("word") >= 0) {
                    icon = 'word';
                } else if (file.type.indexOf("excel") >= 0 || file.type.indexOf("spreadsheet") >= 0) {
                    icon = 'excel';
                } else if (file.type.indexOf("powerpoint") >= 0 || file.type.indexOf("presentation") >= 0) {
                    icon = 'powerpoint';
                } else if (file.type.indexOf("image") >= 0) {
                    // icon = 'image';
                    return URL.createObjectURL(file);
                } else if (file.type.indexOf("audio") >= 0) {
                    icon = 'audio';
                } else if (file.type.indexOf("video") >= 0) {
                    icon = 'video';
                } else if (file.type.indexOf("pdf") >= 0) {
                    icon = 'pdf';
                } else if (file.type.indexOf("text") >= 0) {
                    icon = 'text';
                } else {
                    icon = 'generic_small';
                }
                return wpmfaddonparams.plugin_url + icon + '.png';
            } else {
                return file.thumbnail;
            }
        },

        /**
         * Render file in upload list
         * @param file
         */
        renderFileUploadRow: function (file) {
            var row = ($(".WpmfGoogleDrive").find('.template-row').clone().removeClass('template-row'));

            row.attr('data-file', file.name).attr('data-id', file.hash);
            row.find('.file-name').text(file.name);
            if (file.size !== 'undefined' && file.size > 0) {
                row.find('.file-size').text(wpmfGoogleDriveModule.humanFileSize(file.size, true));
            }
            row.find('.upload-thumbnail img').attr('src', wpmfGoogleDriveModule.getThumbnail(file));

            row.addClass('template-upload');
            row.find('.upload-status').removeClass().addClass('upload-status queue').text(wpmfaddonparams.str_inqueue);
            row.find('.upload-status-icon').removeClass().addClass('upload-status-icon fa fa-circle');

            $(".WpmfGoogleDrive .fileupload-list .files").append(row);

            $('.WpmfGoogleDrive .fileuploadform div.fileupload-drag-drop').fadeOut();

            return row;
        },

        /**
         * Render the progress of uploading cloud files
         * @param file
         * @param status
         */
        uploadProgress: function (file, status) {
            var row = $(".WpmfGoogleDrive .fileupload-list [data-id='" + file.hash + "']");

            row.find('.progress')
                .attr('aria-valuenow', status.percentage)
                .children().first().fadeIn()
                .animate({
                    width: status.percentage + '%'
                }, 'slow', function () {
                    if (status.progress === 'uploading_to_cloud') {
                        row.find('.upload-status').removeClass().addClass('upload-status uploading').text(wpmfaddonparams.str_uploading_cloud);
                    }
                });
        },

        /**
         * when upload file finish
         * @param file
         */
        uploadFinished: function (file) {
            var row = $(".WpmfGoogleDrive .fileupload-list [data-id='" + file.hash + "']");

            row.addClass('template-download').removeClass('template-upload');
            row.find('.file-name').text(file.name);
            row.find('.upload-thumbnail img').attr('src', wpmfGoogleDriveModule.getThumbnail(file));
            row.find('.upload-status').removeClass().addClass('upload-status succes').text(wpmfaddonparams.str_success);
            row.find('.upload-progress').slideUp();
            row.animate({"opacity": "0"}, "slow", function () {
                if ($(this).parent().find('.template-upload').length <= 1) {
                    $(this).closest('.fileuploadform').find('div.fileupload-drag-drop').fadeIn();

                    /* Update Filelist */
                    var formData = {
                        listtoken: file.listtoken
                    };

                    wpmfGoogleDriveModule.getDriveFileList();
                }

                $(this).remove();
            });

            $('input[type="submit"]').prop('disabled', false)
        },

        /**
         * Insert file to editor
         */
        insertFile: function () {
            var code = '';
            $('.wpmf_checkfile:checked').each(function (i, v) {

                var src = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].src;
                var type_insert = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].type_insert;
                var title = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].title;
                if (type_insert === 'download') {
                    code += '<a href="' + src + '">' + title + '</a>';
                } else if (type_insert === 'pdf') {
                    code += '<a class="wpmf-pdfemb-viewer" data-wpmf_pdf_embed="link" href="' + src + '">' + title + '</a>';
                } else if (type_insert === 'image') {
                    var link_img = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].lightboxlink;
                    code += '<img src="' + link_img + '">';
                } else {
                    code += '<iframe class="wpmf-google-inserted" style="width: 100%;" src="' + src + '" width="300" height="480" frameborder="0" scrolling="no" allowfullscreen="allowfullscreen"></iframe>';
                }
            });

            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor(code);
        },

        /**
         * Insert PDF embed to post content
         */
        embedPdf: function () {
            var code = '';
            $('.wpmf_checkfile:checked').each(function (i, v) {

                var src = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].src;
                var type_insert = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].type_insert;
                var title = wpmfGoogleDriveModule.listFilesCurrentFolder[$(v).val()].title;
                if (type_insert === 'download') {
                    code += '<a href="' + src + '">' + title + '</a>';
                } else if (type_insert === 'pdf') {
                    code += '<a class="wpmf-pdfemb-viewer" data-wpmf_pdf_embed="embed" href="' + src + '">' + title + '</a>';
                } else {
                    code += '<iframe class="wpmf-google-inserted" style="width: 100%;" src="' + src + '" width="300" height="480" frameborder="0" scrolling="no" allowfullscreen="allowfullscreen"></iframe>';
                }
            });

            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor(code);
        },

        /**
         * show/hide button
         */
        displayButton: function () {
            var not_pdf = $('.wpmf_checkfile:checked[data-type!="application/pdf"]').length;
            var is_pdf = $('.wpmf_checkfile:checked[data-type="application/pdf"]').length;
            var length_checked = $('.wpmf_checkfile:checked').length;
            if (length_checked > 0) {
                $('.wpmfmedia-button-gginsert:not(.media_page_wpmf-google-drive-page .wpmfmedia-button-gginsert),.wpmf-open-popup-ggmedia').show();
            } else {
                $('.wpmfmedia-button-gginsert:not(.media_page_wpmf-google-drive-page .wpmfmedia-button-gginsert),.wpmf-open-popup-ggmedia').hide();
            }

            if (not_pdf > 0) {
                $('.wpmfmedia-button-ggembed').hide();
            } else {
                if (is_pdf > 0) {
                    $('.wpmfmedia-button-ggembed').show();
                } else {
                    $('.wpmfmedia-button-ggembed').hide();
                }
            }
        }
    };

    /**
     * Google tree module
     * @type {{}}
     */
    wpmfGoogleTreeModule = {
        /**
         * options
         * @type {{root: string, showroot: *, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}}
         */
        options: {
            'root': '/',
            'showroot': '',
            'onclick': function (elem, type, file) {
            },
            'oncheck': function (elem, checked, type, file) {
            },
            'usecheckboxes': true, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        },
        init: function () {
            wpmfGoogleTreeModule.options.showroot = wpmfaddonparams.media_folder;
            if ($('.librarytree').length === 0) {
                return;
            }
            $wpmfggtree = $('.librarytree');
            if (wpmfGoogleTreeModule.options.showroot !== '') {
                $wpmfggtree.html('<ul class="jaofiletree"><li data-id="0" class="directory collapsed selected"><a class="title-folder title-root" href="#" data-id="0" data-file="' + wpmfGoogleTreeModule.options.root + '" data-type="dir">' + wpmfGoogleTreeModule.options.showroot + '</a></li></ul>');
            }
            wpmfGoogleTreeModule.openfolder(wpmfGoogleTreeModule.options.root);
        },
        /**
         * open folder tree by dir name
         * @param dir dir name
         * @param callback
         */
        openfolder: function (dir, callback) {
            $wpmfggtree = $('.librarytree');
            if (typeof $wpmfggtree === "undefined")
                return;
            wpmfGoogleDriveModule.wpmfcurrentFolderId = $wpmfggtree.find('a[data-file="' + dir + '"]').data('id');
            if ($wpmfggtree.find('a[data-file="' + dir + '"]').parent().hasClass('expanded') || $wpmfggtree.find('a[data-file="' + dir + '"]').parent().hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }
            var ret;
            ret = $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    dir: dir,
                    id: wpmfGoogleDriveModule.wpmfcurrentFolderId,
                    action: 'wpmf',
                    task: 'get_terms',
                    wpmf_display_media: $('#wpmf-display-media-filters').val()
                },
                context: $wpmfggtree,
                dataType: 'json',
                beforeSend: function () {
                    this.find('a[data-file="' + dir + '"]').parent().addClass('wait');
                }
            }).done(function (datas) {
                ret = '<ul class="jaofiletree" style="display: none">';
                for (var ij = 0; ij < datas.length; ij++) {
                    if (datas[ij].type === 'dir') {
                        var classe = 'directory collapsed';
                    } else {
                        classe = 'file ext_' + datas[ij].ext;
                    }

                    if (parseInt(datas[ij].id) === parseInt(wpmfGoogleDriveModule.wpmfcurrentFolderId)) {
                        classe += ' selected';
                    }

                    ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                    if (datas[ij].count_child > 0) {
                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                    } else {
                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                    }

                    if (parseInt(datas[ij].id) === parseInt(wpmfGoogleDriveModule.wpmfcurrentFolderId)) {
                        ret += '<i class="zmdi zmdi-folder-outline"></i>';
                    } else {
                        ret += '<i class="zmdi zmdi-folder"></i>';
                    }

                    ret += '<a href="#" class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                    ret += '</li>';
                }
                ret += '</ul>';

                this.find('a[data-file="' + dir + '"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');

                this.find('a[data-file="' + dir + '"]').after(ret);
                this.find('a[data-file="' + dir + '"]').next().slideDown(wpmfGoogleTreeModule.options.expandSpeed, wpmfGoogleTreeModule.options.expandEasing,
                    function () {
                        $wpmfggtree.trigger('afteropen');
                        $wpmfggtree.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });
                wpmfGoogleTreeModule.setevents();

            }).done(function () {
                $wpmfggtree.trigger('afteropen');
                $wpmfggtree.trigger('afterupdate');
            });
        },

        /**
         * close folder tree by dir name
         * @param dir
         */
        closedir: function (dir) {
            if (typeof $wpmfggtree === "undefined")
                return;
            $wpmfggtree.find('a[data-file="' + dir + '"]').next().slideUp(wpmfGoogleTreeModule.options.collapseSpeed, wpmfGoogleTreeModule.options.collapseEasing, function () {
                $(this).remove();
            });

            $wpmfggtree.find('a[data-file="' + dir + '"]').parent().removeClass('expanded').addClass('collapsed');
            wpmfGoogleTreeModule.setevents();

            //Trigger custom event
            $wpmfggtree.trigger('afterclose');
            $wpmfggtree.trigger('afterupdate');
        },

        /**
         * init event click to open/close folder tree
         */
        setevents: function () {
            $wpmfggtree = $('.librarytree');
            $wpmfggtree.find('li a,li .icon-open-close').unbind('click');

            //Bind for collapse or expand elements
            $wpmfggtree.find('li.directory a').bind('click', function (e) {
                e.preventDefault();
                $("#librarytree").find('li').removeClass('selected');
                $("#librarytree").find('i.zmdi').removeClass('zmdi-folder-outline').addClass("zmdi-folder");
                $(this).parent().addClass("selected");
                $(this).parent().find(' > i.zmdi').removeClass("zmdi-folder").addClass("zmdi-folder-outline");
                wpmfGoogleTreeModule.openfolder($(this).attr('data-file'));
            });

            /* open folder tree use icon */
            $wpmfggtree.find('li.directory.collapsed .icon-open-close').bind('click', function () {
                wpmfGoogleTreeModule.openfolder($(this).attr('data-file'));
            });

            /* close folder tree use icon */
            $wpmfggtree.find('li.directory.expanded .icon-open-close').bind('click', function () {
                wpmfGoogleTreeModule.closedir($(this).attr('data-file'));
            });
        }
    };

    $(document).ready(function () {
        wpmfGoogleDriveModule.init();
        wpmfGoogleTreeModule.init();
    });
}(jQuery));
String.prototype.hashCode = function () {
    var hash = 0, i, char;
    if (this.length === 0)
        return hash;
    for (i = 0, l = this.length; i < l; i++) {
        char = this.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash |= 0; // Convert to 32bit integer
    }
    return Math.abs(hash);
};