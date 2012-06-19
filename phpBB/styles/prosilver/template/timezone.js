(function($) { // Avoid conflicts with other libraries

/**
* Hide the optgroups that are not the selected timezone
*
* @param	bool	keep_selection		Shall we keep the value selected, or shall the user be forced to repick one.
*/
phpbb.timezone_switch_date = function(keep_selection) {
	$('#timezone > optgroup').css('display', 'none');
	$("#timezone > optgroup[label='" + $('#tz_date').val() + "']").css('display', 'block');

	if ($("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option").size() == 1) {
		// If there is only one timezone for the selected date, we just select that automatically.
		$("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option:first").attr('selected', true);
		keep_selection = true;
	}

	if (typeof keep_selection !== 'undefined' && !keep_selection) {
		$('#timezone > option:first').attr('selected', true);
	}
}

/**
* Display the date/time select
*/
phpbb.timezone_enable_date_selection = function() {
	$('#tz_select_date').css('display', 'block');
}

/**
* Preselect a date/time or suggest one, if it is not picked.
*
* @param	bool	force_selector		Shall we select the suggestion?
*/
phpbb.timezone_preselect_select = function(force_selector) {

	// The offset returned here is in minutes and negated.
	// http://www.w3schools.com/jsref/jsref_getTimezoneOffset.asp
	var offset = (new Date()).getTimezoneOffset();

	if (offset < 0) {
		var sign = '+';
		offset = -offset;
	} else {
		var sign = '-';
	}

	var minutes = offset % 60;
	var hours = (offset - minutes) / 60;

	if (hours < 10) {
		hours = '0' + hours.toString();
	} else {
		hours = hours.toString();
	}

	if (minutes < 10) {
		minutes = '0' + minutes.toString();
	} else {
		minutes = minutes.toString();
	}

	var prefix = 'GMT' + sign + hours + ':' + minutes;
	var prefix_length = prefix.length;
	var selector_options = $('#tz_date > option');

	for (var i = 0; i < selector_options.length; ++i) {
		var option = selector_options[i];

		if (option.value.substring(0, prefix_length) == prefix) {
			if ($('#tz_date').val() != option.value && !force_selector) {
				// We do not select the option for the user, but notify him,
				// that we would suggest a different setting.
				$('#tz_select_date_suggest').css('display', 'inline');
				$('#tz_select_date_suggest').attr('title', $('#tz_select_date_suggest').attr('data-l-suggestion').replace("%s", option.innerHTML));
				$('#tz_select_date_suggest').attr('value', $('#tz_select_date_suggest').attr('data-l-suggestion').replace("%s", option.innerHTML.substring(0, 9)));
				phpbb.timezone_switch_date(true);
			} else {
				option.selected = true;
				phpbb.timezone_switch_date(!force_selector);
				$('#tz_select_date_suggest').css('display', 'none');
			}
			break;
		}
	}
}

})(jQuery); // Avoid conflicts with other libraries

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
