//var bbcb_mg_img_path = "images/bbcb_mg/images/";
//var bbcb_mg_img_path = "images/bbcb_mg/images/png/";
//var bbcb_mg_img_ext = "" + bbcb_mg_img_ext;
//var bbcb_mg_img_path = "images/bbcb_mg/images/gif/";
//var bbcb_mg_img_ext = ".gif";

var Quote = 0;
var Bold = 0;
var Italic = 0;
var Underline = 0;
var Strikeout = 0;
var Code = 0;
var flash = 0;
var fc = 0;
var fs = 0;
var ft = 0;
var center = 0;
var right = 0;
var left = 0;
var justify = 0;
var fade = 0;
var marqd = 0;
var marqu = 0;
var marql = 0;
var marqr = 0;
var mail = 0;
var imgba = 0;
var video = 0;
var googlevideo = 0;
var youtube = 0;
var vimeo = 0;
var quicktime = 0;
var ram = 0;
var emff = 0;
var hr = 0;
var bullet = 0;
var rainbow = 0;
var superscript = 0;
var subscript = 0;
var List = 0;
var Spoiler = 0;
var Cell = 0;
var Table = 0;
var Td = 0;

bbcode = new Array();
bbtags = new Array(
'[b]','[/b]',
'[i]','[/i]',
'[u]','[/u]',
'[quote]','[/quote]',
'[code]','[/code]',
'[list]','[/list]',
'[list=]','[/list]',
'[align=]','[/align]',
'[center]','[/center]',
'[color=]','[/color]',
'[img]','[/img]',
'[img align=left]','[/img]',
'[img align=right]','[/img]',
'[imgba]','[/imgba]',
'[albumimg]','[/albumimg]',
'[albumimg align=left]','[/albumimg]',
'[albumimg align=right]','[/albumimg]',
'[url]','[/url]',
'[email]','[/email]',
'[blur]','[/blur]',
'[fade]','[/fade]',
'[spoiler]','[/spoiler]',
'[cell]','[/cell]',
'[marquee=]','[/marquee]',
'[highlight=]','[/highlight]',
'[flipv]','[/flipv]',
'[fliph]','[/fliph]',
'[swf width=200 height=200]','[/swf]',
'[php]','[/php]',
'[wave]','[/wave]',
'[stream]','[/stream]',
'[real]','[/real]',
'[video width=320 height=240]','[/video]',
'[googlevideo]','[/googlevideo]',
'[youtube]','[/youtube]',
'[vimeo]','[/vimeo]',
'[emff]','[/emff]'
);

/*
if (plugins.album) {
	bbtags.splice(0, 0,
		'[albumimg]','[/albumimg]',
		'[albumimg align=left]','[/albumimg]',
		'[albumimg align=right]','[/albumimg]'
	);
}
*/

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf('msie') != -1) && (clientPC.indexOf('opera') == -1));
var is_win = ((clientPC.indexOf('win') != -1) || (clientPC.indexOf('16bit') != -1));
var is_iphone = ((clientPC.indexOf('iphone'))!=-1);

var baseHeight;

// phpBB3 onload function...
//onload_functions.push('initInsertions()');

/**
* Fix a bug involving the TextRange object. From
* http://www.frostjedi.com/terra/scripts/demo/caretBug.html
*/
function initInsertions()
{
	var doc;

	if (document.forms[form_name])
	{
		doc = document;
	}
	else
	{
		doc = opener.document;
	}

	var textarea = doc.forms[form_name].elements[text_name];

	if (is_ie && typeof(baseHeight) != 'number')
	{
		textarea.focus();
		baseHeight = doc.selection.createRange().duplicate().boundingHeight;

		if (!document.forms[form_name])
		{
			document.body.focus();
		}
	}
}

