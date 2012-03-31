(function($) {  // Avoid conflicts with other libraries

"use strict";

var img_templates = {
	up: $('.template-up-img'),
	up_disabled: $('.template-up-img-disabled'),
	down: $('.template-down-img'),
	down_disabled: $('.template-down-img-disabled')
};

/**
 * The following callbacks are  for reording forums in acp_forums. forum_down
 * is triggered when a forum is moved down, and forum_up is triggered when
 * a forum is moved up. It moves the row up or down, and deactivates /
 * activates any up / down icons that require it (the ones at the top or bottom).
 */
phpbb.add_ajax_callback('forum_down', function() {
	var el = $(this),
		tr = el.parents('tr');

	if (tr.is(':first-child'))
	{
		var up_img = img_templates.up.clone().attr('href', tr.attr('data-up'));
		el.parents('span').siblings('.up').html(up_img);

		tr.next().find('.up').html(img_templates.up_disabled);

		phpbb.ajaxify({
			selector: el.parents('span').siblings('.up').children('a'),
			callback: 'forum_up'
		});
	}

	tr.insertAfter(tr.next());

	if (tr.is(':last-child'))
	{
		el.replaceWith(img_templates.down_disabled);

		var down_img = img_templates.down.clone().attr('href', tr.attr('data-down'));
		tr.prev().find('.down').html(down_img);

		phpbb.ajaxify({
			selector: tr.prev().find('.down').children('a'),
			callback: 'forum_down'
		});
	}
});

phpbb.add_ajax_callback('forum_up', function() {
	var el = $(this),
		tr = el.parents('tr');

	if (tr.is(':last-child'))
	{
		var down_img = img_templates.down.clone().attr('href', tr.attr('data-down'));
		el.parents('span').siblings('.down').html(down_img);

		tr.prev().find('.down').html(img_templates.down_disabled);

		phpbb.ajaxify({
			selector: el.parents('span').siblings('.down').children('a'),
			callback: 'forum_down'
		});
	}

	tr.insertBefore(tr.prev());

	if (tr.is(':first-child'))
	{
		el.replaceWith(img_templates.up_disabled);

		var up_img = img_templates.up.clone().attr('href', tr.attr('data-up'));
		tr.next().find('.up').html(up_img);

		phpbb.ajaxify({
			selector: tr.next().find('.up').children('a'),
			callback: 'forum_up'
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
