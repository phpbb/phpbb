/**
 * phpBB Plupload EXIF/JPEG compatibility layer.
 *
 * Purpose:
 * - Keep phpBB 3.3.17's original assets/plupload/plupload.full.min.js untouched.
 * - Fix modern-browser EXIF orientation handling without editing the bundled vendor file.
 *
 * Must be loaded AFTER plupload.full.min.js and BEFORE assets/javascript/plupload.js.
 */
(function (window) {
    'use strict';

    // Preserve the behaviour of the previous custom build: modern browsers are
    // treated as already applying EXIF orientation while decoding JPEG images.
    window.browserRotates = true;

    function byteAt(binary, offset) {
        return binary.charCodeAt(offset) & 255;
    }

    /**
     * Reset EXIF Orientation (IFD0 tag 0x0112) to 1 in a JPEG binary string.
     * Malformed/unsupported metadata is left untouched.
     */
    function normalizeExifOrientation(binary) {
        if (typeof binary !== 'string' || binary.length < 12 ||
            byteAt(binary, 0) !== 0xFF || byteAt(binary, 1) !== 0xD8) {
            return binary;
        }

        var position = 2;

        while (position + 4 <= binary.length) {
            if (byteAt(binary, position) !== 0xFF) {
                position++;
                continue;
            }

            var marker = byteAt(binary, position + 1);

            // Start Of Scan / End Of Image: no more metadata headers follow.
            if (marker === 0xDA || marker === 0xD9) {
                break;
            }

            // Standalone markers have no length field.
            if (marker === 0xD8 || marker === 0x01 ||
                (marker >= 0xD0 && marker <= 0xD7)) {
                position += 2;
                continue;
            }

            var segmentLength = (byteAt(binary, position + 2) << 8) |
                byteAt(binary, position + 3);

            if (segmentLength < 2 || position + 2 + segmentLength > binary.length) {
                break;
            }

            var payload = position + 4;

            if (marker === 0xE1 &&
                payload + 14 <= binary.length &&
                binary.substr(payload, 6) === 'Exif\x00\x00') {

                var tiff = payload + 6;
                var littleEndian =
                    byteAt(binary, tiff) === 0x49 && byteAt(binary, tiff + 1) === 0x49;
                var bigEndian =
                    byteAt(binary, tiff) === 0x4D && byteAt(binary, tiff + 1) === 0x4D;

                if (!littleEndian && !bigEndian) {
                    return binary;
                }

                function read16(offset) {
                    return littleEndian
                        ? byteAt(binary, offset) | (byteAt(binary, offset + 1) << 8)
                        : (byteAt(binary, offset) << 8) | byteAt(binary, offset + 1);
                }

                function read32(offset) {
                    if (littleEndian) {
                        return (
                            byteAt(binary, offset) |
                            (byteAt(binary, offset + 1) << 8) |
                            (byteAt(binary, offset + 2) << 16) |
                            (byteAt(binary, offset + 3) << 24)
                        ) >>> 0;
                    }

                    return (
                        (byteAt(binary, offset) << 24) |
                        (byteAt(binary, offset + 1) << 16) |
                        (byteAt(binary, offset + 2) << 8) |
                        byteAt(binary, offset + 3)
                    ) >>> 0;
                }

                if (tiff + 8 > binary.length || read16(tiff + 2) !== 42) {
                    return binary;
                }

                var ifd0Offset = read32(tiff + 4);
                var ifd0 = tiff + ifd0Offset;

                if (ifd0Offset > binary.length || ifd0 + 2 > binary.length) {
                    return binary;
                }

                var entries = read16(ifd0);

                for (var index = 0; index < entries; index++) {
                    var entry = ifd0 + 2 + index * 12;

                    if (entry + 12 > binary.length) {
                        return binary;
                    }

                    if (read16(entry) === 0x0112 &&
                        read16(entry + 2) === 3 &&
                        read32(entry + 4) === 1) {

                        var valuePosition = entry + 8;
                        var replacement = littleEndian ? '\x01\x00' : '\x00\x01';

                        return binary.substring(0, valuePosition) +
                            replacement +
                            binary.substring(valuePosition + 2);
                    }
                }

                return binary;
            }

            position += 2 + segmentLength;
        }

        return binary;
    }

    // Keep the helper globally available, matching the previous template's API.
    window.phpbbNormalizeExifOrientation = normalizeExifOrientation;

    function patchHtml5Runtime() {
        var moxie = window.moxie;

        if (!moxie || !moxie.runtime || !moxie.runtime.Runtime) {
            return;
        }

        var Runtime = moxie.runtime.Runtime;
        var OriginalHtml5Runtime = Runtime.getConstructor('html5');

        if (!OriginalHtml5Runtime || OriginalHtml5Runtime.__phpbbExifCompatWrapped) {
            return;
        }

        function PhpbbHtml5Runtime() {
            // Build the completely stock HTML5 runtime first.
            OriginalHtml5Runtime.apply(this, arguments);

            var shim = this.getShim && this.getShim();
            var OriginalImage = shim && shim.Image;

            if (!OriginalImage) {
                return;
            }

            function PhpbbHtml5Image() {
                // Build the completely stock HTML5 image extension first.
                OriginalImage.apply(this, arguments);

                var originalResize = this.resize;
                var originalGetAsBinaryString = this.getAsBinaryString;
                var didResize = false;
                var preserveHeaders = true;

                if (typeof originalResize === 'function') {
                    this.resize = function (rect, ratio, options) {
                        didResize = true;
                        preserveHeaders = !options || options.preserveHeaders !== false;

                        var tiff = this.meta && this.meta.tiff;

                        // Stock mOxie rotates again when preserveHeaders=false.
                        // The old custom build bypassed that transform because the
                        // browser had already oriented the decoded pixels. Feeding
                        // Orientation=1 only for the stock resize call gives the same
                        // result without editing the vendor library.
                        if (window.browserRotates === true && !preserveHeaders && tiff) {
                            var hadOrientation = Object.prototype.hasOwnProperty.call(tiff, 'Orientation');
                            var originalOrientation = tiff.Orientation;
                            tiff.Orientation = 1;

                            try {
                                return originalResize.apply(this, arguments);
                            } finally {
                                if (hadOrientation) {
                                    tiff.Orientation = originalOrientation;
                                } else {
                                    delete tiff.Orientation;
                                }
                            }
                        }

                        return originalResize.apply(this, arguments);
                    };
                }

                if (typeof originalGetAsBinaryString === 'function') {
                    this.getAsBinaryString = function (type, quality) {
                        var binary = originalGetAsBinaryString.apply(this, arguments);
                        var jpeg = (type || this.type) === 'image/jpeg';

                        if (!didResize || !jpeg || typeof binary !== 'string') {
                            return binary;
                        }

                        // With preserved headers, the old custom build made the
                        // re-encoded upright pixels explicitly Orientation=1.
                        if (window.browserRotates === true && preserveHeaders) {
                            binary = normalizeExifOrientation(binary);
                        }

                        return binary;
                    };
                }
            }

            shim.Image = PhpbbHtml5Image;
        }

        PhpbbHtml5Runtime.__phpbbExifCompatWrapped = true;
        Runtime.addConstructor('html5', PhpbbHtml5Runtime);
    }

    patchHtml5Runtime();
}(window));
