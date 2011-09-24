(function($) {  // Avoid conflicts with other libraries



phpbb.add_ajax_callback('forum_down', function(el) {
	var tr = $(el).parents('tr');
	if (tr.is(':first-child'))
	{
		$(el).parents('span').siblings('.up').html('<a href="' + tr.data('up') + '"><img src="./images/icon_up.gif" alt="Move up" title="Move up" /></a>');
		tr.next().find('.up').html('<img src="./images/icon_up_disabled.gif" alt="Move up" title="Move up" />');
		phpbb.ajaxify({selector: $(el).parents('span').siblings('.up').children('a')}, false, 'forum_up');
	}
	tr.insertAfter(tr.next());
	if (tr.is(':last-child'))
	{
		$(el).html('<img src="./images/icon_down_disabled.gif" alt="Move down" title="Move down" />');
		tr.prev().find('.down').html('<a href="' + tr.data('down') + '"><img src="./images/icon_down.gif" alt="Move down" title="Move down" /></a>');
		phpbb.ajaxify({selector: tr.prev().find('.down').children('a')}, false, 'forum_down');
	}
}).add_ajax_callback('forum_up', function(el) {
	var tr = $(el).parents('tr');
	if (tr.is(':last-child'))
	{
		$(el).parents('span').siblings('.down').html('<a href="' + tr.data('down') + '"><img src="./images/icon_down.gif" alt="Move down" title="Move down" /></a>');
		tr.prev().find('.down').html('<img src="./images/icon_down_disabled.gif" alt="Move down" title="Move down" />');
		phpbb.ajaxify({selector: $(el).parents('span').siblings('.down').children('a')}, false, 'forum_down');
	}
	tr.insertBefore(tr.prev());
	if (tr.is(':first-child'))
	{
		$(el).html('<img src="./images/icon_up_disabled.gif" alt="Move up" title="Move up" />');
		tr.next().find('.up').html('<a href="' + tr.data('up') + '"><img src="./images/icon_up.gif" alt="Move up" title="Move up" /></a>');
		phpbb.ajaxify({selector: tr.next().find('.up').children('a')}, false, 'forum_up');
	}
}).add_ajax_callback('act_deact', function(el, res) {
	$(el).text(res.text);
	var new_href = $(el).attr('href');
	if (new_href.indexOf('deactivate') !== -1)
	{
		new_href = new_href.replace('deactivate', 'activate')
	}
	else
	{
		new_href = new_href.replace('activate', 'deactivate')
	}
	$(el).attr('href', new_href);
}).add_ajax_callback('row_delete', function(el) {
	var tr = $(el).parents('tr');
	tr.remove();
});



$('[data-ajax]').each(function() {
	var fn = ($(this).data('ajax') !== 'true') ? $(this).data('ajax') : null;
	phpbb.ajaxify({selector: this}, $(this).data('refresh') !== undefined, fn);
});



})(jQuery); // Avoid conflicts with other libraries
