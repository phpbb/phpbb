/**
*
* @file ajax_postfunctions.js
* @copyright (C) 2005
* @author alcaeus
* @email < mods@alcaeus.org >
*
**/

// Inline search
function AJAXSearch(keywords)
{
	if (!ajax_core_defined)
	{
		return;
	}

	//Send search results
	if (keywords != '')
	{
		ShowEmptyTitle(false);
		var url = CMS_PAGE_SEARCH;
		var params = 'show_results=topics&is_ajax=1&search_fields=all&search_keywords=' + ajax_escape(keywords);
		if (S_SID != '')
		{
			params += '&sid=' + S_SID;
		}
		if (!loadXMLDoc(url, params, 'GET', 'search_req_change'))
		{
			ShowEmptyTitle(false);
			AJAXFinishSearch('', 0, 0);
		}
	}
	else
	{
		ShowEmptyTitle(true);
		AJAXFinishSearch('', 0, 0);
	}
}

function search_req_change()
{
	if (!ajax_core_defined)
	{
		return;
	}

	//Check if the request is completed, if not, just skip over
	if (request.readyState == 4)
	{
		var keywords = '';
		var search_id = 0;
		var results = 0;
		//If the request wasn't successful, we just hide any information we have.
		if (request.status == 200)
		{
			if (response_obj = request.responseXML)
			{
				//Now parse whatever we got
				response = request.responseXML.documentElement;
				if (AJAX_DEBUG_RESULTS)
				{
					alert(request.responseText);
				}
				//Don't react if no valid response was received
				if (response != null)
				{
					keywords = getFirstTagValue('keywords', response);
					if (search_id = getFirstTagValue('search_id', response))
					{
						results = getFirstTagValue('results', response);
					}
				}
			}
		}
		AJAXFinishSearch(keywords, search_id, results);
	}
}

function AJAXFinishSearch(keywords, search_id, results)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var restable = getElementById('searchresults_tbl');
	var reslink = getElementById('searchresults_lnk');

	if ((restable == null) || (reslink == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXFinishSearch: some HTML elements could not be found');
		}
		return;
	}

	if (results == 0)
	{
		setInnerText(reslink, L_AJAX_NO_RESULTS);
		reslink.setAttribute('href', '', 'false');
		restable.style.display = 'none';
	}
	else if (results == 1)
	{
		setInnerText(reslink, L_RESULT);
		reslink.setAttribute('href', CMS_PAGE_VIEWTOPIC + '?' + POST_TOPIC_URL + '=' + search_id + '&highlight=' + keywords, 'false');
		restable.style.display = '';
	}
	else
	{
		setInnerText(reslink, sprintf(L_RESULTS, results));
		reslink.setAttribute('href', CMS_PAGE_SEARCH + '?search_id=' + search_id, 'false');
		restable.style.display = '';
	}
}

function SubjectCheck(subject)
{
	ShowEmptyTitle(subject == '');
}

function ShowEmptyTitle(show)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var subject_tbl = getElementById('subject_error_tbl');

	if (subject_tbl == null)
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXShowEmptyTitle: some HTML elements could not be found');
		}
		return;
	}

	subject_tbl.style.display = (show) ? '' : 'none';
}

// Username checking (post topic/post only)
function AJAXCheckPostUsername(username)
{
	if (!ajax_core_defined)
	{
		return;
	}

	if (username != '')
	{
		error_handler = 'AJAXFinishCheckPostUsername';
		var url = 'ajax.' + php_ext;
		var params = 'mode=checkusername_post&username=' + ajax_escape(username);
		if (S_SID != '')
		{
			params += '&sid='+S_SID;
		}
		if (!loadXMLDoc(url, params, 'GET', 'error_req_change'))
		{
			AJAXFinishCheckPostUsername(AJAX_OP_COMPLETED, '');
		}
	}
	else
	{
		AJAXFinishCheckPostUsername(AJAX_OP_COMPLETED, '');
	}
}

function AJAXFinishCheckPostUsername(result_code, error_msg)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var username_tbl = getElementById('post_username_error_tbl');
	var username_text = getElementById('post_username_error_text');

	if ((username_tbl == null) || (username_text == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXFinishCheckPostUsername: some HTML elements could not be found');
		}
		return;
	}

	username_tbl.style.display = (result_code != AJAX_OP_COMPLETED) ? '' : 'none';
	setInnerText(username_text, error_msg);
}

// Username checking (post topic/post only)
var timer_id = 0;
var last_username = '';

function AJAXCheckPMUsername(username)
{
	if (!ajax_core_defined)
	{
		return;
	}

	if (timer_id > 0)
	{
		clearTimeout(timer_id);
		timer_id = 0;
	}

	timer_id = setTimeout('AJAXSubmitCheckPMUsername(\'' + username + '\')', KEYUP_TIMEOUT);
}

