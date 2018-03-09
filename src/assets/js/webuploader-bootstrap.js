+function ($) {
    "use strict";
    $.fn.webUploader = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.yiiActiveForm');
            return false;
        }
    };

    var defaults = {
        fileExistsClass: '.webuploader-exists',
        fileEmptyClass: '.webuploader-new',
        filenameClass: '.webuploader-filename',
        fileUploadClass: '.webuploader-upload',
        previewClass: '.webuploader-preview',
        containerClass: '.webuploader',
        preview: undefined,
        container: undefined,
        uploader: undefined,
        input: undefined,
        uploadButton: undefined,
        removeButton: undefined,
        auto: false,
        accept: null,
        swf: null,
        serverUrl: null,
        fieldName: null,
        options: {}
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $this = $(this);
                var settings = $.extend({}, defaults, options || {});
                settings.container = settings.container || $(this).parents(settings.containerClass);
                settings.preview = settings.preview || $(settings.container).find(settings.previewClass);
                settings.input = settings.input || $(settings.container).find('input[type="hidden"]');
                settings.uploadButton = settings.uploadButton || $(settings.container).find('[data-upload="webuploader"]');
                settings.removeButton = settings.removeButton || $(settings.container).find('[data-remove="webuploader"]');

                settings.uploader = WebUploader.create($.extend(
                    {}, settings.options || {},
                    {
                        auto: settings.auto,
                        pick: {
                            id: $this,
                            multiple: false
                        },
                        accept: settings.accept,
                        swf: settings.swf,
                        fileVal: settings.fieldName,
                        server: settings.serverUrl,
                        duplicate: false
                    }
                ));

                $this.data('webuploader', settings);
                listen(settings, $this);
            });
        }
    };

    function listen(settings, context) {
        var uploader = settings.uploader;
        var removeButton = settings.removeButton;

        uploader.on('beforeFileQueued', $.proxy(beforeChange, context));
        uploader.on('fileQueued', $.proxy(change, context));
        uploader.on('fileDequeued', $.proxy(removeFile, context));
        uploader.on('uploadProgress', $.proxy(uploadProgress, context));
        uploader.on('uploadSuccess', $.proxy(uploadSuccess, context));
        uploader.on('uploadStart', $.proxy(uploadStart, context));
        uploader.on('uploadComplete', $.proxy(uploadComplete, context));
        removeButton.on('click.webuploader', $.proxy(removeFile, context));


    }

    function beforeChange(file) {
        var settings = $(this).data('webuploader');
        var uploader = settings.uploader;
        if (uploader.getFiles().length > 0) {
            uploader.reset();
            uploader.addFiles(file);

            return false;
        }

        return true;
    }

    function change(file) {
        var settings = $(this).data('webuploader');
        var uploader = settings.uploader;
        var preview = settings.preview;
        var container = settings.container;
        var removeButton = settings.removeButton;
        var uploadButton = settings.uploadButton;

        if (preview.length > 0 && (typeof file.type !== "undefined" ? file.type.match(/^image\/(gif|png|jpeg)$/) : file.name.match(/\.(gif|png|jpe?g)$/i))) {
            uploader.makeThumb(file, function (error, ret) {
                if (error) {
                    preview.html('Preview image error.<br />' + file.name);
                } else {
                    var img = $('<img>');
                    img[0].src = ret;
                    if (preview.css('max-height') != 'none') {
                        img.css('max-height', parseInt(preview.css('max-height'), 10) - parseInt(preview.css('padding-top'), 10) - parseInt(preview.css('padding-bottom'), 10) - parseInt(preview.css('border-top'), 10) - parseInt(preview.css('border-bottom'), 10))
                    }
                    preview.html(img);
                }
            }, 1, 1);
        }
        container.find(settings.filenameClass).text(file.name);
        container.addClass(filterClassName(settings.fileExistsClass))
            .removeClass(filterClassName(settings.fileEmptyClass))
            .removeClass(filterClassName(settings.fileUploadClass));

        if (!$(removeButton).data('ladda')) {
            $(removeButton).data('ladda', Ladda.create($(removeButton)[0]));
        }
        if (!$(uploadButton).data('ladda')) {
            $(uploadButton).data('ladda', Ladda.create($(uploadButton)[0]));
        }

        if (removeButton.length > 0) {
            removeButton.off('click.webuploader').on('click.webuploader', function (e) {
                e.preventDefault();

                if (!$(this).data('ladda').isLoading()) {
                    uploader.removeFile(file, true);
                }
                return false;
            });
        }
        
        if (uploadButton.length > 0) {
            uploadButton.off('click.webuploader').on('click.webuploader', function (e) {
                e.preventDefault();
                if (!$(this).data('ladda').isLoading()) {
                    uploader.upload(file);
                }

                return false;
            });
        }
    }

    function uploadSuccess(file, response) {
        var settings = $(this).data('webuploader');
        var container = settings.container;
        var input = settings.input;
        if (response.status == 200) {
            container.addClass(filterClassName(settings.fileUploadClass)).removeClass(filterClassName(settings.fileExistsClass));
            input.val(response.data).trigger('change');
        } else {
            console.log(response);
        }
    }

    function uploadComplete(file) {
        var settings = $(this).data('webuploader');
        var container = settings.container;
        var uploadButton = settings.uploadButton;
        var removeButton = settings.removeButton;

        $(uploadButton).data('ladda').stop();
        $(removeButton).data('ladda').stop();
    }

    function uploadStart() {
        var settings = $(this).data('webuploader');
        var container = settings.container;
        var uploadButton = settings.uploadButton;
        var removeButton = settings.removeButton;

        $(uploadButton).data('ladda').start();
        $(removeButton).data('ladda').start();
    }

    function uploadProgress(file, percentage) {
        var settings = $(this).data('webuploader');
        var container = settings.container;
        var uploadButton = settings.uploadButton;
        var removeButton = settings.removeButton;
        uploadButton.data('ladda').setProgress(percentage);
        removeButton.data('ladda').setProgress(percentage);
    }

    function removeFile() {
        var settings = $(this).data('webuploader');
        var preview = settings.preview;
        var container = settings.container;
        var input = settings.input;

        container.find(settings.filenameClass).text('');
        container.addClass(filterClassName(settings.fileEmptyClass))
            .removeClass(filterClassName(settings.fileExistsClass))
            .removeClass(filterClassName(settings.fileUploadClass));
        input.val('').trigger('change');

        preview.length > 0 && preview.html('');

    }

    function filterClassName(className) {
        return className.replace(/^\./, '');
    }


}(window.jQuery);
