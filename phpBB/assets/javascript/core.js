var phpbb = {};
phpbb.alert_time = 100;

(function($) {  // Avoid conflicts with other libraries

"use strict";

// define a couple constants for keydown functions.
var keymap = {
	ENTER: 13,
	ESC: 27
};

var dark = $('#darkenwrapper');
var loading_alert = $('#loadingalert');


/**
 * Display a loading screen.
 *
 * @returns object Returns loading_alert.
 */
phpbb.loading_alert = function() {
	if (dark.is(':visible'))
	{
		loading_alert.fadeIn(phpbb.alert_time);
	}
	else
	{
		loading_alert.show();
		dark.fadeIn(phpbb.alert_time, function() {
			// Wait five seconds and display an error if nothing has been returned by then.
			setTimeout(function() {
				if (loading_alert.is(':visible'))
				{
					phpbb.alert($('#phpbb_alert').attr('data-l-err'), $('#phpbb_alert').attr('data-l-timeout-processing-req'));
				}
			}, 5000);
		});
	}

	return loading_alert;
}

/**
 * Display a simple alert similar to JSs native alert().
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param string title Title of the message, eg "Information" (HTML).
 * @param string msg Message to display (HTML).
 * @param bool fadedark Remove the dark background when done? Defaults
 * 	to yes.
 *
 * @returns object Returns the div created.
 */
phpbb.alert = function(title, msg, fadedark) {
	var div = $('#phpbb_alert');
	div.find('.alert_title').html(title);
	div.find('.alert_text').html(msg);

	div.bind('click', function(e) {
		e.stopPropagation();
	});
	dark.one('click', function(e) {
		var fade;

		div.find('.alert_close').unbind('click');
		fade = (typeof fadedark !== 'undefined' && !fadedark) ? div : dark;
		fade.fadeOut(phpbb.alert_time, function() {
			div.hide();
		});

		e.preventDefault();
		e.stopPropagation();
	});

	$(document).bind('keydown', function(e) {
		if (e.keyCode === keymap.ENTER || e.keyCode === keymap.ESC) {
			dark.trigger('click');

			e.preventDefault();
			e.stopPropagation();
		}
	});

	div.find('.alert_close').one('click', function(e) {
		dark.trigger('click');

		e.preventDefault();
	});

	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(phpbb.alert_time, function() {
			dark.append(div);
			div.fadeIn(phpbb.alert_time);
		});
	}
	else if (dark.is(':visible'))
	{
		dark.append(div);
		div.fadeIn(phpbb.alert_time);
	}
	else
	{
		dark.append(div);
		div.show();
		dark.fadeIn(phpbb.alert_time);
	}

	return div;
}

/**
 * Display a simple yes / no box to the user.
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param string msg Message to display (HTML).
 * @param function callback Callback. Bool param, whether the user pressed
 * 	yes or no (or whatever their language is).
 * @param bool fadedark Remove the dark background when done? Defaults
 * 	to yes.
 *
 * @returns object Returns the div created.
 */
phpbb.confirm = function(msg, callback, fadedark) {
	var div = $('#phpbb_confirm');
	div.find('.alert_text').html(msg);

	div.bind('click', function(e) {
		e.stopPropagation();
	});

	var click_handler = function(e) {
		var res = this.className === 'button1';
		var fade = (typeof fadedark !== 'undefined' && !fadedark && res) ? div : dark;
		fade.fadeOut(phpbb.alert_time, function() {
			div.hide();
		});
		div.find('input[type="button"]').unbind('click', click_handler);
		callback(res);

		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}
	};
	div.find('input[type="button"]').one('click', click_handler);

	dark.one('click', function(e) {
		div.find('.alert_close').unbind('click');
		dark.fadeOut(phpbb.alert_time, function() {
			div.hide();
		});
		callback(false);

		e.preventDefault();
		e.stopPropagation();
	});

	$(document).bind('keydown', function(e) {
		if (e.keyCode === keymap.ENTER) {
			$('input[type="button"].button1').trigger('click');
			e.preventDefault();
			e.stopPropagation();
		} else if (e.keyCode === keymap.ESC) {
			$('input[type="button"].button2').trigger('click');
			e.preventDefault();
			e.stopPropagation();
		}
	});

	div.find('.alert_close').one('click', function(e) {
		var fade = (typeof fadedark !== 'undefined' && fadedark) ? div : dark;
		fade.fadeOut(phpbb.alert_time, function() {
			div.hide();
		});
		callback(false);

		e.preventDefault();
	});

	if (loading_alert.is(':visible'))
	{
		loading_alert.fadeOut(phpbb.alert_time, function() {
			dark.append(div);
			div.fadeIn(phpbb.alert_time);
		});
	}
	else if (dark.is(':visible'))
	{
		dark.append(div);
		div.fadeIn(phpbb.alert_time);
	}
	else
	{
		dark.append(div);
		div.show();
		dark.fadeIn(phpbb.alert_time);
	}

	return div;
}

/**
 * Turn a querystring into an array.
 *
 * @argument string string The querystring to parse.
 * @returns object The object created.
 */
phpbb.parse_querystring = function(string) {
	var params = {}, i, split;

	string = string.split('&');
	for (i = 0; i < string.length; i++)
	{
		split = string[i].split('=');
		params[split[0]] = decodeURIComponent(split[1]);
	}
	return params;
}


