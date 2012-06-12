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

function phpbb_preselect_tz_select(force_selector, l_suggestion)
{

	var selector = document.getElementById('tz_date');
	// The offset returned here is in minutes and negated.
	// http://www.w3schools.com/jsref/jsref_getTimezoneOffset.asp
	var offset = (new Date()).getTimezoneOffset();
	if (offset < 0)
	{
		var sign = '+';
		offset = -offset;
	}
	else
	{
		var sign = '-';
	}
	var minutes = offset % 60;
	var hours = (offset - minutes) / 60;
	if (hours < 10)
	{
		hours = '0' + hours.toString();
	}
	else
	{
		hours = hours.toString();
	}
	if (minutes < 10)
	{
		minutes = '0' + minutes.toString();
	}
	else
	{
		minutes = minutes.toString();
	}
	var prefix = 'GMT' + sign + hours + ':' + minutes;
	var prefix_length = prefix.length;
	for (var i = 0; i < selector.options.length; ++i)
	{
		var option = selector.options[i];
		if (option.value.substring(0, prefix_length) == prefix)
		{
			if (selector.value && selector.value != option.value && !force_selector)
			{
				// We do not select the option for the user, but notify him,
				// that we would suggest a different setting.
				document.getElementById("tz_select_date_suggest").style.display = "inline";
				document.getElementById("tz_select_date_suggest").title = l_suggestion.replace("%s", option.innerHTML);
				document.getElementById("tz_select_date_suggest").innerHTML = l_suggestion.replace("%s", option.innerHTML.substring(0, 9));
				phpbb_switch_tz_date(true);
			}
			else
			{
				// Firefox scrolls the selector only to put the option into view;
				// for negative-offset timezones, this means the first timezone
				// of a particular offset will be the bottom one, and selected,
				// with all other timezones not visible. Not much can be done
				// about that here unfortunately.
				option.selected = true;
				phpbb_switch_tz_date(!force_selector);
				document.getElementById("tz_select_date_suggest").style.display = "none";
			}
			break;
		}
	}
}