function AJAXSubmitCheckPMUsername(username)
{
	if (!ajax_core_defined || (last_username == username))
	{
		return;
	}

	last_username = username;
	timer_id = 0;

	error_handler = 'AJAXFinishCheckPMUsername';
	var url = 'ajax.' + php_ext;
	var params = 'mode=checkusername_pm&username=' + ajax_escape(username);
	if (S_SID != '')
	{
		params += '&sid=' + S_SID;
	}
	if (!loadXMLDoc(url, params, 'GET', 'error_req_change'))
	{
		AJAXFinishCheckPMUsername(AJAX_OP_COMPLETED, '');
	}
}

function AJAXFinishCheckPMUsername(result_code, error_msg)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var username_tbl = getElementById('pm_username_error_tbl');
	var username_text = getElementById('pm_username_error_text');
	var username_select = getElementById('pm_username_select');

	if ((username_tbl == null) || (username_text == null) || (username_select == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXFinishCheckPMUsername: some HTML elements could not be found');
		}
		return;
	}

	var display = 'none';
	var displaytext = '';
	if (result_code == AJAX_PM_USERNAME_SELECT)
	{
		username_select.innerHTML = error_msg;
		username_select.style.display = '';

		display = '';
		displaytext = L_MORE_MATCHES;
	}
	else
	{
		if ((result_code == AJAX_PM_USERNAME_ERROR) && (error_msg != ''))
		{
			display = '';
			displaytext = error_msg;
		}
		else
		{
			display = 'none';
			displaytext = '';
		}

		username_select.style.display = 'none';
	}
	username_tbl.style.display = display;
	setInnerText(username_text, displaytext);
}

function AJAXSelectPMUsername(selectfield)
{
	if ((!ajax_core_defined) || (selectfield.value == '-1'))
	{
		return;
	}

	var username_tbl = getElementById('pm_username_error_tbl');
	var username_text = getElementById('pm_username_error_text');
	var username_select = getElementById('pm_username_select');

	if ((username_tbl == null) || (username_text == null) || (username_select == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXSelectPMUsername: some HTML elements could not be found');
		}
		return;
	}

	document.forms['post'].username.value = selectfield.value;

	username_select.innerHTML = '';
	username_select.style.display = 'none';
	username_tbl.style.display = 'none';
	setInnerText(username_text, '');
}

// Instant post preview
function AJAXPreview(mode, post_id)
{
	if (!ajax_core_defined)
	{
		return true;
	}

	if (!checkForm())
	{
		return false;
	}

	var url = 'ajax.' + php_ext;
	var params = (mode == 0) ? 'mode=post_preview' : 'mode=pm_preview';
	params += '&'+ POST_POST_URL + '=' + post_id;
	if (S_SID != '')
	{
		params += '&sid=' + S_SID;
	}
	if (document.forms['post'].username)
	{
		params += '&username=' + ajax_escape(document.forms['post'].username.value);
	}
	if (document.forms['post'].subject)
	{
		params += '&subject=' + ajax_escape(document.forms['post'].subject.value);
	}
	if (document.forms['post'].disable_html && document.forms['post'].disable_html.checked)
	{
		params += '&disable_html=True';
	}
	if (document.forms['post'].disable_bbcode && document.forms['post'].disable_bbcode.checked)
	{
		params += '&disable_bbcode=True';
	}
	if (document.forms['post'].disable_smilies && document.forms['post'].disable_smilies.checked)
	{
		params += '&disable_smilies=True';
	}
	if (document.forms['post'].attach_sig && document.forms['post'].attach_sig.checked)
	{
		params += '&attach_sig=True';
	}
	if (document.forms['post'].message)
	{
		params += '&message=' + ajax_escape(document.forms['post'].message.value);
	}

	return !loadXMLDoc(url, params, 'POST', 'post_preview_change');
}

function post_preview_change()
{
	//Check if the request is completed, if not, just skip over
	if (request.readyState == 4)
	{
		var result = AJAX_OP_COMPLETED;
		var error_msg = '';
		//If the request wasn't successful, we just hide any information we have.
		if (request.status == 200)
		{
			if (AJAX_DEBUG_RESULTS)
			{
				alert(request.responseText);
			}
			var result_data = parseResult(request.responseText);
			for (var i = 0; i < result_data.length; i++)
			{
				var str = (result_data[i][0] + ' = result_data[i][1];');
				eval(str);
			}

			if (result != AJAX_PREVIEW)
			{
				if (AJAX_DEBUG_REQUEST_ERRORS)
				{
					alert('result_code: '+result+'; error: '+error_msg);
				}
			}
		}

		AJAXFinishPreview(result, error_msg);
		delete request;
	}
}

function AJAXFinishPreview(result_code, code)
{
	if (!ajax_core_defined)
	{
		return true;
	}

	var preview = getElementById('preview_box');
	if (!preview)
	{
		return;
	}

	if (result_code == AJAX_PREVIEW)
	{
		preview.innerHTML = code;
		preview.style.display = '';
		window.scrollTo(0, 0);
	}
	else
	{
		preview.style.display = 'none';
	}
}
