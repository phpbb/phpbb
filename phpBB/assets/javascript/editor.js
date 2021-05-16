/* global phpbb */

/**
* bbCode control by subBlue design [ www.subBlue.com ]
* Includes unixsafe colour palette selector by SHS`
*/

// Startup variables
var imageTag = false;
var theSelection = false;
var bbcodeEnabled = true;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion, 10); // Get browser version

var is_ie = ((clientPC.indexOf('msie') !== -1) && (clientPC.indexOf('opera') === -1));
var is_win = ((clientPC.indexOf('win') !== -1) || (clientPC.indexOf('16bit') !== -1));
var baseHeight;

/**
* Fix a bug involving the TextRange object. From
* http://www.frostjedi.com/terra/scripts/demo/caretBug.html
*/
function initInsertions() {
	var doc;

	if (document.forms[form_name]) {
		doc = document;
	} else {
		doc = opener.document;
	}

	var textarea = doc.forms[form_name].elements[text_name];

	if (is_ie && typeof(baseHeight) !== 'number') {
		textarea.focus();
		baseHeight = doc.selection.createRange().duplicate().boundingHeight;

		if (!document.forms[form_name]) {
			document.body.focus();
		}
	}
}

/**
* bbstyle
*/
function bbstyle(bbnumber) {
	if (bbnumber !== -1) {
		bbfontstyle(bbtags[bbnumber], bbtags[bbnumber+1]);
	} else {
		insert_text('[*]');
		document.forms[form_name].elements[text_name].focus();
	}
}

/**
* Apply bbcodes
*/
function bbfontstyle(bbopen, bbclose) {
	theSelection = false;

	var textarea = document.forms[form_name].elements[text_name];

	textarea.focus();

	if ((clientVer >= 4) && is_ie && is_win) {
		// Get text selection
		theSelection = document.selection.createRange().text;

		if (theSelection) {
			// Add tags around selection
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			textarea.focus();
			theSelection = '';
			return;
		}
	} else if (textarea.selectionEnd && (textarea.selectionEnd - textarea.selectionStart > 0)) {
		mozWrap(textarea, bbopen, bbclose);
		textarea.focus();
		theSelection = '';
		return;
	}

	//The new position for the cursor after adding the bbcode
	var caret_pos = getCaretPosition(textarea).start;
	var new_pos = caret_pos + bbopen.length;

	// Open tag
	insert_text(bbopen + bbclose);

	// Center the cursor when we don't have a selection
	// Gecko and proper browsers
	if (!isNaN(textarea.selectionStart)) {
		textarea.selectionStart = new_pos;
		textarea.selectionEnd = new_pos;
	}
	// IE
	else if (document.selection) {
		var range = textarea.createTextRange();
		range.move("character", new_pos);
		range.select();
		storeCaret(textarea);
	}

	textarea.focus();
}

/**
* Insert text at position
*/
function insert_text(text, spaces, popup) {
	var textarea;

	if (!popup) {
		textarea = document.forms[form_name].elements[text_name];
	} else {
		textarea = opener.document.forms[form_name].elements[text_name];
	}

	if (spaces) {
		text = ' ' + text + ' ';
	}

	// Since IE9, IE also has textarea.selectionStart, but it still needs to be treated the old way.
	// Therefore we simply add a !is_ie here until IE fixes the text-selection completely.
	if (!isNaN(textarea.selectionStart) && !is_ie) {
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '');
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	} else if (textarea.createTextRange && textarea.caretPos) {
		if (baseHeight !== textarea.caretPos.boundingHeight) {
			textarea.focus();
			storeCaret(textarea);
		}

		var caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) === ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
	} else {
		textarea.value = textarea.value + text;
	}

	if (!popup) {
		textarea.focus();
	}
}

/**
* Add inline attachment at position
*/
function attachInline(index, filename) {
	insert_text('[attachment=' + index + ']' + filename + '[/attachment]');
	document.forms[form_name].elements[text_name].focus();
}

