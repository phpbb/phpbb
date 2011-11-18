(function($) {  // Avoid conflicts with other libraries


// This callback finds the post from the delete link, and removes it.
phpbb.add_ajax_callback('post_delete', function() {
	var el = $(this);
	if (el.data('refresh') === undefined)
	{
		var post_id = el[0].href.split('&p=')[1];
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
	if (res.success) {
		var zebra = $('.zebra');
		zebra.html(res.MESSAGE_TEXT);
		$(zebra.get(1)).remove();
	}
});;



$('[data-ajax]').each(function() {
	var $this = $(this), ajax = $this.data('ajax');
	if (ajax !== 'false')
	{
		var fn = (ajax !== 'true') ? ajax : null;
		phpbb.ajaxify({selector: this}, $this.data('refresh') !== undefined, fn);
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
		var action = $('#quick-mod-select').val()
		if (action === 'make_normal')
		{
			return !($(this).find('select option[value="make_global"]').length);
		}
		else if (action.slice(-4) === 'lock')
		{
			// Return false for both lock and unlock
			return false;
		}
		// make_sticky, make_announce and make_global all use AJAX.
		return !(action === 'delete_topic' || action.slice(0, 5) === 'make_');
	}
}, true);



})(jQuery); // Avoid conflicts with other libraries
