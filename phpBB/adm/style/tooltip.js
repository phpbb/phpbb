/*
javascript for Bubble Tooltips by Alessandro Fulciniti
- http://pro.html.it - http://web-graphics.com
obtained from: http://web-graphics.com/mtarchive/001717.php

phpBB Development Team:
	- modified to adhere to our coding guidelines
	- integration into our design
	- added ability to perform tooltips on select elements
	- further adjustements
*/

(function($) { // Avoid conflicts with other libraries

'use strict';

var head_text, tooltip_mode, tooltips;
tooltips = [];

/**
* Enable tooltip replacements for links
*/
function enable_tooltips_link(id, headline, sub_id) {
	var links, i, hold;

	head_text = headline;

	if (!document.getElementById || !document.getElementsByTagName) {
		return;
	}

	hold = document.createElement('span');
	hold.id = '_tooltip_container';
	hold.setAttribute('id', '_tooltip_container');
	hold.style.position = 'absolute';

	document.getElementsByTagName('body')[0].appendChild(hold);

	if (id === null) {
		links = document.getElementsByTagName('a');
	} else {
		links = document.getElementById(id).getElementsByTagName('a');
	}

	for (i = 0; i < links.length; i++) {
		if (sub_id) {
			if (links[i].id.substr(0, sub_id.length) === sub_id) {
				prepare(links[i]);
			}
		} else {
			prepare(links[i]);
		}
	}

	tooltip_mode = 'link';
}

/**
 * Enable tooltip replacements for selects
 * @param {string} id ID tag of select
 * @param {string} headline Text that should appear on top of tooltip
 * @param {string} sub_id Sub ID that should only be using tooltips (optional)
*/
function enable_tooltips_select(id, headline, sub_id) {
	var $links, hold;

	head_text = headline;

	if (!document.getElementById || !document.getElementsByTagName) {
		return;
	}

	hold = document.createElement('span');
	hold.id = '_tooltip_container';
	hold.setAttribute('id', '_tooltip_container');
	hold.style.position = 'absolute';
	$('body').append(hold);

	if (id === null) {
		$links = $('.roles-options li');
	} else {
		$links = $('.roles-options li', '#' + id);
	}

	$links.each(function () {
		var $this = $(this);

		if (sub_id) {
			if ($this.parent().attr('id').substr(0, sub_id.length) === sub_id) {
				prepare($this);
			}
		} else {
			prepare($this);
		}
	});

	tooltip_mode = 'select';
}

/**
 * Prepare elements to replace
 *
 * @param {object} $element Element to prepare for tooltips
*/
function prepare($element) {
	var tooltip, text, desc, title;

	text = $element.attr('data-title');;

	if (text === null || text.length === 0) {
		return;
	}

	tooltip = create_element('span', 'tooltip');

	title = create_element('span', 'top');
	title.appendChild(document.createTextNode(head_text));
	tooltip.appendChild(title);

	desc = create_element('span', 'bottom');
	desc.innerHTML = text;
	tooltip.appendChild(desc);

	set_opacity(tooltip);

	tooltips[$element.attr('data-id')] = tooltip;
	$element.on('mouseover', show_tooltip);
	$element.on('mouseout', hide_tooltip);

	if (tooltip_mode === 'link') {
		$element.onmousemove = locate;
	}
}

/**
 * Show tooltip
 *
 * @param {object} $element Element passed by .on()
*/
function show_tooltip($element) {
	var $this = $($element.target);
	$('#_tooltip_container').append(tooltips[$this.attr('data-id')]);
	locate($this);
}

/**
 * Hide tooltip
 *
 * @param {object} $element Element passed by .on()
*/
function hide_tooltip($element) {
	var d = document.getElementById('_tooltip_container');
	if (d.childNodes.length > 0) {
		d.removeChild(d.firstChild);
	}
}

/**
* Set opacity on tooltip element
*/
function set_opacity(element) {
	element.style.filter = 'alpha(opacity:95)';
	element.style.KHTMLOpacity = '0.95';
	element.style.MozOpacity = '0.95';
	element.style.opacity = '0.95';
}

/**
* Create new element
*/
function create_element(tag, c) {
	var x = document.createElement(tag);
	x.className = c;
	x.style.display = 'block';
	return x;
}

/**
 * Correct positioning of tooltip container
 *
 * @param {object} $element Tooltip element that should be positioned
*/
function locate($element) {
	var offset;

	$element = $element.parent();
	offset = $element.offset();

	if (tooltip_mode === 'link') {
		$('#_tooltip_container').css({
			top: offset.top + 20,
			left: offset.left - 20
		});
	} else {
		$('#_tooltip_container').css({
			top: offset.top + 30,
			left: offset.left - 205
		});
	}
}

$(function() {
	var $options;

	// Enable tooltips
	enable_tooltips_select('set-permissions', $('#set-permissions').attr('data-role-description'), 'role');

	$options = $('.roles-options li');

	// Prepare highlighting of select options and settings update
	$options.each(function () {
		var $this = $(this);
		var $roles_options = $this.closest('.roles-options');

		// Correctly show selected option
		if (typeof $this.attr('data-selected') !== 'undefined') {
			$this.closest('.roles-options').children('span').text($this.text());
			$('')
		}

		$this.on('mouseover', function (e) {
			var $this = $(this);
			$options.removeClass('roles-highlight');
			$this.addClass('roles-highlight');
		}).on('click', function (e) {
			var $this = $(this);

			// Update settings
			set_role_settings($this.attr('data-id'), $this.attr('data-target-id'));
			init_colours($this.attr('data-target-id').replace('advanced', ''));

			// Set selected setting
			$roles_options.children('span').text($this.text());
			$roles_options.children('input[type=hidden]').val($this.attr('data-id'));

			// Trigger hiding of selection options
			$('body').trigger('click');
		});
	});
});

})(jQuery); // Avoid conflicts with other libraries
