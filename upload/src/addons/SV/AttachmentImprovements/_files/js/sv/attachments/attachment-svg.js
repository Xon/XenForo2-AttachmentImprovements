// noinspection ES6ConvertVarToLetConst
var SV = window.SV || {};
// XF22 compat shim
SV.$ = SV.$ || window.jQuery || null;

(function (document, window) {
    "use strict";
    // XF2.2 compat
    const $ = SV.$,
        xf22 = typeof XF.on !== 'function',
        on = xf22 ? function (element, namespacedEvent, handler) {
            $(element).on(namespacedEvent, handler);
        } : XF.on;

    if (!xf22) {
        const oldResize = XF.ImageTools.resize.bind(XF.ImageTools);
        XF.ImageTools.resize = function (file, maxWidth, maxHeight, asType) {
            if (file.type === 'image/svg+xml') {
                return new Promise((resolve, reject) => {
                    reject(new Error('The file is an SVG.'));
                });
            }
            return oldResize(file, maxWidth, maxHeight, asType);
        };
    }

    on(document, 'editor:first-start', function() {
        // note; when editing posts, the editor.js is loaded *after* the attachment manager
        const FE = ( $ ? $.FE : null ) || window.FroalaEditor || XF.FE;
        FE.IMAGE_ALLOW_REGEX = /\.(gif|jpe?g|tiff?|png|webp|bmp|svg)$/i;
        FE.DEFAULTS.imageAllowedTypes.push('svg+xml');
    });
})(document, window);