function split_lines(text)
{
	var lines = text.split('\n');
	var splitLines = new Array();
	var j = 0;
	for(i = 0; i < lines.length; i++)
	{
		if (lines[i].length <= 80)
		{
			splitLines[j] = lines[i];
			j++;
		}
		else
		{
			var line = lines[i];
			do
			{
				var splitAt = line.indexOf(' ', 80);

				if (splitAt == -1)
				{
					splitLines[j] = line;
					j++;
				}
				else
				{
					splitLines[j] = line.substring(0, splitAt);
					line = line.substring(splitAt);
					j++;
				}
			}
			while(splitAt != -1);
		}
	}
	return splitLines;
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = (typeof(txtarea.textLength) == 'undefined') ? txtarea.value.length : txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;
	if (selEnd == 1 || selEnd == 2)
	{
		selEnd = selLength;
	}
	var s1 = (txtarea.value).substring(0, selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selStart + open.length;
	txtarea.selectionEnd = selEnd + open.length;
	//txtarea.selectionStart = selEnd + open.length + close.length;
	//txtarea.selectionEnd = txtarea.selectionStart;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;
	return;
}

function mozInsertOld(txtarea, openTag, closeTag)
{
	if (txtarea.selectionEnd > txtarea.value.length)
	{
		txtarea.selectionEnd = txtarea.value.length;
	}
	var startPos = txtarea.selectionStart;
	var endPos = txtarea.selectionEnd + openTag.length;
	txtarea.value=txtarea.value.slice(0, startPos) + openTag + txtarea.value.slice(startPos);
	txtarea.value=txtarea.value.slice(0, endPos) + closeTag + txtarea.value.slice(endPos);
	txtarea.selectionStart = startPos + openTag.length;
	txtarea.selectionEnd = endPos;
	txtarea.focus();
}

function mozInsert(txtarea, openTag, closeTag)
{
	var sel_start = txtarea.selectionStart;
	var sel_end = txtarea.selectionEnd;
	mozWrap(txtarea, openTag, closeTag)
	txtarea.selectionStart = sel_start + openTag.length;
	txtarea.selectionEnd = sel_end + openTag.length;
	txtarea.focus();
}

// Insert at Caret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl)
{
	if (textEl.createTextRange)
	{
		textEl.caretPos = document.selection.createRange().duplicate();
	}
}

function bbcbmg_insert(text, spaces, popup)
{
	var txtarea;

	if (!popup)
	{
		txtarea = document.forms[form_name].elements[text_name];
	}
	else
	{
		txtarea = opener.document.forms[form_name].elements[text_name];
	}

	if (spaces)
	{
		text = ' ' + text + ' ';
	}

	if (!isNaN(txtarea.selectionStart))
	{
		//mozInsert(txtarea, text, "");
		var sel_start = txtarea.selectionStart;
		var sel_end = txtarea.selectionEnd;
		mozWrap(txtarea, text, '');
		txtarea.selectionStart = sel_start + text.length;
		txtarea.selectionEnd = sel_end + text.length;
	}
	else if (txtarea.createTextRange && txtarea.caretPos)
	{
		if (baseHeight != txtarea.caretPos.boundingHeight)
		{
			txtarea.focus();
			storeCaret(txtarea);
		}
		var caret_pos = txtarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
	}
	else
	{
		txtarea.value = txtarea.value + text;
	}
	txtarea.focus();
	return;
}

function PostWrite(text)
{
	bbcbmg_insert(text, false, false);
}

function emoticon(text)
{
	bbcbmg_insert(text, true, false);
}

// Shows the help messages in the helpline window
function helpline(help)
{
	document.getElementById('helpbox').innerHTML = eval("s_" + help + "_help");
	//document.forms[form_name].helpbox.value = eval("s_" + help + "_help");
}

// Replacement for arrayname.length property
function getarraysize(thearray)
{
	for (i = 0; i < thearray.length; i++)
	{
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
		{
			return i;
		}
	}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value)
{
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray)
{
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}

function checkForm()
{
	formErrors = false;
	if (document.forms[form_name].elements[text_name].value.length < 4)
	{
		formErrors = s_formerrors;
	}
	if (formErrors)
	{
		alert(formErrors);
		return false;
	}
	else
	{
		//bbstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

function bbfontstyle(bbopen, bbclose)
{
	var txtarea = document.forms[form_name].elements[text_name];
	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (!theSelection)
		{
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				var text = bbopen + bbclose;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else
			{
				txtarea.value += bbopen + bbclose;
			}
			txtarea.focus();
			return;
		}
		document.selection.createRange().text = bbopen + theSelection + bbclose;
		txtarea.focus();
		return;
	}
	else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
	{
		mozInsert(txtarea, bbopen, bbclose);
		return;
	}
	else
	{
		txtarea.value += bbopen + bbclose;
		txtarea.focus();
	}
	storeCaret(txtarea);
}

function bbstyle(bbnumber)
{
	var txtarea = document.forms[form_name].elements[text_name];

	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1)
	{ // Close all open tags & default button names
		while (bbcode[0])
		{
			butnumber = arraypop(bbcode) - 1;
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				var text = bbtags[butnumber + 1];
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
			{
				mozInsert(txtarea, bbtags[butnumber + 1], "");
			}
			else
			{
				txtarea.value += bbtags[butnumber + 1];
			}
			buttext = eval('document.forms[form_name].addbbcode' + butnumber + '.value');
			eval('document.forms[form_name].addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}
	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection)
		{
			// Add tags around selection
			document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++)
	{
		if (bbcode[i] == bbnumber+1)
		{
			bblast = i;
			donotinsert = true;
		}
	}
	if (donotinsert)
	{ // Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast])
		{
			butnumber = arraypop(bbcode) - 1;
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				var text = bbtags[butnumber + 1];
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
			{
				mozInsert(txtarea, bbtags[butnumber + 1], "");
			}
			else
			{
				txtarea.value += bbtags[butnumber + 1];
			}
			buttext = eval('document.forms[form_name].addbbcode' + butnumber + '.value');
			eval('document.forms[form_name].addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
			imageTag = false;
		}
		txtarea.focus();
		return;
	}
	else
	{ // Open tags
		if (imageTag && (bbnumber != 14))
		{ // Close image tag before adding another
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				var text = bbtags[15];
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
			{
				mozInsert(txtarea, bbtags[15], "");
			}
			else
			{
				txtarea.value += bbtags[15];
			}
			lastValue = arraypop(bbcode) - 1; // Remove the close image tag from the list
			document.forms[form_name].addbbcode14.value = "Img"; // Return button back to normal state
			imageTag = false;
		}
		// Open tag
		if (bbnumber == 16)
		{
			var url = prompt(s_url_insert, s_url_insert_tip);

			if (url == null)
			{
				return;
			}
			else if (!url)
			{
				alert(s_gen_error + s_url_error);
				return;
			}
			else
			{
				var title = prompt(s_url_title_insert, s_url_title_insert_tip);
				if (title == null)
				{
					return;
				}
				else if (!title)
				{
					var text = "[url]" + url + "[/url]";
				}
				else
				{
					var text = "[url=" + url + "]" + title + "[/url]";
				}
			}
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
			{
				mozInsert(txtarea, text, "");
			}
			else
			{
				txtarea.value += text;
			}
		}
		else
		{
			var text = bbtags[bbnumber];
			if (txtarea.createTextRange && txtarea.caretPos)
			{
				if (baseHeight != txtarea.caretPos.boundingHeight)
				{
					txtarea.focus();
					storeCaret(txtarea);
				}
				var caretPos = txtarea.caretPos;
				caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
			}
			else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
			{
				mozInsert(txtarea, bbtags[bbnumber], "");
			}
			else
			{
				txtarea.value += bbtags[bbnumber];
			}
			if ((bbnumber == 14) && (imageTag == false))
			{
				imageTag = 1;
			}
			arraypush(bbcode, bbnumber + 1);
			eval('document.forms[form_name].addbbcode' + bbnumber + '.value += "*"');
		}
		txtarea.focus();
		return;
	}
	storeCaret(txtarea);
}

// Mighty Gorgon - Highlight/Copy
function copymetasearch()
{
	document.forms[form_name].elements[text_name].select();
	document.forms[form_name].elements[text_name].focus();
	if ((navigator.appName=="Microsoft Internet Explorer") && (parseInt(navigator.appVersion)>=4))
	{
		textRange = document.forms[form_name].elements[text_name].createTextRange();
		textRange.execCommand("RemoveFormat");
		textRange.execCommand("Copy");
	}
}
// Mighty Gorgon - Highlight/Copy

function BBCmail()
{
	var FoundErrors = '';
	var entermail = prompt(s_email_insert, s_email_insert_tip);
	if (!entermail)
	{
		FoundErrors += s_email_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[email]" + entermail + "[/email]";
	PostWrite(ToAdd);
}

function BBCurl()
{
	var FoundErrors = '';
	var enterURL = prompt(s_url_insert, s_url_insert_tip);
	var enterTITLE = prompt(s_url_title_insert, s_url_title_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_url_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	if (enterTITLE == null)
	{
		var ToAdd = "[url]" + enterURL + "[/url]";
	}
	else if (!enterTITLE)
	{
		var ToAdd = "[url]" + enterURL + "[/url]";
	}
	else
	{
		var ToAdd = "[url=" + enterURL + "]" + enterTITLE + "[/url]";
	}
	PostWrite(ToAdd);
}

function BBCimg()
{
	var FoundErrors = '';
	var enterURL = prompt(s_img_insert, s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_img_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[img]" + enterURL + "[/img]";
	PostWrite(ToAdd);
}

function BBCimgl()
{
	var FoundErrors = '';
	var enterURL = prompt(s_img_insert, s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_img_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[img align=left]" + enterURL + "[/img]";
	PostWrite(ToAdd);
}

function BBCimgr()
{
	var FoundErrors = '';
	var enterURL = prompt(s_img_insert, s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_img_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[img align=right]" + enterURL + "[/img]";
	PostWrite(ToAdd);
}

function BBCimgba()
{
	var FoundErrors = '';
	var enterURLB = prompt(s_img_insert, s_url_insert_tip);
	if (!enterURLB)
	{
		FoundErrors += s_img_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var enterURLA = prompt(s_img_insert, s_url_insert_tip);
	if (!enterURLA)
	{
		FoundErrors += s_img_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[imgba before=" + enterURLB + " after=" + enterURLB + " w=600 h=400]Before And After[/imgba]";
	PostWrite(ToAdd);
}

function BBCalbumimg()
{
	var FoundErrors = '';
	var enterURL = prompt(s_albumimg_insert, s_albumimg_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_albumimg_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[albumimg]" + enterURL + "[/albumimg]";
	PostWrite(ToAdd);
}

function BBCalbumimgl()
{
	var FoundErrors = '';
	var enterURL = prompt(s_albumimg_insert, s_albumimg_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_albumimg_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[albumimg align=left]" + enterURL + "[/albumimg]";
	PostWrite(ToAdd);
}

function BBCalbumimgr()
{
	var FoundErrors = '';
	var enterURL = prompt(s_albumimg_insert, s_albumimg_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_albumimg_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[albumimg align=right]" + enterURL + "[/albumimg]";
	PostWrite(ToAdd);
}

function BBCram()
{
	var FoundErrors = '';
	var enterURL = prompt(s_ram_insert ,s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_file_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[ram]" + enterURL + "[/ram]";
	PostWrite(ToAdd);
}

function BBCvideo()
{
	var FoundErrors = '';
	var enterFURL = prompt(s_video_insert, s_url_insert_tip);
	if (!enterFURL)
	{
		FoundErrors += s_file_insert_error;
	}
	var enterW = prompt(s_video_w_insert, "320");
	if (!enterW)
	{
		FoundErrors += s_video_w_error;
	}
	var enterH = prompt(s_video_h_insert, "240");
	if (!enterH)
	{
		FoundErrors += s_video_h_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[video width="+enterW+" height="+enterH+"]"+enterFURL + "[/video]";
	PostWrite(ToAdd);
}

function BBCflash()
{
	var FoundErrors = '';
	var enterFURL = prompt(s_flash_insert, s_url_insert_tip);
	if (!enterFURL)
	{
		FoundErrors += s_file_insert_error;
	}
	var enterW = prompt(s_flash_w_insert, "320");
	if (!enterW)
	{
		FoundErrors += s_flash_w_error;
	}
	var enterH = prompt(s_flash_h_insert, "240");
	if (!enterH)
	{
		FoundErrors += s_flash_h_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[flash width="+enterW+" height="+enterH+"]"+enterFURL + "[/flash]";
	PostWrite(ToAdd);
}

function BBCstream()
{
	var FoundErrors = '';
	var enterURL = prompt(s_stream_insert, s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_file_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[stream]" + enterURL + "[/stream]";
	PostWrite(ToAdd);
}

function BBCgooglevideo()
{
	var FoundErrors = '';
	var enterURL = prompt(s_googlevideo_insert, s_id_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_id_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[googlevideo]" + enterURL + "[/googlevideo]";
	PostWrite(ToAdd);
}

function BBCyoutube()
{
	var FoundErrors = '';
	var enterURL = prompt(s_youtube_insert, s_id_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_id_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[youtube]" + enterURL + "[/youtube]";
	PostWrite(ToAdd);
}

function BBCvimeo()
{
	var FoundErrors = '';
	var enterURL = prompt(s_vimeo_insert, s_id_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_id_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[vimeo]" + enterURL + "[/vimeo]";
	PostWrite(ToAdd);
}

function BBCemff()
{
	var FoundErrors = '';
	var enterURL = prompt(s_emff_insert, s_url_insert_tip);
	if (!enterURL)
	{
		FoundErrors += s_file_insert_error;
	}
	if (FoundErrors)
	{
		alert(s_gen_error + FoundErrors);
		return;
	}
	var ToAdd = "[emff]" + enterURL + "[/emff]";
	PostWrite(ToAdd);
}

function BBCbold()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[b]" + theSelection + "[/b]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		//mozWrap(txtarea, "[b]", "[/b]");
		mozInsert(txtarea, "[b]", "[/b]");
		return;
	}
	if (Bold == 0)
	{
		ToAdd = "[b]";
		document.forms[form_name].bold_img.src = bbcb_mg_img_path + "bold1" + bbcb_mg_img_ext;
		Bold = 1;
	}
	else
	{
		ToAdd = "[/b]";
		document.forms[form_name].bold_img.src = bbcb_mg_img_path + "bold" + bbcb_mg_img_ext;
		Bold = 0;
	}
	PostWrite(ToAdd);
}

function BBCitalic()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[i]" + theSelection + "[/i]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[i]", "[/i]");
		return;
	}
	if (Italic == 0)
	{
		ToAdd = "[i]";
		document.forms[form_name].italic.src = bbcb_mg_img_path + "italic1" + bbcb_mg_img_ext;
		Italic = 1;
	}
	else
	{
		ToAdd = "[/i]";
		document.forms[form_name].italic.src = bbcb_mg_img_path + "italic" + bbcb_mg_img_ext;
		Italic = 0;
	}
	PostWrite(ToAdd);
}

function BBCunder()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[u]" + theSelection + "[/u]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[u]", "[/u]");
		return;
	}
	if (Underline == 0)
	{
		ToAdd = "[u]";
		document.forms[form_name].under.src = bbcb_mg_img_path + "under1" + bbcb_mg_img_ext;
		Underline = 1;
	}
	else
	{
		ToAdd = "[/u]";
		document.forms[form_name].under.src = bbcb_mg_img_path + "under" + bbcb_mg_img_ext;
		Underline = 0;
	}
	PostWrite(ToAdd);
}

function BBCstrike()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[strike]" + theSelection + "[/strike]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[strike]", "[/strike]");
		return;
	}
	if (Strikeout == 0)
	{
		ToAdd = "[strike]";
		document.strik.src = bbcb_mg_img_path + "strike1" + bbcb_mg_img_ext;
		Strikeout = 1;
	}
	else
	{
		ToAdd = "[/strike]";
		document.strik.src = bbcb_mg_img_path + "strike" + bbcb_mg_img_ext;
		Strikeout = 0;
	}
	PostWrite(ToAdd);
}

function BBClist() {
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[list]" + theSelection + "[/list]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[list]", "[/list]");
		return;
	}
	if (List == 0)
	{
		ToAdd = "[list]";
		document.listdf.src = bbcb_mg_img_path + "list1" + bbcb_mg_img_ext;
		List = 1;
	}
	else
	{
		ToAdd = "[/list]";
		document.listdf.src = bbcb_mg_img_path + "list" + bbcb_mg_img_ext;
		List = 0;
	}
	PostWrite(ToAdd);
}

function BBClistO() {
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[list=1]" + theSelection + "[/list]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[list=1]", "[/list]");
		return;
	}
	if (List == 0)
	{
		ToAdd = "[list=1]";
		document.listodf.src = bbcb_mg_img_path + "list_o1" + bbcb_mg_img_ext;
		List = 1;
	}
	else
	{
		ToAdd = "[/list]";
		document.listodf.src = bbcb_mg_img_path + "list_o" + bbcb_mg_img_ext;
		List = 0;
	}
	PostWrite(ToAdd);
}

function BBCquick()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[quick]" + theSelection + "[/quick]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[quick]", "[/quick]");
		return;
	}
	if (quicktime == 0)
	{
		ToAdd = "[quick]";
		document.quick.src = bbcb_mg_img_path + "quick1" + bbcb_mg_img_ext;
		quicktime = 1;
	}
	else
	{
		ToAdd = "[/quick]";
		document.quick.src = bbcb_mg_img_path + "quick" + bbcb_mg_img_ext;
		quicktime = 0;
	}
	PostWrite(ToAdd);
}

function BBCsup()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[sup]" + theSelection + "[/sup]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[sup]", "[/sup]");
		return;
	}
	if (superscript == 0)
	{
		ToAdd = "[sup]";
		document.supscript.src = bbcb_mg_img_path + "sup1" + bbcb_mg_img_ext;
		superscript = 1;
	}
	else
	{
		ToAdd = "[/sup]";
		document.supscript.src = bbcb_mg_img_path + "sup" + bbcb_mg_img_ext;
		superscript = 0;
	}
	PostWrite(ToAdd);
}

function BBCsub()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[sub]" + theSelection + "[/sub]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[sub]", "[/sub]");
		return;
	}
	if (subscript == 0)
	{
		ToAdd = "[sub]";
		document.subs.src = bbcb_mg_img_path + "sub1" + bbcb_mg_img_ext;
		subscript = 1;
	}
	else
	{
		ToAdd = "[/sub]";
		document.subs.src = bbcb_mg_img_path + "sub" + bbcb_mg_img_ext;
		subscript = 0;
	}
	PostWrite(ToAdd);
}

function BBCgrad()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[rainbow]" + theSelection + "[/rainbow]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[rainbow]", "[/rainbow]");
		return;
	}
	if (rainbow == 0)
	{
		ToAdd = "[rainbow]";
		document.rainb.src = bbcb_mg_img_path + "grad1" + bbcb_mg_img_ext;
		rainbow = 1;
	}
	else
	{
		ToAdd = "[/rainbow]";
		document.rainb.src = bbcb_mg_img_path + "grad" + bbcb_mg_img_ext;
		rainbow = 0;
	}
	PostWrite(ToAdd);
}

function BBCgrad2() {
	var oSelect,oSelectRange;
	document.forms[form_name].elements[text_name].focus();
	oSelect = document.selection;
	oSelectRange = oSelect.createRange();
	if (oSelectRange.text.length < 1)
	{
		alert(s_grad_select);
		return;
	}
	if (oSelectRange.text.length > 120)
	{
		alert(s_grad_error);
		return;
	}
	showModalDialog(s_grad_path, oSelectRange, "help:no; center:yes; status:no; dialogHeight:50px; dialogWidth:50px");
}

function BBChr()
{
	ToAdd = "[hr]";
	PostWrite(ToAdd);
}

function BBCbullet()
{
	ToAdd = "[*]";
	PostWrite(ToAdd);
}

function BBCmarqu()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[marquee direction=up]" + theSelection + "[/marquee]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[marquee direction=up]", "[/marquee]");
		return;
	}
	if (marqu == 0)
	{
		ToAdd = "[marquee direction=up]";
		document.forms[form_name].marqu.src = bbcb_mg_img_path + "marqu1" + bbcb_mg_img_ext;
		marqu = 1;
	}
	else
	{
		ToAdd = "[/marquee]";
		document.forms[form_name].marqu.src = bbcb_mg_img_path + "marqu" + bbcb_mg_img_ext;
		marqu = 0;
	}
	PostWrite(ToAdd);
}

function BBCmarqd()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[marquee direction=down]" + theSelection + "[/marquee]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[marquee direction=down]", "[/marquee]");
		return;
	}
	if (marqd == 0)
	{
		ToAdd = "[marquee direction=down]";
		document.forms[form_name].marqd.src = bbcb_mg_img_path + "marqd1" + bbcb_mg_img_ext;
		marqd = 1;
	}
	else
	{
		ToAdd = "[/marquee]";
		document.forms[form_name].marqd.src = bbcb_mg_img_path + "marqd" + bbcb_mg_img_ext;
		marqd = 0;
	}
	PostWrite(ToAdd);
}

