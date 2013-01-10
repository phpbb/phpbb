(function($) { // Avoid conflicts with other libraries

"use strict";

$('#tz_date').change(function() {
	phpbb.timezoneSwitchDate(false);
});

$(document).ready(
	phpbb.timezoneEnableDateSelection
);

})(jQuery); // Avoid conflicts with other libraries
