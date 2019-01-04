var wpmfDropboxModule;
var wpmfDropboxTreeModule;
(function ($) {
    wpmfDropboxModule = {
        /**
         * root folder
         * @type {string}
         */
        dropboxcurrentfolderPath: '',
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
        listdbxfiles_currentFolder: {},
        /**
         * current folder id
         * @type {number}
         */
        wpmfdbxcurrentFolderId: 0,
        init: function () {
            /* Call ajax get dropbox files */
            wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
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

                wpmfDropboxModule.displayButton();
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
                        action: 'wpmf-dropbox-deletefolder',
                        path: ids
                    };

                    wpmfDropboxModule.ajaxDropbox(data);
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
                wpmfDropboxModule.sortfilename = $(this).data('sort');
                $('.wpmfaddonsort').removeClass('active');
                $(this).addClass('active');
                wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
            });

            /* Search folders and files */
            $('.wpmfaddon-search-input').on('change', function () {
                wpmfDropboxModule.searchfilename = $(this).val();
                wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
            });

            /* Refesh the results */
            $('.wpmfaddonrefresh').on('click', function () {
                wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
            });

            /* popup folder tree */
            $('.wpmf-open-popup-dbxmedia').magnificPopup({
                type: 'inline',
                midClick: true // Allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source in href.
            });

            // end view folder tree ================

            /* click dropbox import button */
            $('.btndbximport').on('click', function () {
                var $this = $(this);
                $this.hide();
                var filesselected = [];
                $('.wpmf_checkfile:checked').each(function (i, v) {
                    filesselected.push($(v).val());
                });
                var ids = filesselected.join();
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'wpmf_dbximport_file',
                        ids: ids,
                        wpmfdbxcurrentFolderId: wpmfDropboxModule.wpmfdbxcurrentFolderId
                    }, beforeSend: function () {
                        $('.process_dbximport_full').show();
                    },
                    complete: function () {

                    }, success: function (res) {
                        var w = $('.process_dbximport').data('w');
                        if (typeof res.status !== "undefined" && res.status === 'error time') {
                            if (typeof res.percent !== "undefined") {
                                var percent = parseFloat(w) + parseFloat(res.percent);
                                if (percent > 100)
                                    percent = 100;
                                $('.process_dbximport').data('w', percent);
                                $('.process_dbximport').css('width', percent + '%');
                                $('.process_dbximport').html(percent + '%');
                            }
                            $this.click();
                        } else {
                            $('.process_dbximport_full').hide();
                            $('.process_dbximport').css('width', '0%').html('0%').data('w', 0);
                            $('.message_import').fadeIn(500).fadeOut(1500).html(wpmfaddonparams.message_import);
                            setTimeout(function () {
                                $('.wpmf-open-popup-dbxmedia').magnificPopup('close');
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
                            file = wpmfDropboxModule.validateFile(file);
                            var row = wpmfDropboxModule.renderFileUploadRow(file);
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

                    },
                    done: function (e, data) {
                        var datas = data;
                        if (datas.result !== false) {
                            $.ajax({
                                method: "POST",
                                dataType: "json",
                                url: ajaxurl,
                                data: {
                                    action: 'wpmf_get-detailFile',
                                    id: datas.result.id,
                                    path: datas.result.path_display,
                                    name: datas.result.name
                                }, beforeSend: function () {

                                },
                                complete: function () {

                                },
                                success: function (response) {
                                    if (response !== false) {
                                        $('.files.layout-grid').append(response.html);
                                        $(".WpmfGoogleDrive .loading").removeClass('search').hide();

                                        if (response.type === 'image' || response.type === 'video' || response.type === 'pdf') {
                                            $('.imgthumb[data-id="' + datas.result.path_display + '"]').attr('src', wpmfaddonparams.img_path + 'Loading_icon.gif');
                                        }
                                        $('.wpmf_checkfile[value="' + datas.result.path_display + '"]').hide();

                                        if (response.type === 'image' || response.type === 'video' || response.type === 'pdf') {
                                            $.ajax({
                                                url: wpmfaddonparams.plugin_url + 'class/dropbox_cache.php?dropboxToken=' + encodeURIComponent(wpmfaddonparams.dropboxToken) + '&path=' + encodeURIComponent(datas.result.path_display)

                                            }).done(function (data) {
                                                var datasfile = JSON.parse(data);
                                                if (response.type !== 'pdf') {
                                                    $('.imgthumb[data-id="' + datas.result.path_display + '"]').attr('src', datasfile.thumb);
                                                }
                                                if (response.type === 'image' || response.type === 'pdf') {
                                                    $('.wpmflinkfile[data-id="' + datas.result.path_display + '"]').attr('href', datasfile.preview_link);
                                                }
                                                $('.wpmf_checkfile[value="' + datas.result.path_display + '"]').show();
                                            });
                                        }

                                        wpmfDropboxModule.listdbxfiles_currentFolder[datas.result.path_display] = {
                                            'ext': response.ext,
                                            'title': response.title,
                                            'type_insert': response.type_insert
                                        };
                                        wpmfDropboxModule.bindEvent();
                                        wpmfDropboxModule.eventDialog($('.wpmfaddonfile[data-id="' + datas.result.path_display + '"]'));
                                        wpmfDropboxModule.loadPopup();
                                        wpmfDropboxModule.initFileDragDropable();
                                    }
                                }
                            });
                        }
                    }
                }).on('fileuploadsubmit', function (e, data) {
                    var datatoken = $(this).attr('data-token');
                    $(".WpmfGoogleDrive .loading").addClass('upload');
                    $(".WpmfGoogleDrive .loading").fadeTo(400, 1);

                    $.each(data.files, function (index, file) {
                        wpmfDropboxModule.uploadStart(file);
                    });

                    $('.gform_button:submit').prop("disabled", false).fadeTo(400, 0.3);

                    data.formData = {
                        action: 'wpmf-dbxupload-file',
                        type: 'do-upload',
                        parentID: wpmfDropboxModule.dropboxcurrentfolderPath,
                        _ajax_nonce: wpmfaddonparams.upload_nonce
                    };

                }).on('fileuploadprogress', function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10) / 2;
                    $.each(data.files, function (index, file) {
                        wpmfDropboxModule.uploadProgress(file, {percentage: 100, progress: 'uploading_to_cloud'});
                    });

                }).on('fileuploadstopped', function () {
                }).on('fileuploaddone', function (e, data) {
                    wpmfDropboxModule.uploadFinished(data.files[0]);
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
         * get dropbox files
         * @param path folder path
         */
        getDriveFileList: function (path) {
            $.ajax({
                method: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: 'wpmf-get-dropboxfilelist',
                    path_display: path,
                    searchfilename: wpmfDropboxModule.searchfilename,
                    sortfilename: wpmfDropboxModule.sortfilename
                }, beforeSend: function () {
                    $(".WpmfGoogleDrive .loading").removeClass('initialize upload error');
                    $(".WpmfGoogleDrive .loading").fadeTo(400, 1);
                },
                complete: function () {
                    $(".WpmfGoogleDrive .loading").removeClass('search').hide();
                },
                success: function (response) {
                    /* Set list files in current folder */
                    wpmfDropboxModule.listdbxfiles_currentFolder = response.files;
                    /* Set breadcrumb */
                    $('.wpmf_dbxbreadcrumb').html(response.breadcrumb);
                    $('.wpmfaddoncheckall').removeClass('active');
                    $('#WpmfGoogleDrive .ajax-filelist .files').html(null);
                    $('#WpmfGoogleDrive .ajax-filelist .files').prepend(response.message);
                    wpmfDropboxModule.displayButton();

                    /* set cache */
                    $.each(response.lists_file_preview, function (i, v) {
                        if (v.type !== 'pdf') {
                            $('.imgthumb[data-id="' + v.path + '"]').attr('src', wpmfaddonparams.img_path + 'Loading_icon.gif');
                        }
                        $('.wpmf_checkfile[value="' + v.path + '"]').hide();

                        $.ajax({
                            url: wpmfaddonparams.plugin_url + 'class/dropbox_cache.php?dropboxToken=' + encodeURIComponent(wpmfaddonparams.dropboxToken) + '&path=' + encodeURIComponent(v.path)

                        }).done(function (data) {
                            var datas = JSON.parse(data);
                            if (v.type !== 'pdf') {
                                $('.imgthumb[data-id="' + v.path + '"]').attr('src', datas.thumb);
                            }
                            if (v.type === 'image' || v.type === 'pdf') {
                                $('.wpmflinkfile[data-id="' + v.path + '"]').attr('href', datas.preview_link);
                            }
                            $('.wpmf_checkfile[value="' + v.path + '"]').show();
                        });
                    });

                    /* Init event */
                    wpmfDropboxModule.bindEvent();
                    wpmfDropboxModule.eventDialog($('.wpmfaddonfile'));
                    wpmfDropboxModule.eventDialogAdd();
                    wpmfDropboxModule.loadPopup();
                    wpmfDropboxModule.initFileDragDropable();

                    /* get share file */
                    $('.wpmfaddonfile.dropbox_video .wpmf_checkfile').hide();
                    $.each(response.videofiles, function (i, v) {
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'wpmf_dropbox_sharefile',
                                id: v
                            },
                            success: function (res) {
                                if (res.status) {
                                    $('.wpmfaddonfile[data-id="' + v + '"]').data('src', res.src);
                                    $('.wpmfaddonfile[data-id="' + v + '"] .imgloading').hide();
                                    $('.wpmfaddonfile[data-id="' + v + '"] .wpmf_checkfile , .wpmfaddonfile[data-id="' + v + '"] .imgfile').show();
                                }
                            }
                        });
                    });
                }
            });
        },

        /**
         * init event
         */
        eventDialog: function (container) {
            /* click edit file */
            $(container).find('.wpmfaddonicon-edit').on('click', function (e) {
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
                            action: 'wpmf-dropbox-editfolder',
                            filename: filename,
                            path: id,
                            parent: wpmfDropboxModule.dropboxcurrentfolderPath,
                            thumbnail: $('.imgthumb[data-id="' + id + '"]').attr('src'),
                            typeaction: 'edit'
                        };
                        wpmfDropboxModule.ajaxDropbox(data);
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
            $(container).find('.wpmfaddonicon-delete').on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var id = $(this).data('id');
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.deletefolder + "'><p><span class='ui-icon ui-icon-alert' style='float:left; margin:0 7px 20px 0;'></span>These items will be permanently deleted and cannot be recovered. Are you sure?</p></div>");
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.delete] = function () {
                    var data = {
                        action: 'wpmf-dropbox-deletefolder',
                        path: id,
                        typeaction: 'delete'
                    };
                    wpmfDropboxModule.ajaxDropbox(data);
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
        },

        /**
         * Show dialog
         */
        eventDialogAdd: function () {
            // click add new folder
            $(".WpmfGoogleDrive .newfolder").click(function () {
                var dialog_html = $("<div class='dialog' title='" + wpmfaddonparams.addfolder + "'><p>" +
                    '<input type="text" name="newfolder" id="newfolder" placeholder="' + wpmfaddonparams.newfolder + '" class="text ui-widget-content ui-corner-all" style=" width: 90%; "/></p></div>');
                var l18nButtons = {};
                l18nButtons[wpmfaddonparams.addfolder] = function () {
                    var data = {
                        action: 'wpmf-dropbox-addfolder',
                        title: encodeURIComponent($('#newfolder').val()),
                        path: wpmfDropboxModule.dropboxcurrentfolderPath,
                        typeaction: 'add'
                    };
                    wpmfDropboxModule.ajaxDropbox(data);
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
         * init event
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

                wpmfDropboxModule.displayButton();
            });

            /* click breadcrumb */
            $('.wpmf_dbxbreadcrumb_folder').on('click', function () {
                wpmfDropboxModule.dropboxcurrentfolderPath = $(this).data('id');
                wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
            });

            /* click open folder */
            $('.entry.folder').not('.newfolder').on('click', function () {
                if (!$(this).hasClass('wpmfunclick')) {
                    wpmfDropboxModule.dropboxcurrentfolderPath = $(this).data('id');
                    wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
                }
            });
        },

        /**
         * call ajax by data
         * @param data
         */
        ajaxDropbox: function (data) {
            var listtoken = data.listtoken;
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: data,
                success: function (response) {
                    if (typeof data.typeaction !== "undefined") {
                        if (data.typeaction === 'add') {
                            $('.entry.folder.newfolder').before(response.html);
                            wpmfDropboxModule.bindEvent();
                            wpmfDropboxModule.eventDialog($('.wpmfaddonfile[data-id="' + response.path + '"]'));
                            wpmfDropboxModule.loadPopup();
                            wpmfDropboxModule.initFileDragDropable();
                        } else if (data.typeaction === 'edit') {
                            $('.wpmfaddonfile[data-id="' + data.path + '"]').replaceWith(response.html);
                            wpmfDropboxModule.bindEvent();
                            wpmfDropboxModule.eventDialog($('.wpmfaddonfile[data-id="' + response.path + '"]'));
                            wpmfDropboxModule.loadPopup();
                            wpmfDropboxModule.initFileDragDropable();
                        } else if (data.typeaction === 'delete') {
                            $('.wpmfaddonfile[data-id="' + data.path + '"]').remove();
                        } else {
                            wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
                        }
                    } else {
                        wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
                    }
                },
                dataType: 'json'
            });
        },

        /**
         * open popup with image file
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
                    } else {
                        fileIds = ui.helper.data('wpmffileIds');
                        newParentId = $(this).data('id');
                        if (typeof fileIds !== "undefined") {
                            $(fileIds.split(',')).each(function () {
                                $('.wpmfaddonfile.file[data-id="' + this + '"]:not(.ui-draggable-dragging)').hide();
                            });
                        }
                    }
                    $.ajax({
                        method: "POST",
                        dataType: 'json',
                        url: ajaxurl,
                        data: {
                            action: "wpmf_dropbox_movefile",
                            fileIds: fileIds,
                            newParentId: newParentId
                        },
                        success: function (response) {
                            wpmfDropboxModule.getDriveFileList(wpmfDropboxModule.dropboxcurrentfolderPath);
                        }
                    });
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
                    icon = 'dropbox_default';
                } else if (file.type.indexOf("word") >= 0) {
                    icon = 'dropbox_doc';
                } else if (file.type.indexOf("excel") >= 0 || file.type.indexOf("spreadsheet") >= 0) {
                    icon = 'dropbox_excel';
                } else if (file.type.indexOf("powerpoint") >= 0 || file.type.indexOf("presentation") >= 0) {
                    icon = 'dropbox_powerpoint';
                } else if (file.type.indexOf("image") >= 0) {
                    return URL.createObjectURL(file);
                } else if (file.type.indexOf("audio") >= 0) {
                    icon = 'dropbox_audio';
                } else if (file.type.indexOf("video") >= 0) {
                    icon = 'dropbox_video';
                } else if (file.type.indexOf("pdf") >= 0) {
                    icon = 'dropbox_pdf';
                } else if (file.type.indexOf("text") >= 0) {
                    icon = 'dropbox_txt';
                } else {
                    icon = 'dropbox_default';
                }

                if (file.name.indexOf(".psd") >= 0) {
                    icon = 'dropbox_psd';
                }
                if (file.name.indexOf(".ai") >= 0) {
                    icon = 'dropbox_ai';
                }
                return wpmfaddonparams.plugin_url_icon + icon + '.png';
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
                row.find('.file-size').text(wpmfDropboxModule.humanFileSize(file.size, true));
            }
            row.find('.upload-thumbnail img').attr('src', wpmfDropboxModule.getThumbnail(file));

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
            row.find('.upload-thumbnail img').attr('src', wpmfDropboxModule.getThumbnail(file));
            row.find('.upload-status').removeClass().addClass('upload-status succes').text(wpmfaddonparams.str_success);
            row.find('.upload-progress').slideUp();
            row.animate({"opacity": "0"}, "slow", function () {
                if ($(this).parent().find('.template-upload').length <= 1) {
                    $(this).closest('.fileuploadform').find('div.fileupload-drag-drop').fadeIn();

                    /* Update Filelist */
                    var formData = {
                        listtoken: file.listtoken
                    };
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
                var type_insert = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].type_insert;
                var ext = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].ext;
                var title = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].title;
                var src = $('.wpmflinkfile[data-id="' + $(v).val() + '"]').data('link');
                if (type_insert === 'image') {
                    code += '<img src="' + src + '">';
                } else if (type_insert === 'video') {
                    var link_embed = $('.wpmfaddonfile[data-id="' + $(v).val() + '"]').data('src');
                    code += '<a href="#" class="wpmf_dbx_video" data-src="' + link_embed + '">';
                    code += '<img src="' + wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].thumbnail + '">';
                    code += '</a>';
                    //code += '[video width="1280" height="720" '+ ext +'="'+ src +'"][/video]';
                } else {
                    if (ext === 'pdf') {
                        var pdfsrc = $('.wpmfaddonfile[data-id="' + $(v).val() + '"]').find('.wpmfaddonicon-download').closest('a').attr('href');
                        code += '<a class="wpmf-pdfemb-viewer" data-wpmf_pdf_embed="link" href="' + pdfsrc + '">' + title + '</a>';
                    } else if (ext === 'mp3') {
                        code += '[audio ' + ext + '="' + src + '"][/audio]';
                    } else {
                        var dllink = src.replace("&dl=0", "&dl=1");
                        code += '<a href="' + dllink + '" download>' + title + '</a>';
                    }
                }
            });

            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor(code);
            return false;
        },

        /**
         * Insert PDF embed to editor
         */
        embedPdf: function () {
            var code = '';
            $('.wpmf_checkfile:checked').each(function (i, v) {
                var type_insert = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].type_insert;
                var ext = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].ext;
                var title = wpmfDropboxModule.listdbxfiles_currentFolder[$(v).val()].title;
                var src = $('.wpmflinkfile[data-id="' + $(v).val() + '"]').data('link');
                if (type_insert === 'image') {
                    code += '<img src="' + src + '">';
                } else if (type_insert === 'video') {
                    code += '[video width="1280" height="720" ' + ext + '="' + src + '"][/video]';
                } else {
                    if (ext === 'pdf') {
                        var pdfsrc = $('.wpmfaddonfile[data-id="' + $(v).val() + '"]').find('.wpmfaddonicon-download').closest('a').attr('href');
                        code += '<a class="wpmf-pdfemb-viewer" data-wpmf_pdf_embed="embed" href="' + pdfsrc + '">' + title + '</a>';
                    } else if (ext === 'mp3') {
                        code += '[audio ' + ext + '="' + src + '"][/audio]';
                    } else {
                        var dllink = src.replace("&dl=0", "&dl=1");
                        code += '<a href="' + dllink + '" download>' + title + '</a>';
                    }
                }
            });

            var win = window.dialogArguments || opener || parent || top;
            win.send_to_editor(code);
            return false;
        },

        /**
         * show/hide button
         */
        displayButton: function () {
            var not_pdf = $('.wpmf_checkfile:checked[data-type!="application/pdf"]').length;
            var is_pdf = $('.wpmf_checkfile:checked[data-type="application/pdf"]').length;
            var length_checked = $('.wpmf_checkfile:checked').length;
            if (length_checked > 0) {
                $('.wpmfmedia-button-dbxinsert:not(.media_page_wpmf-dropbox-page .wpmfmedia-button-dbxinsert),.wpmf-open-popup-dbxmedia').show();
            } else {
                $('.wpmfmedia-button-dbxinsert:not(.media_page_wpmf-dropbox-page .wpmfmedia-button-dbxinsert),.wpmf-open-popup-dbxmedia').hide();
            }

            if (not_pdf > 0) {
                $('.wpmfmedia-button-dbxembed').hide();
            } else {
                if (is_pdf > 0) {
                    $('.wpmfmedia-button-dbxembed').show();
                } else {
                    $('.wpmfmedia-button-dbxembed').hide();
                }
            }
        }
    };

    /**
     * Google tree module
     * @type {{}}
     */
    wpmfDropboxTreeModule = {
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
            wpmfDropboxTreeModule.options.showroot = wpmfaddonparams.media_folder;
            if ($('.librarytree').length === 0) {
                return;
            }
            $wpmfggtree = $('.librarytree');
            if (wpmfDropboxTreeModule.options.showroot !== '') {
                $wpmfggtree.html('<ul class="jaofiletree"><li data-id="0" class="directory collapsed selected"><a class="title-folder title-root" href="#" data-id="0" data-file="' + wpmfDropboxTreeModule.options.root + '" data-type="dir">' + wpmfDropboxTreeModule.options.showroot + '</a></li></ul>');
            }
            wpmfDropboxTreeModule.openfolder(wpmfDropboxTreeModule.options.root);
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
            wpmfDropboxModule.wpmfdbxcurrentFolderId = $wpmfggtree.find('a[data-file="' + dir + '"]').data('id');
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
                    id: wpmfDropboxModule.wpmfdbxcurrentFolderId,
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

                    if (parseInt(datas[ij].id) === parseInt(wpmfDropboxModule.wpmfdbxcurrentFolderId)) {
                        classe += ' selected';
                    }

                    ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                    if (datas[ij].count_child > 0) {
                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                    } else {
                        ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                    }

                    if (parseInt(datas[ij].id) === parseInt(wpmfDropboxModule.wpmfdbxcurrentFolderId)) {
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
                this.find('a[data-file="' + dir + '"]').next().slideDown(wpmfDropboxTreeModule.options.expandSpeed, wpmfDropboxTreeModule.options.expandEasing,
                    function () {
                        $wpmfggtree.trigger('afteropen');
                        $wpmfggtree.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });
                wpmfDropboxTreeModule.setevents();

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
            $wpmfggtree.find('a[data-file="' + dir + '"]').next().slideUp(wpmfDropboxTreeModule.options.collapseSpeed, wpmfDropboxTreeModule.options.collapseEasing, function () {
                $(this).remove();
            });

            $wpmfggtree.find('a[data-file="' + dir + '"]').parent().removeClass('expanded').addClass('collapsed');
            wpmfDropboxTreeModule.setevents();

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
                wpmfDropboxTreeModule.openfolder($(this).attr('data-file'));
            });

            /* open folder tree use icon */
            $wpmfggtree.find('li.directory.collapsed .icon-open-close').bind('click', function () {
                wpmfDropboxTreeModule.openfolder($(this).attr('data-file'));
            });

            /* close folder tree use icon */
            $wpmfggtree.find('li.directory.expanded .icon-open-close').bind('click', function () {
                wpmfDropboxTreeModule.closedir($(this).attr('data-file'));
            });
        }
    };

    $(document).ready(function () {
        wpmfDropboxModule.init();
        wpmfDropboxTreeModule.init();
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