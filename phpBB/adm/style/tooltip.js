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

var tooltips = [];

/**
 * Enable tooltip replacements for selects
 * @param {string} id ID tag of select
 * @param {string} headline Text that should appear on top of tooltip
 * @param {string} sub_id Sub ID that should only be using tooltips (optional)
*/
phpbb.enableTooltipsSelect = function (id, headline, sub_id) {
	var $links, hold;

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
				phpbb.prepareTooltips($this, headline);
			}
		} else {
			phpbb.prepareTooltips($this, headline);
		}
	});
};

/**
 * Prepare elements to replace
 *
 * @param {object} $element Element to prepare for tooltips
 * @param {string} head_text Text heading to display
*/
phpbb.prepareTooltips = function ($element, head_text) {
	var tooltip, text, desc, title;

	text = $element.attr('data-title');

	if (text === null || text.length === 0) {
		return;
	}

	title = phpbb.createElement('span', 'top');
	title.appendChild(document.createTextNode(head_text));

	desc = phpbb.createElement('span', 'bottom');
	desc.innerHTML = text;

	tooltip = phpbb.createElement('span', 'tooltip');
	tooltip.appendChild(title);
	tooltip.appendChild(desc);

	tooltips[$element.attr('data-id')] = tooltip;
	$element.on('mouseover', phpbb.showTooltip);
	$element.on('mouseout', phpbb.hideTooltip);
};

/**
 * Show tooltip
 *
 * @param {object} $element Element passed by .on()
*/
phpbb.showTooltip = function ($element) {
	var $this = $($element.target);
	$('#_tooltip_container').append(tooltips[$this.attr('data-id')]);
	phpbb.positionTooltip($this);
};

/**
 * Hide tooltip
*/
phpbb.hideTooltip = function () {
	var d = document.getElementById('_tooltip_container');
	if (d.childNodes.length > 0) {
		d.removeChild(d.firstChild);
	}
};

/**
 * Create new element
 *
 * @param {string} tag HTML tag
 * @param {string} c Element's class
 *
 * @return {object} Created element
*/
phpbb.createElement = function (tag, c) {
	var x = document.createElement(tag);
	x.className = c;
	x.style.display = 'block';
	return x;
};

/**
 * Correct positioning of tooltip container
 *
 * @param {object} $element Tooltip element that should be positioned
*/
phpbb.positionTooltip = function ($element) {
	var offset;

	$element = $element.parent();
	offset = $element.offset();

	$('#_tooltip_container').css({
		top: offset.top + 30,
		left: offset.left - 205
	});
};

/**
 * Prepare roles drop down select
 */
phpbb.prepareRolesDropdown = function () {
	var $options = $('.roles-options li');

	// Prepare highlighting of select options and settings update
	$options.each(function () {
		var $this = $(this);
		var $roles_options = $this.closest('.roles-options');

		// Correctly show selected option
		if (typeof $this.attr('data-selected') !== 'undefined') {
			$this.closest('.roles-options').children('span').text($this.text());
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
};

// Run onload functions for RolesDropdown and tooltips
$(function() {
	// Enable tooltips
	phpbb.enableTooltipsSelect('set-permissions', $('#set-permissions').attr('data-role-description'), 'role');

	// Prepare dropdown
	phpbb.prepareRolesDropdown();
});

})(jQuery); // Avoid conflicts with other libraries
