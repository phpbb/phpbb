(function($) {  // Avoid conflicts with other libraries

"use strict";

var img_templates = {
	up: $('.template-up-img'),
	up_disabled: $('.template-up-img-disabled'),
	down: $('.template-down-img'),
	down_disabled: $('.template-down-img-disabled')
};

/**
 * The following callbacks are for reording items. row_down
 * is triggered when an item is moved down, and row_up is triggered when
 * an item is moved up. It moves the row up or down, and deactivates /
 * activates any up / down icons that require it (the ones at the top or bottom).
 */
phpbb.add_ajax_callback('row_down', function() {
	var el = $(this),
		tr = el.parents('tr'),
		tr_swap = tr.next();

	/*
	* If the element was the first one, we have to:
	* - Add the up-link to the row we moved
	* - Remove the up-link on the next row
	*/
	if (tr.is(':first-child'))
	{
		var up_img = img_templates.up.clone().attr('href', tr.attr('data-up'));
		tr.find('.up').html(up_img);

		phpbb.ajaxify({
			selector: tr.find('.up').children('a'),
			callback: 'row_up',
			overlay: false
		});

		tr_swap.find('.up').html(img_templates.up_disabled.clone());
	}

	tr.insertAfter(tr_swap);

	/*
	* As well as:
	* - Remove the down-link on the moved row, if it is now the last row
	* - Add the down-link to the next row, if it was the last row
	*/
	if (tr.is(':last-child'))
	{
		tr.find('.down').html(img_templates.down_disabled.clone());

		var down_img = img_templates.down.clone().attr('href', tr_swap.attr('data-down'));
		tr_swap.find('.down').html(down_img);

		phpbb.ajaxify({
			selector: tr_swap.find('.down').children('a'),
			callback: 'row_down',
			overlay: false
		});
	}
});

phpbb.add_ajax_callback('row_up', function() {
	var el = $(this),
		tr = el.parents('tr'),
		tr_swap = tr.prev();

	/*
	* If the element was the last one, we have to:
	* - Add the down-link to the row we moved
	* - Remove the down-link on the next row
	*/
	if (tr.is(':last-child'))
	{
		var down_img = img_templates.down.clone().attr('href', tr.attr('data-down'));
		tr.find('.down').html(down_img);

		phpbb.ajaxify({
			selector: tr.find('.down').children('a'),
			callback: 'row_down',
			overlay: false
		});

		tr_swap.find('.down').html(img_templates.down_disabled.clone());
	}

	tr.insertBefore(tr_swap);

	/*
	* As well as:
	* - Remove the up-link on the moved row, if it is now the first row
	* - Add the up-link to the previous row, if it was the first row
	*/
	if (tr.is(':first-child'))
	{
		tr.find('.up').html(img_templates.up_disabled.clone());

		var up_img = img_templates.up.clone().attr('href', tr_swap.attr('data-up'));
		tr_swap.find('.up').html(up_img);

		phpbb.ajaxify({
			selector: tr_swap.find('.up').children('a'),
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
phpbb.add_ajax_callback('activate_deactivate', function(res) {
	var el = $(this),
		new_href = el.attr('href');

	el.text(res.text);

	if (new_href.indexOf('deactivate') !== -1)
	{
		new_href = new_href.replace('deactivate', 'activate')
	}
	else
	{
		new_href = new_href.replace('activate', 'deactivate')
	}

	el.attr('href', new_href);
});

/**
 * The removes the parent row of the link or form that triggered the callback,
 * and is good for stuff like the removal of forums.
 */
phpbb.add_ajax_callback('row_delete', function() {
	$(this).parents('tr').remove();
});



$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax'),
		fn;

	if (ajax !== 'false')
	{
		fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({
			selector: this,
			refresh: $this.attr('data-refresh') !== undefined,
			callback: fn
		});
	}
});



})(jQuery); // Avoid conflicts with other libraries
