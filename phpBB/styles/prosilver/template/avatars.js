(function($) {  // Avoid conflicts with other libraries

"use strict";

function avatar_hide() {
    $('#avatar_options > div').hide();

    var selected = $('#avatar_driver').val();
    $('#avatar_option_' + selected).show();
}

avatar_hide();
$('#avatar_driver').bind('change', avatar_hide);

})(jQuery); // Avoid conflicts with other libraries
