/**
* 
* @file $Id ajax_core.js
* @copyright (C) 2005
* @author alcaeus
* @email < mods@alcaeus.org >
*
**/

// This is the only value you should change
// It defines the time in milliseconds that the script waits before automatically submitting the fields for usernames (New PM and username search)
var KEYUP_TIMEOUT = 500;

var request = null;
var error_handler = '';

// Don't want to use const, var works in JS 1.0 as well :)
var AJAX_OP_COMPLETED = 0;
var AJAX_ERROR = 1;
var AJAX_CRITICAL_ERROR = 2;
var AJAX_POST_SUBJECT_EDITED = 3;
var AJAX_POST_TEXT_EDITED = 4;
var AJAX_POLL_RESULT = 5;
var AJAX_WATCH_TOPIC = 6;
var AJAX_LOCK_TOPIC = 7;
var AJAX_MARK_TOPIC = 8;
var AJAX_MARK_FORUM = 9;
var AJAX_PM_USERNAME_FOUND = 10;
var AJAX_PM_USERNAME_SELECT = 11;
var AJAX_PM_USERNAME_ERROR = 12;
var AJAX_PREVIEW = 13;
var AJAX_DELETE_POST = 14;
var AJAX_DELETE_TOPIC = 15;
var AJAX_TOPIC_TYPE = 16;
var AJAX_TOPIC_MOVE = 17;
var AJAX_POST_LIKE = 18;
var AJAX_POST_UNLIKE = 19;

var AJAX_DEBUG_RESULTS = 0;
var AJAX_DEBUG_REQUEST_ERRORS = 0;
var AJAX_DEBUG_HTML_ERRORS = 0;

// Determine whether AJAX is available
if (window.XMLHttpRequest)
{
	var tempvar = new XMLHttpRequest();
	ajax_core_defined = (tempvar == null) ? 0 : 1;
	delete(tempvar);
}
//Use the IE/Windows ActiveX version
else if (window.ActiveXObject)
{
	var tempvar= new ActiveXObject("Microsoft.XMLHTTP");
	ajax_core_defined = (tempvar == null) ? 0 : 1;
	delete(tempvar);
}
else
{
	ajax_core_defined = 0;
}