function BBCmarql()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[marquee direction=left]" + theSelection + "[/marquee]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[marquee direction=left]", "[/marquee]");
		return;
	}
	if (marql == 0)
	{
		ToAdd = "[marquee direction=left]";
		document.forms[form_name].marql.src = bbcb_mg_img_path + "marql1" + bbcb_mg_img_ext;
		marql = 1;
	}
	else
	{
		ToAdd = "[/marquee]";
		document.forms[form_name].marql.src = bbcb_mg_img_path + "marql" + bbcb_mg_img_ext;
		marql = 0;
	}
	PostWrite(ToAdd);
}

function BBCmarqr()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[marquee direction=right]" + theSelection + "[/marquee]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[marquee direction=right]", "[/marquee]");
		return;
	}
	if (marqr == 0)
	{
		ToAdd = "[marquee direction=right]";
		document.forms[form_name].marqr.src = bbcb_mg_img_path + "marqr1" + bbcb_mg_img_ext;
		marqr = 1;
	}
	else
	{
		ToAdd = "[/marquee]";
		document.forms[form_name].marqr.src = bbcb_mg_img_path + "marqr" + bbcb_mg_img_ext;
		marqr = 0;
	}
	PostWrite(ToAdd);
}

function BBCdir(dirc)
{
	document.forms[form_name].elements[text_name].dir=(dirc);
}