/**
* Add quote text to message
*/
function addquote(post_id, username, l_wrote, attributes) {
	var message_name = 'message_' + post_id;
	var theSelection = '';
	var divarea = false;
	var i;

	if (l_wrote === undefined) {
		// Backwards compatibility
		l_wrote = 'wrote';
	}
	if (typeof attributes !== 'object') {
		attributes = {};
	}

	if (document.all) {
		divarea = document.all[message_name];
	} else {
		divarea = document.getElementById(message_name);
	}

	// Get text selection - not only the post content :(
	// IE9 must use the document.selection method but has the *.getSelection so we just force no IE
	if (window.getSelection && !is_ie && !window.opera) {
		theSelection = window.getSelection().toString();
	} else if (document.getSelection && !is_ie) {
		theSelection = document.getSelection();
	} else if (document.selection) {
		theSelection = document.selection.createRange().text;
	}

	if (theSelection === '' || typeof theSelection === 'undefined' || theSelection === null) {
		if (divarea.innerHTML) {
			theSelection = divarea.innerHTML.replace(/<br>/ig, '\n');
			theSelection = theSelection.replace(/<br\/>/ig, '\n');
			theSelection = theSelection.replace(/&lt\;/ig, '<');
			theSelection = theSelection.replace(/&gt\;/ig, '>');
			theSelection = theSelection.replace(/&amp\;/ig, '&');
			theSelection = theSelection.replace(/&nbsp\;/ig, ' ');
		} else if (document.all) {
			theSelection = divarea.innerText;
		} else if (divarea.textContent) {
			theSelection = divarea.textContent;
		} else if (divarea.firstChild.nodeValue) {
			theSelection = divarea.firstChild.nodeValue;
		}
	}

	if (theSelection) {
		if (bbcodeEnabled) {
			attributes.author = username;
			insert_text(generateQuote(theSelection, attributes));
		} else {
			insert_text(username + ' ' + l_wrote + ':' + '\n');
			var lines = split_lines(theSelection);
			for (i = 0; i < lines.length; i++) {
				insert_text('> ' + lines[i] + '\n');
			}
		}
	}
}

/**
* Create a quote block for given text
*
* Possible attributes:
*   - author:  author's name (usually a username)
*   - post_id: post_id of the post being quoted
*   - user_id: user_id of the user being quoted
*   - time:    timestamp of the original message
*
* @param  {!string} text       Quote's text
* @param  {!Object} attributes Quote's attributes
* @return {!string}            Quote block to be used in a new post/text
*/
function generateQuote(text, attributes) {
	text = text.replace(/^\s+/, '').replace(/\s+$/, '');
	var quote = '[quote';
	if (attributes.author) {
		// Add the author as the BBCode's default attribute
		quote += '=' + formatAttributeValue(attributes.author);
		delete attributes.author;
	}
	for (var name in attributes) {
		if (attributes.hasOwnProperty(name)) {
			var value = attributes[name];
			quote += ' ' + name + '=' + formatAttributeValue(value.toString());
		}
	}
	quote += ']';
	var newline = ((quote + text + '[/quote]').length > 80 || text.indexOf('\n') > -1) ? '\n' : '';
	quote += newline + text + newline + '[/quote]';

	return quote;
}

