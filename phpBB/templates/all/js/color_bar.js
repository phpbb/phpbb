function bbcb_cb_vars_reassign_start()
{
	form_name = ((typeof(form_name_thisform) != "undefined") && (form_name_thisform != null) && (form_name_thisform != '')) ? form_name_thisform : form_name;
	text_name = ((typeof(text_name_thisform) != "undefined") && (text_name_thisform != null) && (text_name_thisform != '')) ? text_name_thisform : text_name;
}

function bbcb_cb_vars_reassign_end()
{
	form_name = ((typeof(form_name_original) != "undefined") && (form_name_original != null) && (form_name_original != '')) ? form_name_original : form_name;
	text_name = ((typeof(text_name_original) != "undefined") && (text_name_original != null) && (text_name_original != '')) ? text_name_original : text_name;
}

function InsertTag(MyString)
{
	var bbopen = '[color=' + MyString + ']';
	var bbclose = '[/color]';

	var txtarea = document.forms[form_name].elements[text_name];
	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection)
		{
			// Add tags around selection
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			txtarea.focus();
			theSelection = '';
			return;
		}
		else
		{
			// Add tags at the end
			txtarea.value += bbopen + bbclose;
			txtarea.focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsertCB(txtarea, bbopen, bbclose);
		return;
	}
	else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
	{
		mozInsertCB(txtarea, bbopen + bbclose, "");
		return;
	}
	else
	{
		txtarea.value += text;
		txtarea.focus();
	}
}

function InsertTagExt(MyString)
{
	var bbopen = '[color=' + MyString + ']';
	var bbclose = '[/color]';

	var txtarea = opener.document.forms[form_name].elements[text_name];
	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection)
		{
			// Add tags around selection
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			txtarea.focus();
			theSelection = '';
			return;
		}
		else
		{
			// Add tags at the end
			txtarea.value += bbopen + bbclose;
			txtarea.focus();
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozInsertCB(txtarea, bbopen, bbclose);
		return;
	}
	else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
	{
		mozInsertCB(txtarea, bbopen + bbclose, "");
		return;
	}
	else
	{
		txtarea.value += text;
		txtarea.focus();
	}
}

function mozInsertCB(txtarea, openTag, closeTag)
{
	if (txtarea.selectionEnd > txtarea.value.length)
	{
		txtarea.selectionEnd = txtarea.value.length;
	}

	var startPos = txtarea.selectionStart;
	var endPos = txtarea.selectionEnd+openTag.length;

	txtarea.value = txtarea.value.slice(0, startPos) + openTag + txtarea.value.slice(startPos);
	txtarea.value = txtarea.value.slice(0, endPos) + closeTag + txtarea.value.slice(endPos);

	txtarea.selectionStart = startPos + openTag.length;
	txtarea.selectionEnd = endPos;
	txtarea.focus();
}

var base_hexa = "0123456789ABCDEF";

function dec2Hexa(number)
{
	return base_hexa.charAt(Math.floor(number / 16)) + base_hexa.charAt(number % 16);
}

function RGB2Hexa(TR, TG, TB)
{
	return "#" + dec2Hexa(TR) + dec2Hexa(TG) + dec2Hexa(TB);
}

function lightCase(MyObject)
{
	document.getElementById('ColorUsed').bgColor = MyObject.bgColor;
}

function lightCase1(MyObject)
{
	document.getElementById('ColorUsed1').bgColor = MyObject.bgColor;
}

col = new Array;
col[0] = new Array(255, 0, 255, 0, 255, -1);
col[1] = new Array(255, -1, 255, 0, 0, 0);
col[2] = new Array(0, 0, 255, 0, 0, 1);
col[3] = new Array(0, 0, 255, -1, 255, 0);
col[4] = new Array(0, 1, 0, 0, 255, 0);
col[5] = new Array(255, 0, 0, 0, 255, -1);
col[6] = new Array(255, -1, 0, 0, 0, 0);

function rgb(pas, w, h, text1, text2, spacer_path)
{
	document.write('<table id=\"ColorPanel\" width=\"100%\" align=\"left\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n<tr>\n<td id=\"ColorUsed\" align=\"center\" width=\"10\" onmouseover=\"helpline(\'s\'); this.style.cursor=\'pointer\';\" onclick=\"bbcb_cb_vars_reassign_start(); if(this.bgColor.length > 0) InsertTag(this.bgColor); bbcb_cb_vars_reassign_end();\"><img src=\"' + spacer_path + 'images\/spacer.gif\" width=\"10\" height=\"' + h + '\" alt=\"\" border=\"1\" \/><\/td>\n<td width=\"5\"><img src=\"' + spacer_path + 'images\/spacer.gif\" width=\"' + w + '\" height=\"' + h + '\" alt=\"\" border=\"0\" \/><\/td>\n<td id=\"ColorUsed1\" align=\"center\" width=\"10\" onmouseover=\"helpline(\'s\'); this.style.cursor=\'pointer\';\" onclick=\"bbcb_cb_vars_reassign_start(); if(this.bgColor.length > 0) InsertTag(this.bgColor); bbcb_cb_vars_reassign_end();\"><img src=\"' + spacer_path + 'images\/spacer.gif\" width=\"10\" height=\"' + h + '\" alt=\"\" border=\"1\" \/><\/td>\n<td width=\"5\"><img src=\"' + spacer_path + 'images\/spacer.gif\" width=' + w + ' height=' + h + ' alt=\"\" border=\"0\" \/><\/td>\n');

	for (j = 0; j < (6 + 1); j++)
	{
		for (i = 0; i < (pas + 1); i++)
		{
			r = Math.floor(col[j][0] + col[j][1] * i * (255) / pas);
			g = Math.floor(col[j][2] + col[j][3] * i * (255) / pas);
			b = Math.floor(col[j][4] + col[j][5] * i * (255) / pas);
			codehex = r + '' + g + '' + b;
			document.write('<td bgColor=\"' + RGB2Hexa(r, g, b) + '\" onclick=\"bbcb_cb_vars_reassign_start(); InsertTag(this.bgColor); bbcb_cb_vars_reassign_end(); lightCase(this);\" onmouseover=\"lightCase1(this); this.style.cursor=\'pointer\';\" title=\"' + RGB2Hexa(r, g, b) + '\" width=\"' + w + '\" height=\"' + h + '\"><img src=\"' + spacer_path + 'images/spacer.gif\" width=\"' + w + '\" height=\"' + h + '\" alt=\"\" border=\"0\" \/><\/td>\n');
		}
	}

	document.write('<\/tr>\n<\/table>\n');
}

function search(text, caract)
{
	for(i = 0; i < text.length; i++)
	{
		if (caract == text.substring(i, i + 1))
		return i + 1;
	}
}