/**
* Hide the optgroups that are not the selected timezone
*
* @param	bool	keep_selection		Shall we keep the value selected, or shall the user be forced to repick one.
*/
function phpbb_switch_tz_date(keep_selection)
{
	$('#timezone > optgroup').css("display", "none");
	$("#timezone > optgroup[label='" + $('#tz_date').val() + "']").css("display", "block");

	if ($("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option").size() == 1)
	{
		// If there is only one timezone for the selected date, we just select that automatically.
		$("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option:first").attr("selected", true);
		keep_selection = true;
	}

	if (typeof keep_selection !== 'undefined')
	{
		if (!keep_selection)
		{
			$('#timezone > option:first').attr("selected", true);
		}
	}
}

/**
* Display the date/time select
*/
function phpbb_enable_tz_dates()
{
	$('#tz_select_date').css("display", "block");
}

phpbb_enable_tz_dates();
