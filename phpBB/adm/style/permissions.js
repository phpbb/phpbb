/**
* Hide and show all checkboxes
* status = true (show boxes), false (hide boxes)
*/
function display_checkboxes(status) {
	const form = document.getElementById('set-permissions');
	const cb = document.getElementsByTagName('input');
	let display;

	// Show
	if (status) {
		display = 'inline';
	}
	// Hide
	else {
		display = 'none';
	}

	for (let i = 0; i < cb.length; i++) {
		if (cb[i].className === 'permissions-checkbox') {
			cb[i].style.display = display;
		}
	}
}

/**
* Change opacity of element
* e = element
* value = 0 (hidden) till 10 (fully visible)
*/
function set_opacity(e, value) {
	e.style.opacity = value / 10;

	// IE opacity currently turned off, because of its astronomical stupidity
	// e.style.filter = 'alpha(opacity=' + value*10 + ')';
}

/**
* Reset the opacity and checkboxes
* block_id = id of the element that needs to be toggled
*/
function toggle_opacity(block_id) {
	const cb = document.getElementById('checkbox' + block_id);
	const fs = document.getElementById('perm' + block_id);

	if (cb.checked) {
		set_opacity(fs, 5);
	} else {
		set_opacity(fs, 10);
	}
}

/**
* Reset the opacity and checkboxes
* value = 0 (checked) and 1 (unchecked)
* except_id = id of the element not to hide
*/
function reset_opacity(status, except_id) {
	const perm = document.getElementById('set-permissions');
	const fs = perm.getElementsByTagName('fieldset');
	let opacity = 5;

	if (status) {
		opacity = 10;
	}

	for (let i = 0; i < fs.length; i++) {
		if (fs[i].className !== 'quick') {
			set_opacity(fs[i], opacity);
		}
	}

	if (typeof (except_id) !== 'undefined') {
		set_opacity(document.getElementById('perm' + except_id), 10);
	}

	// Reset checkboxes too
	marklist('set-permissions', 'inherit', !status);
}

/**
* Check whether we have a full radiobutton row of true
* index = offset for the row of inputs (0 == first row, 1 == second, 2 == third),
* rb = array of radiobuttons
*/
function get_radio_status(index, rb) {
	for (let i = index; i < rb.length; i += 3) {
		if (rb[i].checked !== true) {
			if (i > index) {
				// At least one is true, but not all (custom)
				return 2;
			}
			// First one is not true
			return 0;
		}
	}

	// All radiobuttons true
	return 1;
}

/**
* Set tab colours
* id = panel the tab needs to be set for,
* init = initialising on open,
* quick = If no calculation needed, this contains the colour
*/
function set_colours(id, init, quick) {
	const table = document.getElementById('table' + id);
	const tab = document.getElementById('tab' + id);

	if (typeof (quick) !== 'undefined') {
		tab.className = 'permissions-preset-' + quick + ' activetab';
		return;
	}

	const rb = table.getElementsByTagName('input');
	let colour = 'custom';

	let status = get_radio_status(0, rb);

	if (status === 1) {
		colour = 'yes';
	} else if (status === 0) {
		// We move on to No
		status = get_radio_status(1, rb);

		if (status === 1) {
			colour = 'no';
		} else if (status === 0) {
			// We move on to Never
			status = get_radio_status(2, rb);

			if (status === 1) {
				colour = 'never';
			}
		}
	}

	if (init) {
		tab.className = 'permissions-preset-' + colour;
	} else {
		tab.className = 'permissions-preset-' + colour + ' activetab';
	}
}

/**
* Initialise advanced tab colours on first load
* block_id = block that is opened
*/
function init_colours(block_id) {
	const block = document.getElementById('advanced' + block_id);
	const panels = block.getElementsByTagName('div');
	const tab = document.getElementById('tab' + id);

	for (let i = 0; i < panels.length; i++) {
		if (panels[i].className === 'permissions-panel') {
			set_colours(panels[i].id.replace(/options/, ''), true);
		}
	}

	tab.className += ' activetab';
}

/**
* Show/hide option panels
* value = suffix for ID to show
* adv = we are opening advanced permissions
* view = called from view permissions
*/
function swap_options(pmask, fmask, cat, adv, view) {
	id = pmask + fmask + cat;
	active_option = active_pmask + active_fmask + active_cat;

	const	old_tab = document.getElementById('tab' + active_option);
	const new_tab = document.getElementById('tab' + id);
	const adv_block = document.getElementById('advanced' + pmask + fmask);

	if (adv_block.style.display === 'block' && adv === true) {
		phpbb.toggleDisplay('advanced' + pmask + fmask, -1);
		reset_opacity(1);
		display_checkboxes(false);
		return;
	}

	// No need to set anything if we are clicking on the same tab again
	if (new_tab === old_tab && !adv) {
		return;
	}

	// Init colours
	if (adv && (pmask + fmask) !== (active_pmask + active_fmask)) {
		init_colours(pmask + fmask);
		display_checkboxes(true);
		reset_opacity(1);
	} else if (adv) {
		// Checkbox might have been clicked, but we need full visibility
		display_checkboxes(true);
		reset_opacity(1);
	}

	// Set active tab
	old_tab.className = old_tab.className.replace(/\ activetab/g, '');
	new_tab.className += ' activetab';

	if (id === active_option && adv !== true) {
		return;
	}

	phpbb.toggleDisplay('options' + active_option, -1);

	// Hiding and showing the checkbox
	if (document.getElementById('checkbox' + active_pmask + active_fmask)) {
		phpbb.toggleDisplay('checkbox' + pmask + fmask, -1);

		if ((pmask + fmask) !== (active_pmask + active_fmask)) {
			document.getElementById('checkbox' + active_pmask + active_fmask).style.display = 'inline';
		}
	}

	if (!view) {
		phpbb.toggleDisplay('advanced' + active_pmask + active_fmask, -1);
	}

	if (!view) {
		phpbb.toggleDisplay('advanced' + pmask + fmask, 1);
	}
	phpbb.toggleDisplay('options' + id, 1);

	active_pmask = pmask;
	active_fmask = fmask;
	active_cat = cat;
}

/**
* Mark all radio buttons in one panel
* id = table ID container, s = status ['y'/'u'/'n']
*/
function mark_options(id, s) {
	const t = document.getElementById(id);

	if (!t) {
		return;
	}

	const rb = t.getElementsByTagName('input');

	for (let r = 0; r < rb.length; r++) {
		if (rb[r].id.substr(rb[r].id.length - 1) === s) {
			rb[r].checked = true;
		}
	}
}

function mark_one_option(id, field_name, s) {
	const t = document.getElementById(id);

	if (!t) {
		return;
	}

	const rb = t.getElementsByTagName('input');

	for (let r = 0; r < rb.length; r++) {
		if (rb[r].id.substr(rb[r].id.length - field_name.length - 3, field_name.length) === field_name && rb[r].id.substr(rb[r].id.length - 1) === s) {
			rb[r].checked = true;
		}
	}
}

/**
* Reset role dropdown field to Select role... if an option gets changed
*/
function reset_role(id) {
	const t = document.getElementById(id);

	if (!t) {
		return;
	}

	t.options[0].selected = true;
}

/**
* Load role and set options accordingly
*/
function set_role_settings(role_id, target_id) {
	settings = role_options[role_id];

	if (!settings) {
		return;
	}

	// Mark all options to no (unset) first...
	mark_options(target_id, 'u');

	for (const r in settings) {
		mark_one_option(target_id, r, (settings[r] === 1) ? 'y' : 'n');
	}
}
