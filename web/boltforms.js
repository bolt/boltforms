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

            preview.appendChild(img);
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
