(function($) {  // Avoid conflicts with other libraries

"use strict";

function avatarHide() {
    $('.[class^="avatar_option_"]').hide();

    var selected = $('#avatar_driver').val();
    $('.avatar_option_' + selected).show();
}

avatarHide();
$('#avatar_driver').bind('change', avatarHide);

})(jQuery); // Avoid conflicts with other libraries