function BBCfade()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[opacity]" + theSelection + "[/opacity]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[opacity]", "[/opacity]");
		return;
	}
	if (fade == 0)
	{
		ToAdd = "[opacity]";
		document.forms[form_name].fade.src = bbcb_mg_img_path + "fade1" + bbcb_mg_img_ext;
		fade = 1;
	}
	else
	{
		ToAdd = "[/opacity]";
		document.forms[form_name].fade.src = bbcb_mg_img_path + "fade" + bbcb_mg_img_ext;
		fade = 0;
	}
	PostWrite(ToAdd);
}

function BBCspoiler()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[spoiler]" + theSelection + "[/spoiler]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[spoiler]", "[/spoiler]");
		return;
	}
	if (Spoiler == 0)
	{
		ToAdd = "[spoiler]";
		document.forms[form_name].spoiler.src = bbcb_mg_img_path + "spoiler1.gif";
		Spoiler = 1;
	}
	else
	{
		ToAdd = "[/spoiler]";
		document.forms[form_name].spoiler.src = bbcb_mg_img_path + "spoiler.gif";
		Spoiler = 0;
	}
	PostWrite(ToAdd);
}

function BBCcell()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[cell class=spoiler]" + theSelection + "[/cell]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[cell class=spoiler]", "[/cell]");
		return;
	}
	if (Cell == 0)
	{
		ToAdd = "[cell class=spoiler]";
		document.forms[form_name].cell.src = bbcb_mg_img_path + "cell1.gif";
		Cell = 1;
	}
	else
	{
		ToAdd = "[/cell]";
		document.forms[form_name].cell.src = bbcb_mg_img_path + "cell.gif";
		Cell = 0;
	}
	PostWrite(ToAdd);
}

