var phpbb = {};

/**
 * Display a simple alert.
 *
 * @param string title Title of the message, eg "Information"
 * @param string msg Message to display. Can be HTML.
 *
 * @return Returns the div created.
 */
phpbb.alert = function(title, msg) {
	var div = $('<div class="jalert"><h3>' + title + '</h3><p>' + msg + '</p></div>');

	$(document).one('click', function(e) {
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
	return div;
}

/**
 * Display a simple yes / no box to the user.
 *
 * @param string msg Message to display. Can be HTML.
 * @param function callback Callback.
 *
 * @return Returns the div created.
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
	return div;
}

/**
 * Clearing up some duplicate code - don't use this.
 */
function handle_refresh(data, refresh, div)
{
	if (data)
	{
		if (typeof refresh === 'function')
		{
			refresh = refresh(data.url)
		}
		else if (typeof refresh !== 'boolean')
		{
			refresh = false;
		}

		if (refresh)
		{
			setTimeout(function() {
				window.location = data.url;
			}, data.time * 1000);
		}
		else
		{
			setTimeout(function() {
				div.hide(300, function() {
					div.remove();
				});
			}, data.time * 1000);
		}
	}
}


/**
 * This function interacts via AJAX with phpBBs confirm_box function.
 *
 * @param string condition The element to capture.
 * @param bool/function refresh If we are sent back a refresh, should it be
 * 	acted upon? This can either be true / false / a function.
 * @param function callback Callback.
 */
phpbb.confirm_box = function(condition, refresh, callback)
{
	__self = this;
	$(condition).click(function() {
		var __self = this;
		$.get(this.href, function(res) {
			res = JSON.parse(res);
			phpbb.confirm(res.MESSAGE_TEXT, function(del) {
				if (del)
				{
					var p = res.S_CONFIRM_ACTION.split('?');
					p[1] += '&confirm=Yes';
					$.post(p[0], p[1], function(res) {
						res = JSON.parse(res);
						var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);

						if (typeof callback !== 'undefined')
						{
							callback(__self);
						}

						handle_refresh(res.REFRESH_DATA, refresh, alert);
					});
				}
			});
		});
		return false;
	});
}

/**
 * Makes a link use AJAX instead of loading an entire page.
 */
phpbb.ajaxify = function(selector, refresh, callback) {
	$(selector).click(function() {
		var __self = this;
		$.get(this.href, function(res) {
			res = JSON.parse(res);
			var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
			if (typeof callback !== 'undefined')
			{
				callback(__self, res);
			}

			handle_refresh(res.REFRESH_DATA, refresh, alert);
		});
		return false;
	});
}

var refresh = function(url) {
	return (url.indexOf('t=') === -1);
}
var callback = function(el) {
	var pid = el.href.split('&p=')[1];
	$(el).parents('div #p' + pid).remove();
}
phpbb.confirm_box('.delete-icon a', refresh, callback);
phpbb.confirm_box('a[href$="ucp.php?mode=delete_cookies"]', true);

phpbb.ajaxify('a[href*="&bookmark=1"]', false, function(el, res) {
	var text = (res.MESSAGE_TEXT.indexOf('Removed') === -1);
	text = (text) ? 'Remove from bookmarks' : 'Bookmark topic';
	$(el).text(el.title = text);
});
phpbb.ajaxify('a[href*="&watch=topic"]', false, function(el, res) {
	var text = (res.MESSAGE_TEXT.indexOf('no longer subscribed') === -1);
	text = (text) ? 'Unsubscribe topic' : 'Subscribe topic';
	$(el).text(el.title = text);
});
phpbb.ajaxify('a[href*="watch=forum"]', false, function(el, res) {
	var text = (res.MESSAGE_TEXT.indexOf('no longer subscribed') === -1);
	text = (text) ? 'Unsubscribe forum' : 'Subscribe forum';
	$(el).text(el.title = text);
});
phpbb.ajaxify('a[href*="mode=bump"]');
phpbb.ajaxify('a[href*="mark="]'); //captures topics and forums
