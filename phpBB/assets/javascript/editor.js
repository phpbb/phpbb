/* global phpbb, text_name, help_line, form_name, bbtags */

/* eslint-disable camelcase, no-unused-vars, no-prototype-builtins */

/**
* BbCode control by subBlue design [ www.subBlue.com ]
* Includes unixsafe colour palette selector by SHS`
*/

// Startup variables
const imageTag = false;
let theSelection = false;
const bbcodeEnabled = true;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
const clientPC = navigator.userAgent.toLowerCase(); // Get client info
const clientVer = parseInt(navigator.appVersion, 10); // Get browser version

const is_ie = ((clientPC.indexOf('msie') !== -1) && (clientPC.indexOf('opera') === -1));
const is_win = ((clientPC.indexOf('win') !== -1) || (clientPC.indexOf('16bit') !== -1));
let baseHeight;

/**
* Shows the help messages in the helpline window
*/
function helpline(help) {
	document.forms[form_name].helpbox.value = help_line[help];
}

/**
* Fix a bug involving the TextRange object. From
* http://www.frostjedi.com/terra/scripts/demo/caretBug.html
*/
function initInsertions() {
	let doc;

	if (document.forms[form_name]) {
		doc = document;
	} else {
		doc = opener.document;
	}

	const textarea = doc.forms[form_name].elements[text_name];

	if (is_ie && typeof (baseHeight) !== 'number') {
		textarea.focus();
		baseHeight = doc.selection.createRange().duplicate().boundingHeight;

		if (!document.forms[form_name]) {
			document.body.focus();
		}
	}
}

/**
* Bbstyle
*/
function bbstyle(bbnumber) {
	if (bbnumber === -1) {
		insert_text('[*]');
		document.forms[form_name].elements[text_name].focus();
	} else {
		bbfontstyle(bbtags[bbnumber], bbtags[bbnumber + 1]);
	}
}

/**
* Apply bbcodes
*/
function bbfontstyle(bbopen, bbclose) {
	theSelection = false;

	const textarea = document.forms[form_name].elements[text_name];

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

	// The new position for the cursor after adding the bbcode
	const caret_pos = getCaretPosition(textarea).start;
	const new_pos = caret_pos + bbopen.length;

	// Open tag
	insert_text(bbopen + bbclose);

	// Center the cursor when we don't have a selection
	// Gecko and proper browsers
	if (!isNaN(textarea.selectionStart)) {
		textarea.selectionStart = new_pos;
		textarea.selectionEnd = new_pos;
	} else if (document.selection) { // IE
		const range = textarea.createTextRange();
		range.move('character', new_pos);
		range.select();
		storeCaret(textarea);
	}

	textarea.focus();
}