function BBCjustify()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[align=justify]" + theSelection + "[/align]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[align=justify]", "[/align]");
		return;
	}
	if (justify == 0)
	{
		ToAdd = "[align=justify]";
		document.forms[form_name].justify.src = bbcb_mg_img_path + "justify1" + bbcb_mg_img_ext;
		justify = 1;
	}
	else
	{
		ToAdd = "[/align]";
		document.forms[form_name].justify.src = bbcb_mg_img_path + "justify" + bbcb_mg_img_ext;
		justify = 0;
	}
	PostWrite(ToAdd);
}

function BBCleft()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[align=left]" + theSelection + "[/align]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[align=left]", "[/align]");
		return;
	}
	if (left == 0)
	{
		ToAdd = "[align=left]";
		document.forms[form_name].left.src = bbcb_mg_img_path + "left1" + bbcb_mg_img_ext;
		left = 1;
	}
	else
	{
		ToAdd = "[/align]";
		document.forms[form_name].left.src = bbcb_mg_img_path + "left" + bbcb_mg_img_ext;
		left = 0;
	}
	PostWrite(ToAdd);
}

function BBCright()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[align=right]" + theSelection + "[/align]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[align=right]", "[/align]");
		return;
	}
	if (right == 0)
	{
		ToAdd = "[align=right]";
		document.forms[form_name].right.src = bbcb_mg_img_path + "right1" + bbcb_mg_img_ext;
		right = 1;
	}
	else
	{
		ToAdd = "[/align]";
		document.forms[form_name].right.src = bbcb_mg_img_path + "right" + bbcb_mg_img_ext;
		right = 0;
	}
	PostWrite(ToAdd);
}

