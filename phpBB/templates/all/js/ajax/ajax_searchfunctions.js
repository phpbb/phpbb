//**************************************************************************
//                          ajax_searchfunctions.js
//                            -------------------
//   begin                : Sunday, Jul 17, 2005
//   copyright            : (C) 2005 alcaeus
//   email                : mods@alcaeus.org
//
//   $Id$
//
//**************************************************************************

//**************************************************************************
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; either version 2 of the License, or
//   (at your option) any later version.
//
//**************************************************************************

//
// Inline search
//
var timer_id = 0;
var last_username = '';

function AJAXUsernameSearch(username, is_search)
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

	timer_id = setTimeout('AJAXSubmitUsernameSearch(\''+username+'\', '+is_search+')', KEYUP_TIMEOUT);
}

function AJAXSubmitUsernameSearch(username, is_search)
{
	if (!ajax_core_defined || (last_username == username))
	{
		return;
	}

	last_username = username;
	timer_id = 0;

	//Send search results
	if (((username != '') || !is_search) && (username != '*'))
	{
		error_handler = 'AJAXFinishUsernameSearch';
		var url = ip_root_path + 'ajax.' + php_ext;
		var params = 'mode=search_user&search=' + is_search + '&username=' + ajax_escape(username);
		if (S_SID != '')
		{
			params += '&sid='+S_SID;
		}
		if (!loadXMLDoc(url, params, 'GET', 'error_req_change'))
		{
			AJAXFinishUsernameSearch(AJAX_ERROR, '');
		}
	}
	else
	{
		AJAXFinishUsernameSearch(AJAX_ERROR, '');
	}
}

function AJAXFinishUsernameSearch(result_code, code)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var userlist = getElementById('username_list');
	var userselect = getElementById('username_select');
	var error_text = getElementById('username_error_text');
	var error_tbl = getElementById('username_error_tbl');

	if ((userlist == null) || (userselect == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXFinishUsernameSearch: some HTML elements could not be found');
		}
		return;
	}

	userlist.style.display = 'none';
	userselect.innerHTML = '';
	if (error_tbl)
	{
		error_tbl.style.display = 'none';
	}
	if (error_text)
	{
		setInnerText(error_text, '&nbsp;');
		error_text.style.display = 'none';
	}

	if (result_code == AJAX_PM_USERNAME_SELECT)
	{
		userselect.innerHTML = code;
		userlist.style.display = '';
	}
	else if (result_code == AJAX_PM_USERNAME_ERROR)
	{
		userlist.style.display = 'none';
		userselect.innerHTML = '';
		if (error_tbl)
		{
			error_tbl.style.display = '';
		}
		if (error_text)
		{
			setInnerText(error_text, code);
			error_text.style.display = '';
		}
	}
}

function AJAXSelectUsername(selectfield)
{
	if ((!ajax_core_defined) || (selectfield.value == '-1'))
	{
		return;
	}

	var usernamelist = getElementById('username_list');
	var usernameselect = getElementById('username_select');
	var username_field = getElementById('username');

	if ((usernamelist == null) || (usernameselect == null) || (username_field == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXSelectUsername: some HTML elements could not be found');
		}
		return;
	}

	username_field.value = selectfield.value;

	usernameselect.innerHTML = '';
	usernamelist.style.display = 'none';
}

//
// Inline QuickSearch
// Returns a short list of possible search results
//
function AJAXQuickSearch(keywords)
{
	if (!ajax_core_defined)
	{
		return;
	}

	//Send search results
	if (keywords != '')
	{
		var url = CMS_PAGE_SEARCH;
		var params = 'show_results=topics&is_ajax=2&search_fields=all&search_keywords=' + ajax_escape(keywords);
		if (S_SID != '')
		{
			params += '&sid='+S_SID;
		}
		if (!loadXMLDoc(url, params, 'GET', 'quicksearch_req_change'))
		{
			AJAXFinishQuickSearch('', '');
		}
	}
	else
	{
		AJAXFinishQuickSearch('', '');
	}
}

function quicksearch_req_change()
{
	if (!ajax_core_defined)
	{
		return;
	}

	//Check if the request is completed, if not, just skip over
	if (request.readyState == 4)
	{
		var results = 0;
		//If the request wasn't successful, we just hide any information we have.
		if (request.status == 200)
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
				results = getFirstTagValue('results', response);
				error_msg = getFirstTagValue('error_msg', response);
			}
		}
		AJAXFinishQuickSearch(results, error_msg);
	}
}

function AJAXFinishQuickSearch(results, error_msg)
{
	if (!ajax_core_defined)
	{
		return;
	}

	var restable = getElementById('searchresults_tbl');
	var reslink = getElementById('searchresults_lnk');
	var restext = getElementById('searchresults_txt');

	if ((restable == null) || (reslink == null) || (restext == null))
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXFinishQuickSearch: some HTML elements could not be found');
		}
		return;
	}

	if (results == 0)
	{
		restable.style.display = 'none';
		setInnerText(reslink, null);
		setInnerText(restext, null);
	}
	else
	{
		restext.innerHTML = error_msg;
		reslink.innerHTML = results;
		restable.style.display = '';
	}
}
