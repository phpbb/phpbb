;(function($) {  //avoid conflicts with other libraries


$.querystring = function(string) {
	var end = {}, i;
	
	string = string.split('&');
	for (i = 0; i < string.length; i++)
	{
		end[string[i].split('=')[0]] = decodeURIComponent(string[i].split('=')[1]);
	}
	return end;
}


var phpbb = {};

var dark = $('<div id="darkenwrapper"><div id="darken">&nbsp;</div></div>');
$('body').append(dark);

var loading_alert = $('<div class="jalert"><h3>Loading</h3><p>Please wait.</p></div>');
$(dark).append(loading_alert);


/**
 * Display a loading screen.
 */
phpbb.loading_alert = function() {
	if (dark.is(':visible'))
	{
		loading_alert.fadeIn();
	}
	else
	{
		loading_alert.show();
		dark.fadeIn();
	}
	
	setTimeout(function() {
		if (loading_alert.is(':visible'))
		{
			phpbb.alert('Error', 'Error processing your request. Please try again.');
		}
	}, 3000);
	return loading_alert;
}

/**
 * Display a simple alert similar to JSs native alert().
 *
 * @param string title Title of the message, eg "Information"
 * @param string msg Message to display. Can be HTML.
 * @param bool fadedark Remove the dark background when done? Defaults
 * 	to yes.
 *
 * @return Returns the div created.
 */
phpbb.alert = function(title, msg, fadedark) {
	var div = $('<div class="jalert"><h3>' + title + '</h3><p>' + msg + '</p></div>');

	$(div).bind('click', function(e) {
		e.stopPropagation();
		return true;
	});
	$(dark).one('click', function(e) {
		var fade = (typeof fadedark !== 'undefined' && !fadedark) ? div : dark;
		fade.fadeOut(function() {
			div.remove();
		});
		return false;
	});

	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(function() {
			$(dark).append(div);
			div.fadeIn();
		});
	}
	else if (dark.is(':visible'))
	{
		$(dark).append(div);
		div.fadeIn();
	}
	else
	{
		$(dark).append(div);
		div.show();
		dark.fadeIn();
	}
	
	return div;
}

/**
 * Display a simple yes / no box to the user.
 *
 * @param string msg Message to display. Can be HTML.
 * @param function callback Callback. Bool param, whether the user pressed
 * 	yes or no (or whatever their language is).
 * @param bool fadedark Remove the dark background when done? Defaults
 * 	to yes.
 *
 * @return Returns the div created.
 */
phpbb.confirm = function(msg, callback, fadedark) {
	var div = $('<div class="jalert"><p>' + msg + '</p>\
		<input type="button" class="jalertbut button1" value="Yes" />&nbsp;\
		<input type="button" class="jalertbut button2" value="No" /></div>');
	
	div.find('.jalertbut').bind('click', function() {
		var res = this.value === 'Yes';
		var fade = (typeof fadedark !== 'undefined' && !fadedark && res) ? div : dark;
		fade.fadeOut(function() {
			div.remove();
		});
		callback(res);
		return false;
	});
	
	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(function() {
			$(dark).append(div);
			div.fadeIn();
		});
	}
	else if (dark.is(':visible'))
	{
		$(dark).append(div);
		div.fadeIn();
	}
	else
	{
		$(dark).append(div);
		div.show();
		dark.fadeIn();
	}
	
	return div;
}


/**
 * Makes a link use AJAX instead of loading an entire page.
 *
 * @param object options Options, if a string will be the selector.
 * @param bool/function refresh If we are sent back a refresh, should it be
 * 	acted upon? This can either be true / false / a function.
 * @param function callback Callback.
 */
