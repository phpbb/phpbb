/**
 * Make some changes to the jQuery core.
 */
$.fn.hide_anim = function() {
	this.animate({opacity: 0}, 300, function() {
		$(this).css('display', 'none')
			.css('opacity', 1);	
	});
}
$.fn.show_anim = function() {
	this.css('opacity', 0)
		.css('display', 'block')
		.animate({opacity: 1}, 300);
}

$.fn.remove_anim = function() {
	this.animate({opacity: 0}, 300, function() {
		$(this).remove();
	});
}


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
		div.remove_anim();
		return false;
	});

	$('body').append(div);
	div.show_anim();
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
		<input type="button" class="jalertbut button1" value="Yes" />&nbsp;\
		<input type="button" class="jalertbut button2" value="No" /></div>');

	$('body').append(div);

	$('.jalertbut').bind('click', function(event) {
		div.remove_anim();
		callback(this.value === 'Yes');
		return false;
	});
	div.show_anim();
	return div;
}

/**
 * Works out what to do with the refresh. Don't use this.
 */


/**
 * Makes a link use AJAX instead of loading an entire page.
 *
 * @param string condition The element to capture.
 * @param bool/function refresh If we are sent back a refresh, should it be
 * 	acted upon? This can either be true / false / a function.
 * @param function callback Callback.
 */
phpbb.ajaxify = function(selector, refresh, callback) {

	//private function to handle refreshes
	function handle_refresh(data, refresh, div)
	{
		if (!data)
		{
			return;
		}
		
		refresh = ((typeof refresh === 'function') ? refresh(data.url) :
			(typeof refresh === 'boolean') && refresh);

		setTimeout(function() {
			if (refresh)
			{
				window.location = data.url;
			}
			else
			{
				div.remove_anim();
			}
		}, data.time * 1000);
	}

	var is_form = $(selector).is('form');
	$(selector + ((is_form) ? ' input:submit' : '')).click(function() {
		var act, data, path, that = this;
		function return_handler(res)
		{
			res = JSON.parse(res);
			
			if (typeof res.S_CONFIRM_ACTION === 'undefined')
			{
				/**
				 * It is a standard link, no confirm_box required.
				 */
				var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				if (typeof callback !== 'undefined')
				{
					callback(that, res);
				}
				handle_refresh(res.REFRESH_DATA, refresh, alert);
			}
			else
			{
				/**
				 * confirm_box - confirm with the user and send back
				 */
				phpbb.confirm(res.MESSAGE_TEXT, function(del) {
					if (del)
					{
						data =  $('<form>' + res.S_HIDDEN_FIELDS + '</form>').serialize();
						path = res.S_CONFIRM_ACTION;
						$.post(path, data + '&confirm=' + res.YES_VALUE, function(res) {
							res = JSON.parse(res);
							var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
							if (typeof callback !== 'undefined')
							{
								callback(that, (is_form) ? act : null);
							}
							handle_refresh(res.REFRESH_DATA, refresh, alert);
						});
					}
				});
			}
		}
		
		if (is_form)
		{
			act = /action\[([a-z]+)\]/.exec(this.name);
			data = decodeURI($(this).closest('form').serialize());
			path = $(this).closest('form').attr('action').replace('&amp;', '&');
			
			if (act)
			{
				act = act[1]
				data += '&action=' + act;
			}
			
			$.post(path, data, return_handler);
		}
		else
		{
			$.get(this.href, return_handler);
		}
		
		return false;
	});
}


//bind the confirm_boxes
var refresh = function(url) {
	return (url.indexOf('t=') === -1);
}
phpbb.ajaxify('.delete-icon a', refresh, function(el) {
	var pid = el.href.split('&p=')[1];
	$(el).parents('div #p' + pid).remove_anim();
});
phpbb.ajaxify('a[href$="ucp.php?mode=delete_cookies"]', true);


//AJAXify some links
phpbb.ajaxify('a[href*="&bookmark=1"]', false, function(el, res) {
	var text = (res.MESSAGE_TEXT.indexOf('Removed') === -1);
	text = (text) ? 'Remove from bookmarks' : 'Bookmark topic';
	$(el).text(el.title = text);
});
phpbb.ajaxify('a[href*="watch=topic"]', false, function(el, res) {
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

phpbb.ajaxify('.mcp_approve', false, function(el, act) {
	$(el).parents((act === 'approve') ? '.rules' : '.post').remove_anim();
});
