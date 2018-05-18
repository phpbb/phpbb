// Show / Hide - BEGIN
var expDays = 90;
var exp = new Date();
var tmp = '';
var tmp_counter = 0;
var tmp_open = 0;

exp.setTime(exp.getTime() + (expDays*24*60*60*1000));

function SetCookie(name, value)
{
	var argv = SetCookie.arguments;
	var argc = SetCookie.arguments.length;
	var expires = (argc > 2) ? argv[2] : null;
	var path = (argc > 3) ? argv[3] : null;
	var domain = (argc > 4) ? argv[4] : null;
	var secure = (argc > 5) ? argv[5] : false;
	document.cookie = name + "=" + escape(value) +
		((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
		((path == null) ? "" : ("; path=" + path)) +
		((domain == null) ? "" : ("; domain=" + domain)) +
		((secure == true) ? "; secure" : "");
}

function getCookieVal(offset)
{
	var endstr = document.cookie.indexOf(";",offset);
	if (endstr == -1)
	{
		endstr = document.cookie.length;
	}
	return unescape(document.cookie.substring(offset, endstr));
}

function GetCookie(name)
{
	var arg = name + "=";
	var alen = arg.length;
	var clen = document.cookie.length;
	var i = 0;
	while (i < clen)
	{
		var j = i + alen;
		if (document.cookie.substring(i, j) == arg)
		{
			return getCookieVal(j);
		}
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0)
		{
			break;
		}
	}
	return null;
}

function ShowHide(id1, id2, id3)
{
	var res = expMenu(id1);
	if (id2 != '')
	{
		expMenu(id2);
	}
	if (id3 != '')
	{
		SetCookie(id3, res, exp);
	}
}

function expMenu(id)
{
	var itm = null;
	if (document.getElementById)
	{
		itm = document.getElementById(id);
	}
	else if (document.all)
	{
		itm = document.all[id];
	}
	else if (document.layers)
	{
		itm = document.layers[id];
	}
	if (!itm)
	{
		// do nothing
	}
	else if (itm.style)
	{
		if (itm.style.display == "none")
		{
			itm.style.display = "";
			return 1;
		}
		else
		{
			itm.style.display = "none";
			return 2;
		}
	}
	else
	{
		itm.visibility = "show";
		return 1;
	}
}
// Show / Hide - END

// Set Width - BEGIN
function setWidth(elementID, tmpWidth)
{
	tmpWidth = (tmpWidth != 'auto') ? tmpWidth + 'px' : tmpWidth;
	$('#' + elementID).css("width", tmpWidth);
	/*
	cellobject = document.getElementById(elementID);
	if (cellobject)
	{
		if ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1))
		{
			cellobject.style.width = tmpWidth;
		}
		else
		{
			cellobject.width = tmpWidth;
		}
	}
	*/
}
// Set Width - END

// Select Text - BEGIN
function IsIEMac()
{
	// Any better way to detect IEMac?
	var ua = String(navigator.userAgent).toLowerCase();
	if(document.all && ua.indexOf("mac") >= 0)
	{
		return true;
	}
	return false;
}

function select_text(obj)
{
	var o = document.getElementById(obj)
	if( !o ) return;
	var r, s;
	if(document.selection && !IsIEMac())
	{
		// Works on: IE5+
		// To be confirmed: IE4? / IEMac fails?
		r = document.body.createTextRange();
		r.moveToElementText(o);
		r.select();
	}
	else if(document.createRange && (document.getSelection || window.getSelection))
	{
		// Works on: Netscape/Mozilla/Konqueror/Safari
		// To be confirmed: Konqueror/Safari use window.getSelection ?
		r = document.createRange();
		r.selectNodeContents(o);
		s = window.getSelection ? window.getSelection() : document.getSelection();
		s.removeAllRanges();
		s.addRange(r);
	}
}
// Select Text - END

// Images, Links, Popup, FORM - BEGIN
function SetLangTheme()
{
	document.ChangeThemeLang.submit();
	return true;
}

function img_popup(image_url, image_width, image_height, popup_rand)
{
	screenwidth = false;
	screenwidth = screen.Width;
	if ( !screenwidth )
	{
		screenwidth = window.outerWidth;
	}

	screenheight = false;
	screenheight = screen.Height;
	if ( !screenheight )
	{
		screenheight = window.outerHeight;
	}

	if ( screenwidth < ( image_width + 30 ) || screenheight < ( image_height + 30 ) || image_width == null || image_height == null )
	{
		window.open(image_url,'limit_image_mod_popup_img_' + popup_rand,'resizable=yes,top=0,left=0,screenX=0,screenY=0,scrollbars=yes',false);
	}
	else
	{
		window.open(image_url,'limit_image_mod_popup_img_' + popup_rand,'resizable=yes,top=0,left=0,screenX=0,screenY=0,height=' + ( image_height + 30 ) + ',width=' + ( image_width + 30 ), false);
	}
}

function popup_open(mypage, myname, w, h, scroll)
{
	LeftPosition = (screen.width) ? (screen.width - w) / 2 : 0;
	TopPosition = (screen.height) ? (screen.height - h) / 2 : 0;
	settings = 'height=' + h + ',width=' + w + ',top=' + TopPosition + ',left=' + LeftPosition + ',scrollbars=' + scroll + ',resizable';
	win = window.open(mypage, myname, settings);
}

function rss_news_help()
{
	window.open("rss_news_help.php?", '_rss_news_help', 'height=600,width=700,resizable=no,scrollbars=yes');
}

function links_me()
{
	window.open("links_popup.php", '_links_me', 'height=220, width=500,resizable=no, scrollbars=no');
}
// Images, Links, Popup, FORM - END

// Quick Quote - BEGIN
function showQuickEditor()
{
	document.getElementById('quick_reply').style.display = "";
	document.post.message.focus();
	return;
}

function addquote(post_id, tag, quickr, parentf)
{
	if (quickr)
	{
		document.getElementById('quick_reply').style.display = "";
	}
	str_find = new Array("&q_mg;", "&lt_mg;", "&gt_mg;");
	str_replace = new Array("\\\"", "<", ">");
	for(var i = 0; i < message[post_id].length; i++)
	{
		for (var j = 0; j < str_find.length; j++)
		{
			if (message[post_id].search(str_find[j]) != -1)
			{
				message[post_id] = message[post_id].replace(str_find[j],str_replace[j]);
			}
		}
	}
	if (parentf == true)
	{
		target_textbox = window.parent.document.post.message;
	}
	else
	{
		target_textbox = document.post.message;
	}
	target_textbox.value += "[" + tag + message[post_id] + tag + "]";
	target_textbox.focus();
	return;
}

function quotename(username)
{
	document.getElementById('quick_reply').style.display = "";
	document.post.message.value += username;
	document.post.message.focus();
	return;
}

function open_postreview(ref)
{
	height = screen.height / 2.23;
	width = screen.width / 2;
	window.open(ref,'_ippostreview','height=' + height + ',width=' + width + ',resizable=yes,scrollbars=yes');
	return;
}
// Quick Quote - END

// phpBB3 Functions - BEGIN

/**
* Window popup
*/
function popup(url, width, height, name)
{
	if (!name)
	{
		name = '_popup';
	}

	window.open(url.replace(/&amp;/g, '&'), name, 'height=' + height + ',resizable=yes,scrollbars=yes, width=' + width);
	return false;
}

/**
* Jump to page
*/
function jumpto()
{
	var page = prompt(jump_page, on_page);

	if (page !== null && !isNaN(page) && page > 0)
	{
		document.location.href = base_url.replace(/&amp;/g, '&') + '&start=' + ((page - 1) * per_page);
	}
}

/**
* Mark/unmark checklist
* id = ID of parent container, name = name prefix, state = state [true/false]
*/
function marklist(id, name, state)
{
	var parent = document.getElementById(id);
	if (!parent)
	{
		eval('parent = document.' + id);
	}

	if (!parent)
	{
		return;
	}

	var rb = parent.getElementsByTagName('input');

	for (var r = 0; r < rb.length; r++)
	{
		if (rb[r].name.substr(0, name.length) == name)
		{
			rb[r].checked = state;
		}
	}
}

/**
* Resize viewable area for attached image or topic review panel (possibly others to come)
* e = element
*/
function viewableArea(e, itself)
{
	if (!e) return;
	if (!itself)
	{
		e = e.parentNode;
	}

	if (!e.vaHeight)
	{
		// Store viewable area height before changing style to auto
		e.vaHeight = e.offsetHeight;
		e.vaMaxHeight = e.style.maxHeight;
		e.style.height = 'auto';
		e.style.maxHeight = 'none';
		e.style.overflow = 'visible';
	}
	else
	{
		// Restore viewable area height to the default
		e.style.height = e.vaHeight + 'px';
		e.style.overflow = 'auto';
		e.style.maxHeight = e.vaMaxHeight;
		e.vaHeight = false;
	}
}

/**
* Set display of page element
* s[-1,0,1] = hide,toggle display,show
*/
function dE(n, s)
{
	var e = document.getElementById(n);

	if (!s)
	{
		s = (e.style.display == '' || e.style.display == 'block') ? -1 : 1;
	}
	e.style.display = (s == 1) ? 'block' : 'none';
}

/**
* Alternate display of subPanels
*/
function subPanels(p)
{
	var i, e, t;

	if (typeof(p) == 'string')
	{
		show_panel = p;
	}

	for (i = 0; i < panels.length; i++)
	{
		e = document.getElementById(panels[i]);
		t = document.getElementById(panels[i] + '-tab');

		if (e)
		{
			if (panels[i] == show_panel)
			{
				e.style.display = 'block';
				if (t)
				{
					t.className = 'activetab';
				}
			}
			else
			{
				e.style.display = 'none';
				if (t)
				{
					t.className = '';
				}
			}
		}
	}
}

/**
* Call print preview
*/
function printPage()
{
	if (is_ie)
	{
		printPreview();
	}
	else
	{
		window.print();
	}
}

/**
* Show/hide groups of blocks
* c = CSS style name
* e = checkbox element
* t = toggle dispay state (used to show 'grip-show' image in the profile block when hiding the profiles)
*/
function displayBlocks(c, e, t)
{
	var s = (e.checked == true) ?  1 : -1;

	if (t)
	{
		s *= -1;
	}

	var divs = document.getElementsByTagName("DIV");

	for (var d = 0; d < divs.length; d++)
	{
		if (divs[d].className.indexOf(c) == 0)
		{
			divs[d].style.display = (s == 1) ? 'none' : 'block';
		}
	}
}

function selectCode(a)
{
	// Get ID of code block
	var e = a.parentNode.parentNode.getElementsByTagName('CODE')[0];

	// Not IE
	if (window.getSelection)
	{
		var s = window.getSelection();
		// Safari
		if (s.setBaseAndExtent)
		{
			s.setBaseAndExtent(e, 0, e, e.innerText.length - 1);
		}
		// Firefox and Opera
		else
		{
			var r = document.createRange();
			r.selectNodeContents(e);
			s.removeAllRanges();
			s.addRange(r);
		}
	}
	// Some older browsers
	else if (document.getSelection)
	{
		var s = document.getSelection();
		var r = document.createRange();
		r.selectNodeContents(e);
		s.removeAllRanges();
		s.addRange(r);
	}
	// IE
	else if (document.selection)
	{
		var r = document.body.createTextRange();
		r.moveToElementText(e);
		r.select();
	}
}

/**
* Play quicktime file by determining it's width/height
* from the displayed rectangle area
*/
function play_qt_file(obj)
{
	var rectangle = obj.GetRectangle();

	if (rectangle)
	{
		rectangle = rectangle.split(',');
		var x1 = parseInt(rectangle[0]);
		var x2 = parseInt(rectangle[2]);
		var y1 = parseInt(rectangle[1]);
		var y2 = parseInt(rectangle[3]);

		var width = (x1 < 0) ? (x1 * -1) + x2 : x2 - x1;
		var height = (y1 < 0) ? (y1 * -1) + y2 : y2 - y1;
	}
	else
	{
		var width = 200;
		var height = 0;
	}

	obj.width = width;
	obj.height = height + 16;

	obj.SetControllerVisible(true);
	obj.Play();
}

// phpBB3 Functions - END

// Dynamic Rating - BEGIN
function set_rate(rate, max)
{
	//var rt = document.getElementById('rate_tag');
	//document.getElementById('rate_tag').innerHTML = '<span class=\"rate' + rate + '\">' + rate + '<\/span>';
	//document.getElementById('rate_img').className = ('icorate' + rate);
	//document.getElementById('rate_value').value = rate;
	document.getElementById('rating').selectedIndex = (rate - 1);
	if (rate == 0)
	{
		for (i = 1; i <= max; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-off';
		}
	}
	else
	{
		for (i = 1; i <= rate; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-on';
		}
		for (i = (rate + 1); i <= max; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-off';
		}
	}
}

function hon_rate(rate, max)
{
	document.getElementById('rating').selectedIndex = rate;
	if (rate == 0)
	{
		for (i = 1; i <= max; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-off';
		}
	}
	else
	{
		for (i = 1; i <= rate; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-on';
		}
		for (i = (rate + 1); i <= max; i++)
		{
			document.getElementById('rate' + i).className = 'img-rate-off';
		}
	}
}

function submit_rate()
{
	//document.ratingform.submit();
	document.forms['ratingform'].submit();

	return true;
}
// Dynamic Rating - END

// Select/Unselect all checkboxes
function setCheckboxes(theForm, elementName, isChecked)
{
	var chkboxes = document.forms[theForm].elements[elementName];
	var count = chkboxes.length;

	if (count)
	{
		for (var i = 0; i < count; i++)
		{
			chkboxes[i].checked = isChecked;
		}
	}
	else
	{
		chkboxes.checked = isChecked;
	}
	return true;
}

// TRIGGER CLICKS ON ROWS
$(document).ready(function() {
	$("td[data-href]").bind('mouseup', function(e){
		if (e.which == 1) {
		window.location.href = $(this).attr('data-href');
		}
	});
	$("div[data-href]").bind('mouseup', function(e){
		if (e.which == 1) {
		window.location.href = $(this).attr('data-href');
		}
	});
});

