function base64toBlobUrl(string) {
    blob = window.dataURLtoBlob(string);
    return URL.createObjectURL(blob);
}

function base64toBlobObject(base64Data, contentType) {
    contentType = contentType || '';
    var replaseString = 'data:' + contentType + ';base64,';
    replaseString = replaseString.replace(" ", "");
    base64Data = base64Data.replace(replaseString, "");
    var sliceSize = 1024;
    var byteCharacters = atob(base64Data);
    var bytesLength = byteCharacters.length;
    var slicesCount = Math.ceil(bytesLength / sliceSize);
    var byteArrays = new Array(slicesCount);

    for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {
        var begin = sliceIndex * sliceSize;
        var end = Math.min(begin + sliceSize, bytesLength);

        var bytes = new Array(end - begin);
        for (var offset = begin, i = 0; offset < end; ++i, ++offset) {
            bytes[i] = byteCharacters[offset].charCodeAt(0);
        }
        byteArrays[sliceIndex] = new Uint8Array(bytes);
    }
    return new Blob(byteArrays, {type: contentType});
}

function resize_image(src, type, quality, max_width, max_height, switchCase) {
    var tmp = new Image(),
        canvas, ctx, oc, octx, width, height;
    type = type || 'image/jpeg';
    quality = quality || 0.92;
    tmp.src = src;
    max_width = max_width || 1024;
    max_height = max_height || 1024;
    $(tmp).load(function () {
        width = tmp.width;
        height = tmp.height;
        canvas = document.createElement("canvas");
        ctx = canvas.getContext("2d");

        if (width > height) {
            if (width > max_width) {
                height *= max_width / width;
                width = max_width;
            }
        } else {
            if (height > max_height) {
                width *= max_height / height;
                height = max_height;
            }
        }

        canvas.width = width;
        canvas.height = height;

        oc = document.createElement('canvas');
        octx = oc.getContext('2d');

        oc.width = width;
        oc.height = height;
        octx.drawImage(tmp, 0, 0, width, height);

        octx.drawImage(oc, 0, 0, width, height);

        ctx.drawImage(oc, 0, 0, width, height,
            0, 0, canvas.width, canvas.height);
        if (switchCase === 0)
            crop(canvas.toDataURL(type, quality));
        else
            addImage(canvas.toDataURL(type, quality));
    });
}