/**
 * Makes a link use AJAX instead of loading an entire page.
 *
 * This function will work for links (both standard links and links which
 * invoke confirm_box) and forms. It will be called automatically for links
 * and forms with the data-ajax attribute set, and will call the necessary
 * callback.
 *
 * For more info, view the following page on the phpBB wiki:
 * http://wiki.phpbb.com/JavaScript_Function.phpbb.ajaxify
 *
 * @param object options Options.
 * @param bool/function refresh If we are sent back a refresh, should it be
 * 	acted upon? This can either be true / false / a function.
 * @param function callback Callback to call on completion of event. Has
 * 	three parameters: the element that the event was evoked from, the JSON
 * 	that was returned and (if it is a form) the form action.
 */
phpbb.ajaxify = function(options) {
	var elements = $(options.selector),
		refresh = options.refresh,
		callback = options.callback,
		is_form = elements.is('form'),
		event_name = is_form ? 'submit' : 'click';

	elements.bind(event_name, function(event) {
		var action, method, data, submit, that = this, $this = $(this);

		if ($this.find('input[type="submit"][data-clicked]').attr('data-ajax') === 'false')
		{
			return;
		}

		/**
		 * This is a private function used to handle the callbacks, refreshes
		 * and alert. It calls the callback, refreshes the page if necessary, and
		 * displays an alert to the user and removes it after an amount of time.
		 *
		 * It cannot be called from outside this function, and is purely here to
		 * avoid repetition of code.
		 *
		 * @param object res The object sent back by the server.
		 */
		function return_handler(res)
		{
			var alert;

			// Is a confirmation required?
			if (typeof res.S_CONFIRM_ACTION === 'undefined')
			{
				// If a confirmation is not required, display an alert and call the
				// callbacks.
				if (typeof res.MESSAGE_TITLE !== 'undefined')
				{
					alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				}
				else
				{
					dark.fadeOut(phpbb.alert_time);
				}

				if (typeof phpbb.ajax_callbacks[callback] === 'function')
				{
					phpbb.ajax_callbacks[callback].call(that, res);
				}

				// If the server says to refresh the page, check whether the page should
				// be refreshed and refresh page after specified time if required.
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

						// Hide the alert even if we refresh the page, in case the user
						// presses the back button.
						dark.fadeOut(phpbb.alert_time, function() {
							alert.hide();
						});
					}, res.REFRESH_DATA.time * 1000); // Server specifies time in seconds
				}
			}
			else
			{
				// If confirmation is required, display a diologue to the user.
				phpbb.confirm(res.MESSAGE_TEXT, function(del) {
					if (del)
					{
						phpbb.loading_alert();
						data =  $('<form>' + res.S_HIDDEN_FIELDS + '</form>').serialize();
						$.ajax({
							url: res.S_CONFIRM_ACTION,
							type: 'POST',
							data: data + '&confirm=' + res.YES_VALUE,
							success: return_handler,
							error: error_handler
						});
					}
				}, false);
			}
		}

		function error_handler()
		{
			var alert;

			alert = phpbb.alert(dark.attr('data-ajax-error-title'), dark.attr('data-ajax-error-text'));

			setTimeout(function () {
				dark.fadeOut(phpbb.alert_time, function() {
					alert.hide();
				});
			}, 5000);
		}

		// If the element is a form, POST must be used and some extra data must
		// be taken from the form.
		var run_filter = (typeof options.filter === 'function');

		if (is_form)
		{
			action = $this.attr('action').replace('&amp;', '&');
			data = $this.serializeArray();
			method = $this.attr('method') || 'GET';

			if ($this.find('input[type="submit"][data-clicked]'))
			{
				submit = $this.find('input[type="submit"][data-clicked]');
				data.push({
					name: submit.attr('name'),
					value: submit.val()
				});
			}
		}
		else
		{
			action = this.href;
			data = null;
			method = 'GET';
		}

		// If filter function returns false, cancel the AJAX functionality,
		// and return true (meaning that the HTTP request will be sent normally).
		if (run_filter && !options.filter.call(this, data))
		{
			return;
		}

		phpbb.loading_alert();

		$.ajax({
			url: action,
			type: method,
			data: data,
			success: return_handler,
			error: error_handler
		});

		event.preventDefault();
	});

	if (is_form) {
		elements.find('input:submit').click(function () {
			var $this = $(this);

			$this.siblings('[data-clicked]').removeAttr('data-clicked');
			$this.attr('data-clicked', 'true');
		});
	}

	return this;
}

phpbb.ajax_callbacks = {};

/**
 * Adds an AJAX callback to be used by phpbb.ajaxify.
 *
 * See the phpbb.ajaxify comments for information on stuff like parameters.
 *
 * @param string id The name of the callback.
 * @param function callback The callback to be called.
 */
phpbb.add_ajax_callback = function(id, callback)
{
	if (typeof callback === 'function')
	{
		phpbb.ajax_callbacks[id] = callback;
	}
	return this;
}


/**
 * This callback alternates text - it replaces the current text with the text in
 * the alt-text data attribute, and replaces the text in the attribute with the
 * current text so that the process can be repeated.
 */
phpbb.add_ajax_callback('alt_text', function(data) {
	var el = $(this),
		alt_text;

	alt_text = el.attr('data-alt-text');
	el.attr('title', alt_text);
	el.text(alt_text);
});


})(jQuery); // Avoid conflicts with other libraries
