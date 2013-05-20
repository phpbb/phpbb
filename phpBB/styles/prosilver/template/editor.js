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
			document.forms[form_name].elements[text_name].focus();
			theSelection = '';
			return;
		}
	} else if (document.forms[form_name].elements[text_name].selectionEnd
			&& (document.forms[form_name].elements[text_name].selectionEnd - document.forms[form_name].elements[text_name].selectionStart > 0)) {
		mozWrap(document.forms[form_name].elements[text_name], bbopen, bbclose);
		document.forms[form_name].elements[text_name].focus();
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
	return;
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
function attach_inline(index, filename) {
	insert_text('[attachment=' + index + ']' + filename + '[/attachment]');
	document.forms[form_name].elements[text_name].focus();
}

/**
* Add quote text to message
*/
function addquote(post_id, username, l_wrote) {
	var message_name = 'message_' + post_id;
	var theSelection = '';
	var divarea = false;
	var i;

	if (l_wrote === undefined) {
		// Backwards compatibility
		l_wrote = 'wrote';
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
			insert_text('[quote="' + username + '"]' + theSelection + '[/quote]');
		} else {
			insert_text(username + ' ' + l_wrote + ':' + '\n');
			var lines = split_lines(theSelection);
			for (i = 0; i < lines.length; i++) {
				insert_text('> ' + lines[i] + '\n');
			}
		}
	}

	return;
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

	if (selEnd === 1 || selEnd === 2) {
		selEnd = selLength;
	}

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
	if (textEl.createTextRange) {
		textEl.caretPos = document.selection.createRange().duplicate();
	}
}

/**
* Color pallette
*/
function colorPalette(dir, width, height) {
	var r = 0, 
		g = 0, 
		b = 0,
		numberList = new Array(6);
		color = '',
		html = '';

	numberList[0] = '00';
	numberList[1] = '40';
	numberList[2] = '80';
	numberList[3] = 'BF';
	numberList[4] = 'FF';

	html += '<table cellspacing="1" cellpadding="0" border="0">';

	for (r = 0; r < 5; r++) {
		if (dir == 'h') {
			html += '<tr>';
		}

		for (g = 0; g < 5; g++) {
			if (dir == 'v') {
				html += '<tr>';
			}

			for (b = 0; b < 5; b++) {
				color = String(numberList[r]) + String(numberList[g]) + String(numberList[b]);
				html += '<td bgcolor="#' + color + '" style="width: ' + width + 'px; height: ' + height + 'px;">';
				html += '<a href="#" onclick="bbfontstyle(\'[color=#' + color + ']\', \'[/color]\'); return false;" style="display: block; width: ' + width + 'px; height: ' + height + 'px; " alt="#' + color + '" title="#' + color + '"></a>';
				html += '</td>';
			}

			if (dir == 'v') {
				html += '</tr>';
			}
		}

		if (dir == 'h') {
			html += '</tr>';
		}
	}
	html += '</table>';
	return html;
}

(function($) {
	$(document).ready(function() {
		$('#color_palette_placeholder').each(function() {
			$(this).html(colorPalette('h', 15, 12));
		});
	});
})(jQuery);

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

/**
* Allow to use tab character when typing code
* Keep indentation of last line of code when typing code
*/
(function($) {
	$(document).ready(function() {
		var doc, textarea, startTags, endTags;

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
		if (!textarea || typeof textarea.selectionStart !== 'number') {
			return;
		}

		// list of allowed start and end bbcode code tags, in lower case
		startTags = ['[code]', '[code='];
		endTags = ['[/code]'];

		function inTag() {
			var start = textarea.selectionStart,
				lastEnd = -1,
				lastStart = -1,
				i, index, value;

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

		function getLastLine() {
			var start = textarea.selectionStart,
				value = textarea.value,
				index = value.lastIndexOf("\n", start - 1);
			return value.substring(index + 1, start);
		}

		function appendCode(code) {
			var start = textarea.selectionStart,
				end = textarea.selectionEnd,
				value = textarea.value;
			textarea.value = value.substr(0, start) + code + value.substr(end);
			textarea.selectionStart = textarea.selectionEnd = start + code.length;
		}

		$(textarea).on('keydown', function(event) {
			var key = event.keyCode || event.which;

			// intercept tabs
			if (key == 9) {
				if (inTag()) {
					appendCode("\t");
					event.preventDefault();
					return;
				}
			}

			// intercept new line characters
			if (key == 13) {
				if (inTag()) {
					var lastLine = getLastLine(),
						code = '' + /^\s*/g.exec(lastLine);
					if (code.length > 0) {
						appendCode("\n" + code);
						event.preventDefault();
						return;
					}
				}
			}
		});
	});
})(jQuery);

