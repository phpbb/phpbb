(function($) {  // Avoid conflicts with other libraries

"use strict";

function avatar_simplify() {
    $('.[class^="av_option_"]').hide();

    var selected = $('#avatar_driver').val();
    $('.av_option_' + selected).show();
}

avatar_simplify();
$('#avatar_driver').bind('change', avatar_simplify);

})(jQuery); // Avoid conflicts with other libraries
