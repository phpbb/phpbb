<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- ?xml version="1.0" encoding="UTF-8"? -->
<!--DOCTYPE PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd" -->
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
{META}
<title>{SITENAME} -+- {PAGE_TITLE}</title>
<style type="text/css">
<!--

BODY {background-color:{T_BODY_BGCOLOR};color:{T_BODY_TEXT};scrollbar-base-color:{T_TH_COLOR2};scrollbar-arrow-color:{T_TH_COLOR1}}
P	{font-family:{T_FONTFACE1};font-size:10pt}

TH	{background-color:{T_TH_COLOR3};font-family:{T_FONTFACE2};font-size:8pt;font-weight:normal}
TH.secondary	{background-color:{T_TH_COLOR3};font-family:{T_FONTFACE1};font-size:10pt;font-weight:normal;text-align:left}
TD.tablebg	{background-color:{T_TH_COLOR1}}
TD.cat	{background-color:{T_TH_COLOR2};font-family:{T_FONTFACE1};font-size:12pt}
TD.row1	{background-color:{T_TD_COLOR1}}
TD.row2	{background-color:{T_TD_COLOR2}}

SPAN.title	{font-family:Impact,sans-serif;font-size:36pt}
SPAN.cattitle	{font-family:{T_FONTFACE1};font-size:12pt;font-weight:bold}
SPAN.gen	{font-family:{T_FONTFACE1};font-size:10pt}
SPAN.gensmall	{font-family:{T_FONTFACE1};font-size:8pt}
SPAN.courier	{font-family:{T_FONTFACE3};font-size:10pt}
SPAN.courier	{font-family:{T_FONTFACE3};font-size:8pt}

SELECT {font-family:Verdana;font-size:8pt} 
INPUT {font-family:Verdana;font-size:8pt}
SELECT.small	{font-family:"Courier New",courier;font-size:8pt;width:140px}
INPUT.text	{font-family:"Courier New",courier;font-size:8pt;}

//-->
</style>
</head>
<body bgcolor="{T_BODY_BGCOLOR}" text="{T_BODY_TEXT}" link="{T_BODY_LINK}" vlink="{T_BODY_VLINK}">

<a name="top"></a>

<script language="javascript" type="text/javascript">
<!--
function refresh_username(selected_username)
{
	opener.document.forms['post'].username.value = selected_username;
}
//-->
</script>

<form method="post" action="{S_SEARCH_ACTION}"><table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg" width="100%"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<th colspan="2">{L_SEARCH_USERNAME}</th>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SEARCH_USERNAME}: </span><br /><span class="gensmall">Use * as a wildcard</span></td>
				<td class="row1" align="center"><input type="text" name="search_author" value="{AUTHOR}" /> <input type="submit" name="search" value="{L_SEARCH}" /></td>
				<!-- BEGIN select_name -->
			</tr>
			<tr>
				<td class="row2"><span class="gen">{L_UPDATE_USERNAME}: </span></td>
				<td class="row2"><span class="cattitle"><select name="author_list">{S_AUTHOR_OPTIONS}</select> <input type="submit" onclick="refresh_username(this.form.author_list.options[this.form.author_list.selectedIndex].value); return false;" name="use" value="{L_SELECT}" /></span></td>
				<!-- END select_name -->
			</tr>
		</table></td>
	</tr>
</table></form>

<div align="center"><span class="gensmall"><a href="javascript:window.close();">{L_CLOSE_WINDOW}</a></span></td></div>

</body>
</html>