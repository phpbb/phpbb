(function($) {  // Avoid conflicts with other libraries



phpbb.add_ajax_callback('post_delete', function(el) {
	if ($(this).data('refresh') === undefined)
	{
		var pid = el.href.split('&p=')[1];
		$(el).parents('div #p' + pid).fadeOut(function() {
			$(this).remove();
		});
	}
}).add_ajax_callback('post_approve', function(el, res, act) {
	$(el).parents((act === 'approve') ? '.rules' : '.post').fadeOut(function() {
		$(this).remove();
	});
}).add_ajax_callback('qr-submit', function(el) {
	$(el).parents('form').fadeOut(function() {
		$(this).remove();
	});
}).add_ajax_callback('row_delete', function(el) {
	var tr = $(el).parents('tr');
	tr.remove();
}).add_ajax_callback('zebra', function(el, res) {
	if (res.success) {
		$('.zebra').html(res.MESSAGE_TEXT);
		$($('.zebra').get(1)).remove();
	}
});;



$('[data-ajax]').each(function() {
	var fn = ($(this).data('ajax') !== 'true') ? $(this).data('ajax') : null;
	phpbb.ajaxify({selector: this}, $(this).data('refresh') !== undefined, fn);
});



phpbb.ajaxify({
	selector: '#quickmodform',
	exception: function(el, act, data) {
		var d = phpbb.parse_querystring(data).action;
		if (d == 'make_normal')
		{
			return !(el.find('select option[value="make_global"]').length);
		}
		return !(d == 'lock' || d == 'unlock' || d == 'delete_topic' || d.slice(0, 5) == 'make_');
	}
}, true);



})(jQuery); // Avoid conflicts with other libraries
