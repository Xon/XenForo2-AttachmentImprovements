var SV = window.SV || {};
(function ($) {
    SV.attachmentHoverUi = function(editor) {
        var dropzoneCounter = 0;
        var template = '';
        function skipDragOperation(e) {
            var c = e.originalEvent.dataTransfer;
            if (!c.types || 1 !== c.types.length || "Files" !== c.types[0] || c.dropEffect === 'none') {
                return true;
            }
            if (c.dropEffect === 'move') {
                c.dropEffect = 'copy';
            }
            return false;
        }
        function dragenter(e) {
            if (skipDragOperation(e)) {
                return;
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
        function dragover(e) {
            if (dropzoneCounter === 0) {
                return;
            }
            skipDragOperation(e);
        }
        function dragleave(e) {
            if (dropzoneCounter === 0) {
                return;
            }
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
                // the draggable plugin forces 'move' rather than 'copy' which behaves very badly with some source programs
                editor.events.on("dragenter", dragenter);
                editor.events.on("dragover", dragover);
                editor.events.on("dragleave", dragleave);
                editor.events.on("drop", dragdrop, 1);
                editor.events.on("document.dragend", dragdrop, 1);
                editor.events.on("document.drop", dragdrop, 1);
            }
        }
    };

    $(document).on('editor:first-start', function() {
        $.FE.PLUGINS.attachmentHoverUi = SV.attachmentHoverUi;
    });
})(jQuery);
