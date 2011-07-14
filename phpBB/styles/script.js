var phpbb = {};

/**
 * Display a simple alert.
 *
 * @param string title Title of the message, eg "Information"
 * @param string msg Message to display. Can be HTML.
 */
phpbb.alert = function(title, msg) {
	var div = $('<div class="jalert"><h3>' + title + '</h3><p>' + msg + '</p></div>');

	$(document).bind('click', function(e) {
		if ($(e.target).parents('.jalert').length)
		{
			return true;
		}
		div.hide(300, function() {
			div.remove();
		});
		return false;
	});

	$('body').append(div);
	div.show(300);
}

/**
 * Display a simple yes / no box to the user.
 *
 * @param string msg Message to display. Can be HTML.
 * @param function callback Callback.
 */
phpbb.confirm = function(msg, callback) {
	var div = $('<div class="jalert"><p>' + msg + '</p>\
		<input type="button" class="jalertbut" value="Yes" />&nbsp;\
		<input type="button" class="jalertbut" value="No" /></div>');

	$('body').append(div);

	$('.jalertbut').bind('click', function(event) {
		div.hide(300, function() {
			div.remove();
		});
		callback(this.value === 'Yes');
		return false;
	});
	div.show(300);
}



$('.delete-icon a').click(function()
{
	var pid = this.href.split('&p=')[1];
	var __self = this;
	$.get(this.href, function(res) {
		res = JSON.parse(res);
		phpbb.confirm(res.MESSAGE_TEXT, function(del) {
			if (del)
			{
				var p = res.S_CONFIRM_ACTION.split('?');
				p[1] += '&confirm=Yes'
				$.post(p[0], p[1], function(res) {
					res = JSON.parse(res);
					phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT)
					$(__self).parents('div #p' + pid).remove();

					//if there is a refresh, check that it isn't to the same place
					if (res.REFRESH_DATA && res.REFRESH_DATA.url.indexOf('t=') === -1)
					{
						setTimeout(function() {
							window.location = res.REFRESH_DATA.url;
						}, res.REFRESH_DATA.time * 1000);
					}
				});
			}
		});
	});
	return false;
});
