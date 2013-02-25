(function($) { // Avoid conflicts with other libraries

$('#tz_date').change(function() {
	phpbb.timezoneSwitchDate(false);
});

$('#tz_select_date_suggest').click(function(){
	phpbb.timezonePreselectSelect(true);
});

$(document).ready(
	phpbb.timezoneEnableDateSelection
);

$(document).ready(
	phpbb.timezonePreselectSelect($('#tz_select_date_suggest').attr('data-is-registration') == 'true')
);

})(jQuery); // Avoid conflicts with other libraries
