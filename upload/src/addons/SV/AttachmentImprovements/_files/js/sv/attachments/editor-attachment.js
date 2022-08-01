(function ($) {
    $.FE.PLUGINS.attachmentHoverUi = function(editor) {

        var dropzoneCounter = 0;
        var template = '';
        function dragenter(e) {
            if (e.originalEvent.dataTransfer.dropEffect === 'move') {
                e.originalEvent.dataTransfer.dropEffect = 'copy';
            }
            dropzoneCounter++;
            if (dropzoneCounter === 1) {
                var $container = $(e.originalEvent.target).closest('.fr-box');
                $container.addClass('dragover');

                if (template !== '') {
                    var $hoverZone = $container.find('.dropzone-hover');
                    if ($hoverZone.length === 0) {
                        XF.setupHtmlInsert(template, function ($html) {
                            $(editor.$wp).append($html);
                        });
                    }
                }
            }
        }
        function dragleave(e) {
            dropzoneCounter--;
            if (dropzoneCounter <= 0) {
                dragdrop(e);
            }
        }
        function dragdrop(e) {
            dropzoneCounter = 0;
            var $container = $(e.originalEvent.target).closest('.fr-box');
            $container.closest('.fr-box').removeClass('dragover');
            // remove rather than hide, as this prevents XF's html => bb-code parser getting confused. and also the editor itself
            $container.find('.dropzone-hover').remove();
        }
        return {
            _init: function() {
                template = $('.js-attachmentDragHoverTemplate').html() || '';
                editor.events.on("dragenter", dragenter);
                editor.events.on("dragleave", dragleave);
                editor.events.on("drop", dragdrop, 1);
                editor.events.on("document.dragend", dragdrop, 1);
                editor.events.on("document.drop", dragdrop, 1);
            }
        }
    }
})(jQuery);
