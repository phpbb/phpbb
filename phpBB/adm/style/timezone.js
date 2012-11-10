(function($) { // Avoid conflicts with other libraries

$('#tz_date').change(function() {
	phpbb.timezone_switch_date(false);
});

$(document).ready(
	phpbb.timezone_enable_date_selection
);

})(jQuery); // Avoid conflicts with other libraries
