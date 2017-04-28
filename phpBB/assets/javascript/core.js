/* global bbfontstyle */

var phpbb = {};
phpbb.alertTime = 100;

(function($) {  // Avoid conflicts with other libraries

'use strict';

// define a couple constants for keydown functions.
var keymap = {
	TAB: 9,
	ENTER: 13,
	ESC: 27
};

var $dark = $('#darkenwrapper');
var $loadingIndicator;
var phpbbAlertTimer = null;

phpbb.isTouch = (window && typeof window.ontouchstart !== 'undefined');

/**
 * Display a loading screen
 *
 * @returns {object} Returns loadingIndicator.
 */
phpbb.loadingIndicator = function() {
	if (!$loadingIndicator) {
		$loadingIndicator = $('<div />', { 
			id: 'loading_indicator', 
			class: 'loading_indicator', 
		});
		$loadingIndicator.appendTo('#page-footer');
	}

	if (!$loadingIndicator.is(':visible')) {
		$loadingIndicator.fadeIn(phpbb.alertTime);
		// Wait 60 seconds and display an error if nothing has been returned by then.
		phpbb.clearLoadingTimeout();
		phpbbAlertTimer = setTimeout(function() {
			phpbb.showTimeoutMessage();
		}, 60000);
	}

	return $loadingIndicator;
};

/**
 * Show timeout message
 */
phpbb.showTimeoutMessage = function () {
	var $alert = $('#phpbb_alert');

	if ($loadingIndicator.is(':visible')) {
		phpbb.alert($alert.attr('data-l-err'), $alert.attr('data-l-timeout-processing-req'));
	}
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
* Close popup alert after a specified delay
*
* @param {int} delay Delay in ms until darkenwrapper's click event is triggered
*/
phpbb.closeDarkenWrapper = function(delay) {
	phpbbAlertTimer = setTimeout(function() {
		$('#darkenwrapper').trigger('click');
	}, delay);
};

/**
 * Display a simple alert similar to JSs native alert().
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param {string} title Title of the message, eg "Information" (HTML).
 * @param {string} msg Message to display (HTML).
 *
 * @returns {object} Returns the div created.
 */
phpbb.alert = function(title, msg) {
	var $alert = $('#phpbb_alert');
	$alert.find('.alert_title').html(title);
	$alert.find('.alert_text').html(msg);

	$(document).on('keydown.phpbb.alert', function(e) {
		if (e.keyCode === keymap.ENTER || e.keyCode === keymap.ESC) {
			phpbb.alert.close($alert, true);
			e.preventDefault();
			e.stopPropagation();
		}
	});
	phpbb.alert.open($alert);

	return $alert;
};

/**
* Handler for opening an alert box.
*
* @param {jQuery} $alert jQuery object.
*/
phpbb.alert.open = function($alert) {
	if (!$dark.is(':visible')) {
		$dark.fadeIn(phpbb.alertTime);
	}

	if ($loadingIndicator && $loadingIndicator.is(':visible')) {
		$loadingIndicator.fadeOut(phpbb.alertTime, function() {
			$dark.append($alert);
			$alert.fadeIn(phpbb.alertTime);
		});
	} else if ($dark.is(':visible')) {
		$dark.append($alert);
		$alert.fadeIn(phpbb.alertTime);
	} else {
		$dark.append($alert);
		$alert.show();
		$dark.fadeIn(phpbb.alertTime);
	}

	$alert.on('click', function(e) {
		e.stopPropagation();
	});

	$dark.one('click', function(e) {
		phpbb.alert.close($alert, true);
		e.preventDefault();
		e.stopPropagation();
	});

	$alert.find('.alert_close').one('click', function(e) {
		phpbb.alert.close($alert, true);
		e.preventDefault();
	});
};

/**
* Handler for closing an alert box.
*
* @param {jQuery} $alert jQuery object.
* @param {bool} fadedark Whether to remove dark background.
*/
phpbb.alert.close = function($alert, fadedark) {
	var $fade = (fadedark) ? $dark : $alert;

	$fade.fadeOut(phpbb.alertTime, function() {
		$alert.hide();
	});

	$alert.find('.alert_close').off('click');
	$(document).off('keydown.phpbb.alert');
};

/**
 * Display a simple yes / no box to the user.
 *
 * You can only call one alert or confirm box at any one time.
 *
 * @param {string} msg Message to display (HTML).
 * @param {function} callback Callback. Bool param, whether the user pressed
 *     yes or no (or whatever their language is).
 * @param {bool} fadedark Remove the dark background when done? Defaults
 *     to yes.
 *
 * @returns {object} Returns the div created.
 */
phpbb.confirm = function(msg, callback, fadedark) {
	var $confirmDiv = $('#phpbb_confirm');
	$confirmDiv.find('.alert_text').html(msg);
	fadedark = fadedark || true;

	$(document).on('keydown.phpbb.alert', function(e) {
		if (e.keyCode === keymap.ENTER || e.keyCode === keymap.ESC) {
			var name = (e.keyCode === keymap.ENTER) ? 'confirm' : 'cancel';

			$('input[name="' + name + '"]').trigger('click');
			e.preventDefault();
			e.stopPropagation();
		}
	});

	$confirmDiv.find('input[type="button"]').one('click.phpbb.confirmbox', function(e) {
		var confirmed = this.name === 'confirm';

		if (confirmed) {
			callback(true);
		}
		$confirmDiv.find('input[type="button"]').off('click.phpbb.confirmbox');
		phpbb.alert.close($confirmDiv, fadedark || !confirmed);

		e.preventDefault();
		e.stopPropagation();
	});

	phpbb.alert.open($confirmDiv);

	return $confirmDiv;
};

/**
 * Turn a querystring into an array.
 *
 * @argument {string} string The querystring to parse.
 * @returns {object} The object created.
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
 * @param {object} options Options.
 */
phpbb.ajaxify = function(options) {
	var $elements = $(options.selector),
		refresh = options.refresh,
		callback = options.callback,
		overlay = (typeof options.overlay !== 'undefined') ? options.overlay : true,
		isForm = $elements.is('form'),
		isText = $elements.is('input[type="text"], textarea'),
		eventName;

	if (isForm) {
		eventName = 'submit';
	} else if (isText) {
		eventName = 'keyup';
	} else {
		eventName = 'click';
	}

	$elements.on(eventName, function(event) {
		var action, method, data, submit, that = this, $this = $(this);

		if ($this.find('input[type="submit"][data-clicked]').attr('data-ajax') === 'false') {
			return;
		}

		/**
		 * Handler for AJAX errors
		 */
		function errorHandler(jqXHR, textStatus, errorThrown) {
			if (typeof console !== 'undefined' && console.log) {
				console.log('AJAX error. status: ' + textStatus + ', message: ' + errorThrown);
			}
			phpbb.clearLoadingTimeout();
			var responseText, errorText = false;
			try {
				responseText = JSON.parse(jqXHR.responseText);
				responseText = responseText.message;
			} catch (e) {}
			if (typeof responseText === 'string' && responseText.length > 0) {
				errorText = responseText;
			} else if (typeof errorThrown === 'string' && errorThrown.length > 0) {
				errorText = errorThrown;
			} else {
				errorText = $dark.attr('data-ajax-error-text-' + textStatus);
				if (typeof errorText !== 'string' || !errorText.length) {
					errorText = $dark.attr('data-ajax-error-text');
				}
			}
			phpbb.alert($dark.attr('data-ajax-error-title'), errorText);
		}

		/**
		 * This is a private function used to handle the callbacks, refreshes
		 * and alert. It calls the callback, refreshes the page if necessary, and
		 * displays an alert to the user and removes it after an amount of time.
		 *
		 * It cannot be called from outside this function, and is purely here to
		 * avoid repetition of code.
		 *
		 * @param {object} res The object sent back by the server.
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
					$dark.fadeOut(phpbb.alertTime);

					if ($loadingIndicator) {
						$loadingIndicator.fadeOut(phpbb.alertTime);
					}
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

					phpbbAlertTimer = setTimeout(function() {
						if (refresh) {
							window.location = res.REFRESH_DATA.url;
						}

						// Hide the alert even if we refresh the page, in case the user
						// presses the back button.
						$dark.fadeOut(phpbb.alertTime, function() {
							if (typeof alert !== 'undefined') {
								alert.hide();
							}
						});
					}, res.REFRESH_DATA.time * 1000); // Server specifies time in seconds
				}
			} else {
				// If confirmation is required, display a dialog to the user.
				phpbb.confirm(res.MESSAGE_BODY, function(del) {
					if (!del) {
						return;
					}

					phpbb.loadingIndicator();
					data =  $('<form>' + res.S_HIDDEN_FIELDS + '</form>').serialize();
					$.ajax({
						url: res.S_CONFIRM_ACTION,
						type: 'POST',
						data: data + '&confirm=' + res.YES_VALUE + '&' + $('form', '#phpbb_confirm').serialize(),
						success: returnHandler,
						error: errorHandler
					});
				}, false);
			}
		}

		// If the element is a form, POST must be used and some extra data must
		// be taken from the form.
		var runFilter = (typeof options.filter === 'function');
		data = {};

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
		} else if (isText) {
			var name = $this.attr('data-name') || this.name;
			action = $this.attr('data-url').replace('&amp;', '&');
			data[name] = this.value;
			method = 'POST';
		} else {
			action = this.href;
			data = null;
			method = 'GET';
		}

		var sendRequest = function() {
			var dataOverlay = $this.attr('data-overlay');
			if (overlay && (typeof dataOverlay === 'undefined' || dataOverlay === 'true')) {
				phpbb.loadingIndicator();
			}

			var request = $.ajax({
				url: action,
				type: method,
				data: data,
				success: returnHandler,
				error: errorHandler,
				cache: false
			});

			request.always(function() {
				if ($loadingIndicator && $loadingIndicator.is(':visible')) {
					$loadingIndicator.fadeOut(phpbb.alertTime);
				}
			});
		};

		// If filter function returns false, cancel the AJAX functionality,
		// and return true (meaning that the HTTP request will be sent normally).
		if (runFilter && !options.filter.call(this, data, event, sendRequest)) {
			return;
		}

		sendRequest();
		event.preventDefault();
	});

	if (isForm) {
		$elements.find('input:submit').click(function () {
			var $this = $(this);

			// Remove data-clicked attribute from any submit button of form
			$this.parents('form:first').find('input:submit[data-clicked]').removeAttr('data-clicked');

			$this.attr('data-clicked', 'true');
		});
	}

	return this;
};

phpbb.search = {
	cache: {
		data: []
	},
	tpl: [],
	container: []
};

/**
 * Get cached search data.
 *
 * @param {string} id Search ID.
 * @returns {bool|object} Cached data object. Returns false if no data exists.
 */
phpbb.search.cache.get = function(id) {
	if (this.data[id]) {
		return this.data[id];
	}
	return false;
};

/**
 * Set search cache data value.
 *
 * @param {string} id		Search ID.
 * @param {string} key		Data key.
 * @param {string} value	Data value.
 */
phpbb.search.cache.set = function(id, key, value) {
	if (!this.data[id]) {
		this.data[id] = { results: [] };
	}
	this.data[id][key] = value;
};

/**
 * Cache search result.
 *
 * @param {string} id		Search ID.
 * @param {string} keyword	Keyword.
 * @param {Array} results	Search results.
 */
phpbb.search.cache.setResults = function(id, keyword, results) {
	this.data[id].results[keyword] = results;
};

/**
 * Trim spaces from keyword and lower its case.
 *
 * @param {string} keyword Search keyword to clean.
 * @returns {string} Cleaned string.
 */
phpbb.search.cleanKeyword = function(keyword) {
	return $.trim(keyword).toLowerCase();
};

/**
 * Get clean version of search keyword. If textarea supports several keywords
 * (one per line), it fetches the current keyword based on the caret position.
 *
 * @param {jQuery} $input	Search input|textarea.
 * @param {string} keyword	Input|textarea value.
 * @param {bool} multiline	Whether textarea supports multiple search keywords.
 *
 * @returns string Clean string.
 */
phpbb.search.getKeyword = function($input, keyword, multiline) {
	if (multiline) {
		var line = phpbb.search.getKeywordLine($input);
		keyword = keyword.split('\n').splice(line, 1);
	}
	return phpbb.search.cleanKeyword(keyword);
};

/**
 * Get the textarea line number on which the keyword resides - for textareas
 * that support multiple keywords (one per line).
 *
 * @param {jQuery} $textarea Search textarea.
 * @returns {int} The line number.
 */
phpbb.search.getKeywordLine = function ($textarea) {
	var selectionStart = $textarea.get(0).selectionStart;
	return $textarea.val().substr(0, selectionStart).split('\n').length - 1;
};

/**
 * Set the value on the input|textarea. If textarea supports multiple
 * keywords, only the active keyword is replaced.
 *
 * @param {jQuery} $input	Search input|textarea.
 * @param {string} value	Value to set.
 * @param {bool} multiline	Whether textarea supports multiple search keywords.
 */
phpbb.search.setValue = function($input, value, multiline) {
	if (multiline) {
		var line = phpbb.search.getKeywordLine($input),
			lines = $input.val().split('\n');
		lines[line] = value;
		value = lines.join('\n');
	}
	$input.val(value);
};

/**
 * Sets the onclick event to set the value on the input|textarea to the
 * selected search result.
 *
 * @param {jQuery} $input		Search input|textarea.
 * @param {object} value		Result object.
 * @param {jQuery} $row			Result element.
 * @param {jQuery} $container	jQuery object for the search container.
 */
phpbb.search.setValueOnClick = function($input, value, $row, $container) {
	$row.click(function() {
		phpbb.search.setValue($input, value.result, $input.attr('data-multiline'));
		$container.hide();
	});
};

/**
 * Runs before the AJAX search request is sent and determines whether
 * there is a need to contact the server. If there are cached results
 * already, those are displayed instead. Executes the AJAX request function
 * itself due to the need to use a timeout to limit the number of requests.
 *
 * @param {Array} data				Data to be sent to the server.
 * @param {object} event			Onkeyup event object.
 * @param {function} sendRequest	Function to execute AJAX request.
 *
 * @returns {bool} Returns false.
 */
phpbb.search.filter = function(data, event, sendRequest) {
	var $this = $(this),
		dataName = ($this.attr('data-name') !== undefined) ? $this.attr('data-name') : $this.attr('name'),
		minLength = parseInt($this.attr('data-min-length'), 10),
		searchID = $this.attr('data-results'),
		keyword = phpbb.search.getKeyword($this, data[dataName], $this.attr('data-multiline')),
		cache = phpbb.search.cache.get(searchID),
		proceed = true;
	data[dataName] = keyword;

	if (cache.timeout) {
		clearTimeout(cache.timeout);
	}

	var timeout = setTimeout(function() {
		// Check min length and existence of cache.
		if (minLength > keyword.length) {
			proceed = false;
		} else if (cache.lastSearch) {
			// Has the keyword actually changed?
			if (cache.lastSearch === keyword) {
				proceed = false;
			} else {
				// Do we already have results for this?
				if (cache.results[keyword]) {
					var response = {
						keyword: keyword,
						results: cache.results[keyword]
					};
					phpbb.search.handleResponse(response, $this, true);
					proceed = false;
				}

				// If the previous search didn't yield results and the string only had characters added to it,
				// then we won't bother sending a request.
				if (keyword.indexOf(cache.lastSearch) === 0 && cache.results[cache.lastSearch].length === 0) {
					phpbb.search.cache.set(searchID, 'lastSearch', keyword);
					phpbb.search.cache.setResults(searchID, keyword, []);
					proceed = false;
				}
			}
		}

		if (proceed) {
			sendRequest.call(this);
		}
	}, 350);
	phpbb.search.cache.set(searchID, 'timeout', timeout);

	return false;
};

/**
 * Handle search result response.
 *
 * @param {object} res			Data received from server.
 * @param {jQuery} $input		Search input|textarea.
 * @param {bool} fromCache		Whether the results are from the cache.
 * @param {function} callback	Optional callback to run when assigning each search result.
 */
phpbb.search.handleResponse = function(res, $input, fromCache, callback) {
	if (typeof res !== 'object') {
		return;
	}

	var searchID = $input.attr('data-results'),
		$container = $(searchID);

	if (this.cache.get(searchID).callback) {
		callback = this.cache.get(searchID).callback;
	} else if (typeof callback === 'function') {
		this.cache.set(searchID, 'callback', callback);
	}

	if (!fromCache) {
		this.cache.setResults(searchID, res.keyword, res.results);
	}

	this.cache.set(searchID, 'lastSearch', res.keyword);
	this.showResults(res.results, $input, $container, callback);
};

/**
 * Show search results.
 *
 * @param {Array} results		Search results.
 * @param {jQuery} $input		Search input|textarea.
 * @param {jQuery} $container	Search results container element.
 * @param {function} callback	Optional callback to run when assigning each search result.
 */
phpbb.search.showResults = function(results, $input, $container, callback) {
	var $resultContainer = $('.search-results', $container);
	this.clearResults($resultContainer);

	if (!results.length) {
		$container.hide();
		return;
	}

	var searchID = $container.attr('id'),
		tpl,
		row;

	if (!this.tpl[searchID]) {
		tpl = $('.search-result-tpl', $container);
		this.tpl[searchID] = tpl.clone().removeClass('search-result-tpl');
		tpl.remove();
	}
	tpl = this.tpl[searchID];

	$.each(results, function(i, item) {
		row = tpl.clone();
		row.find('.search-result').html(item.display);

		if (typeof callback === 'function') {
			callback.call(this, $input, item, row, $container);
		}
		row.appendTo($resultContainer).show();
	});
	$container.show();
};

/**
 * Clear search results.
 *
 * @param {jQuery} $container Search results container.
 */
phpbb.search.clearResults = function($container) {
	$container.children(':not(.search-result-tpl)').remove();
};

$('#phpbb').click(function() {
	var $this = $(this);

	if (!$this.is('.live-search') && !$this.parents().is('.live-search')) {
		$('.live-search').hide();
	}
});

phpbb.history = {};

/**
* Check whether a method in the native history object is supported.
*
* @param {string} fn Method name.
* @returns {bool} Returns true if the method is supported.
*/
phpbb.history.isSupported = function(fn) {
	return !(typeof history === 'undefined' || typeof history[fn] === 'undefined');
};

/**
* Wrapper for the pushState and replaceState methods of the
* native history object.
*
* @param {string} mode		Mode. Either push or replace.
* @param {string} url		New URL.
* @param {string} [title]	Optional page title.
* @param {object} [obj]		Optional state object.
*/
phpbb.history.alterUrl = function(mode, url, title, obj) {
	var fn = mode + 'State';

	if (!url || !phpbb.history.isSupported(fn)) {
		return;
	}
	if (!title) {
		title = document.title;
	}
	if (!obj) {
		obj = null;
	}

	history[fn](obj, title, url);
};

/**
* Wrapper for the native history.replaceState method.
*
* @param {string} url		New URL.
* @param {string} [title]	Optional page title.
* @param {object} [obj]		Optional state object.
*/
phpbb.history.replaceUrl = function(url, title, obj) {
	phpbb.history.alterUrl('replace', url, title, obj);
};

/**
* Wrapper for the native history.pushState method.
*
* @param {string} url		New URL.
* @param {string} [title]	Optional page title.
* @param {object} [obj]		Optional state object.
*/
phpbb.history.pushUrl = function(url, title, obj) {
	phpbb.history.alterUrl('push', url, title, obj);
};

/**
* Hide the optgroups that are not the selected timezone
*
* @param {bool} keepSelection Shall we keep the value selected, or shall the
* 	user be forced to repick one.
*/
phpbb.timezoneSwitchDate = function(keepSelection) {
	var $timezoneCopy = $('#timezone_copy');
	var $timezone = $('#timezone');
	var $tzDate = $('#tz_date');
	var $tzSelectDateSuggest = $('#tz_select_date_suggest');

	if ($timezoneCopy.length === 0) {
		// We make a backup of the original dropdown, so we can remove optgroups
		// instead of setting display to none, because IE and chrome will not
		// hide options inside of optgroups and selects via css
		$timezone.clone()
			.attr('id', 'timezone_copy')
			.css('display', 'none')
			.attr('name', 'tz_copy')
			.insertAfter('#timezone');
	} else {
		// Copy the content of our backup, so we can remove all unneeded options
		$timezone.html($timezoneCopy.html());
	}

	if ($tzDate.val() !== '') {
		$timezone.children('optgroup').remove(':not([data-tz-value="' + $tzDate.val() + '"])');
	}

	if ($tzDate.val() === $tzSelectDateSuggest.attr('data-suggested-tz')) {
		$tzSelectDateSuggest.css('display', 'none');
	} else {
		$tzSelectDateSuggest.css('display', 'inline');
	}

	var $tzOptions = $timezone.children('optgroup[data-tz-value="' + $tzDate.val() + '"]').children('option');

	if ($tzOptions.length === 1) {
		// If there is only one timezone for the selected date, we just select that automatically.
		$tzOptions.prop('selected', true);
		keepSelection = true;
	}

	if (typeof keepSelection !== 'undefined' && !keepSelection) {
		var $timezoneOptions = $timezone.find('optgroup option');
		if ($timezoneOptions.filter(':selected').length <= 0) {
			$timezoneOptions.filter(':first').prop('selected', true);
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
* @param {bool} forceSelector Shall we select the suggestion?
*/
phpbb.timezonePreselectSelect = function(forceSelector) {

	// The offset returned here is in minutes and negated.
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

	var prefix = 'UTC' + sign + hours + ':' + minutes;
	var prefixLength = prefix.length;
	var selectorOptions = $('option', '#tz_date');
	var i;

	var $tzSelectDateSuggest = $('#tz_select_date_suggest');

	for (i = 0; i < selectorOptions.length; ++i) {
		var option = selectorOptions[i];

		if (option.value.substring(0, prefixLength) === prefix) {
			if ($('#tz_date').val() !== option.value && !forceSelector) {
				// We do not select the option for the user, but notify him,
				// that we would suggest a different setting.
				phpbb.timezoneSwitchDate(true);
				$tzSelectDateSuggest.css('display', 'inline');
			} else {
				option.selected = true;
				phpbb.timezoneSwitchDate(!forceSelector);
				$tzSelectDateSuggest.css('display', 'none');
			}

			var suggestion = $tzSelectDateSuggest.attr('data-l-suggestion');

			$tzSelectDateSuggest.attr('title', suggestion.replace('%s', option.innerHTML));
			$tzSelectDateSuggest.attr('value', suggestion.replace('%s', option.innerHTML.substring(0, 9)));
			$tzSelectDateSuggest.attr('data-suggested-tz', option.innerHTML);

			// Found the suggestion, there cannot be more, so return from here.
			return;
		}
	}
};

phpbb.ajaxCallbacks = {};

/**
 * Adds an AJAX callback to be used by phpbb.ajaxify.
 *
 * See the phpbb.ajaxify comments for information on stuff like parameters.
 *
 * @param {string} id The name of the callback.
 * @param {function} callback The callback to be called.
 */
phpbb.addAjaxCallback = function(id, callback) {
	if (typeof callback === 'function') {
		phpbb.ajaxCallbacks[id] = callback;
	}
	return this;
};

/**
 * This callback handles live member searches.
 */
phpbb.addAjaxCallback('member_search', function(res) {
	phpbb.search.handleResponse(res, $(this), false, phpbb.getFunctionByName('phpbb.search.setValueOnClick'));
});

/**
 * This callback alternates text - it replaces the current text with the text in
 * the alt-text data attribute, and replaces the text in the attribute with the
 * current text so that the process can be repeated.
 */
phpbb.addAjaxCallback('alt_text', function() {
	var $anchor,
		updateAll = $(this).data('update-all'),
		altText;

	if (updateAll !== undefined && updateAll.length) {
		$anchor = $(updateAll);
	} else {
		$anchor = $(this);
	}

	$anchor.each(function() {
		var $this = $(this);
		altText = $this.attr('data-alt-text');
		$this.attr('data-alt-text', $this.text());
		$this.attr('title', $.trim(altText));
		$this.text(altText);
	});
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
	var $anchor,
		updateAll = $(this).data('update-all') ,
		toggleText,
		toggleUrl,
		toggleClass;

	if (updateAll !== undefined && updateAll.length) {
		$anchor = $(updateAll);
	} else {
		$anchor = $(this);
	}

	$anchor.each(function() {
		var $this = $(this);

		// Toggle link url
		toggleUrl = $this.attr('data-toggle-url');
		$this.attr('data-toggle-url', $this.attr('href'));
		$this.attr('href', toggleUrl);

		// Toggle class of link parent
		toggleClass = $this.attr('data-toggle-class');
		$this.attr('data-toggle-class', $this.children().attr('class'));
		$this.children('.icon').attr('class', toggleClass);

		// Toggle link text
		toggleText = $this.attr('data-toggle-text');
		$this.attr('data-toggle-text', $this.children('span').text());
		$this.attr('title', $.trim(toggleText));
		$this.children('span').text(toggleText);
	});
});

/**
* Automatically resize textarea
*
* This function automatically resizes textarea elements when user
* types text.
*
* @param {jQuery} $items jQuery object(s) to resize
* @param {object} [options] Optional parameter that adjusts default
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
phpbb.resizeTextArea = function($items, options) {
	// Configuration
	var configuration = {
		minWindowHeight: 500,
		minHeight: 200,
		maxHeight: 500,
		heightDiff: 200,
		resizeCallback: function() {},
		resetCallback: function() {}
	};

	if (phpbb.isTouch) {
		return;
	}

	if (arguments.length > 1) {
		configuration = $.extend(configuration, options);
	}

	function resetAutoResize(item) {
		var $item = $(item);
		if ($item.hasClass('auto-resized')) {
			$(item)
				.css({ height: '', resize: '' })
				.removeClass('auto-resized');
			configuration.resetCallback.call(item, $item);
		}
	}

	function autoResize(item) {
		function setHeight(height) {
			height += parseInt($item.css('height'), 10) - $item.innerHeight();
			$item
				.css({ height: height + 'px', resize: 'none' })
				.addClass('auto-resized');
			configuration.resizeCallback.call(item, $item);
		}

		var windowHeight = $(window).height();

		if (windowHeight < configuration.minWindowHeight) {
			resetAutoResize(item);
			return;
		}

		var maxHeight = Math.min(
				Math.max(windowHeight - configuration.heightDiff, configuration.minHeight),
				configuration.maxHeight
			),
			$item = $(item),
			height = parseInt($item.innerHeight(), 10),
			scrollHeight = (item.scrollHeight) ? item.scrollHeight : 0;

		if (height < 0) {
			return;
		}

		if (height > maxHeight) {
			setHeight(maxHeight);
		} else if (scrollHeight > (height + 5)) {
			setHeight(Math.min(maxHeight, scrollHeight));
		}
	}

	$items.on('focus change keyup', function() {
		$(this).each(function() {
			autoResize(this);
		});
	}).change();

	$(window).resize(function() {
		$items.each(function() {
			if ($(this).hasClass('auto-resized')) {
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
* @returns {boolean} True if cursor is in bbcode tag
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
	if (lastStart === -1) {
		return false;
	}

	if (start > 0) {
		for (i = 0; i < endTags.length; i++) {
			index = value.lastIndexOf(endTags[i], start - 1);
			lastEnd = Math.max(lastEnd, index);
		}
	}

	return (lastEnd < lastStart);
};


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
	* @returns {string} Line of text
	*/
	function getLastLine(stripCodeStart) {
		var start = textarea.selectionStart,
			value = textarea.value,
			index = value.lastIndexOf('\n', start - 1);

		value = value.substring(index + 1, start);

		if (stripCodeStart) {
			for (var i = 0; i < startTags.length; i++) {
				index = value.lastIndexOf(startTags[i]);
				if (index >= 0) {
					var tagLength = startTags[i].length;

					value = value.substring(index + tagLength);
					if (startTags[i].lastIndexOf(startTagsEnd) !== tagLength) {
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
	* @param {string} text Text to append
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
		if (key === keymap.TAB	&&
			!event.ctrlKey		&&
			!event.shiftKey		&&
			!event.altKey		&&
			!event.metaKey) {
			if (inTag()) {
				appendText('\t');
				event.preventDefault();
				return;
			}
		}

		// intercept new line characters
		if (key === keymap.ENTER) {
			if (inTag()) {
				var lastLine = getLastLine(true),
					code = '' + /^\s*/g.exec(lastLine);

				if (code.length > 0) {
					appendText('\n' + code);
					event.preventDefault();
				}
			}
		}
	});
};

/**
 * Show drag and drop animation when textarea is present
 *
 * This function will enable the drag and drop animation for a specified
 * textarea.
 *
 * @param {HTMLElement} textarea Textarea DOM object to apply editor to
 */
phpbb.showDragNDrop = function(textarea) {
	if (!textarea) {
		return;
	}

	$('body').on('dragenter dragover', function () {
		$(textarea).addClass('drag-n-drop');
	}).on('dragleave dragout dragend drop', function() {
		$(textarea).removeClass('drag-n-drop');
	});
	$(textarea).on('dragenter dragover', function () {
		$(textarea).addClass('drag-n-drop-highlight');
	}).on('dragleave dragout dragend drop', function() {
		$(textarea).removeClass('drag-n-drop-highlight');
	});
};

/**
* List of classes that toggle dropdown menu,
* list of classes that contain visible dropdown menu
*
* Add your own classes to strings with comma (probably you
* will never need to do that)
*/
phpbb.dropdownHandles = '.dropdown-container.dropdown-visible .dropdown-toggle';
phpbb.dropdownVisibleContainers = '.dropdown-container.dropdown-visible';

/**
* Dropdown toggle event handler
* This handler is used by phpBB.registerDropdown() and other functions
*/
phpbb.toggleDropdown = function() {
	var $this = $(this),
		options = $this.data('dropdown-options'),
		parent = options.parent,
		visible = parent.hasClass('dropdown-visible'),
		direction;

	if (!visible) {
		// Hide other dropdown menus
		$(phpbb.dropdownHandles).each(phpbb.toggleDropdown);

		// Figure out direction of dropdown
		direction = options.direction;
		var verticalDirection = options.verticalDirection,
			offset = $this.offset();

		if (direction === 'auto') {
			if (($(window).width() - $this.outerWidth(true)) / 2 > offset.left) {
				direction = 'right';
			} else {
				direction = 'left';
			}
		}
		parent.toggleClass(options.leftClass, direction === 'left')
			.toggleClass(options.rightClass, direction === 'right');

		if (verticalDirection === 'auto') {
			var height = $(window).height(),
				top = offset.top - $(window).scrollTop();

			verticalDirection = (top < height * 0.7) ? 'down' : 'up';
		}
		parent.toggleClass(options.upClass, verticalDirection === 'up')
			.toggleClass(options.downClass, verticalDirection === 'down');
	}

	options.dropdown.toggle();
	parent.toggleClass(options.visibleClass, !visible)
		.toggleClass('dropdown-visible', !visible);

	// Check dimensions when showing dropdown
	// !visible because variable shows state of dropdown before it was toggled
	if (!visible) {
		var windowWidth = $(window).width();

		options.dropdown.find('.dropdown-contents').each(function() {
			var $this = $(this);

			$this.css({
				marginLeft: 0,
				left: 0,
				maxWidth: (windowWidth - 4) + 'px'
			});

			var offset = $this.offset().left,
				width = $this.outerWidth(true);

			if (offset < 2) {
				$this.css('left', (2 - offset) + 'px');
			} else if ((offset + width + 2) > windowWidth) {
				$this.css('margin-left', (windowWidth - offset - width - 2) + 'px');
			}

			// Check whether the vertical scrollbar is present.
			$this.toggleClass('dropdown-nonscroll', this.scrollHeight === $this.innerHeight());

		});
		var freeSpace = parent.offset().left - 4;

		if (direction === 'left') {
			options.dropdown.css('margin-left', '-' + freeSpace + 'px');

			// Try to position the notification dropdown correctly in RTL-responsive mode
			if (options.dropdown.hasClass('dropdown-extended')) {
				var contentWidth,
					fullFreeSpace = freeSpace + parent.outerWidth();

				options.dropdown.find('.dropdown-contents').each(function() {
					contentWidth = parseInt($(this).outerWidth(), 10);
					$(this).css({ marginLeft: 0, left: 0 });
				});

				var maxOffset = Math.min(contentWidth, fullFreeSpace) + 'px';
				options.dropdown.css({
					width: maxOffset,
					marginLeft: -maxOffset
				});
			}
		} else {
			options.dropdown.css('margin-right', '-' + (windowWidth + freeSpace) + 'px');
		}
	}

	// Prevent event propagation
	if (arguments.length > 0) {
		try {
			var e = arguments[0];
			e.preventDefault();
			e.stopPropagation();
		} catch (error) { }
	}
	return false;
};

/**
* Toggle dropdown submenu
*/
phpbb.toggleSubmenu = function(e) {
	$(this).siblings('.dropdown-submenu').toggle();
	e.preventDefault();
};

/**
* Register dropdown menu
* Shows/hides dropdown, decides which side to open to
*
* @param {jQuery} toggle Link that toggles dropdown.
* @param {jQuery} dropdown Dropdown menu.
* @param {Object} options List of options. Optional.
*/
phpbb.registerDropdown = function(toggle, dropdown, options) {
	var ops = {
			parent: toggle.parent(), // Parent item to add classes to
			direction: 'auto', // Direction of dropdown menu. Possible values: auto, left, right
			verticalDirection: 'auto', // Vertical direction. Possible values: auto, up, down
			visibleClass: 'visible', // Class to add to parent item when dropdown is visible
			leftClass: 'dropdown-left', // Class to add to parent item when dropdown opens to left side
			rightClass: 'dropdown-right', // Class to add to parent item when dropdown opens to right side
			upClass: 'dropdown-up', // Class to add to parent item when dropdown opens above menu item
			downClass: 'dropdown-down' // Class to add to parent item when dropdown opens below menu item
		};
	if (options) {
		ops = $.extend(ops, options);
	}
	ops.dropdown = dropdown;

	ops.parent.addClass('dropdown-container');
	toggle.addClass('dropdown-toggle');

	toggle.data('dropdown-options', ops);

	toggle.click(phpbb.toggleDropdown);
	$('.dropdown-toggle-submenu', ops.parent).click(phpbb.toggleSubmenu);
};

/**
* Get the HTML for a color palette table.
*
* @param {string} dir Palette direction - either v or h
* @param {int} width Palette cell width.
* @param {int} height Palette cell height.
*/
phpbb.colorPalette = function(dir, width, height) {
	var r, g, b,
		numberList = new Array(6),
		color = '',
		html = '';

	numberList[0] = '00';
	numberList[1] = '40';
	numberList[2] = '80';
	numberList[3] = 'BF';
	numberList[4] = 'FF';

	var tableClass = (dir === 'h') ? 'horizontal-palette' : 'vertical-palette';
	html += '<table class="not-responsive colour-palette ' + tableClass + '" style="width: auto;">';

	for (r = 0; r < 5; r++) {
		if (dir === 'h') {
			html += '<tr>';
		}

		for (g = 0; g < 5; g++) {
			if (dir === 'v') {
				html += '<tr>';
			}

			for (b = 0; b < 5; b++) {
				color = '' + numberList[r] + numberList[g] + numberList[b];
				html += '<td style="background-color: #' + color + '; width: ' + width + 'px; height: ' +
					height + 'px;"><a href="#" data-color="' + color + '" style="display: block; width: ' +
					width + 'px; height: ' + height + 'px; " alt="#' + color + '" title="#' + color + '"></a>';
				html += '</td>';
			}

			if (dir === 'v') {
				html += '</tr>';
			}
		}

		if (dir === 'h') {
			html += '</tr>';
		}
	}
	html += '</table>';
	return html;
};

/**
* Register a color palette.
*
* @param {jQuery} el jQuery object for the palette container.
*/
phpbb.registerPalette = function(el) {
	var	orientation	= el.attr('data-orientation'),
		height		= el.attr('data-height'),
		width		= el.attr('data-width'),
		target		= el.attr('data-target'),
		bbcode		= el.attr('data-bbcode');

	// Insert the palette HTML into the container.
	el.html(phpbb.colorPalette(orientation, width, height));

	// Add toggle control.
	$('#color_palette_toggle').click(function(e) {
		el.toggle();
		e.preventDefault();
	});

	// Attach event handler when a palette cell is clicked.
	$(el).on('click', 'a', function(e) {
		var color = $(this).attr('data-color');

		if (bbcode) {
			bbfontstyle('[color=#' + color + ']', '[/color]');
		} else {
			$(target).val(color);
		}
		e.preventDefault();
	});
};

/**
* Set display of page element
*
* @param {string} id The ID of the element to change
* @param {int} action Set to 0 if element display should be toggled, -1 for
*			hiding the element, and 1 for showing it.
* @param {string} type Display type that should be used, e.g. inline, block or
*			other CSS "display" types
*/
phpbb.toggleDisplay = function(id, action, type) {
	if (!type) {
		type = 'block';
	}

	var $element = $('#' + id);

	var display = $element.css('display');
	if (!action) {
		action = (display === '' || display === type) ? -1 : 1;
	}
	$element.css('display', ((action === 1) ? type : 'none'));
};

/**
* Toggle additional settings based on the selected
* option of select element.
*
* @param {jQuery} el jQuery select element object.
*/
phpbb.toggleSelectSettings = function(el) {
	el.children().each(function() {
		var $this = $(this),
			$setting = $($this.data('toggle-setting'));
		$setting.toggle($this.is(':selected'));

		// Disable any input elements that are not visible right now
		if ($this.is(':selected')) {
			$($this.data('toggle-setting') + ' input').prop('disabled', false);
		} else {
			$($this.data('toggle-setting') + ' input').prop('disabled', true);
		}
	});
};

/**
* Get function from name.
* Based on http://stackoverflow.com/a/359910
*
* @param {string} functionName Function to get.
* @returns function
*/
phpbb.getFunctionByName = function (functionName) {
	var namespaces = functionName.split('.'),
		func = namespaces.pop(),
		context = window;

	for (var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	}
	return context[func];
};

/**
* Register page dropdowns.
*/
phpbb.registerPageDropdowns = function() {
	var $body = $('body');

	$body.find('.dropdown-container').each(function() {
		var $this = $(this),
			$trigger = $this.find('.dropdown-trigger:first'),
			$contents = $this.find('.dropdown'),
			options = {
				direction: 'auto',
				verticalDirection: 'auto'
			},
			data;

		if (!$trigger.length) {
			data = $this.attr('data-dropdown-trigger');
			$trigger = data ? $this.children(data) : $this.children('a:first');
		}

		if (!$contents.length) {
			data = $this.attr('data-dropdown-contents');
			$contents = data ? $this.children(data) : $this.children('div:first');
		}

		if (!$trigger.length || !$contents.length) {
			return;
		}

		if ($this.hasClass('dropdown-up')) {
			options.verticalDirection = 'up';
		}
		if ($this.hasClass('dropdown-down')) {
			options.verticalDirection = 'down';
		}
		if ($this.hasClass('dropdown-left')) {
			options.direction = 'left';
		}
		if ($this.hasClass('dropdown-right')) {
			options.direction = 'right';
		}

		phpbb.registerDropdown($trigger, $contents, options);
	});

	// Hide active dropdowns when click event happens outside
	$body.click(function(e) {
		var $parents = $(e.target).parents();
		if (!$parents.is(phpbb.dropdownVisibleContainers)) {
			$(phpbb.dropdownHandles).each(phpbb.toggleDropdown);
		}
	});
};

/**
 * Handle avatars to be lazy loaded.
 */
phpbb.lazyLoadAvatars = function loadAvatars() {
	$('.avatar[data-src]').each(function () {
		var $avatar = $(this);

		$avatar
			.attr('src', $avatar.data('src'))
			.removeAttr('data-src');
	});
};

$(window).load(phpbb.lazyLoadAvatars);

/**
* Apply code editor to all textarea elements with data-bbcode attribute
*/
$(function() {
	$('textarea[data-bbcode]').each(function() {
		phpbb.applyCodeEditor(this);
	});

	phpbb.registerPageDropdowns();

	$('#color_palette_placeholder').each(function() {
		phpbb.registerPalette($(this));
	});

	// Update browser history URL to point to specific post in viewtopic.php
	// when using view=unread#unread link.
	phpbb.history.replaceUrl($('#unread[data-url]').data('url'));

	// Hide settings that are not selected via select element.
	$('select[data-togglable-settings]').each(function() {
		var $this = $(this);

		$this.change(function() {
			phpbb.toggleSelectSettings($this);
		});
		phpbb.toggleSelectSettings($this);
	});
});

})(jQuery); // Avoid conflicts with other libraries
