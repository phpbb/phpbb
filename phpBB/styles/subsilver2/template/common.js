(function($) {  // Avoid conflicts with other libraries

"use strict";

// Toggle notification list
$('#notification_list_button').click(function(e) {
	$('#notification_list').toggle();
	e.preventDefault();
});
$(document).click(function(e) {
    var target = e.target;

    if (!$(target).is('#notification_list') && !$(target).is('#notification_list_button') && !$(target).parents().is('#notification_list')) {
        $('#notification_list').hide();
    }
});

})(jQuery); // Avoid conflicts with other libraries
