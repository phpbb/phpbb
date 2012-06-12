function phpbb_switch_tz_date(keep_selection)
{
	var timezone_groups = document.getElementById("timezone");
	for (var i = 0; i < timezone_groups.childElementCount; i++) {
		if (timezone_groups.children[i].tagName == "OPTGROUP" &&
			timezone_groups.children[i].label != document.getElementById("tz_date").value)
		{
			timezone_groups.children[i].style.display = "none";
		}
		else if (timezone_groups.children[i].tagName == "OPTGROUP")
		{
			// Display other options
			timezone_groups.children[i].style.display = "block";
		}
	}
	if (typeof keep_selection !== 'undefined')
	{
		if (!keep_selection)
		{
			timezone_groups.children[0].selected = true;
		}
	}
}

function phpbb_enable_tz_dates()
{
	var tz_select_date = document.getElementById("tz_select_date");
	tz_select_date.style.display = "block";
}