function BBCcenter()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[align=center]" + theSelection + "[/align]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[align=center]", "[/align]");
		return;
	}
	if (center == 0)
	{
		ToAdd = "[align=center]";
		document.forms[form_name].center.src = bbcb_mg_img_path + "center1" + bbcb_mg_img_ext;
		center = 1;
	}
	else
	{
		ToAdd = "[/align]";
		document.forms[form_name].center.src = bbcb_mg_img_path + "center" + bbcb_mg_img_ext;
		center = 0;
	}
	PostWrite(ToAdd);
}

function BBCft()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[font=\"" + document.forms[form_name].ft.value + "\"]" + theSelection + "[/font]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[font=\"" + document.forms[form_name].ft.value + "\"]", "[/font]");
		return;
	}
	ToAdd = "[font=\"" + document.forms[form_name].ft.value + "\"]" + " " + "[/font]";
	PostWrite(ToAdd);
}

function BBCfs()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[size=" + document.forms[form_name].fs.value+"]" + theSelection + "[/size]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[size=" + document.forms[form_name].fs.value+"]", "[/size]");
		return;
	}
	ToAdd = "[size=" + document.forms[form_name].fs.value+"]"+" " + "[/size]";
	PostWrite(ToAdd);
}

function BBCfc()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[color=" + document.forms[form_name].fc.value+"]" + theSelection + "[/color]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[color=" + document.forms[form_name].fc.value+"]", "[/color]");
		return;
	}
	ToAdd = "[color=" + document.forms[form_name].fc.value+"]"+" " + "[/color]";
	PostWrite(ToAdd);
}

