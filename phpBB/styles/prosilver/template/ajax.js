(function($) {  // Avoid conflicts with other libraries

"use strict";

// This callback finds the post from the delete link, and removes it.
phpbb.add_ajax_callback('post_delete', function() {
	var el = $(this),
		post_id;

	if (el.attr('data-refresh') === undefined)
	{
		post_id = el[0].href.split('&p=')[1];
		el.parents('#p' + post_id).fadeOut(function() {
			$(this).remove();
		});
	}
});

// This callback removes the approve / disapprove div or link.
phpbb.add_ajax_callback('post_approve', function(res, act) {
	$(this).parents((act === 'approve') ? '.rules' : '.post').fadeOut(function() {
		$(this).remove();
	});
});

// This callback handles the removal of the quick reply form.
phpbb.add_ajax_callback('qr-submit', function() {
	$(this).parents('form').fadeOut(function() {
		$(this).remove();
	});
});

// This removes the parent row of the link or form that fired the callback.
phpbb.add_ajax_callback('row_delete', function() {
	$(this).parents('tr').remove();
});

// This handles friend / foe additions removals.
phpbb.add_ajax_callback('zebra', function(res) {
	var zebra;

	if (res.success) {
		zebra = $('.zebra');
		zebra.html(res.MESSAGE_TEXT);
		$(zebra.get(1)).remove();
	}
});



$('[data-ajax]').each(function() {
	var $this = $(this),
		ajax = $this.attr('data-ajax'),
		fn;

	if (ajax !== 'false')
	{
		fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({selector: this}, $this.attr('data-refresh') !== undefined, fn);
	}
});



/**
 * This AJAXifies the quick-mod tools. The reason it cannot be a standard
 * callback / data attribute is that it requires exceptions - some of the options
 * can be ajaxified, while others cannot.
 */
phpbb.ajaxify({
	selector: '#quickmodform',
	exception: function(act, data) {
		var action = $('#quick-mod-select').val();

		if (action === 'make_normal')
		{
			return !($(this).find('select option[value="make_global"]').length);
		}
		else if (action === 'lock' || action === 'unlock')
		{
			return false;
		}

		if (action === 'delete_topic' || action === 'make_sticky' || action === 'make_announce' || action === 'make_global') {
			return false;
		}

		return true;
	}
}, true);



})(jQuery); // Avoid conflicts with other libraries
