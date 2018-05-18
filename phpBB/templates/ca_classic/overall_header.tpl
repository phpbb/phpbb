<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html dir="{S_CONTENT_DIRECTION}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta name="Author" content="http://www.phpbbstyles.com" />
{META}
{NAV_LINKS}
<title>{SITENAME} :: {PAGE_TITLE}</title>
<link rel="stylesheet" href="templates/ca_classic/style.css" type="text/css" />
<style type="text/css">
<!--
/* Specifiy background images for selected styles
   This can't be done within the external style sheet as NS4 sees image paths relative to
   the page which called the style sheet (i.e. this page in the root phpBB directory)
   whereas all other browsers see image paths relative to the style sheet. Stupid NS again!
*/
TH, TD.th	{ background-image: url(templates/ca_classic/images/cell1l.jpg) }
TH.thCornerR	{ background-image: url(templates/ca_classic/images/cell1r.jpg) }
TD.rowpic	{ background-image: url(templates/ca_classic/images/cell2r.jpg); background-repeat: repeat-y }
TD.cat,TD.catHead,TD.catSides,TD.catLeft,TD.catBottom { background-image: url(templates/ca_classic/images/cell2l.jpg) }
TD.catRight { background-image: url(templates/ca_classic/images/cell2r.jpg) }
TD.bodyline	{ background-image: url(templates/ca_classic/images/mainbg.jpg) }

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("templates/ca_classic/formIE.css"); 
-->
</style>
<!-- BEGIN switch_enable_pm_popup -->
<script language="Javascript" type="text/javascript">
<!--
	if ( {PRIVATE_MESSAGE_NEW_FLAG} )
	{
		window.open('{U_PRIVATEMSGS_POPUP}', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');;
	}
//-->
</script>
<!-- END switch_enable_pm_popup -->
<script language="javascript" type="text/javascript">
<!--

var PreloadFlag = false;
var expDays = 90;
var exp = new Date(); 
var tmp = '';
var tmp_counter = 0;
var tmp_open = 0;

exp.setTime(exp.getTime() + (expDays*24*60*60*1000));

function changeImages()
{
	if (document.images)
	{
		for (var i=0; i<changeImages.arguments.length; i+=2)
		{
			document[changeImages.arguments[i]].src = changeImages.arguments[i+1];
		}
	}
}

function newImage(arg)
{
	if (document.images)
	{
		rslt = new Image();
		rslt.src = arg;
		return rslt;
	}
}

function PreloadImages()
{
	if (document.images)
	{
		// preload all rollover images
		<!-- BEGIN switch_user_logged_out -->
		img0 = newImage('templates/ca_classic/images/lang_english/btn_login_on.gif');
		img1 = newImage('templates/ca_classic/images/lang_english/btn_register_on.gif');
		<!-- END switch_user_logged_out -->
		<!-- BEGIN switch_user_logged_in -->
		img2 = newImage('templates/ca_classic/images/lang_english/btn_pm_on.gif');
		img3 = newImage('templates/ca_classic/images/lang_english/btn_profile_on.gif');
		img4 = newImage('templates/ca_classic/images/lang_english/btn_groups_on.gif');
		img5 = newImage('templates/ca_classic/images/lang_english/btn_logout_on.gif');
		<!-- END switch_user_logged_in -->
		img6 = newImage('templates/ca_classic/images/lang_english/btn_faq_on.gif');
		img7 = newImage('templates/ca_classic/images/lang_english/btn_search_on.gif');
		img8 = newImage('templates/ca_classic/images/lang_english/btn_users_on.gif');
		img9 = newImage('templates/ca_classic/images/lang_english/btn_index_on.gif');
		PreloadFlag = true;
	}
	return true;
}


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
			return getCookieVal(j);
		i = document.cookie.indexOf(" ", i) + 1;
		if (i == 0)
			break;
	} 
	return null;
}

