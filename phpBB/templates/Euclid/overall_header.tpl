<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- ?xml version="1.0" encoding="UTF-8"? -->
<!--DOCTYPE PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd" -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset={S_CONTENT_ENCODING}" />
<meta http-equiv="Content-Style-Type" content="text/css" />
{META}
<title>{SITENAME} -+- {PAGE_TITLE}</title>
<style type="text/css">
<!--

BODY {background-color:{T_BODY_BGCOLOR};color:{T_BODY_TEXT};scrollbar-base-color:{T_TH_COLOR2};scrollbar-arrow-color:{T_TH_COLOR1}}
P	{font-family:{T_FONTFACE1};font-size:10pt}

TH	{background-color:{T_TH_COLOR3};color:{T_FONTCOLOR2};font-family:{T_FONTFACE2};font-size:8pt;font-weight:bold}
TH.secondary	{background-color:{T_TH_COLOR3};color:{T_FONTCOLOR2};font-family:{T_FONTFACE1};font-size:10pt;font-weight:normal;text-align:left}
TD.tablebg	{background-color:{T_TH_COLOR1}}
TD.cat	{background-color:{T_TH_COLOR2};font-family:{T_FONTFACE1};font-size:12pt}
TD.row1	{background-color:{T_TD_COLOR1}}
TD.row2	{background-color:{T_TD_COLOR2}}

SPAN.title	{font-family:{T_FONTFACE2};font-size:26pt}
SPAN.cattitle	{font-family:{T_FONTFACE1};font-size:12pt;font-weight:bold}
SPAN.gen	{font-family:{T_FONTFACE1};font-size:10pt}
SPAN.gensmall	{font-family:{T_FONTFACE1};font-size:8pt}
SPAN.courier	{font-family:{T_FONTFACE3};font-size:10pt}

SELECT {font-family:Verdana;font-size:8pt} 
INPUT {font-family:Verdana;font-size:8pt}
SELECT.small	{font-family:"Courier New",courier;font-size:8pt;width:140px}
INPUT.text	{font-family:"Courier New",courier;font-size:8pt;}

INPUT.outsidetable {background-color:{T_TD_COLOR1}}
INPUT.mainoptiontable {background-color:{T_TD_COLOR1}}
INPUT.liteoptiontable {background-color:{T_TD_COLOR1}}

A.forumlinks {font-weight:bold}
A {text-decoration:none}
A:hover {color:{T_BODY_HLINK};text-decoration:underline}

HR {border: solid {T_FONTCOLOR1} 0px; border-top-width: 1px; height: 0px; }

@import url("templates/Euclid/ie_form_elements.css"); 

//-->
</style>
</head>
<body bgcolor="{T_BODY_BGCOLOR}" text="{T_BODY_TEXT}" link="{T_BODY_LINK}" vlink="{T_BODY_VLINK}">

<a name="top"></a>

<form method="post" action="{S_LOGIN_ACTION}"><table width="98%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg" width="100%"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="row2"><table width="100%" cellspacing="0" border="0">
					<tr>
						<td><span class="title"><a href="{U_INDEX}"><img src="templates/Euclid/images/logo_phpBB.gif" border="0" alt="" title="" /></a></span></td>
						<td width="100%" align="center" valign="top"><span class="title"><b>{SITENAME}</b></span><br /><span class="gen">{SITE_DESCRIPTION}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td><span class="gensmall"><a href="{U_FAQ}">{L_FAQ}</a>&nbsp;|&nbsp;<a href="{U_MEMBERLIST}">{L_MEMBERLIST}</a>
						<!-- BEGIN switch_user_logged_out -->
						&nbsp;|&nbsp;<a href="{U_REGISTER}">{L_REGISTER}</a>
						<!-- END switch_user_logged_out -->
						|&nbsp;<a href="{U_SEARCH}">{L_SEARCH}</a></span></td>
						<td align="right"><span class="gensmall"><a href="{U_PROFILE}">{L_PROFILE}</a>&nbsp;|&nbsp;<a href="{U_GROUP_CP}">{L_USERGROUPS}</a>&nbsp;|&nbsp;<a href="{U_LOGIN_LOGOUT}">{L_LOGIN_LOGOUT}</a></span></td>
					</tr>
					<!-- BEGIN switch_user_logged_out -->
					<tr>
						<td colspan="3" align="right" valign="bottom"><span class="gensmall">{L_USERNAME}: <input class="text" type="text" name="username" size="15" />&nbsp;&nbsp;&nbsp;{L_PASSWORD}: <input type="password" name="password" size="15" />&nbsp;&nbsp;&nbsp;{L_AUTO_LOGIN}</span>:&nbsp;<input class="text" type="checkbox" name="autologin" />&nbsp;&nbsp;&nbsp;<input class="mainoptiontable" type="submit" name="login" value="{L_LOGIN}" />&nbsp;</td>
					</tr>
					<!-- END switch_user_logged_out -->
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td width="100%"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td><span class="gensmall">
				<!-- BEGIN switch_user_logged_in -->
				{LAST_VISIT_DATE} / <a href="{U_SEARCH_NEW}">{L_SEARCH_NEW}</a>
				<!-- END switch_user_logged_in -->
				</span></td>
				<td align="right"><span class="gensmall">{CURRENT_TIME}</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" /><br />