function BBChl()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[highlight=#FFFFAA]" + theSelection + "[/highlight]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[highlight=#FFFFAA]", "[/highlight]");
		return;
	}
	ToAdd = "[highlight=#FFFFAA]"+" " + "[/highlight]";
	PostWrite(ToAdd);
}

function BBCphpbbmod()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = theSelection + "OPEN [b][/b]\nFIND\n[codeblock][/codeblock]\nREPLACE WITH\n[codeblock][/codeblock]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "", "OPEN [b][/b]\nFIND\n[codeblock][/codeblock]\nREPLACE WITH\n[codeblock][/codeblock]");
		return;
	}
	ToAdd = "OPEN [b][/b]\nFIND\n[codeblock][/codeblock]\nREPLACE WITH\n[codeblock][/codeblock]";
	PostWrite(ToAdd);
}

function BBCcode()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[code linenumbers=false]" + theSelection + "[/code]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[code linenumbers=false]", "[/code]");
		return;
	}
	if (Code == 0)
	{
		ToAdd = "[code linenumbers=false]";
		document.forms[form_name].code.src = bbcb_mg_img_path + "code1" + bbcb_mg_img_ext;
		Code = 1;
	}
	else
	{
		ToAdd = "[/code]";
		document.forms[form_name].code.src = bbcb_mg_img_path + "code" + bbcb_mg_img_ext;
		Code = 0;
	}
	PostWrite(ToAdd);
}

