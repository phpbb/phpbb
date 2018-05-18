var xmlHttp;
var field;

function ChangeStatus(current, type, b_id, cms_type)
{
	xmlHttp = GetXmlHttpObject();
	if (xmlHttp == null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}

	switch (type)
	{
		case 0:
			field = "active_";
			break;
		case 1:
			field = "border_";
			break;
		case 2:
			field = "titlebar_";
			break;
		case 3:
			field = "local_";
			break;
		case 4:
			field = "background_";
			break;
	}

	var url = ip_root_path + 'cms_db_update.' + php_ext;
	var params = 'mode=update_block&type=' + type + '&b_id=' + b_id + '&status=' + document.getElementById(field + b_id).value + '&cms_type=' + cms_type;
	if (S_SID != '')
	{
		params += '&sid=' + S_SID;
	}
	url = url + "?" + params;

	xmlHttp.open("GET", url, true);
	xmlHttp.send(null);

	if (document.getElementById(field + b_id).value == 0)
	{
		current.src = "templates/all/images/cms/turn_" + field + "on.png"
		document.getElementById(field + b_id).value = 1;
	}
	else
	{
		current.src = "templates/all/images/cms/turn_" + field + "off.png"
		document.getElementById(field + b_id).value = 0;
	}
}

function ChangeMenuOrder(m_id)
{
	xmlHttp = GetXmlHttpObject();
	if (xmlHttp == null)
	{
		alert ("Browser does not support HTTP Request");
		return;
	}

	var url = ip_root_path + 'cms_db_update.' + php_ext;
	var params = 'mode=update_menu_order';
	if (S_SID != '')
	{
		params += '&sid=' + S_SID;
	}
	url = url + "?" + params;

	xmlHttp.open("GET", url, true);
	xmlHttp.send(null);
}

function GetXmlHttpObject()
{
	var xmlHttp = null;
	try
	{
		// Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch (e)
	{
		//Internet Explorer
		try
		{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
		}
	}
	return xmlHttp;
}

// AJAX CMS - BEGIN

/***********************************************
* Dynamic Ajax Content- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* This notice MUST stay intact for legal use
* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
***********************************************/

var bustcachevar = 1; //bust potential caching of external pages after initial request? (1=yes, 0=no)
var loadedobjects = "";
var rootdomain = "http://" + window.location.hostname;
//var bustcacheparameter=""

function ajaxpage(url, params, containerid)
{
	var page_request = false;
	var block_config = document.getElementById('block_config');
	block_config.innerHTML = '<img src="images/loading.gif" alt="" title="" style="vertical-align:middle" />';

	if (window.XMLHttpRequest) // if Mozilla, Safari etc
	{
		page_request = new XMLHttpRequest();
	}
	else if (window.ActiveXObject) // if IE
	{
		try
		{
			page_request = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e)
		{
			try
			{
				page_request = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e){}
		}
	}
	else
	{
		return false;
	}
	page_request.onreadystatechange = function()
	{
		loadpage(page_request, containerid);
	}
	//if (bustcachevar) //if bust caching of external page
	//bustcacheparameter=(url.indexOf("?")!=-1)? "&"+new Date().getTime() : "?"+new Date().getTime()
	page_request.open('GET', url + params, true);
	page_request.send(null);
}

function loadpage(page_request, containerid)
{
	if (page_request.readyState == 4 && (page_request.status == 200 || window.location.href.indexOf("http") == -1))
	{
		document.getElementById(containerid).innerHTML=page_request.responseText;
	}
}

function loadobjs()
{
	if (!document.getElementById)
	{
		return;
	}
	for (i = 0; i < arguments.length; i++)
	{
		var file = arguments[i];
		var fileref = "";
		if (loadedobjects.indexOf(file) == -1) //Check to see if this object has not already been added to page before proceeding
		{
			if (file.indexOf(".js")!=-1) //If object is a js file
			{
				fileref=document.createElement('script');
				fileref.setAttribute("type","text/javascript");
				fileref.setAttribute("src", file);
			}
			else if (file.indexOf(".css")!=-1) //If object is a css file
			{
				fileref=document.createElement("link");
				fileref.setAttribute("rel", "stylesheet");
				fileref.setAttribute("type", "text/css");
				fileref.setAttribute("href", file);
			}
		}
		if (fileref!="")
		{
			document.getElementsByTagName("head").item(0).appendChild(fileref);
			loadedobjects += file + " "; //Remember this object as being already added to page
		}
	}
}

// AJAX CMS - END
