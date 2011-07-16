var phpbb = {};

/**
 * Display a simple alert similar to JSs native alert().
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
 * Works out what to do with the refresh. Don't use this.
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

function parse_hidden(inputs)
{
	var end = [];
	$(inputs).each(function() {
		if (this.type === 'hidden')
		{
			end.push(this.name + '=' + this.value);
		}
	});
	return end.join('&');
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
	$(condition).click(function() {
		var __self = this;
		$.get(this.href, function(res) {
			res = JSON.parse(res);
			phpbb.confirm(res.MESSAGE_TEXT, function(del) {
				if (del)
				{
					var p = res.S_CONFIRM_ACTION.split('?');
					$.post(p[0], p[1] + '&confirm=Yes', function(res) {
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
 *
 * @param string condition The element to capture.
 * @param bool/function refresh If we are sent back a refresh, should it be
 * 	acted upon? This can either be true / false / a function.
 * @param function callback Callback.
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


//bind the confirm_boxes
var refresh = function(url) {
	return (url.indexOf('t=') === -1);
}
phpbb.confirm_box('.delete-icon a', refresh, function(el) {
	var pid = el.href.split('&p=')[1];
	$(el).parents('div #p' + pid).remove();
});
phpbb.confirm_box('a[href$="ucp.php?mode=delete_cookies"]', true);


//AJAXify some links
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



/**
 * Forms have to be captured manually, as they're all different.
 */
$('input[name^="action"]').click(function(e) {
	var __self = this;
	var path = $(this).parents('form')[0].action.replace('&amp;', '&');
	var action = (this.name === 'action[approve]') ? 'approve' : 'disapprove';
	var data = {
		action: action,
		post_id_list: [$(this).siblings('input[name="post_id_list[]"]')[0].value]
	};
	$.post(path, data, function(res) {
		res = JSON.parse(res);
		phpbb.confirm(res.MESSAGE_TEXT, function(del) {
			if (del)
			{
				path = res.S_CONFIRM_ACTION;
				data =  parse_hidden(res.S_HIDDEN_FIELDS);
				$.post(path, data + '&confirm=Yes', function(res) {
					console.log(res);
					res = JSON.parse(res);
					var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
					
					$(__self).parents((action === 'approve') ? '.rules' : '.post').remove();
					
					setTimeout(function() {
						alert.hide(300, function() {
							alert.remove();
						});
					}, 5000);
				});
			}
		});
	});
	return false;
});