function BBCquote()
{
	var txtarea = document.forms[form_name].elements[text_name];

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text;
		if (theSelection != '')
		{
			document.selection.createRange().text = "[quote]" + theSelection + "[/quote]";
			document.forms[form_name].elements[text_name].focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsert(txtarea, "[quote]", "[/quote]");
		return;
	}
	if (Quote == 0)
	{
		ToAdd = "[quote]";
		document.forms[form_name].quote.src = bbcb_mg_img_path + "quote1" + bbcb_mg_img_ext;
		Quote = 1;
	}
	else
	{
		ToAdd = "[/quote]";
		document.forms[form_name].quote.src = bbcb_mg_img_path + "quote" + bbcb_mg_img_ext;
		Quote = 0;
	}
	PostWrite(ToAdd);
}

// Div Expand js
function selectAll(elementId)
{
	var element = document.getElementById(elementId);
	if ( document.selection )
	{
		var range = document.body.createTextRange();
		range.moveToElementText(element);
		range.select();
	}
	if ( window.getSelection )
	{
		var range = document.createRange();
		range.selectNodeContents(element);
		var blockSelection = window.getSelection();
		blockSelection.removeAllRanges();
		blockSelection.addRange(range);
	}
}

function resizeLayer(layerId, newHeight)
{
	var myLayer = document.getElementById(layerId);
	myLayer.style.height = newHeight + 'px';
}

function codeDivStart()
{
	var randomId = Math.floor(Math.random() * 2000);
	var imgSrc = 'images/bbcb_mg/images/';
	document.write('<div class="codetitle">Code:<img src="' + imgSrc + 'nav_expand.gif" width="14" height="10" title="' + s_view_more_code +'" onclick="resizeLayer(' + randomId + ', 200)" onmouseover="this.style.cursor = \'pointer\'" /><img src="' + imgSrc + 'nav_expand_more.gif" width="14" height="10" title="View Even More of this Code" onclick="resizeLayer(' + randomId + ', 500)" onmouseover="this.style.cursor = \'pointer\'" /><img src="' + imgSrc + 'nav_contract.gif" width="14" height="10" title="View Less of this Code" onclick="resizeLayer(' + randomId + ', 50)" onmouseover="this.style.cursor = \'pointer\'" /><img src="' + imgSrc + 'nav_select_all.gif" width="14" height="10" title="Select All of this Code" onclick="selectAll(' + randomId + ')" onmouseover="this.style.cursor = \'pointer\'" /></div><div class="codediv" id="' + randomId + '">');
}

