<!-- DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"  />
<meta http-equiv="Content-Style-Type" content="text/css" />
<style type="text/css">
<!--
-->
</style>



{META}
<title>{SITENAME} :: {PAGE_TITLE}</title>
<style type="text/css">
<!--
/* subSilver Theme for phpBB2
 * Created by subBlue design
 * http://www.subBlue.com
 */

body { 	background-color:{T_BODY_BGCOLOR};
		scrollbar-face-color: #C8D1D7; scrollbar-highlight-color: #EAF0F7;
		scrollbar-shadow-color: #95AFC4; scrollbar-3dlight-color: #D6DDE2;
		scrollbar-arrow-color:  #006699; scrollbar-track-color: #EFEFEF;
		scrollbar-darkshadow-color: #7294AF;
}

font	{ font-family: Verdana, Arial, Helvetica, sans-serif }
td		{ font-family: Verdana, Arial, Helvetica, sans-serif }
th		{ font-family: Verdana, Arial, Helvetica, sans-serif }
P		{ font-family: Verdana, Arial, Helvetica, sans-serif }
hr		{ height: 1px; color:{T_TR_COLOR3} }


/* Forum colours */
.bodyline	{ background-color:#FFFFFF; border: {T_TD_COLOR1}; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px }
.forumline	{ background-color:#FFFFFF; border: 2px #006699 solid }


/* Main table cell colours and backgrounds */
TH			{ background-color: {T_TH_COLOR3}; height: 25px; font-size: 11px; line-height : 100%; font-weight: bold; color: #FFB163; background-image: url(templates/subSilver/images/cellpic3.gif) }
TD.tablebg	{ background-color: #000000 }
TD.cat		{ background-color: {T_TH_COLOR1}; height: 28px; background-image: url(templates/subSilver/images/cellpic1.gif) }
TD.row1		{ background-color: {T_TR_COLOR1} }
TD.row2		{ background-color: {T_TR_COLOR2} }
TD.row3		{ background-color: {T_TR_COLOR3} }
TD.spaceRow { background-color: {T_TR_COLOR3}; border: #FFFFFF; border-style: solid; border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }
TD.rowpic	{ background-color: #FFFFFF; background-image: url(templates/subSilver/images/cellpic2.jpg); background-repeat: repeat-y }
td.icqback	{ background-image: url(templates/subSilver/images/icon_icq_add.gif); background-repeat: no-repeat }


/* Setting additional nice borders for the main table cells */
TD.catHead,TD.catSides,TD.catLeft,TD.catRight,TD.catBottom { background-color:{T_TH_COLOR1}; height: 28px; background-image: url(templates/subSilver/images/cellpic1.gif); border: #FFFFFF; border-style: solid; }	

TD.catHead	 { height: 29px; border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 0px }	
TD.catSides  { border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }	
TD.catLeft	 { border-left-width: 1px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px }	
TD.catRight	 { border-left-width: 0px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }	
TD.catBottom { height: 29px; border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 1px }	

TH.thHead,TH.thSides,TH.thTop,TH.thLeft,TH.thRight,TH.thBottom,TH.thCornerL,TH.thCornerR { border: #FFFFFF; border-style: solid; }

TH.thHead	 { font-weight : bold; font-size: 12px; height: 25px; border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 0px }	
TH.thSides	 { border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }	
TH.thTop	 { border-left-width: 0px; border-top-width: 1px; border-right-width: 0px; border-bottom-width: 0px }	
TH.thLeft	 { border-left-width: 1px; border-top-width: 0px; border-right-width: 0px; border-bottom-width: 0px }	
TH.thRight	 { border-left-width: 0px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }	
TH.thBottom  { border-left-width: 1px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 1px }	
TH.thCornerL { border-left-width: 1px; border-top-width: 1px; border-right-width: 0px; border-bottom-width: 0px }	
TH.thCornerR { border-left-width: 0px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 0px }	

TD.row3Right	 { background-color: {T_TR_COLOR3}; border: #FFFFFF; border-style: solid;  border-left-width: 0px; border-top-width: 0px; border-right-width: 1px; border-bottom-width: 0px }

/* The largest text used in the index page title and toptic title etc. */
.maintitle	{ font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; font-size : 22px; font-weight : bold; text-decoration : none; line-height : 120%; color : #000000;}


/* General normal text */
.gen		{ font-size : 12px; color : #000000; }
a.gen		{ color: #006699; text-decoration: none; }
a.gen:hover	{ color: #C23030; text-decoration: underline; }

/* General medium text */
.genmed		{ font-size : 11px; color : #000000; }
a.genmed		{ text-decoration: none; color : #006699; }
a.genmed:hover	{ text-decoration: underline; color : #C23030; }


/* General small */
.gensmall		{ font-size : 10px; color : #000000; }
a.gensmall		{ color: #006699; text-decoration: none; }
a.gensmall:hover	{ color: #C23030; text-decoration: underline; }


/* The register, login, search etc links at the top of the page */
.mainmenu			{ font-size : 11px; text-decoration : none; color : #000000 }
a.mainmenu			{ text-decoration: none; color : #006699;  }
a.mainmenu:hover	{ text-decoration: underline; color : #C23030; }


/* Forum categories */
.cattitle			{ font-size : 12px; line-height : 100%; letter-spacing: 1px; font-weight : bold; text-decoration : none; color : #004c75 }
a.cattitle			{ text-decoration: none; color : #004c75; }
a.cattitle:hover	{ text-decoration: underline; }


/* Forum title: Text and link to the forums used in: index.php */
.forumlink			{ font-size : 12px; font-weight : bold; text-decoration : none; color : #136C99; }
a.forumlink			{ text-decoration: none; color : #136C99; }
a.forumlink:hover	{ text-decoration: underline; color : #D68000; }


/* Used for the navigation text, (Page 1,2,3 etc) and the navigation bar when in a forum */
.nav			{ font-size : 11px; font-weight : bold; text-decoration : none; color : #000000;}
a.nav			{ text-decoration: none; color : #006699; }
a.nav:hover		{ text-decoration: underline; }


/* titles for the topics: can specify viewed link colour too */
.topictitle			{ font-size : 11px; font-weight : bold; text-decoration : none; color : #000000; }
a.topictitle		{ text-decoration: none; color : #006699; }
a.topictitle:hover	{ text-decoration: underline; color : #D68000; }
a.topictitle:visited	{ text-decoration: none; color : #5584AA; }


/* Name of poster in viewmsg.php and viewtopic.php and other places */
.name			{ font-size : 11px; text-decoration : none; color : #000000;}
a.name			{ color: #006699; text-decoration: none;}
a.name:hover	{ color: #C23030; text-decoration: underline;}


/* Location, number of posts, post date etc */
.postdetails		{ font-size : 10px; color : #000000; }
a.postdetails		{ color: #006699; text-decoration: none; }
a.postdetails:hover	{ color: #C23030; text-decoration: underline; }


/* The content of the posts (body of text) */
.postbody { font-size : 12px; line-height: 150%}

a.postlink	{ text-decoration: none; color : {T_BODY_LINK} }
a.postlink:hover { text-decoration: underline; color : #C23030 }


/* Quote Code (currently not used) */
.code	{ font-family: Courier, Courier New; font-size: 11px; color: #006600;
		  background-color: #FAFAFA; border: {T_TR_COLOR3}; border-style: solid;
		  border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
		}
.quote	{ font-family: Verdana, Arial; font-size: 11px; color: #444444; line-height: 125%;
		  background-color: #FAFAFA; border: {T_TR_COLOR3}; border-style: solid;
		  border-left-width: 1px; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px
		}
.signature	{ font-size : 11px; text-decoration : none; line-height : 150%; color : #333366; }
.editedby	{ font-size : 10px; line-height : 100%; color : #333333; }


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
background-color : {T_TR_COLOR1}; 
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

.helpline { background-color: {T_TR_COLOR2}; border-style: none; }


/* Copyright and bottom info */
.copyright		{ font-family: Verdana, Arial, Helvetica, sans-serif; color: #555555; font-size: 10px; letter-spacing: -1px;}
a.copyright		{ color: #333333; text-decoration: none;}
a.copyright:hover { color: #000000; text-decoration: underline;}
-->
</style>
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
		  <span class="gen">The development pad for subBlue design<br />
			  &nbsp; </span> 
			  
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
<br />
