(function($) {  // Avoid conflicts with other libraries

"use strict";

var imgTemplates = {
	up: $('.template-up-img'),
	upDisabled: $('.template-up-img-disabled'),
	down: $('.template-down-img'),
	downDisabled: $('.template-down-img-disabled')
};

/**
 * The following callbacks are for reording items. row_down
 * is triggered when an item is moved down, and row_up is triggered when
 * an item is moved up. It moves the row up or down, and deactivates /
 * activates any up / down icons that require it (the ones at the top or bottom).
 */
phpbb.addAjaxCallback('row_down', function() {
	var el = $(this),
		tr = el.parents('tr'),
		trSwap = tr.next();

	/*
	* If the element was the first one, we have to:
	* - Add the up-link to the row we moved
	* - Remove the up-link on the next row
	*/
	if (tr.is(':first-child')) {
		var upImg = imgTemplates.up.clone().attr('href', tr.attr('data-up'));
		tr.find('.up').html(upImg);

		phpbb.ajaxify({
			selector: tr.find('.up').children('a'),
			callback: 'row_up',
			overlay: false
		});

		trSwap.find('.up').html(imgTemplates.upDisabled.clone());
	}

	tr.insertAfter(trSwap);

	/*
	* As well as:
	* - Remove the down-link on the moved row, if it is now the last row
	* - Add the down-link to the next row, if it was the last row
	*/
	if (tr.is(':last-child')) {
		tr.find('.down').html(imgTemplates.downDisabled.clone());

		var downImg = imgTemplates.down.clone().attr('href', trSwap.attr('data-down'));
		trSwap.find('.down').html(downImg);

		phpbb.ajaxify({
			selector: trSwap.find('.down').children('a'),
			callback: 'row_down',
			overlay: false
		});
	}
});

phpbb.addAjaxCallback('row_up', function() {
	var el = $(this),
		tr = el.parents('tr'),
		trSwap = tr.prev();

	/*
	* If the element was the last one, we have to:
	* - Add the down-link to the row we moved
	* - Remove the down-link on the next row
	*/
	if (tr.is(':last-child')) {
		var downImg = imgTemplates.down.clone().attr('href', tr.attr('data-down'));
		tr.find('.down').html(downImg);

		phpbb.ajaxify({
			selector: tr.find('.down').children('a'),
			callback: 'row_down',
			overlay: false
		});

		trSwap.find('.down').html(imgTemplates.downDisabled.clone());
	}

	tr.insertBefore(trSwap);

	/*
	* As well as:
	* - Remove the up-link on the moved row, if it is now the first row
	* - Add the up-link to the previous row, if it was the first row
	*/
	if (tr.is(':first-child')) {
		tr.find('.up').html(imgTemplates.upDisabled.clone());

		var upImg = imgTemplates.up.clone().attr('href', trSwap.attr('data-up'));
		trSwap.find('.up').html(upImg);

		phpbb.ajaxify({
			selector: trSwap.find('.up').children('a'),
			callback: 'row_up',
			overlay: false
		});
	}
});

/**
 * This callback replaces activate links with deactivate links and vice versa.
 * It does this by replacing the text, and replacing all instances of "activate"
 * in the href with "deactivate", and vice versa.
 */
phpbb.addAjaxCallback('activate_deactivate', function(res) {
	var el = $(this),
		newHref = el.attr('href');

	el.text(res.text);

	if (newHref.indexOf('deactivate') !== -1) {
		newHref = newHref.replace('deactivate', 'activate');
	} else {
		newHref = newHref.replace('activate', 'deactivate');
	}

	el.attr('href', newHref);
});

/**
 * The removes the parent row of the link or form that triggered the callback,
 * and is good for stuff like the removal of forums.
 */
phpbb.addAjaxCallback('row_delete', function() {
	$(this).parents('tr').remove();
});



$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax'),
		fn;

	if (ajax !== 'false') {
		fn = (ajax !== 'true') ? ajax : null;
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
$(document).ready(function() {
	phpbb.resizeTextArea($('textarea:not(.no-auto-resize)'), {minHeight: 75});
});


})(jQuery); // Avoid conflicts with other libraries