// General function. This one is the mother of all AJAX functions ;)
function loadXMLDoc(url, params, submitmethod, changehandler)
{
	if ((submitmethod != 'GET') && (submitmethod != 'POST'))
	{
		submitmethod = 'GET';
	}

	//Use the native object available in all browsers (IE >= 7)
	if (window.XMLHttpRequest)
	{
		request = new XMLHttpRequest();
		var is_activex = false;
	}
	//Use the ActiveX version for IE < 7
	else if (window.ActiveXObject)
	{
		request = new ActiveXObject("Microsoft.XMLHTTP");
		var is_activex = true;
	}

	if (!request)
	{
		return false;
	}

	eval("request.onreadystatechange = " + changehandler);
	if (submitmethod == 'POST')
	{
		request.open(submitmethod, url, true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=' + ajax_page_charset);
		request.send(params);
	}
	else
	{
		request.open(submitmethod, url + '?' + params, true);
		request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=' + ajax_page_charset);
		if (is_activex)
		{
			// This seems to be an issue in the ActiveX-Object: no parameter needed
			request.send();
		}
		else
		{
			// The native versions take null as a parameter
			request.send(null);
		}
	}

	return true;
}

function getTagValues(tagname, haystack)
{
	var tag_array = haystack.getElementsByTagName(tagname);
	var result_array = Array();
	for (i = 0; i < tag_array.length; i++)
	{
		result_array[i] = (tag_array[i].firstChild && tag_array[i].firstChild.data) ? tag_array[i].firstChild.data : '';
	}
	return result_array;
}

function getFirstTagValue(tagname, haystack)
{
	var tag_array = haystack.getElementsByTagName(tagname);
	if ((tag_array.length > 0) && (tag_array[0].firstChild))
	{
		return (tag_array[0].firstChild.data) ? tag_array[0].firstChild.data : '';
	}
	return '';
}

// This function is used to parse any standard error file
function error_req_change()
{
	//Check if the request is completed, if not, just skip over
	if (request.readyState == 4)
	{
		var result_code = AJAX_OP_COMPLETED;
		var error_msg = '';
		//If the request wasn't successful, we just hide any information we have.
		if (request.status == 200)
		{
			var response = request.responseXML.documentElement;
			if (AJAX_DEBUG_RESULTS)
			{
				alert(request.responseText);
			}
			//Don't react if no valid response was received
			if (response != null)
			{
				result_code = getFirstTagValue('result', response);
				error_msg = getFirstTagValue('error_msg', response);
			}
		}

		eval(error_handler+"(result_code, error_msg);");
		delete request;
	}
}

// Just like sprintf() in php, replacements can be any type
function sprintf(text, replacements)
{
	var i = 0;
	//This prevents us from having to create an array for replacements with one value checking for type 'object' may not be really smart, but who cares ;)
	if ((typeof replacements) != 'object')
	{
		var repl = Array(1);
		repl[0] = replacements;
	}
	else
	{
		var repl = replacements;
	}

	while (((charindex = text.indexOf('%s')) >= 0) && (i < repl.length))
	{
		var temptext = text.substr(0, charindex);
		text = temptext + repl[i] + text.substr(charindex+2, text.length);
		i++;
	}

	return text;
}

function getElementById(ElementId)
{
	if (document.documentElement)
	{
		return document.getElementById(ElementId);
	}
	else
	{
		return document.all[ElementId];
	}
}

function rtrim(text)
{
	if (text == '')
	{
		return '';
	}

	var part = '';
	var i = text.length;
	do
	{
		part = text.substring(i-1, i);
		i--;
	} while ((part == ' ') || (part == '\n') || (part == '\r'));
	text = text.substring(0, i+1);

	return text;
}

function ltrim(text)
{
	if (text == '')
	{
		return '';
	}

	var part = '';
	var i = 0;
	do
	{
		part = text.substring(i, i+1);
		i++;
	} while ((part == ' ') || (part == '\n') || (part == '\r'));
	text = text.substring(i-1, text.length);

	return text;
}

function trim(text)
{
	return ltrim(rtrim(text));
}

function setClickEventHandler(obj, handler)
{
	if (obj.onclick)
	{
		eval('obj.onclick = function() { '+handler+' }');
	}
	else
	{
		obj.setAttribute('onclick', handler, 'false');
	}
}

function setInnerText(obj, newtext)
{
	if (newtext == '')
	{
		newtext = '&nbsp;';
	}

	if (obj.innerText)
	{
		obj.innerText = newtext;
	}
	else if (obj.firstChild)
	{
		obj.firstChild.nodeValue = newtext;
	}
	else
	{
		obj.innerHTML = newtext;
	}
}

// Separate escaping function to fix bug with + and % signs in QuickEdit and QuickPreview
function ajax_escape(text)
{
	text = escape(text).replace(/(\%)/g, "%25");
	return text.replace(/(\+)/g, "%2b");
}

// This function is a workaround for long posts being truncated in PITA browsers
function parseResult(response)
{
	var res = response.match(/\<response\>((.|\s)+?)\<\/response\>/gm);
	var fields = new Array();
	if (res != null)
	{
		contents = RegExp.$1;
		res = contents.match(/\<.+?\>((.|\s)+?)\<\/.+?\>/gm);
		if (res == null)
		{
			return fields;
		}

		for (var i = 0; i < res.length; i++)
		{
			var field = new Array();
			res[i].match(/^\<(.+?)\>/g);
			field[0] = RegExp.$1;
			res[i].match(/\<.+?\>((.|\s)+)\<\/.+?\>/gm);
			field[1] = unhtmlspecialchars(RegExp.$1);

			fields[i] = field;
		}
	}

	return fields;
}

function unhtmlspecialchars(text)
{
	text = text.replace(/%u28/g, '(');
	text = text.replace(/%u29/g, ')');
	text = text.replace(/&quot;/g, '"');
	text = text.replace(/&lt;/g, '<');
	text = text.replace(/&gt;/g, '>');
	text = text.replace(/&amp;/g, '&');
	text = text.replace(/%u5b/g, '[');
	text = text.replace(/%u5d/g, ']');
	text = text.replace(/%u7b/g, '{');
	text = text.replace(/%u7d/g, '}');

	return text;
}

function utf8_decode(text)
{
	while (res = text.match(/&#(\d{1,4});/))
	{
		num = res[0];
		pos = text.indexOf(num);
		if (pos == -1)
		{
			return text;
		}

		text = text.substring(0, pos) + unescape('%u' + parseInt(num.substring(2, num.length-1)).toString(16)) + text.substring(pos+num.length, text.length);
	}

	return text;
}

function writediv(dest_div, dest_string)
{
	document.getElementById(dest_div).innerHTML = dest_string;
}

function file_request(file_requested)
{
	if(window.XMLHttpRequest) // FIREFOX
	{
		xhr_object = new XMLHttpRequest();
	}
	else if(window.ActiveXObject) // IE
	{
		xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else
	{
		return(false);
	}
	xhr_object.open("GET", file_requested, false);
	xhr_object.send(null);
	if(xhr_object.readyState == 4)
	{
		return(xhr_object.responseText);
	}
	else
	{
		return(false);
	}
}
