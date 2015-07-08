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
 * @param {string} [subId] Sub ID that should only be using tooltips (optional)
*/
phpbb.enableTooltipsSelect = function (id, headline, subId) {
	var $links, hold;

	hold = $('<span />', {
		id:		'_tooltip_container',
		css: {
			position: 'absolute'
		}
	});

	$('body').append(hold);

	if (!id) {
		$links = $('.roles-options li');
	} else {
		$links = $('.roles-options li', '#' + id);
	}

	$links.each(function () {
		var $this = $(this);

		if (subId) {
			if ($this.parent().attr('id').substr(0, subId.length) === subId) {
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
 * @param {jQuery} $element Element to prepare for tooltips
 * @param {string} headText Text heading to display
*/
phpbb.prepareTooltips = function ($element, headText) {
	var $tooltip, text, $desc, $title;

	text = $element.attr('data-title');

	if (text === null || text.length === 0) {
		return;
	}

	$title = $('<span />', {
		class: 'top',
		css: {
			display:	'block'
		}
	})
		.append(document.createTextNode(headText));

	$desc = $('<span />', {
		class: 'bottom',
		html: text,
		css: {
			display: 'block'
		}
	});

	$tooltip = $('<span />', {
		class: 'tooltip',
		css: {
			display: 'block'
		}
	})
		.append($title)
		.append($desc);

	tooltips[$element.attr('data-id')] = $tooltip;
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
 * Correct positioning of tooltip container
 *
 * @param {jQuery} $element Tooltip element that should be positioned
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
	var $rolesOptions = $options.closest('.roles-options');
	var $span = $rolesOptions.children('span');

	// Prepare highlighting of select options and settings update
	$options.each(function () {
		var $this = $(this);

		// Correctly show selected option
		if (typeof $this.attr('data-selected') !== 'undefined') {
			$rolesOptions.closest('.roles-options')
				.children('span')
				.text($this.text())
				.attr('data-default', $this.text())
				.attr('data-default-val', $this.attr('data-id'));
		}

		$this.on('mouseover', function () {
			var $this = $(this);
			$options.removeClass('roles-highlight');
			$this.addClass('roles-highlight');
		}).on('click', function () {
			var $this = $(this);

			// Update settings
			set_role_settings($this.attr('data-id'), $this.attr('data-target-id'));
			init_colours($this.attr('data-target-id').replace('advanced', ''));

			// Set selected setting
			$rolesOptions.children('span')
				.text($this.text());
			$rolesOptions.children('input[type=hidden]')
				.val($this.attr('data-id'));

			// Trigger hiding of selection options
			$('body').trigger('click');
		});
	});

	// Save default text of drop down if there is no default set yet
	if (typeof $span.attr('data-default') === 'undefined') {
		$span.attr('data-default', $span.text());
	}

	// Prepare resetting drop down on form reset
	$options.closest('form').on('reset', function () {
		$span.text($span.attr('data-default'));
		$rolesOptions.children('input[type=hidden]')
			.val($span.attr('data-id'));
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
