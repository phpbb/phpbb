/* global phpbb */

(function($) { // Avoid conflicts with other libraries
	'use strict';

	$('#tz_date').change(() => {
		phpbb.timezoneSwitchDate(false);
	});

	$('#tz_select_date_suggest').click(() => {
		phpbb.timezonePreselectSelect(true);
	});

	$(() => {
		phpbb.timezoneEnableDateSelection();
		phpbb.timezonePreselectSelect($('#tz_select_date_suggest').attr('timezone-preselect') === 'true');
	});
})(jQuery); // Avoid conflicts with other libraries
