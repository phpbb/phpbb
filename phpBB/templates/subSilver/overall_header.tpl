<!-- DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}" />
<meta http-equiv="Content-Style-Type" content="text/css" />
{META}
{NAV_LINKS}
<title>{SITENAME} :: {PAGE_TITLE}</title>
<link rel="stylesheet" href="templates/subSilver/{T_HEAD_STYLESHEET}" type="text/css" />
<style type="text/css">
<!--
/* Specifiy background images for selected styles
   This can't be done within the external style sheet as NS4 sees image paths relative to
   the page which called the style sheet (i.e. this page in the root phpBB directory)
   whereas all other browsers see image paths relative to the style sheet. Stupid NS again!
*/
TH			{ background-image: url(templates/subSilver/images/cellpic3.gif) }
TD.cat		{ background-image: url(templates/subSilver/images/cellpic1.gif) }
TD.rowpic	{ background-image: url(templates/subSilver/images/cellpic2.jpg); background-repeat: repeat-y }
td.icqback	{ background-image: url(templates/subSilver/images/icon_icq_add.gif); background-repeat: no-repeat }
TD.catHead,TD.catSides,TD.catLeft,TD.catRight,TD.catBottom { background-image: url(templates/subSilver/images/cellpic1.gif) }


/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("templates/subSilver/formIE.css"); 
-->
</style>

<!-- BEGIN switch_enable_pm_popup -->
<script language="Javascript" type="text/javascript">
<!--
	var new_pm_flag = {PRIVATE_MESSAGE_NEW_FLAG};

	if( new_pm_flag )
	{
		window.open('{U_PRIVATEMSGS_POPUP}', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');;
	}
//-->
</script>
<!-- END switch_enable_pm_popup -->
</head>

<body bgcolor="{T_BODY_BGCOLOR}" text="{T_BODY_TEXT}" link="{T_BODY_LINK}" vlink="{T_BODY_VLINK}">
<span class="gen"><a name="top"></a></span><table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
<tr> 
	<td class="bodyline"> 
		  <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr> 
			  
			
		<td> <a href="{U_INDEX}"><img src="templates/subSilver/images/logo_phpBB.gif" border="0" alt="Forum Home" vspace="1" /></a> 
		</td>
			  
			
		<td align="center" width="100%" valign="middle"><span class="maintitle">{SITENAME}</span><br />
		  <span class="gen">{SITE_DESCRIPTION}<br />&nbsp; </span> 
			  
		  <table cellspacing="0" cellpadding="2" border="0">
			<tr> 
			  <td valign="top" nowrap="nowrap" align="center"><span class="mainmenu">&nbsp;<a href="{U_FAQ}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_faq.gif" width="12" height="13" border="0" align="top" alt="{L_FAQ}" hspace="3" />{L_FAQ}</a></span><span class="mainmenu">&nbsp;&nbsp;&nbsp;<a href="{U_SEARCH}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_search.gif" width="12" height="13" border="0" align="top" alt="{L_SEARCH}" hspace="3" />{L_SEARCH}</a>&nbsp;&nbsp;&nbsp;<a href="{U_MEMBERLIST}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_members.gif" width="12" height="13" border="0" align="top" alt="{L_MEMBERLIST}" hspace="3" />{L_MEMBERLIST}</a>&nbsp;&nbsp;&nbsp;<a href="{U_GROUP_CP}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_groups.gif" width="12" height="13" border="0" align="top" alt="{L_USERGROUPS}" hspace="3" />{L_USERGROUPS}</a>&nbsp;&nbsp;&nbsp;<a href="{U_REGISTER}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_register.gif" width="12" height="13" border="0" align="top" alt="{L_REGISTER}" hspace="3" />{L_REGISTER}</a></span></td>
			</tr>
			<tr> 
			  <td nowrap="nowrap" valign="top" height="25" align="center"><span class="mainmenu">&nbsp;<a href="{U_PROFILE}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_profile.gif" width="12" height="13" border="0" align="top" alt="{L_PROFILE}" hspace="3" />{L_PROFILE}</a>&nbsp;&nbsp;&nbsp;<a href="{U_PRIVATEMSGS}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_message.gif" width="12" height="13" border="0" align="top" alt="{PRIVATE_MESSAGE_INFO}" hspace="3" />{PRIVATE_MESSAGE_INFO}</a>&nbsp;&nbsp;&nbsp;<a href="{U_LOGIN_LOGOUT}" class="mainmenu"><img src="templates/subSilver/images/icon_mini_login.gif" width="12" height="13" border="0" align="top" alt="{L_LOGIN_LOGOUT}" hspace="3" />{L_LOGIN_LOGOUT}</a></span></td>
			</tr>
		  </table>
			</td>
			</tr>
		  </table>
<span class="mainmenu"> <br /> </span>