//-->
</script>
</head>
<body bgcolor="#F8F8F8" text="#000000" link="#043698" vlink="#003090" onload="PreloadImages();">
<a name="top"></a>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center" class="forumline"> 
	<tr> 
		<td class="bodyline"><table width="100%" cellspacing="0" cellpadding="0" border="0">
			<tr> 
				<td align="center" valign="top"><a href="{U_INDEX}"><img src="templates/ca_classic/images/logo_phpBB.gif" border="0" alt="{L_INDEX}" vspace="2" /></a></td>
			</tr>
			<!--<tr>
				<td align="center" width="100%" valign="middle"><span class="maintitle">{SITENAME}</span><br />
				<span class="subtitle">{SITE_DESCRIPTION}<br />&nbsp;</span></td>
			</tr>-->
		</table>
		</td></tr>
		<tr>
			<td align="center" width="100%" valign="middle" class="th" nowrap="nowrap"><span class="mainmenu">
				<!-- BEGIN switch_user_logged_out -->
				<a title="{L_LOGIN_LOGOUT}" href="{U_LOGIN_LOGOUT}" class="mainmenu" onmouseover="changeImages('btn_top_login', 'templates/ca_classic/images/lang_english/btn_login_on.gif'); return true;" onmouseout="changeImages('btn_top_login', 'templates/ca_classic/images/lang_english/btn_login.gif'); return true;"><img name="btn_top_login" src="templates/ca_classic/images/lang_english/btn_login.gif" height="18" border="0" alt="{L_LOGIN_LOGOUT}" hspace="1" /></a>
				<a title="{L_REGISTER}" href="{U_REGISTER}" class="mainmenu" onmouseover="changeImages('btn_top_register', 'templates/ca_classic/images/lang_english/btn_register_on.gif'); return true;" onmouseout="changeImages('btn_top_register', 'templates/ca_classic/images/lang_english/btn_register.gif'); return true;"><img name="btn_top_register" src="templates/ca_classic/images/lang_english/btn_register.gif" height="18" border="0" alt="{L_REGISTER}" hspace="1" /></a>
				<!-- END switch_user_logged_out -->
				<!-- BEGIN switch_user_logged_in -->
				<a title="{L_PROFILE}" href="{U_PROFILE}" class="mainmenu" onmouseover="changeImages('btn_top_profile', 'templates/ca_classic/images/lang_english/btn_profile_on.gif'); return true;" onmouseout="changeImages('btn_top_profile', 'templates/ca_classic/images/lang_english/btn_profile.gif'); return true;"><img name="btn_top_profile" src="templates/ca_classic/images/lang_english/btn_profile.gif" height="18" border="0" alt="{L_PROFILE}" hspace="1" /></a>
				<a title="{PRIVATE_MESSAGE_INFO}" href="{U_PRIVATEMSGS}" class="mainmenu" onmouseover="changeImages('btn_top_pm', 'templates/ca_classic/images/lang_english/btn_pm_on.gif'); return true;" onmouseout="changeImages('btn_top_pm', 'templates/ca_classic/images/lang_english/btn_pm.gif'); return true;"><img name="btn_top_pm" src="templates/ca_classic/images/lang_english/btn_pm.gif" height="18" border="0" alt="{PRIVATE_MESSAGE_INFO}" hspace="1" /></a>
				<!-- END switch_user_logged_in -->
				<a title="{L_FAQ}" href="{U_FAQ}" class="mainmenu" onmouseover="changeImages('btn_top_faq', 'templates/ca_classic/images/lang_english/btn_faq_on.gif'); return true;" onmouseout="changeImages('btn_top_faq', 'templates/ca_classic/images/lang_english/btn_faq.gif'); return true;"><img name="btn_top_faq" src="templates/ca_classic/images/lang_english/btn_faq.gif" height="18" border="0" alt="{L_FAQ}" hspace="1" /></a>
				<a title="{L_MEMBERLIST}" href="{U_MEMBERLIST}" class="mainmenu" onmouseover="changeImages('btn_top_users', 'templates/ca_classic/images/lang_english/btn_users_on.gif'); return true;" onmouseout="changeImages('btn_top_users', 'templates/ca_classic/images/lang_english/btn_users.gif'); return true;"><img name="btn_top_users" src="templates/ca_classic/images/lang_english/btn_users.gif" height="18" border="0" alt="{L_MEMBERLIST}" hspace="1" /></a>
				<a title="{L_LOGIN_LOGOT}" href="{U_SEARCH}" class="mainmenu" onmouseover="changeImages('btn_top_search', 'templates/ca_classic/images/lang_english/btn_search_on.gif'); return true;" onmouseout="changeImages('btn_top_search', 'templates/ca_classic/images/lang_english/btn_search.gif'); return true;"><img name="btn_top_search" src="templates/ca_classic/images/lang_english/btn_search.gif" height="18" border="0" alt="{L_SEARCH}" hspace="1" /></a>
				<!-- BEGIN switch_user_logged_in -->
				<a title="{L_USERGROUPS}" href="{U_GROUP_CP}" class="mainmenu" onmouseover="changeImages('btn_top_groups', 'templates/ca_classic/images/lang_english/btn_groups_on.gif'); return true;" onmouseout="changeImages('btn_top_groups', 'templates/ca_classic/images/lang_english/btn_groups.gif'); return true;"><img name="btn_top_groups" src="templates/ca_classic/images/lang_english/btn_groups.gif" height="18" border="0" alt="{L_USERGROUPS}" hspace="1" /></a>
				<a title="{L_LOGIN_LOGOT}" href="{U_LOGIN_LOGOUT}" class="mainmenu" onmouseover="changeImages('btn_top_logout', 'templates/ca_classic/images/lang_english/btn_logout_on.gif'); return true;" onmouseout="changeImages('btn_top_logout', 'templates/ca_classic/images/lang_english/btn_logout.gif'); return true;"><img name="btn_top_logout" src="templates/ca_classic/images/lang_english/btn_logout.gif" height="18" border="0" alt="{L_LOGIN_LOGOUT}" hspace="1" /></a>
				<!-- END switch_user_logged_in -->
				<a title="{L_INDEX}" href="{U_INDEX}" class="mainmenu" onmouseover="changeImages('btn_top_index', 'templates/ca_classic/images/lang_english/btn_index_on.gif'); return true;" onmouseout="changeImages('btn_top_index', 'templates/ca_classic/images/lang_english/btn_index.gif'); return true;"><img name="btn_top_index" src="templates/ca_classic/images/lang_english/btn_index.gif" height="18" border="0" alt="{L_INDEX}" hspace="1" /></a>
			</span></td></tr>
		</table>

		<br />