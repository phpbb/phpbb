/**
 * phpBB Plupload JPEG APP4 compatibility layer.
 *
 * Prevents APP4 metadata segments from being copied back into JPEG files that
 * have been re-encoded by the bundled Plupload/mOxie HTML5 image resizer.
 *
 * Must be loaded AFTER plupload.full.min.js and BEFORE assets/javascript/plupload.js.
 */
(function (window) {
    'use strict';

    function byteAt(binary, offset) {
        return binary.charCodeAt(offset) & 255;
    }

    function stripJpegApp4(binary) {
        if (typeof binary !== 'string' || binary.length < 4 ||
            byteAt(binary, 0) !== 0xFF || byteAt(binary, 1) !== 0xD8) {
            return binary;
        }

        var output = binary.substring(0, 2);
        var position = 2;

        while (position < binary.length) {
            var markerStart = position;

            if (byteAt(binary, position) !== 0xFF) {
                return binary;
            }

            while (position < binary.length && byteAt(binary, position) === 0xFF) {
                position++;
            }

            if (position >= binary.length) {
                return binary;
            }

            var marker = byteAt(binary, position);
            var codePosition = position;
            position++;

            if (marker === 0x00) {
                return binary;
            }

            if (marker === 0xDA) {
                output += binary.substring(markerStart);
                return output;
            }

            if (marker === 0xD9 || marker === 0xD8 || marker === 0x01 ||
                (marker >= 0xD0 && marker <= 0xD7)) {
                output += binary.substring(markerStart, position);
                if (marker === 0xD9) {
                    output += binary.substring(position);
                    return output;
                }
                continue;
            }

            if (codePosition + 2 >= binary.length) {
                return binary;
            }

            var segmentLength = (byteAt(binary, codePosition + 1) << 8) |
                byteAt(binary, codePosition + 2);

            if (segmentLength < 2) {
                return binary;
            }

            var segmentEnd = codePosition + 1 + segmentLength;

            if (segmentEnd > binary.length) {
                return binary;
            }

            if (marker !== 0xE4) {
                output += binary.substring(markerStart, segmentEnd);
            }

            position = segmentEnd;
        }

        return output;
    }

    function patchHtml5Runtime() {
        var moxie = window.moxie;

        if (!moxie || !moxie.runtime || !moxie.runtime.Runtime) {
            return;
        }

        var Runtime = moxie.runtime.Runtime;
        var OriginalHtml5Runtime = Runtime.getConstructor('html5');

        if (!OriginalHtml5Runtime || OriginalHtml5Runtime.__phpbbApp4CompatWrapped) {
            return;
        }

        function PhpbbHtml5Runtime() {
            OriginalHtml5Runtime.apply(this, arguments);

            var shim = this.getShim && this.getShim();
            var OriginalImage = shim && shim.Image;

            if (!OriginalImage) {
                return;
            }

            function PhpbbHtml5Image() {
                OriginalImage.apply(this, arguments);

                var originalResize = this.resize;
                var originalGetAsBinaryString = this.getAsBinaryString;
                var didResize = false;

                if (typeof originalResize === 'function') {
                    this.resize = function () {
                        didResize = true;
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

                        return stripJpegApp4(binary);
                    };
                }
            }

            shim.Image = PhpbbHtml5Image;
        }

        PhpbbHtml5Runtime.__phpbbApp4CompatWrapped = true;
        Runtime.addConstructor('html5', PhpbbHtml5Runtime);
    }

    patchHtml5Runtime();
}(window));