/**
* Insert text at position
*/
function insert_text(text, spaces, popup) {
	let textarea;

	if (popup) {
		textarea = opener.document.forms[form_name].elements[text_name];
	} else {
		textarea = document.forms[form_name].elements[text_name];
	}

	if (spaces) {
		text = ' ' + text + ' ';
	}

	// Since IE9, IE also has textarea.selectionStart, but it still needs to be treated the old way.
	// Therefore we simply add a !is_ie here until IE fixes the text-selection completely.
	if (!isNaN(textarea.selectionStart) && !is_ie) {
		const sel_start = textarea.selectionStart;
		const sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '');
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	} else if (textarea.createTextRange && textarea.caretPos) {
		if (baseHeight !== textarea.caretPos.boundingHeight) {
			textarea.focus();
			storeCaret(textarea);
		}

		const caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) === ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
	} else {
		textarea.value += text;
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
	const message_name = 'message_' + post_id;
	let theSelection = '';
	let divarea = false;
	let i;

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
			theSelection = theSelection.replace(/&lt;/ig, '<');
			theSelection = theSelection.replace(/&gt;/ig, '>');
			theSelection = theSelection.replace(/&amp;/ig, '&');
			theSelection = theSelection.replace(/&nbsp;/ig, ' ');
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
			insert_text(username + ' ' + l_wrote + ':\n');
			const lines = split_lines(theSelection);
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
	let quote = '[quote';
	if (attributes.author) {
		// Add the author as the BBCode's default attribute
		quote += '=' + formatAttributeValue(attributes.author);
		delete attributes.author;
	}
	for (const name in attributes) {
		if (attributes.hasOwnProperty(name)) {
			const value = attributes[name];
			quote += ' ' + name + '=' + formatAttributeValue(value.toString());
		}
	}
	quote += ']';
	const newline = ((quote + text + '[/quote]').length > 80 || text.indexOf('\n') > -1) ? '\n' : '';
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

	const singleQuoted = '\'' + str.replace(/[\\']/g, '\\$&') + '\'';
	const doubleQuoted = '"' + str.replace(/[\\"]/g, '\\$&') + '"';

	return (singleQuoted.length < doubleQuoted.length) ? singleQuoted : doubleQuoted;
}

function split_lines(text) {
	const lines = text.split('\n');
	const splitLines = [];
	let j = 0;
	let i;

	for (i = 0; i < lines.length; i++) {
		if (lines[i].length <= 80) {
			splitLines[j] = lines[i];
			j++;
		} else {
			let line = lines[i];
			let splitAt;
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
			while (splitAt !== -1);
		}
	}
	return splitLines;
}

/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close) {
	const selLength = (typeof (txtarea.textLength) === 'undefined') ? txtarea.value.length : txtarea.textLength;
	const selStart = txtarea.selectionStart;
	const selEnd = txtarea.selectionEnd;
	const scrollTop = txtarea.scrollTop;

	const s1 = (txtarea.value).substring(0, selStart);
	const s2 = (txtarea.value).substring(selStart, selEnd);
	const s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selStart + open.length;
	txtarea.selectionEnd = selEnd + open.length;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;
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
function CaretPosition() {
	const start = null;
	const end = null;
}

/**
* Get the caret position in an textarea
*/
function getCaretPosition(txtarea) {
	const caretPos = new CaretPosition();

	// Simple Gecko/Opera way
	if (txtarea.selectionStart || txtarea.selectionStart === 0) {
		caretPos.start = txtarea.selectionStart;
		caretPos.end = txtarea.selectionEnd;
	} else if (document.selection) { // Dirty and slow IE way
		// Get current selection
		const range = document.selection.createRange();

		// A new selection of the whole textarea
		const range_all = document.body.createTextRange();
		range_all.moveToElementText(txtarea);

		// Calculate selection start point by moving beginning of range_all to beginning of range
		let sel_start;
		for (sel_start = 0; range_all.compareEndPoints('StartToStart', range) < 0; sel_start++) {
			range_all.moveStart('character', 1);
		}

		txtarea.sel_start = sel_start;

		// We ignore the end value for IE, this is already dirty enough and we don't need it
		caretPos.start = txtarea.sel_start;
		caretPos.end = txtarea.sel_start;
	}

	return caretPos;
}

/**
* Allow to use tab character when typing code
* Keep indentation of last line of code when typing code
*/
(function ($) {
	$(document).ready(() => {
		let doc;
		const textarea = doc.forms[form_name].elements[text_name];

		// Find textarea, make sure browser supports necessary functions
		if (document.forms[form_name]) {
			doc = document;
		} else {
			doc = opener.document;
		}

		if (!doc.forms[form_name]) {
			return;
		}

		phpbb.applyCodeEditor(textarea);
		if ($('#attach-panel').length !== 0) {
			phpbb.showDragNDrop(textarea);
		}

		$('textarea').on('keydown', e => {
			if (e.which === 13 && (e.metaKey || e.ctrlKey)) {
				$(this).closest('form').find(':submit').click();
			}
		});
	});
})(jQuery);

/* eslint-enable camelcase, no-unused-vars,no-prototype-builtins */
