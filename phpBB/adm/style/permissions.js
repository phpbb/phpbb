/**
* Hide and show all checkboxes
* status = true (show boxes), false (hide boxes)
*/
function display_checkboxes(status) {
	var form = document.getElementById('set-permissions');
	var cb = document.getElementsByTagName('input');
	var display;

	//show
	if (status) {
		display = 'inline';
	}
	//hide
	else {
		display = 'none';
	}

	for (var i = 0; i < cb.length; i++ ) {
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
	e.style.opacity = value/10;

	//IE opacity currently turned off, because of its astronomical stupidity
	//e.style.filter = 'alpha(opacity=' + value*10 + ')';
}

/**
* Reset the opacity and checkboxes
* block_id = id of the element that needs to be toggled
*/
function toggle_opacity(block_id) {
	var cb = document.getElementById('checkbox' + block_id);
	var fs = document.getElementById('perm' + block_id);

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
	var perm = document.getElementById('set-permissions');
	var fs = perm.getElementsByTagName('fieldset');
	var opacity = 5;

	if (status) {
		opacity = 10;
	}

	for (var i = 0; i < fs.length; i++ ) {
		if (fs[i].className !== 'quick') {
			set_opacity(fs[i], opacity);
		}
	}

	if (typeof(except_id) !== 'undefined') {
		set_opacity(document.getElementById('perm' + except_id), 10);
	}

	//reset checkboxes too
	marklist('set-permissions', 'inherit', !status);
}

/**
* Check whether we have a full radiobutton row of true
* index = offset for the row of inputs (0 == first row, 1 == second, 2 == third),
* rb = array of radiobuttons
*/
function get_radio_status(index, rb) {
	for (var i = index; i < rb.length; i = i + 3 ) {
		if (rb[i].checked !== true) {
			if (i > index) {
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
function set_colours(id, init, quick) {
	var table = document.getElementById('table' + id);
	var tab = document.getElementById('tab' + id);

	if (typeof(quick) !== 'undefined') {
		tab.className = 'permissions-preset-' + quick + ' activetab';
		return;
	}

	var rb = table.getElementsByTagName('input');
	var colour = 'custom';

	var status = get_radio_status(0, rb);

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
	var block = document.getElementById('advanced' + block_id);
	var panels = block.getElementsByTagName('div');
	var tab = document.getElementById('tab' + id);

	for (var i = 0; i < panels.length; i++) {
		if (panels[i].className === 'permissions-panel') {
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
function swap_options(pmask, fmask, cat, adv, view) {
	id = pmask + fmask + cat;
	active_option = active_pmask + active_fmask + active_cat;

	var	old_tab = document.getElementById('tab' + active_option);
	var new_tab = document.getElementById('tab' + id);
	var adv_block = document.getElementById('advanced' + pmask + fmask);

	if (adv_block.style.display === 'block' && adv === true) {
		phpbb.toggleDisplay('advanced' + pmask + fmask, -1);
		reset_opacity(1);
		display_checkboxes(false);
		return;
	}

	// no need to set anything if we are clicking on the same tab again
	if (new_tab === old_tab && !adv) {
		return;
	}

	// init colours
	if (adv && (pmask + fmask) !== (active_pmask + active_fmask)) {
		init_colours(pmask + fmask);
		display_checkboxes(true);
		reset_opacity(1);
	} else if (adv) {
		//Checkbox might have been clicked, but we need full visibility
		display_checkboxes(true);
		reset_opacity(1);
	}

	// set active tab
	old_tab.className = old_tab.className.replace(/\ activetab/g, '');
	new_tab.className = new_tab.className + ' activetab';

	if (id === active_option && adv !== true) {
		return;
	}

	phpbb.toggleDisplay('options' + active_option, -1);

	//hiding and showing the checkbox
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
	var t = document.getElementById(id);

	if (!t) {
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++) {
		if (rb[r].id.substr(rb[r].id.length-1) === s) {
			rb[r].checked = true;
		}
	}
}

function mark_one_option(id, field_name, s) {
	var t = document.getElementById(id);

	if (!t) {
		return;
	}

	var rb = t.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++) {
		if (rb[r].id.substr(rb[r].id.length-field_name.length-3, field_name.length) === field_name && rb[r].id.substr(rb[r].id.length-1) === s) {
			rb[r].checked = true;
		}
	}
}

/**
 * (Re)set the permission role dropdown.
 *
 * Try and match the set permissions to an existing role.
 * Otherwise reset the dropdown to "Select a role.."
 *
 * @param {string}	id		The fieldset identifier
 * @returns {void}
 */
function reset_role(id) {
	var t = document.getElementById(id);

	if (!t) {
		return;
	}

	// Before resetting the role dropdown, try and match any permission role
	var parent = t.parentNode,
		roleId = match_role_settings(id.replace('role', 'perm')),
		text = no_role_assigned,
		index = 0;

	// If a role permissions was matched, grab that option's value and index
	if (roleId) {
		for (var i = 0; i < t.options.length; i++) {
			if (parseInt(t.options[i].value, 10) === roleId) {
				text = t.options[i].text;
				index = i;
				break;
			}
		}
	}

	// Update the select's value and selected index
	t.value = roleId;
	t.options[index].selected = true;

	// Update the dropdown trigger to show the new value
	parent.querySelector('span.dropdown-trigger').innerText = text;
	parent.querySelector('input[data-name^=role]').value = roleId;
}

/**
* Load role and set options accordingly
*/
function set_role_settings(role_id, target_id) {
	var settings = role_options[role_id];

	if (!settings) {
		return;
	}

	// Mark all options to no (unset) first...
	mark_options(target_id, 'u');

	for (var r in settings) {
		mark_one_option(target_id, r, (settings[r] === 1) ? 'y' : 'n');
	}
}

/**
 * Match the set permissions against the available roles.
 *
 * @param {string}	id		The parent fieldset identifier
 * @return {number}			The permission role identifier
 */
function match_role_settings(id) {
	var fieldset = document.getElementById(id),
		radios = fieldset.getElementsByTagName('input'),
		set = {};

	// Iterate over all the radio buttons
	for (var i = 0; i < radios.length; i++) {
		var matches = radios[i].id.match(/setting\[\d+]\[\d+]\[([a-z_]+)]/);

		// Make sure the name attribute matches, the radio is checked and it is not the "No" (-1) value.
		if (matches !== null && radios[i].checked && radios[i].value !== '-1') {
			set[matches[1]] = parseInt(radios[i].value, 10);
		}
	}

	// Sort and stringify the 'set permissions' object
	set = sort_and_stringify(set);

	// Iterate over the available role options and return the first match
	for (var r in role_options)
	{
		if (sort_and_stringify(role_options[r]) === set) {
			return parseInt(r, 10);
		}
	}

	return 0;
}

/**
 * Sort and stringify an Object so it can be easily compared against another object.
 *
 * @param {object}	obj		The object to sort (by key) and stringify
 * @return {string}			The sorted object as a string
 */
function sort_and_stringify(obj) {
	return JSON.stringify(Object.keys(obj).sort().reduce(function (result, key) {
		result[key] = obj[key];
		return result;
	}, {}));
}
