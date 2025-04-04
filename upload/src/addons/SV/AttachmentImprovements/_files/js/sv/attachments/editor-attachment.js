// noinspection ES6ConvertVarToLetConst
var SV = window.SV || {};
// XF22 compat shim
SV.$ = SV.$ || window.jQuery || null;

(function ( document) {
    "use strict";
    // XF2.2 compat
    var $ = SV.$;
    SV.attachmentHoverUi = function(editor) {
        let dropzoneCounter = 0;
        let template = '';
        function skipDragOperation(e) {
            let c = e.originalEvent.dataTransfer;
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
                let container = e.originalEvent.target.closest('.fr-box');
                if (!container) {
                    return;
                }
                container.classList.add('dragover');

                if (template !== '') {
                    let hoverZone = container.querySelector(':scope .dropzone-hover');
                    if (!hoverZone) {
                        XF.setupHtmlInsert(template, function (html) {
                            if (XF.FE) {
                                editor.$wp.append(html);
                            } else {
                                $(editor.$wp).append(html);
                            }
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
            let container = e.originalEvent.target.closest('.fr-box');
            if (!container) {
                return;
            }
            let editorBox = container.closest('.fr-box');
            if (editorBox) {
                editorBox.classList.remove('dragover');
            }
            // remove rather than hide, as this prevents XF's html => bb-code parser getting confused. and also the editor itself
            let hoverZone = container.querySelector(':scope .dropzone-hover');
            if (hoverZone) {
                hoverZone.remove();
            }
        }
        return {
            _init: function() {
                let templateElement = document.querySelector('.js-attachmentDragHoverTemplate');
                if (templateElement) {
                    template = templateElement.innerHTML || '';
                }
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

    if (typeof XF.on !== "function") { // XF 2.2
        $(document).on('editor:first-start', function() {
            $.FE.PLUGINS.attachmentHoverUi = SV.attachmentHoverUi;
        });
    } else {
        XF.on(document, 'editor:first-start', function() {
            XF.FE.PLUGINS.attachmentHoverUi = SV.attachmentHoverUi;
        });
    }

})(document);
