<!-- DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"  />
<meta http-equiv="Content-Style-Type" content="text/css" />

<title>{SITENAME} -+- {PAGE_TITLE}</title>
<style type="text/css">
<!--
/* subSilver Theme for phpBB2
 * Created by subBlue design
 * http://www.subBlue.com
 */

body { 	background-color:#E5E5E5;
		scrollbar-face-color: #C8D1D7; scrollbar-highlight-color: #EAF0F7;
		scrollbar-shadow-color: #95AFC4; scrollbar-3dlight-color: #D6DDE2;
		scrollbar-arrow-color:  #006699; scrollbar-track-color: #EFEFEF;
		scrollbar-darkshadow-color: #7294AF;
}

font	{ font-family: Verdana, Arial, Helvetica, sans-serif }
td		{ font-family: Verdana, Arial, Helvetica, sans-serif }
th		{ font-family: Verdana, Arial, Helvetica, sans-serif }
P		{ font-family: Verdana, Arial, Helvetica, sans-serif }
hr		{ height: 1px; color:#c2cdd6 }


/* Forum colours */
.bodyline	{ background-color:#FFFFFF; border: #AEBDC4; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px }
.forumline	{ background-color:#FFFFFF; border: 2px #006699 solid }


/* Main table cell colours and backgrounds */
TH			{ background-color: #1B7CAD; height: 25px; font-size: 11px; line-height : 100%; font-weight: bold; color: #FFB163; background-image: url(templates/subSilver/images/cellpic3.gif) }
TD.tablebg	{ background-color: #000000 }
TD.cat		{ background-color: #CBD3D9; height: 28px; background-image: url(templates/subSilver/images/cellpic1.gif) }
TD.row1		{ background-color: #EFEFEF }

TH.thHead,TH.thSides,TH.thTop,TH.thLeft,TH.thRight,TH.thBottom,TH.thCornerL,TH.thCornerR { border: #FFFFFF; border-style: solid; }

TH.thHead	 { font-weight : bold; font-size: 12px; height: 25px; border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 0px }
TH.thSides	 { border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }
TH.thTop	 { border-left-width: 0px; border-top-width: 1px; border-right-width: 0px; border-bottom-width: 0px }
TH.thLeft	 { border-left-width: 1px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px }
TH.thRight	 { border-left-width: 0px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }
TH.thBottom  { border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 1px }
TH.thCornerL { border-left-width: 1px; border-top-width: 1px; border-right-width: 0px; border-bottom-width: 0px }
TH.thCornerR { border-left-width: 0px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 0px }

TD.row3Right	 { background-color: #c2cdd6; border: #FFFFFF; border-style: solid;  border-left-width: 0px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }


/* General normal text */
.gen		{ font-size : 12px; color : #000000; }
a.gen		{ color: #006699; text-decoration: none; }
a:hover.gen	{ color: #C23030; text-decoration: underline; }

/* General medium text */
.genmed		{ font-size : 11px; color : #000000; }
a.genmed		{ text-decoration: none; color : #006699; }
a:hover.genmed	{ text-decoration: underline; color : #C23030; }


/* General small */
.gensmall		{ font-size : 10px; color : #000000; }
a.gensmall		{ color: #006699; text-decoration: none; }
a:hover.gensmall	{ color: #C23030; text-decoration: underline; }

/* Form elements */
input,textarea, select {
color : #000000;
font-family : Verdana, Arial, Helvetica, sans-serif;
font-size : 11px;
font-weight : normal;
border-color : #000000;
}

/* The text input fields background colour */
input.post, textarea.post, select {
background-color : #FFFFFF;
}

input { text-indent : 2px; }

/* The buttons used for bbCode styling in message post */
input.button {
background-color : #EFEFEF;
color : #000000;
font-family : Verdana, Arial, Helvetica, sans-serif;
font-size : 11px;
}


/* The main submit button option */
input.mainoption {
background-color : #FAFAFA;
font-weight : bold;
}

/* None bold submit button */
input.liteoption {
background-color : #FAFAFA;
font-weight : normal;
}

/* Import the fancy styles for IE only (NS4.x doesn't use the @import function) */
@import url("templates/subSilver/formIE.css");

-->
</style>

</head>

<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5584AA">

<script language="javascript" type="text/javascript">
<!--
function refresh_username(selected_username)
{
	opener.document.forms['post'].username.value = selected_username;
}
//-->
</script>

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center" height="100%">
  <tr>
	<td class="bodyline" valign="top"> 
	  <form method="post" name="search" action="{S_SEARCH_ACTION}">
	    <table width="100%" border="0" cellspacing="1" cellpadding="4" class="forumline">
		  <tr> 
		    <th class="thHead">{L_SEARCH_USERNAME}</th>
		</tr>
		<tr>
		    <td valign="top" class="row1"><span class="genmed"><br />
			  <input type="text" name="search_author" value="{AUTHOR}" class="post" />
			  <input type="submit" name="search" value="{L_SEARCH}" class="liteoption" />
			  </span><br />
			  <span class="gensmall">Use * as a wildcard</span><br />
			<br />
			  <!-- BEGIN select_name -->
			  <span class="genmed">{L_UPDATE_USERNAME}<br />
			<select name="author_list">{S_AUTHOR_OPTIONS}</select>
			  <input type="submit" class="liteoption" onClick="refresh_username(this.form.author_list.options[this.form.author_list.selectedIndex].value);return false;" name="use" value="{L_SELECT}" />
			</span><br />
			<br />
			  <!-- END select_name -->
			  <span class="gen"><a href="javascript:window.close();" class="gen">Close window</a></span> 
			</td>
		</tr>
	  </table>
	  </form>
</td>
  </tr>
</table>
&nbsp;
</body>
</html>

