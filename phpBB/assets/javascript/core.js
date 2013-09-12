var phpbb = {};
phpbb.alertTime = 100;

(function($) {  // Avoid conflicts with other libraries

"use strict";

// define a couple constants for keydown functions.
var keymap = {
	ENTER: 13,
	ESC: 27,
    UP: 38,
    DOWN: 40
};

var dark = $('#darkenwrapper');
var loadingAlert = $('#loadingalert');
var phpbbAlertTimer = null;


/**
 * Display a loading screen
 *
 * @returns object Returns loadingAlert.
 */
phpbb.loadingAlert = function() {
	if (dark.is(':visible')) {
		loadingAlert.fadeIn(phpbb.alertTime);
	} else {
		loadingAlert.show();
		dark.fadeIn(phpbb.alertTime, function() {
			// Wait five seconds and display an error if nothing has been returned by then.
			phpbbAlertTimer = setTimeout(function() {
				if (loadingAlert.is(':visible')) {
					phpbb.alert($('#phpbb_alert').attr('data-l-err'), $('#phpbb_alert').attr('data-l-timeout-processing-req'));
				}
			}, 5000);
		});
	}

	return loadingAlert;
};

/**
 * Clear loading alert timeout
*/
phpbb.clearLoadingTimeout = function() {
	if (phpbbAlertTimer !== null) {
		clearTimeout(phpbbAlertTimer);
		phpbbAlertTimer = null;
	}
};

/**
 * Display a simple alert similar to JSs native alert().
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param string title Title of the message, eg "Information" (HTML).
 * @param string msg Message to display (HTML).
 * @param bool fadedark Remove the dark background when done? Defaults
 *     to yes.
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
		fade.fadeOut(phpbb.alertTime, function() {
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

	if (loadingAlert.is(':visible')) {
		loadingAlert.fadeOut(phpbb.alertTime, function() {
			dark.append(div);
			div.fadeIn(phpbb.alertTime);
		});
	} else if (dark.is(':visible')) {
		dark.append(div);
		div.fadeIn(phpbb.alertTime);
	} else {
		dark.append(div);
		div.show();
		dark.fadeIn(phpbb.alertTime);
	}

	return div;
};

/**
 * Display a simple yes / no box to the user.
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param string msg Message to display (HTML).
 * @param function callback Callback. Bool param, whether the user pressed
 *     yes or no (or whatever their language is).
 * @param bool fadedark Remove the dark background when done? Defaults
 *     to yes.
 *
 * @returns object Returns the div created.
 */
phpbb.confirm = function(msg, callback, fadedark) {
	var div = $('#phpbb_confirm');
	div.find('.alert_text').html(msg);

	div.bind('click', function(e) {
		e.stopPropagation();
	});

	var clickHandler = function(e) {
		var res = this.name === 'confirm';
		var fade = (typeof fadedark !== 'undefined' && !fadedark && res) ? div : dark;
		fade.fadeOut(phpbb.alertTime, function() {
			div.hide();
		});
		div.find('input[type="button"]').unbind('click', clickHandler);
		callback(res);

		if (e) {
			e.preventDefault();
			e.stopPropagation();
		}
	};
	div.find('input[type="button"]').one('click', clickHandler);

	dark.one('click', function(e) {
		div.find('.alert_close').unbind('click');
		dark.fadeOut(phpbb.alertTime, function() {
			div.hide();
		});
		callback(false);

		e.preventDefault();
		e.stopPropagation();
	});

	$(document).bind('keydown', function(e) {
		if (e.keyCode === keymap.ENTER) {
			$('input[name="confirm"]').trigger('click');
			e.preventDefault();
			e.stopPropagation();
		} else if (e.keyCode === keymap.ESC) {
			$('input[name="cancel"]').trigger('click');
			e.preventDefault();
			e.stopPropagation();
		}
	});

	div.find('.alert_close').one('click', function(e) {
		var fade = (typeof fadedark !== 'undefined' && fadedark) ? div : dark;
		fade.fadeOut(phpbb.alertTime, function() {
			div.hide();
		});
		callback(false);

		e.preventDefault();
	});

	if (loadingAlert.is(':visible')) {
		loadingAlert.fadeOut(phpbb.alertTime, function() {
			dark.append(div);
			div.fadeIn(phpbb.alertTime);
		});
	} else if (dark.is(':visible')) {
		dark.append(div);
		div.fadeIn(phpbb.alertTime);
	} else {
		dark.append(div);
		div.show();
		dark.fadeIn(phpbb.alertTime);
	}

	return div;
};

/**
 * Turn a querystring into an array.
 *
 * @argument string string The querystring to parse.
 * @returns object The object created.
 */
phpbb.parseQuerystring = function(string) {
	var params = {}, i, split;

	string = string.split('&');
	for (i = 0; i < string.length; i++) {
		split = string[i].split('=');
		params[split[0]] = decodeURIComponent(split[1]);
	}
	return params;
};


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
 *     acted upon? This can either be true / false / a function.
 * @param function callback Callback to call on completion of event. Has
 *     three parameters: the element that the event was evoked from, the JSON
 *     that was returned and (if it is a form) the form action.
 */
phpbb.ajaxify = function(options) {
	var elements = $(options.selector),
		refresh = options.refresh,
		callback = options.callback,
		overlay = (typeof options.overlay !== 'undefined') ? options.overlay : true,
		isForm = elements.is('form'),
		eventName = isForm ? 'submit' : 'click';

	elements.bind(eventName, function(event) {
		var action, method, data, submit, that = this, $this = $(this);

		if ($this.find('input[type="submit"][data-clicked]').attr('data-ajax') === 'false') {
			return;
		}

		/**
		 * Handler for AJAX errors
		 */
		function errorHandler(jqXHR, textStatus, errorThrown) {
			if (console && console.log) {
				console.log('AJAX error. status: ' + textStatus + ', message: ' + errorThrown);
			}
			phpbb.clearLoadingTimeout();
			var errorText = false;
			if (typeof errorThrown === 'string' && errorThrown.length > 0) {
				errorText = errorThrown;
			}
			else {
				errorText = dark.attr('data-ajax-error-text-' + textStatus);
				if (typeof errorText !== 'string' || !errorText.length) 
					errorText = dark.attr('data-ajax-error-text');
			}
			phpbb.alert(dark.attr('data-ajax-error-title'), errorText);
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
		function returnHandler(res) {
			var alert;

			phpbb.clearLoadingTimeout();

			// Is a confirmation required?
			if (typeof res.S_CONFIRM_ACTION === 'undefined') {
				// If a confirmation is not required, display an alert and call the
				// callbacks.
				if (typeof res.MESSAGE_TITLE !== 'undefined') {
					alert = phpbb.alert(res.MESSAGE_TITLE, res.MESSAGE_TEXT);
				} else {
					dark.fadeOut(phpbb.alertTime);
				}

				if (typeof phpbb.ajaxCallbacks[callback] === 'function') {
					phpbb.ajaxCallbacks[callback].call(that, res);
				}

				// If the server says to refresh the page, check whether the page should
				// be refreshed and refresh page after specified time if required.
				if (res.REFRESH_DATA) {
					if (typeof refresh === 'function') {
						refresh = refresh(res.REFRESH_DATA.url);
					} else if (typeof refresh !== 'boolean') {
						refresh = false;
					}

					setTimeout(function() {
						if (refresh) {
							window.location = res.REFRESH_DATA.url;
						}

						// Hide the alert even if we refresh the page, in case the user
						// presses the back button.
						dark.fadeOut(phpbb.alertTime, function() {
							alert.hide();
						});
					}, res.REFRESH_DATA.time * 1000); // Server specifies time in seconds
				}
			} else {
				// If confirmation is required, display a dialog to the user.
				phpbb.confirm(res.MESSAGE_BODY, function(del) {
					if (del) {
						phpbb.loadingAlert();
						data =  $('<form>' + res.S_HIDDEN_FIELDS + '</form>').serialize();
						$.ajax({
							url: res.S_CONFIRM_ACTION,
							type: 'POST',
							data: data + '&confirm=' + res.YES_VALUE,
							success: returnHandler,
							error: errorHandler
						});
					}
				}, false);
			}
		}

		// If the element is a form, POST must be used and some extra data must
		// be taken from the form.
		var runFilter = (typeof options.filter === 'function');

		if (isForm) {
			action = $this.attr('action').replace('&amp;', '&');
			data = $this.serializeArray();
			method = $this.attr('method') || 'GET';

			if ($this.find('input[type="submit"][data-clicked]')) {
				submit = $this.find('input[type="submit"][data-clicked]');
				data.push({
					name: submit.attr('name'),
					value: submit.val()
				});
			}
		} else {
			action = this.href;
			data = null;
			method = 'GET';
		}

		// If filter function returns false, cancel the AJAX functionality,
		// and return true (meaning that the HTTP request will be sent normally).
		if (runFilter && !options.filter.call(this, data)) {
			return;
		}

		if (overlay && (typeof $this.attr('data-overlay') === 'undefined' || $this.attr('data-overlay') === 'true')) {
			phpbb.loadingAlert();
		}

		$.ajax({
			url: action,
			type: method,
			data: data,
			success: returnHandler,
			error: errorHandler
		});

		event.preventDefault();
	});

	if (isForm) {
		elements.find('input:submit').click(function () {
			var $this = $(this);

			$this.siblings('[data-clicked]').removeAttr('data-clicked');
			$this.attr('data-clicked', 'true');
		});
	}

	return this;
};

/**
* Hide the optgroups that are not the selected timezone
*
* @param	bool	keepSelection		Shall we keep the value selected, or shall the user be forced to repick one.
*/
phpbb.timezoneSwitchDate = function(keepSelection) {
	if ($('#timezone_copy').length === 0) {
		// We make a backup of the original dropdown, so we can remove optgroups
		// instead of setting display to none, because IE and chrome will not
		// hide options inside of optgroups and selects via css
		$('#timezone').clone().attr('id', 'timezone_copy').css('display', 'none').attr('name', 'tz_copy').insertAfter('#timezone');
	} else {
		// Copy the content of our backup, so we can remove all unneeded options
		$('#timezone').replaceWith($('#timezone_copy').clone().attr('id', 'timezone').css('display', 'block').attr('name', 'tz'));
	}

	if ($('#tz_date').val() !== '') {
		$('#timezone > optgroup').remove(":not([label='" + $('#tz_date').val() + "'])");
	}

	if ($('#tz_date').val() === $('#tz_select_date_suggest').attr('data-suggested-tz')) {
		$('#tz_select_date_suggest').css('display', 'none');
	} else {
		$('#tz_select_date_suggest').css('display', 'inline');
	}

	if ($("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option").size() === 1) {
		// If there is only one timezone for the selected date, we just select that automatically.
		$("#timezone > optgroup[label='" + $('#tz_date').val() + "'] > option:first").attr('selected', true);
		keepSelection = true;
	}

	if (typeof keepSelection !== 'undefined' && !keepSelection) {
		var timezoneOptions = $('#timezone > optgroup option');
		if (timezoneOptions.filter(':selected').length <= 0) {
			timezoneOptions.filter(':first').attr('selected', true);
		}
	}
};

/**
* Display the date/time select
*/
phpbb.timezoneEnableDateSelection = function() {
	$('#tz_select_date').css('display', 'block');
};

/**
* Preselect a date/time or suggest one, if it is not picked.
*
* @param	bool	forceSelector		Shall we select the suggestion?
*/
phpbb.timezonePreselectSelect = function(forceSelector) {

	// The offset returned here is in minutes and negated.
	// http://www.w3schools.com/jsref/jsref_getTimezoneOffset.asp
	var offset = (new Date()).getTimezoneOffset();
	var sign = '-';

	if (offset < 0) {
		sign = '+';
		offset = -offset;
	}

	var minutes = offset % 60;
	var hours = (offset - minutes) / 60;

	if (hours < 10) {
		hours = '0' + hours.toString();
	} else {
		hours = hours.toString();
	}

	if (minutes < 10) {
		minutes = '0' + minutes.toString();
	} else {
		minutes = minutes.toString();
	}

	var prefix = 'GMT' + sign + hours + ':' + minutes;
	var prefixLength = prefix.length;
	var selectorOptions = $('#tz_date > option');
	var i;

	for (i = 0; i < selectorOptions.length; ++i) {
		var option = selectorOptions[i];

		if (option.value.substring(0, prefixLength) === prefix) {
			if ($('#tz_date').val() !== option.value && !forceSelector) {
				// We do not select the option for the user, but notify him,
				// that we would suggest a different setting.
				phpbb.timezoneSwitchDate(true);
				$('#tz_select_date_suggest').css('display', 'inline');
			} else {
				option.selected = true;
				phpbb.timezoneSwitchDate(!forceSelector);
				$('#tz_select_date_suggest').css('display', 'none');
			}

			$('#tz_select_date_suggest').attr('title', $('#tz_select_date_suggest').attr('data-l-suggestion').replace("%s", option.innerHTML));
			$('#tz_select_date_suggest').attr('value', $('#tz_select_date_suggest').attr('data-l-suggestion').replace("%s", option.innerHTML.substring(0, 9)));
			$('#tz_select_date_suggest').attr('data-suggested-tz', option.innerHTML);

			// Found the suggestion, there cannot be more, so return from here.
			return;
		}
	}
};

// Toggle notification list
$('#notification_list_button').click(function(e) {
	$('#notification_list').toggle();
	e.preventDefault();
});
$('#phpbb').click(function(e) {
    var target = $(e.target);

    if (!target.is('#notification_list') && !target.is('#notification_list_button') && !target.parents().is('#notification_list')) {
        $('#notification_list').hide();
    }
});

phpbb.ajaxCallbacks = {};

/**
 * Adds an AJAX callback to be used by phpbb.ajaxify.
 *
 * See the phpbb.ajaxify comments for information on stuff like parameters.
 *
 * @param string id The name of the callback.
 * @param function callback The callback to be called.
 */
phpbb.addAjaxCallback = function(id, callback) {
	if (typeof callback === 'function') {
		phpbb.ajaxCallbacks[id] = callback;
	}
	return this;
};


/**
 * This callback alternates text - it replaces the current text with the text in
 * the alt-text data attribute, and replaces the text in the attribute with the
 * current text so that the process can be repeated.
 */
phpbb.addAjaxCallback('alt_text', function() {
	var el = $(this),
		altText;

	altText = el.attr('data-alt-text');
	el.attr('data-alt-text', el.text());
	el.attr('title', altText);
	el.text(altText);
});

/**
 * This callback is based on the alt_text callback.
 *
 * It replaces the current text with the text in the alt-text data attribute,
 * and replaces the text in the attribute with the current text so that the
 * process can be repeated.
 * Additionally it replaces the class of the link's parent
 * and changes the link itself.
 */
phpbb.addAjaxCallback('toggle_link', function() {
	var el = $(this),
		toggleText,
		toggleUrl,
		toggleClass;

	// Toggle link text

	toggleText = el.attr('data-toggle-text');
	el.attr('data-toggle-text', el.text());
	el.attr('title', toggleText);
	el.text(toggleText);

	// Toggle link url
	toggleUrl = el.attr('data-toggle-url');
	el.attr('data-toggle-url', el.attr('href'));
	el.attr('href', toggleUrl);

	// Toggle class of link parent
	toggleClass = el.attr('data-toggle-class');
	el.attr('data-toggle-class', el.parent().attr('class'));
	el.parent().attr('class', toggleClass);
});

/**
* Automatically resize textarea
*
* This function automatically resizes textarea elements when user
* types text.
*
* @param {jQuery} items jQuery object(s) to resize
* @param {object} options Optional parameter that adjusts default
* 	configuration. See configuration variable
*
* Optional parameters:
*	minWindowHeight {number} Minimum browser window height when textareas are resized. Default = 500
*	minHeight {number} Minimum height of textarea. Default = 200
*	maxHeight {number} Maximum height of textarea. Default = 500
*	heightDiff {number} Minimum difference between window and textarea height. Default = 200
*	resizeCallback {function} Function to call after resizing textarea
*	resetCallback {function} Function to call when resize has been canceled

*		Callback function format: function(item) {}
*			this points to DOM object
*			item is a jQuery object, same as this
*/
phpbb.resizeTextArea = function(items, options) {
	// Configuration
	var configuration = {
		minWindowHeight: 500,
		minHeight: 200,
		maxHeight: 500,
		heightDiff: 200,
		resizeCallback: function(item) { },
		resetCallback: function(item) { }
	};

	if (arguments.length > 1)
	{
		configuration = $.extend(configuration, options);
	}

	function resetAutoResize(item) 
	{
		var $item = $(item);
		if ($item.hasClass('auto-resized'))
		{
			$(item).css({height: '', resize: ''}).removeClass('auto-resized');
			configuration.resetCallback.call(item, $item);
		}
	}

	function autoResize(item) 
	{
		function setHeight(height)
		{
			$item.css({height: height + 'px', resize: 'none'}).addClass('auto-resized');
			configuration.resizeCallback.call(item, $item);
		}

		var windowHeight = $(window).height();

		if (windowHeight < configuration.minWindowHeight)
		{
			resetAutoResize(item);
			return;
		}

		var maxHeight = Math.min(Math.max(windowHeight - configuration.heightDiff, configuration.minHeight), configuration.maxHeight),
			$item = $(item),
			height = parseInt($item.height()),
			scrollHeight = (item.scrollHeight) ? item.scrollHeight : 0;

		if (height > maxHeight)
		{
			setHeight(maxHeight);
		}
		else if (scrollHeight > (height + 5))
		{
			setHeight(Math.min(maxHeight, scrollHeight));
		}
	}

	items.bind('focus change keyup', function() {
		$(this).each(function() {
			autoResize(this);
		});
	}).change();

	$(window).resize(function() {
		items.each(function() {
			if ($(this).hasClass('auto-resized'))
			{
				autoResize(this);
			}
		});
	});
};

/**
* Check if cursor in textarea is currently inside a bbcode tag
*
* @param {object} textarea Textarea DOM object
* @param {Array} startTags List of start tags to look for
*		For example, Array('[code]', '[code=')
* @param {Array} endTags List of end tags to look for
*		For example, Array('[/code]')
*
* @return {boolean} True if cursor is in bbcode tag
*/
phpbb.inBBCodeTag = function(textarea, startTags, endTags) {
	var start = textarea.selectionStart,
		lastEnd = -1,
		lastStart = -1,
		i, index, value;

	if (typeof start !== 'number') {
		return false;
	}

	value = textarea.value.toLowerCase();

	for (i = 0; i < startTags.length; i++) {
		var tagLength = startTags[i].length;
		if (start >= tagLength) {
			index = value.lastIndexOf(startTags[i], start - tagLength);
			lastStart = Math.max(lastStart, index);
		}
	}
	if (lastStart == -1) return false;

	if (start > 0) {
		for (i = 0; i < endTags.length; i++) {
			index = value.lastIndexOf(endTags[i], start - 1);
			lastEnd = Math.max(lastEnd, index);
		}
	}

	return (lastEnd < lastStart);
}


/**
* Adjust textarea to manage code bbcode
*
* This function allows to use tab characters when typing code
* and keeps indentation of previous line of code when adding new
* line while typing code.
*
* Editor's functionality is changed only when cursor is between
* [code] and [/code] bbcode tags.
*
* @param {object} textarea Textarea DOM object to apply editor to
*/
phpbb.applyCodeEditor = function(textarea) {
	// list of allowed start and end bbcode code tags, in lower case
	var startTags = ['[code]', '[code='],
		startTagsEnd = ']',
		endTags = ['[/code]'];

	if (!textarea || typeof textarea.selectionStart !== 'number') {
		return;
	}

	if ($(textarea).data('code-editor') === true) {
		return;
	}

	function inTag() {
		return phpbb.inBBCodeTag(textarea, startTags, endTags);
	}

	/**
	* Get line of text before cursor
	*
	* @param {boolean} stripCodeStart If true, only part of line
	*		after [code] tag will be returned.
	*
	* @return {string} Line of text
	*/
	function getLastLine(stripCodeStart) {
		var start = textarea.selectionStart,
			value = textarea.value,
			index = value.lastIndexOf("\n", start - 1);

		value = value.substring(index + 1, start);

		if (stripCodeStart) {
			for (var i = 0; i < startTags.length; i++) {
				index = value.lastIndexOf(startTags[i]);
				if (index >= 0) {
					var tagLength = startTags[i].length;

					value = value.substring(index + tagLength);
					if (startTags[i].lastIndexOf(startTagsEnd) != tagLength) {
						index = value.indexOf(startTagsEnd);

						if (index >= 0) {
							value = value.substr(index + 1);
						}
					}
				}
			}
		}

		return value;
	}

	/**
	* Append text at cursor position
	*
	* @param {string} Text Text to append
	*/
	function appendText(text) {
		var start = textarea.selectionStart,
			end = textarea.selectionEnd,
			value = textarea.value;

		textarea.value = value.substr(0, start) + text + value.substr(end);
		textarea.selectionStart = textarea.selectionEnd = start + text.length;
	}

	$(textarea).data('code-editor', true).on('keydown', function(event) {
		var key = event.keyCode || event.which;

		// intercept tabs
		if (key == 9) {
			if (inTag()) {
				appendText("\t");
				event.preventDefault();
				return;
			}
		}

		// intercept new line characters
		if (key == 13) {
			if (inTag()) {
				var lastLine = getLastLine(true),
					code = '' + /^\s*/g.exec(lastLine);

				if (code.length > 0) {
					appendText("\n" + code);
					event.preventDefault();
					return;
				}
			}
		}
	});
};

/**
* Apply code editor to all textarea elements with data-bbcode attribute
*/
$(document).ready(function() {
	$('textarea[data-bbcode]').each(function() {
		phpbb.applyCodeEditor(this);
	});
});


/**
 * Shows options when user inputs something like jQuery UI Autocomplete
 *
 * @param {Object} config Basic Suggest Configuration. See the parameters below.
 *
 * Mandatory Parameters
 * src {String} The page from where to get the suggestions
 * inputID {String} The ID of the input/textarea
 * 
 * Optional Parameters
 * allowMultiple {Bool} Whether to allow multiple values on input. Default False.
 * minCharacters {Number} Mininmum characters typed before its starts suggesting. Default 2.
 * maxOptions {Number} Maximum no of Results to show. Default 20.
 * delim {String} Delimiter for multiple options (allowMultiple should be set to true). Default \n.
 * 
 * You can also invoke this using markup and without using Javascript using the given attributes
 * Add data-suggest in the controller element to invoke this e.g. data-suggest='username'
 * Using data-allowmultiple you can set whether multiple options are possible or not
 * Using data-delim you can set the delimiter which will be used to seperate multiple options e.g. data-delim=","
 *
 * @return null
 */

phpbb.suggest = function(config)
{
    var options = {
        inputID: null,
        src: null,
        delim: '\n',
        minCharacters: 2,
        maxOptions: 20,
        allowMultiple: false
    };

    config = $.extend(options, config);
    var control = $("#" + config.inputID); //The input box

    //Only allow multiple values for textarea
    config.allowMultiple = config.allowMultiple && config.delim && control.is("textarea");

    // Prevent browser suggestions
    control.attr("autocomplete","off");

    control.keydown(function (e)
    {
        if (e.keyCode == keymap.UP || e.keyCode == keymap.DOWN || e.keyCode == keymap.ENTER)
        {
            //Supress the default behaviour of the above keys
            e.preventDefault();
        }
            
    });
    
    //When someones inputs something
    control.keyup(function (e)
    {
        var partialLen = partialText().length;
        
        //Capture the key up/down events
        //And if captured then don't proceed forward as user has only pressed arrow key
        if (keyCapture($(this), e.keyCode))
        {
            return false;
        }

        //If users deletes everthing
        if (partialLen == 0)
        {
            hideUl();
        }
                    
        //Let the length of the string be more than
        //minCharacters
        if (partialLen <= config.minCharacters)
        {
            hideUl();
            return;
        }

        //Now make the request
        sendRequest(partialText());

        
    });

    /*
     * This is a private function. It captures keyboard events and performs appropriate actions
     *
     * @param {jQuery} element The input whose keyboard events is to be monitored
     * @param {Number} keyCode The code of the key pressed
     *
     * @return true If some action key was pressed such as ENTER, UP ARROW
     * @return false If key pressed was not an action key
     */

    function keyCapture(element, keyCode)
    {

        var validKey = false,
        allowedKeys = [13, 38, 40, 27]; //Allowed: UP, DOWN, ENTER, ESC

        $.each(allowedKeys, function (key, value)
        {
            if (keyCode == value)
            {
                validKey = true;
            }
        });

        //Was valid action taking key was pressed?
        if (!validKey)
        {
            return false;
        }

        var children = $("#" + element.attr("ul-container")).children("li")
        ,selectedOption;
        
        //Check for any previously selected option
        $.each(children, function(key, value) 
        {
                if ($(value).attr("ul-selected") == "1")
                {
                    selectedOption = $(value);
                }
        });
        
        //If some option is already selected
        if (selectedOption)
        {
            //If UP arrow was pressed
            if (keyCode == keymap.UP)
            {
                //Was the first option previously selected?
                if (selectedOption == $(children[0]))
                {
                    //Now its time to give focus to the input as there is nothing to select
                    element.focus();
                }
                else
                {
                    //Select the previous option
                    selectOption(selectedOption, selectedOption.prev("li"));
                }
            }
            else if (keyCode == keymap.DOWN)
            {
                //Was the last element previously selected?
                if (selectedOption == $(children[children.length-1]))
                {
                    //Now its time to give focus to the input as there is nothing to select
                    element.focus();
                }
                else
                {
                    //Select the next option
                    selectOption(selectedOption, selectedOption.next("li"));
                }
            }
            else if (keyCode == keymap.ENTER)
            {
                selectedOption.click();
                hideUl();
                selectOption(selectedOption, false);
            }
        }
        else
        {
            if (keyCode == keymap.UP)
            {
                //Select the last option
                selectOption(false, children[children.length-1]);
            }
            else if (keyCode == keymap.DOWN)
            {
                //Select the first option
                selectOption(false, children[0]);
            }
        }

        if (keyCode == keymap.ESC)
        {
            hideUl();
        }
        return true; //Arrow Key event captured

    }
    
    /*
     * Highlights the given option and de-Highlights the given one
     *
     * @param {jQuery} deselect The handle of the LI Element to de-Highlight
     * @param {jQuery} select   The handle of the LI Element to Highlight
     *
     * @return null
     */

    function selectOption(deselect, select)
    {
        $(select).addClass("phpbb-suggest-select")
                  .attr("ul-selected","1");
        $(deselect).removeClass("phpbb-suggest-select")
                   .removeAttr("ul-selected");
    }
    
    /*
     * Extracts the partial text that the user has entered
     * If multiple options are enabled then return the last one
     *
     * @return {String} The partial text
     */

    function partialText()
    {
        if (config.allowMultiple)
        {
            var partialValues = control.val().split(config.delim);
            
            //return the last value of the array
            return partialValues[partialValues.length-1];
        }
        else
        {
            return control.val();
        }
    }
    
    /*
     * This function is used to make an AJAX Request and to get the options
     *
     * @param {String} partial The partial text that user has entered
     *
     * @return null
     */

    function sendRequest(partial)
    {
        $.post(config.src, {
            partial: partial
        },
        function (data)
        {
            var usrAry = $.parseJSON(data);

            //Now show the floating div in appropriate
            //position
            //pass the array for peace of mind
            showUl(usrAry);

        });

    }
    
    /*
     * This is a private function which creates a UL division to put the suggestions into
     * and returns the jQuery handle of UL division
     * OR Returns the jQuery handle if UL is already there
     *
     * @return {jQuery} Handle to the UL Element
     *
     */

    function createContainer() 
    {
        //If ul already present then return handle to it
        if ($("#" + config.inputID + "-suggest").length != 0 || control.attr("ul-container"))
        {
            // Reposition as the size of the text area changes according to the input
            return $("#" + config.inputID + "-suggest")
                    .css("top",control.offset().top+control.outerHeight());
        }

        //Else create a new ul
        else
        {
            var id      = config.inputID+"-suggest";
            var cont    = document.createElement("ul");
            cont.id     = id;

            //Add just next to the input
            control.after(cont);

            //Stores the id of the ul container
            control.attr("ul-container", id);

            //Set the CSS and position of ul
            cont=$(cont);
            cont.addClass("phpbb-suggest")
                .css("left",control.offset().left)
                .css("top",control.offset().top+control.outerHeight())
                .css("min-width",control.outerWidth());
            return cont;
        }
    }

    /*
     * This is a private function.It shows the UL division to the user
     *
     * @param {Array} usersArray Array consisting of all the Options to suggest
     *
     * @return null
     */

    function showUl(usersArray)
    {
        //If empty then don't even bother
        if (usersArray.length == 0)
        {
            hideUl()
            return;
        }
        //Uptil now we have result stored in users_array
        var cont = createContainer();

        //Remove any previous results stored
        cont.empty();


        //Show users in the list
        var newLi;
        $.each(usersArray, function (key, value)
        {
            newLi       = document.createElement("li");
            var input   = $("[ul-container="+cont.attr("id")+"]");

            //decide what happens when user selects any option
            $(newLi).text(value).click(function ()
            {
                if (config.allowMultiple)
                {
                    //Get all the values of the textarea and split into array
                    var usernameList = control.val().split(config.delim);
                    usernameList[usernameList.length-1] = value+config.delim;

                    //Join all the values and insert it into textarea
                    input.val(usernameList.join(config.delim));

                }
                else
                {
                    input.val(value);
                }
                
                //Now place the cursor to the end of the string
                input[0].setSelectionRange(input.val().length, input.val().length);
                input.focus();

                cont.hide();
            
            });

            
            //Add the options to the UL list
            cont.append(newLi);
        });
                    
                    
        //show the div to the user
        cont.show();
    }
    
    /*
     * This is a private function. This hides the UL Division from the user
     *
     * @return null
     */

    function hideUl()
    {
        $("#" + config.inputID+"-suggest").hide();
    }
                


}

// Map here the URL to the available options
var suggestMap = {
    username: "memberlist.php"
}


$('[data-suggest]').each(function () {
    var $this = $(this),
        ajax = $this.attr('data-suggest'),
        allowMultiple = $this.attr('data-allowmultiple') !== undefined ? $this.attr('data-allowmultiple') : false,
        delim = $this.attr('data-delim') !== undefined ? $this.attr('data-delim') : false;
    if (allowMultiple == 'true')
    {
        if (!delim)
        {
            delim = undefined;
        }
        phpbb.suggest({
            src: "memberlist.php", //Get from suggestMap
            allowMultiple: true,
            delim: delim,
            inputID: this.id
        });
            
    }
    else
    {
        phpbb.suggest({
            src: "memberlist.php", //Get from suggestMap
            inputID: this.id
        });
    }
});

})(jQuery); // Avoid conflicts with other libraries
