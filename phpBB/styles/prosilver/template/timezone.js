function phpbb_preselect_tz_select()
{
	var selector = document.getElementsByClassName('tz_select')[0];
	if (selector.value)
	{
		return;
	}
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
			// Firefox scrolls the selector only to put the option into view;
			// for negative-offset timezones, this means the first timezone
			// of a particular offset will be the bottom one, and selected,
			// with all other timezones not visible. Not much can be done
			// about that here unfortunately.
			option.selected = true;
			break;
		}
	}
}
