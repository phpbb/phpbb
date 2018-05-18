/* global phpbb */

(function($) { // Avoid conflicts with other libraries

'use strict';

$('#tz_date').change(function() {
	phpbb.timezoneSwitchDate(false);
});

$('#tz_select_date_suggest').click(function(){
	phpbb.timezonePreselectSelect(true);
});

$(function () {
	phpbb.timezoneEnableDateSelection();
	phpbb.timezonePreselectSelect($('#tz_select_date_suggest').attr('timezone-preselect') === 'true');
});

})(jQuery); // Avoid conflicts with other libraries
