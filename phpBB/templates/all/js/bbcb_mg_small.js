function bbcb_ui_vars_reassign_start()
{
	form_name = ((typeof(form_name_thisform) != "undefined") && (form_name_thisform != null) && (form_name_thisform != '')) ? form_name_thisform : form_name;
	text_name = ((typeof(text_name_thisform) != "undefined") && (text_name_thisform != null) && (text_name_thisform != '')) ? text_name_thisform : text_name;
}

function bbcb_ui_vars_reassign_end()
{
	form_name = ((typeof(form_name_original) != "undefined") && (form_name_original != null) && (form_name_original != '')) ? form_name_original : form_name;
	text_name = ((typeof(text_name_original) != "undefined") && (text_name_original != null) && (text_name_original != '')) ? text_name_original : text_name;
}

function emoticon_sc(text)
{
	var txtarea = opener.document.forms[form_name].elements[text_name];
	text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos)
	{
		if (opener.baseHeight != txtarea.caretPos.boundingHeight)
		{
			txtarea.focus();
			opener.storeCaret(txtarea);
		}
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		txtarea.focus();
	}
	else if ((txtarea.selectionEnd | txtarea.selectionEnd == 0) && (txtarea.selectionStart | txtarea.selectionStart == 0))
	{
		mozInsert_sc(txtarea, text, "");
	}
	else
	{
		txtarea.value += text;
		txtarea.focus();
	}
}

function mozInsert_sc(txtarea, openTag, closeTag)
{
	if (txtarea.selectionEnd > txtarea.value.length)
	{
		txtarea.selectionEnd = txtarea.value.length;
	}

	var startPos = txtarea.selectionStart;
	var endPos = txtarea.selectionEnd + openTag.length;

	txtarea.value = txtarea.value.slice(0, startPos) + openTag + txtarea.value.slice(startPos);
	txtarea.value = txtarea.value.slice(0, endPos) + closeTag + txtarea.value.slice(endPos);

	txtarea.selectionStart = startPos + openTag.length;
	txtarea.selectionEnd = endPos;
	txtarea.focus();
}
