/**
 * Closure to handled image specific uploads
 * @param file
 * @param preview
 */
var $handleImage = function (file, preview) {
    var reader = new FileReader();
    reader.onload = function (e) {
        var img = new Image();
        img.src = e.target.result;
        img.onload = function () {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            img.width = 128;
            img.height = 128;
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0, img.width, img.height);

            var upload = document.createElement('div');
            upload.className = 'boltforms-preview-image';
            upload.appendChild(img);

            preview.appendChild(upload);
        }
    };
    reader.readAsDataURL(file);
};

/**
 * Handler for file uploads.
 *
 * @param files
 * @param preview
 */
function handleFiles(files, preview) {
    preview = document.getElementById(preview);
    preview.innerHTML = '';

    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var imageType = /^image\//;

        if (imageType.test(file.type)) {
            $handleImage(file, preview);
        } else {
            console.debug(file.type);
        }
    }
}


/**
 * Polyfill for browsers that don't support element.closest()
 * We need to use this to find the relevant form for a recaptcha-protected submit button.
 *
 */

if (window.Element && !Element.prototype.closest) {
    Element.prototype.closest =
        function(s) {
            var matches = (this.document || this.ownerDocument).querySelectorAll(s),
                i,
                el = this;
            do {
                i = matches.length;
                while (--i >= 0 && matches.item(i) !== el) {};
            } while ((i < 0) && (el = el.parentElement));
            return el;
        };
}

(function () {
    var els = document.getElementsByClassName('g-recaptcha-button');
    for (var i = 0; i < els.length; ++i) {
        grecaptcha.render(els[i], {
            sitekey: els[i].getAttribute('data-sitekey'),
            size: 'invisible',
            callback: function(token) {
                if (token) {
                    els[i].closest('form').submit();
                }
            }
        });
    }
})()
