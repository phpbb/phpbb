(function($) {  // Avoid conflicts with other libraries


//This callback finds the post from the delete link, and removes it.
phpbb.add_ajax_callback('post_delete', function() {
	var el = $(this);
	if (el.data('refresh') === undefined)
	{
		var pid = el[0].href.split('&p=')[1];
		el.parents('div #p' + pid).fadeOut(function() {
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
	var $this = $(this);
	if ($this.data('ajax') !== 'false')
	{
		var fn = ($this.data('ajax') !== 'true') ? $this.data('ajax') : null;
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
		var action = phpbb.parse_querystring(data).action;
		if (action === 'make_normal')
		{
			return !($(this).find('select option[value="make_global"]').length);
		}
		else if (action.slice(-4) === 'lock')
		{
			return false;
		}
		return !(action === 'delete_topic' || action.slice(0, 5) === 'make_');
	}
}, true);



})(jQuery); // Avoid conflicts with other libraries
