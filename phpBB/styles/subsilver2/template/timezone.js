(function($) { // Avoid conflicts with other libraries

$('#tz_date').change(function() {
	phpbb.timezone_switch_date(false);
});

$('#tz_select_date_suggest').click(function(){
	phpbb.timezone_preselect_select(true);
});

$(document).ready(
	phpbb.timezone_enable_date_selection
);

$(document).ready(
	phpbb.timezone_preselect_select($('#tz_select_date_suggest').attr('data-is-registration') == 'true')
);

})(jQuery); // Avoid conflicts with other libraries