phpbb.ajaxify = function(options, refresh, callback) {

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
				dark.fadeOut(function() {
					div.remove();
				});
			}
		}, data.time * 1000);
	}

	var selector = (typeof options === 'string') ? options : options.selector;
	var is_form = $(selector).is('form');
	if (is_form && typeof selector === 'object')
	{
		selector = $(selector).find('input:submit');
	}
	else if (is_form)
	{
		selector += ' input:submit';
	}
	
	$(selector).click(function() {
		var act, data, path, that = this;
		
		if ($(this).data('ajax') == false)
		{
			return true;
		}
		
		function return_handler(res)
		{
			res = JSON.parse(res);
			
			if (typeof res.S_CONFIRM_ACTION === 'undefined')
			{
				/**
				 * It is a standard link, no confirm_box required.
				 */
				var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				callback = phpbb.ajax_callbacks[callback];
				if (typeof callback === 'function')
				{
					callback(that, (is_form) ? act : null);
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
						phpbb.loading_alert();
						$.post(path, data + '&confirm=' + res.YES_VALUE, function(res) {
							res = JSON.parse(res);
							var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
							callback = phpbb.ajax_callbacks[callback];
							if (typeof callback === 'function')
							{
								callback(that, res, (is_form) ? act : null);
							}
							handle_refresh(res.REFRESH_DATA, refresh, alert);
						});
					}
				}, false);
			}
		}
		
		var run_exception = typeof options.exception === 'function';
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
			else
			{
				data += '&' + this.name + '=' + this.value;
			}

			if (run_exception && options.exception($(this).parents('form'), act, data))
			{
				return true;
			}
			phpbb.loading_alert();
			$.post(path, data, return_handler);
		}
		else
		{
			if (run_exception && options.exception($(this).parents('form')))
			{
				return true;
			}
			phpbb.loading_alert();
			$.get(this.href, return_handler);
		}
		
		return false;
	});
	return this;
}

phpbb.ajax_callbacks = {};
phpbb.add_ajax_callback = function(id, callback)
{
	if (typeof callback === 'function')
	{
		phpbb.ajax_callbacks[id] = callback;
	}
	return this;
}


phpbb.add_ajax_callback('post_delete', function(el) {
	if ($(this).data('refresh') === undefined)
	{
		var pid = el.href.split('&p=')[1];
		$(el).parents('div #p' + pid).fadeOut(function() {
			$(this).remove();
		});
	}
}).add_ajax_callback('bookmark', function(el, res) {
	var text = (res.MESSAGE_TEXT.indexOf('Removed') === -1);
	text = (text) ? 'Remove from bookmarks' : 'Bookmark topic';
	$(el).text(el.title = text);
}).add_ajax_callback('topic_subscribe', function(el) {
	$(el).text(el.title = 'Unsubscribe topic');
}).add_ajax_callback('topic_unsubscribe', function(el) {
	$(el).text(el.title = 'Subscribe forum');
}).add_ajax_callback('forum_subscribe', function(el) {
	$(el).text(el.title = 'Unsubscribe topic');
}).add_ajax_callback('forum_unsubscribe', function(el) {
	$(el).text(el.title = 'Subscribe forum');
}).add_ajax_callback('post_approve', function(el, res, act) {
	$(el).parents((act === 'approve') ? '.rules' : '.post').fadeOut(function() {
		$(this).remove();
	});
}).add_ajax_callback('qr-submit', function(el) {
	$(el).parents('form').fadeOut(function() {
		$(this).remove();
	});
});



$('[data-ajax]').each(function() {
	var fn = ($(this).data('ajax') !== 'true') ? $(this).data('ajax') : null;
	phpbb.ajaxify({selector: this}, $(this).data('refresh') !== undefined, fn);
});



phpbb.ajaxify({
	selector: '#quickmodform',
	exception: function(el, act, data) {
		var d = $.querystring(data).action;
		if (d == 'make_normal')
		{
			return !(el.find('select option[value="make_global"]').length);
		}
		return !(d == 'lock' || d == 'unlock' || d == 'delete_topic' || d.slice(0, 5) == 'make_');
	}
}, true);


})(jQuery); //avoid conflicts with other libraries
