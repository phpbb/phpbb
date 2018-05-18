/**
* 
* @file $Id ajax_forumfunctions.js
* @copyright (C) 2006
* @author alcaeus
* @email < mods@alcaeus.org >
* 
**/

// Mark a forum as read
function AJAXMarkForum(forum_id)
{
	if (!ajax_core_defined || (forum_id == 0))
	{
		return true;
	}

	var url = 'ajax.' + php_ext;
	var params = 'mode=mark_forum';
	if (S_SID != '')
	{
		params += '&sid=' + S_SID;
	}
	params += '&' + POST_FORUM_URL + '=' + forum_id;
	return !loadXMLDoc(url, params, 'GET', 'mark_forum_change');
}

function mark_forum_change()
{
	//Check if the request is completed, if not, just skip over
	if (request.readyState == 4)
	{
		var result_code = AJAX_ERROR;
		var forum_id = '';
		var forumimage = '0';
		var imagetext = '0';
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

				if (result_code == AJAX_MARK_FORUM)
				{
					forum_id = getFirstTagValue('forumid', response);
					forumimage = getFirstTagValue('forumimage', response);
					imagetext = getFirstTagValue('imagetext', response);
				}
				else
				{
					error_msg = getFirstTagValue('error_msg', response);
					if (AJAX_DEBUG_REQUEST_ERRORS)
					{
						alert('result_code: '+result_code+'; error: '+error_msg);
					}
				}
			}
		}

		AJAXFinishMarkForum(result_code, forum_id, forumimage, imagetext);
		delete request;
	}
}

function AJAXFinishMarkForum(result_code, forum_id, forumimage, imagetext)
{
	if (!ajax_core_defined || (result_code != AJAX_MARK_FORUM))
	{
		return;
	}

	var forum_image = getElementById('forumimage_'+forum_id);
	if (forum_image == null)
	{
		if (AJAX_DEBUG_HTML_ERRORS)
		{
			alert('AJAXMarkForum: some HTML elements could not be found');
		}
		return;
	}

	forum_image.setAttribute('src', forumimage, 'false');
	forum_image.setAttribute('alt', imagetext, 'false');
	forum_image.setAttribute('title', imagetext, 'false');
}
