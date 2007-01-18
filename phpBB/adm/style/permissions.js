
/**
* Check whether we have a full radiobutton row of true
* index = offset for the row of inputs (0 == first row, 1 == second, 2 == third),
* rb = array of radiobuttons
*/
function get_radio_status(index, rb) 
{
	for (var i = index; i < rb.length; i = i + 3 )
	{
		if (rb[i].checked != true)
		{
			if (i > index)
			{
				//at least one is true, but not all (custom)
				return 2;
			}
			//first one is not true
			return 0;
		}
	}

	// all radiobuttons true
	return 1;
}

/**
* Set tab colours
* id = panel the tab needs to be set for, 
* init = initialising on open, 
* quick = If no calculation needed, this contains the colour
*/
function set_colours(id, init, quick)
{
	var table = document.getElementById('table' + id);
	var tab = document.getElementById('tab' + id);

	if (typeof(quick) != 'undefined') 
	{
		tab.className = 'perm_preset_' + quick + ' activetab';
		return;
	}

	var rb = table.getElementsByTagName('input');
	var colour = 'custom';

	var status = get_radio_status(0, rb);

	if (status == 1)
	{
		colour = 'yes';
	}
	else if (status == 0) 
	{
		// We move on to No
		status = get_radio_status(1, rb);

		if (status == 1)
		{
			colour = 'no';
		}
		else if (status == 0) 
		{
			// We move on to Mever
			status = get_radio_status(2, rb);

			if (status == 1)
			{
				colour = 'never';
			}
		}
	}

	if (init)
	{
		tab.className = 'perm_preset_' + colour;
	}
	else
	{
		tab.className = 'perm_preset_' + colour + ' activetab';
	}
}

/**
* Initialise advanced tab colours on first load
* block_id = block that is opened
*/
function init_colours(block_id)
{	
	var block = document.getElementById('advanced' + block_id);
	var panels = block.getElementsByTagName('div');
	var tab = document.getElementById('tab' + id);

	for (var i = 0; i < panels.length; i++)
	{
		if(panels[i].className == 'perm_panel')
		{
			set_colours(panels[i].id.replace(/options/, ''), true);
		}
	}

	tab.className = tab.className + ' activetab';
}

/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swap_options(pmask, fmask, cat, adv, view)
{
	id = pmask + fmask + cat;
	active_option = active_pmask + active_fmask + active_cat;

	var	old_tab = document.getElementById('tab' + active_option);	
	var new_tab = document.getElementById('tab' + id);
	var adv_block = document.getElementById('advanced' + pmask + fmask);

	if (adv_block.style.display == 'block' && adv == true)
	{
		dE('advanced' + pmask + fmask, -1);
		document.getElementById('checkbox' + pmask + fmask).style.display = 'inline';
		return;
	}

	// init colours
	if (adv)
	{
		dE('checkbox' + pmask + fmask, -1);
		init_colours(pmask + fmask);
	}

	// no need to set anything if we are clicking on the same tab again
	if (new_tab == old_tab && !adv)
	{
		return;
	}

	// set active tab
	old_tab.className = old_tab.className.replace(/\ activetab/g, '');
	new_tab.className = new_tab.className + ' activetab';

	if (id == active_option && adv != true)
	{
		return;
	}

	dE('options' + active_option, -1);

	if (!view)
	{
		dE('advanced' + active_pmask + active_fmask, -1);
	}

	if (!view)
	{
		dE('advanced' + pmask + fmask, 1);
	}
	dE('options' + id, 1);

	active_pmask = pmask;
	active_fmask = fmask;
	active_cat = cat;
}

/**
* Mark all radio buttons in one panel
* id = table ID container, s = status ['y'/'u'/'n']
*/
function mark_options(id, s)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].id.substr(rb[r].id.length-1) == s)
		{
			rb[r].checked = true;
		}
	}
}

function mark_one_option(id, field_name, s)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].id.substr(rb[r].id.length-field_name.length-3, field_name.length) == field_name && rb[r].id.substr(rb[r].id.length-1) == s)
		{
			rb[r].checked = true;
		}
	}
}

/**
* Reset role dropdown field to Select role... if an option gets changed
*/
function reset_role(id)
{
	var t = document.getElementById(id);

	if (!t)
	{
		return;
	}

	t.options[0].selected = true;
}

/**
* Load role and set options accordingly
*/
function set_role_settings(role_id, target_id)
{
	settings = role_options[role_id];

	if (!settings)
	{
		return;
	}

	// Mark all options to no (unset) first...
	mark_options(target_id, 'u');

	for (var r in settings)
	{
		mark_one_option(target_id, r, (settings[r] == 1) ? 'y' : 'n');
	}
}