/**
* Format given string to be used as an attribute value
*
* Will return the string as-is if it can be used in a BBCode without quotes. Otherwise,
* it will use either single- or double- quotes depending on whichever requires less escaping.
* Quotes and backslashes are escaped with backslashes where necessary
*
* @param  {!string} str Original string
* @return {!string}     Same string if possible, escaped string within quotes otherwise
*/
function formatAttributeValue(str) {
	if (!/[ "'\\\]]/.test(str)) {
		// Return as-is if it contains none of: space, ' " \ or ]
		return str;
	}
	var singleQuoted = "'" + str.replace(/[\\']/g, '\\$&') + "'",
		doubleQuoted = '"' + str.replace(/[\\"]/g, '\\$&') + '"';

	return (singleQuoted.length < doubleQuoted.length) ? singleQuoted : doubleQuoted;
}

function split_lines(text) {
	var lines = text.split('\n');
	var splitLines = new Array();
	var j = 0;
	var i;

	for(i = 0; i < lines.length; i++) {
		if (lines[i].length <= 80) {
			splitLines[j] = lines[i];
			j++;
		} else {
			var line = lines[i];
			var splitAt;
			do {
				splitAt = line.indexOf(' ', 80);

				if (splitAt === -1) {
					splitLines[j] = line;
					j++;
				} else {
					splitLines[j] = line.substring(0, splitAt);
					line = line.substring(splitAt);
					j++;
				}
			}
			while(splitAt !== -1);
		}
	}
	return splitLines;
}

/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close) {
	var selLength = (typeof(txtarea.textLength) === 'undefined') ? txtarea.value.length : txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selStart + open.length;
	txtarea.selectionEnd = selEnd + open.length;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;

	return;
}

/**
* Insert at Caret position. Code from
* http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
*/
function storeCaret(textEl) {
	if (textEl.createTextRange && document.selection) {
		textEl.caretPos = document.selection.createRange().duplicate();
	}
}

/**
* Caret Position object
*/
function caretPosition() {
	var start = null;
	var end = null;
}

/**
* Get the caret position in an textarea
*/
function getCaretPosition(txtarea) {
	var caretPos = new caretPosition();

	// simple Gecko/Opera way
	if (txtarea.selectionStart || txtarea.selectionStart === 0) {
		caretPos.start = txtarea.selectionStart;
		caretPos.end = txtarea.selectionEnd;
	}
	// dirty and slow IE way
	else if (document.selection) {
		// get current selection
		var range = document.selection.createRange();

		// a new selection of the whole textarea
		var range_all = document.body.createTextRange();
		range_all.moveToElementText(txtarea);

		// calculate selection start point by moving beginning of range_all to beginning of range
		var sel_start;
		for (sel_start = 0; range_all.compareEndPoints('StartToStart', range) < 0; sel_start++) {
			range_all.moveStart('character', 1);
		}

		txtarea.sel_start = sel_start;

		// we ignore the end value for IE, this is already dirty enough and we don't need it
		caretPos.start = txtarea.sel_start;
		caretPos.end = txtarea.sel_start;
	}

	return caretPos;
}
/* import Tribute from './jquery.tribute'; */

(function($) {
	'use strict';

	/**
	 * Mentions data returned from ajax requests
	 * @typedef {Object} MentionsData
	 * @property {string} name User/group name
	 * @property {string} id User/group ID
	 * @property {{img: string, group: string}} avatar Avatar data
	 * @property {string} rank User rank or empty string for groups
	 * @property {number} priority Priority of data entry
	 */

	/**
	 * Mentions class
	 * @constructor
	 */
	function Mentions() {
		let $mentionDataContainer = $('[data-mention-url]:first');
		let mentionURL = $mentionDataContainer.data('mentionUrl');
		let mentionNamesLimit = $mentionDataContainer.data('mentionNamesLimit');
		let mentionTopicId = $mentionDataContainer.data('topicId');
		let mentionUserId = $mentionDataContainer.data('userId');
		let queryInProgress = null;
		let cachedNames = [];
		let cachedAll = [];
		let cachedSearchKey = 'name';
		let tribute = null;

		/**
		 * Get default avatar
		 * @param {string} type Type of avatar; either 'g' for group or user on any other value
		 * @returns {string} Default avatar svg code
		 */
		function defaultAvatar(type) {
			if (type === 'g') {
				return '<svg class="mention-media-avatar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24"><path fill-rule="evenodd" d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>';
			} else {
				return '<svg class="mention-media-avatar" xmlns="http://www.w3.org/2000/svg" viewbox="0 0 24 24"><path fill-rule="evenodd" d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/></svg>';
			}
		}

		/**
		 * Get avatar HTML for data and type of avatar
		 *
		 * @param {object} data
		 * @param {string} type
		 * @return {string} Avatar HTML
		 */
		function getAvatar(data, type) {
			const avatarToHtml = (avatarData) => {
				if (avatarData.html !== '') {
					return avatarData.html;
				} else {
					return '<img class="avatar" src="' + avatarData.src + '" width="' + avatarData.width + '" height="' + avatarData.height + '" alt="' + avatarData.title + '" />';
				}
			}

			return data.html === '' && data.src === '' ? defaultAvatar(type) : "<span class='mention-media-avatar'>" + avatarToHtml(data)+ "</span>";
		}

		/**
		 * Get cached keyword for query string
		 * @param {string} query Query string
		 * @returns {?string} Cached keyword if one fits query, else empty string if cached keywords exist, null if cached keywords do not exist
		 */
		function getCachedKeyword(query) {
			if (!cachedNames) {
				return null;
			}

			let i;

			for (i = query.length; i > 0; i--) {
				let startStr = query.substr(0, i);
				if (cachedNames[startStr]) {
					return startStr;
				}
			}

			return '';
		}

		/**
		 * Get names matching query
		 * @param {string} query Query string
		 * @param {Object.<number, MentionsData>} items List of {@link MentionsData} items
		 * @param {string} searchKey Key to use for matching items
		 * @returns {Object.<number, MentionsData>} List of {@link MentionsData} items filtered with query and by searchKey
		 */
		function getMatchedNames(query, items, searchKey) {
			let i;
			let itemsLength;
			let matchedNames = [];
			for (i = 0, itemsLength = items.length; i < itemsLength; i++) {
				let item = items[i];
				if (isItemMatched(query, item, searchKey)) {
					matchedNames.push(item);
				}
			}
			return matchedNames;
		}

		/**
		 * Return whether item is matched by query
		 *
		 * @param {string} query Search query string
		 * @param {MentionsData} item Mentions data item
		 * @param {string }searchKey Key to use for matching items
		 * @return {boolean} True if items is matched, false otherwise
		 */
		function isItemMatched(query, item, searchKey) {
			return String(item[searchKey]).toLowerCase().indexOf(query.toLowerCase()) === 0;
		}

		/**
		 * Filter items by search query
		 *
		 * @param {string} query Search query string
		 * @param {Object.<number, MentionsData>} items List of {@link MentionsData} items
		 * @return {Object.<number, MentionsData>} List of {@link MentionsData} items filtered with query and by searchKey
		 */
		function itemFilter(query, items) {
			let i;
			let len;
			let highestPriorities = {u: 1, g: 1};
			let _unsorted = {u: {}, g: {}};
			let _exactMatch = [];
			let _results = [];

			// Reduce the items array to the relevant ones
			items = getMatchedNames(query, items, 'name');

			// Group names by their types and calculate priorities
			for (i = 0, len = items.length; i < len; i++) {
				let item = items[i];

				// Check for unsupported type - in general, this should never happen
				if (!_unsorted[item.type]) {
					continue;
				}

				// Current user doesn't want to mention themselves with "@" in most cases -
				// do not waste list space with their own name
				if (item.type === 'u' && item.id === String(mentionUserId)) {
					continue;
				}

				// Exact matches should not be prioritised - they always come first
				if (item.name === query) {
					_exactMatch.push(items[i]);
					continue;
				}

				// If the item hasn't been added yet - add it
				if (!_unsorted[item.type][item.id]) {
					_unsorted[item.type][item.id] = item;
					continue;
				}

				// Priority is calculated as the sum of priorities from different sources
				_unsorted[item.type][item.id].priority += parseFloat(item.priority.toString());

				// Calculate the highest priority - we'll give it to group names
				highestPriorities[item.type] = Math.max(highestPriorities[item.type], _unsorted[item.type][item.id].priority);
			}

			// All types of names should come at the same level of importance,
			// otherwise they will be unlikely to be shown
			// That's why we normalize priorities and push names to a single results array
			$.each(['u', 'g'], function(key, type) {
				if (_unsorted[type]) {
					$.each(_unsorted[type], function(name, value) {
						// Normalize priority
						value.priority /= highestPriorities[type];

						// Add item to all results
						_results.push(value);
					});
				}
			});

			// Sort names by priorities - higher values come first
			_results = _results.sort(function(a, b) {
				return b.priority - a.priority;
			});

			// Exact match is the most important - should come above anything else
			$.each(_exactMatch, function(name, value) {
				_results.unshift(value);
			});

			return _results;
		}

		/**
		 * remoteFilter callback filter function
		 * @param {string} query Query string
		 * @param {function} callback Callback function for filtered items
		 */
		function remoteFilter(query, callback) {
			/*
			* Do not make a new request until the previous one for the same query is returned
			* This fixes duplicate server queries e.g. when arrow keys are pressed
			*/
			if (queryInProgress === query) {
				setTimeout(function() {
					remoteFilter(query, callback);
				}, 1000);
				return;
			}

			let cachedKeyword = getCachedKeyword(query),
				cachedNamesForQuery = (cachedKeyword !== null) ? cachedNames[cachedKeyword] : null;

			/*
			* Use cached values when we can:
			* 1) There are some names in the cache relevant for the query
			*    (cache for the query with the same first characters contains some data)
			* 2) We have enough names to display OR
			*    all relevant names have been fetched from the server
			*/
			if (cachedNamesForQuery &&
				(getMatchedNames(query, cachedNamesForQuery, cachedSearchKey).length >= mentionNamesLimit ||
					cachedAll[cachedKeyword])) {
				callback(cachedNamesForQuery);
				return;
			}

			queryInProgress = query;

			let params = {keyword: query, topic_id: mentionTopicId, _referer: location.href};
			$.getJSON(mentionURL, params, function(data) {
				cachedNames[query] = data.names;
				cachedAll[query] = data.all;
				callback(data.names);
			}).always(function() {
				queryInProgress = null;
			});
		}

		/**
		 * Generate menu item HTML representation. Also ensures that mention-list
		 * class is set for unordered list in mention container
		 *
		 * @param {object} data Item data
		 * @returns {string} HTML representation of menu item
		 */
		function menuItemTemplate(data) {
			const itemData = data;
			const avatar = getAvatar(itemData.avatar, itemData.type);
			const rank = (itemData.rank) ? "<span class='mention-rank'>" + itemData.rank + "</span>" : '';
			const $mentionContainer = $('.' + tribute.current.collection.containerClass);

			if (typeof $mentionContainer !== 'undefined' && $mentionContainer.children('ul').hasClass('mention-list') === false) {
				$mentionContainer.children('ul').addClass('mention-list');
			}

			return "<span class='mention-media'>" + avatar + "</span><span class='mention-name'>" + itemData.name + rank + "</span>";
		}

		this.isEnabled = function() {
			return $mentionDataContainer.length;
		};

		this.handle = function(textarea) {
			tribute = new Tribute({
				trigger: '@',
				allowSpaces: true,
				containerClass: 'mention-container',
				selectClass: 'is-active',
				itemClass: 'mention-item',
				menuItemTemplate: menuItemTemplate,
				selectTemplate: function (item) {
					return '[mention=' + item.type + ':' + item.id + ']' + item.name + '[/mention]';
				},
				menuItemLimit: mentionNamesLimit,
				values: function (text, cb) {
					remoteFilter(text, users => cb(users));
				},
				lookup: function (element) {
					return element.hasOwnProperty('name') ? element.name : '';
				}
			});

			tribute.search.filter = itemFilter;

			tribute.attach($(textarea));
		};
	}
	phpbb.mentions = new Mentions();

	$(document).ready(function() {
		let doc;
		let textarea;

		// find textarea, make sure browser supports necessary functions
		if (document.forms[form_name]) {
			doc = document;
		} else {
			doc = opener.document;
		}

		if (!doc.forms[form_name]) {
			return;
		}

		textarea = doc.forms[form_name].elements[text_name];

		/**
		 * Allow to use tab character when typing code
		 * Keep indentation of last line of code when typing code
		 */
		phpbb.applyCodeEditor(textarea);

		if ($('#attach-panel').length) {
			phpbb.showDragNDrop(textarea);
		}

		if (phpbb.mentions.isEnabled()) {
			phpbb.mentions.handle(textarea);
		}

		$('textarea').on('keydown', function (e) {
			if (e.which === 13 && (e.metaKey || e.ctrlKey)) {
				$(this).closest('form').find(':submit').click();
			}
		});
	});
})(jQuery);
