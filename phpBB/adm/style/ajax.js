/* global phpbb */

(function($) {  // Avoid conflicts with other libraries

'use strict';

/**
 * The following callbacks are for reording items. row_down
 * is triggered when an item is moved down, and row_up is triggered when
 * an item is moved up. It moves the row up or down, and deactivates /
 * activates any up / down icons that require it (the ones at the top or bottom).
 */
phpbb.addAjaxCallback('row_down', function(res) {
	if (typeof res.success === 'undefined' || !res.success) {
		return;
	}

	var $firstTr = $(this).parents('tr'),
		$secondTr = $firstTr.next();

	$firstTr.insertAfter($secondTr);
});

phpbb.addAjaxCallback('row_up', function(res) {
	if (typeof res.success === 'undefined' || !res.success) {
		return;
	}

	var $secondTr = $(this).parents('tr'),
		$firstTr = $secondTr.prev();

	$secondTr.insertBefore($firstTr);
});

/**
 * This callback replaces activate links with deactivate links and vice versa.
 * It does this by replacing the text, and replacing all instances of "activate"
 * in the href with "deactivate", and vice versa.
 */
phpbb.addAjaxCallback('activate_deactivate', function(res) {
	var $this = $(this),
		newHref = $this.attr('href');

	$this.text(res.text);

	if (newHref.indexOf('deactivate') !== -1) {
		newHref = newHref.replace('deactivate', 'activate');
	} else {
		newHref = newHref.replace('activate', 'deactivate');
	}

	$this.attr('href', newHref);
});

/**
 * The removes the parent row of the link or form that triggered the callback,
 * and is good for stuff like the removal of forums.
 */
phpbb.addAjaxCallback('row_delete', function(res) {
	if (res.SUCCESS !== false) {
		$(this).parents('tr').remove();
	}
});



$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax');

	if (ajax !== 'false') {
		var fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({
			selector: this,
			refresh: $this.attr('data-refresh') !== undefined,
			callback: fn
		});
	}
});

/**
* Automatically resize textarea
*/
$(function() {
	phpbb.resizeTextArea($('textarea:not(.no-auto-resize)'), {minHeight: 75});
});


})(jQuery); // Avoid conflicts with other libraries
