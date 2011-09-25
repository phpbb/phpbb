var phpbb = {};

(function($) {  // Avoid conflicts with other libraries



var dark = $('#darkenwrapper'),
	loading_alert = $('#loadingalert');


/**
 * Display a loading screen.
 *
 * @returns object Returns loading_alert.
 */
phpbb.loading_alert = function() {
	if (dark.is(':visible'))
	{
		loading_alert.fadeIn(100);
	}
	else
	{
		loading_alert.show();
		dark.fadeIn(100, function() {
			setTimeout(function() {
				if (loading_alert.is(':visible'))
				{
					phpbb.alert($('body').data('l-err'), $('body').data('l-err-processing-req'));
				}
			}, 5000);
		});
	}

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
 * @returns object Returns the div created.
 */
phpbb.alert = function(title, msg, fadedark) {
	var div = $('#phpbb_alert');
	div.find('h3').html(title);
	div.find('p').html(msg);

	div.bind('click', function(e) {
		e.stopPropagation();
		return true;
	});
	dark.one('click', function(e) {
		var fade = (typeof fadedark !== 'undefined' && !fadedark) ? div : dark;
		fade.fadeOut(100, function() {
			div.hide();
		});
		return false;
	});
	
	$(document).bind('keydown', function(e) {
		if (e.keyCode === 13 || e.keyCode === 27) {
			dark.trigger('click');
			return false;
		}
		return true;
	});
	
	div.find('.alert_close').one('click', function() {
		dark.trigger('click');
	});

	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(100, function() {
			dark.append(div);
			div.fadeIn(100);
		});
	}
	else if (dark.is(':visible'))
	{
		dark.append(div);
		div.fadeIn(100);
	}
	else
	{
		dark.append(div);
		div.show();
		dark.fadeIn(100);
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
 * @returns object Returns the div created.
 */
phpbb.confirm = function(msg, callback, fadedark) {
	var div = $('#phpbb_confirm');
	div.find('p').html(msg);

	div.bind('click', function(e) {
		e.stopPropagation();
		return true;
	});
	div.find('input[type="button"]').one('click', function() {
		var res = this.className === 'button1';
		var fade = (typeof fadedark !== 'undefined' && !fadedark && res) ? div : dark;
		fade.fadeOut(100, function() {
			div.hide();
		});
		div.find('input[type="button"]').unbind('click');
		callback(res);
		return false;
	});

	dark.one('click', function(e) {
		var fade = (typeof fadedark !== 'undefined' && !fadedark && res) ? div : dark;
		fade.fadeOut(100, function() {
			div.hide();
		});
		callback(false);
		return false;
	});
	
	$(document).bind('keydown', function(e) {
		if (e.keyCode === 13) {
			$('input[type="button"].button1').trigger('click');
			return false;
		} else if (e.keyCode === 27) {
			$('input[type="button"].button2').trigger('click');
			return false;
		}
		return true;
	});
	
	div.find('.alert_close').one('click', function() {
		dark.trigger('click');
	});
	
	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(100, function() {
			dark.append(div);
			div.fadeIn(100);
		});
	}
	else if (dark.is(':visible'))
	{
		dark.append(div);
		div.fadeIn(100);
	}
	else
	{
		dark.append(div);
		div.show();
		dark.fadeIn(100);
	}
	
	return div;
}

/**
 * Turn a querystring into an array.
 *
 * @argument string string The querystring to parse.
 * @returns array The array created.
 */
phpbb.parse_querystring = function(string) {
	var end = {}, i;
	
	string = string.split('&');
	for (i = 0; i < string.length; i++)
	{
		end[string[i].split('=')[0]] = decodeURIComponent(string[i].split('=')[1]);
	}
	return end;
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
	var selector = $((typeof options === 'string') ? options : options.selector);
	var is_form = selector.is('form');
	if (is_form)
	{
		selector = selector.find('input:submit');
	}
	
	selector.click(function() {
		var act, data, path, that = this;
		
		if ($(this).data('ajax') == false)
		{
			return true;
		}
		
		function return_handler(res)
		{
			if (typeof res.S_CONFIRM_ACTION === 'undefined')
			{
				// It is a standard link, no confirm_box required.
				if (typeof res.MESSAGE_TITLE !== 'undefined')
				{
					var alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				}
				else
				{
					dark.fadeOut(100);
				}
				
				if (typeof phpbb.ajax_callbacks[callback] === 'function')
				{
					phpbb.ajax_callbacks[callback](that, res, (is_form) ? act : null);
				}

				if (res.REFRESH_DATA)
				{
					if (typeof refresh === 'function')
					{
						refresh = refresh(res.REFRESH_DATA.url);
					}
					else if (typeof refresh !== 'boolean')
					{
						refresh = false;
					}
		
					setTimeout(function() {
						if (refresh)
						{
							window.location = res.REFRESH_DATA.url;
						}
						
						dark.fadeOut(100, function() {
							alert.hide();
						});
					}, res.REFRESH_DATA.time * 1000);
				}
			}
			else
			{
				// confirm_box - confirm with the user and send back
				phpbb.confirm(res.MESSAGE_TEXT, function(del) {
					if (del)
					{
						phpbb.loading_alert();
						data =  $('<form>' + res.S_HIDDEN_FIELDS + '</form>').serialize();
						$.post(res.S_CONFIRM_ACTION, data + '&confirm=' + res.YES_VALUE, return_handler);
					}
				}, false);
			}
		}
		
		var run_exception = (typeof options.exception === 'function');
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


phpbb.add_ajax_callback('alt_text', function(el) {
	var alt_text = $(el).data('alt-text');
	$(el).data('alt-text', $(el).text());
	$(el).text(el.title = alt_text);
});


})(jQuery); // Avoid conflicts with other libraries