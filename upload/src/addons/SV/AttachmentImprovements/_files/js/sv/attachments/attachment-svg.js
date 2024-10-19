(function ( ) {
    "use strict";
    const oldResize  = XF.ImageTools.resize;
    XF.ImageTools.resize =  function(file, maxWidth, maxHeight, asType) {
        if (file.type === 'image/svg+xml') {
            return new Promise((resolve, reject) =>
            {
                reject(new Error('The file is an SVG.'));
            });
        }
        return oldResize(file, maxWidth, maxHeight, asType);
    };
})();