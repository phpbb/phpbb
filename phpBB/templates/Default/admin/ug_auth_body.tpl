<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>phpBB - auth testing</title>
<script language="JavaScript" type="text/javascript">
<!--
function open_new_window(strURL){
	weblinkswin = window.open(strURL, "_weblinks", "LEFT=2,HEIGHT=400,resizable=yes,scrollbars=yes,TOP=2,WIDTH=740");
}
//-->
</script>
<style type="text/css">
<!--
	P {font-family:Verdana,serif;font-size:8pt}

	H1 {font-family:Arial,Helvetica,sans-serif;font-size:14pt;text-align:center}
	H2 {font-family:Arial,Helvetica,sans-serif;font-size:12pt;}
	H3 {font-family:Arial,Helvetica,sans-serif;font-size:10pt;}

	TH {background-color:#CCCCCC;font-family:Verdana,serif;font-size:8pt}
	TD {font-family:Verdana,serif;font-size:8pt}

	TD.row1 {background-color:#DDDDDD}
	TD.row2 {background-color:#EEEEEE}

	TD.row1authuser {background-color:#FF8888}
	TD.row2authuser {background-color:#EE8888}

	TD.row1authgroup {background-color:#77FF77}
	TD.row2authgroup {background-color:#66EE66}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1>{L_USER_OR_GROUP} Authorisation Control</h1>

<form method="post" action="{S_USER_AUTH_ACTION}">

<h2>{L_USER_OR_GROUPNAME}: {USERNAME}</h2>

<p>{USER_GROUP_MEMBERSHIPS}</p>

<h3>Access to Forums</h3>

<p>Remember that there are two possible places for controlling access to forums, user and group auth control. Removing access rights from a user will not affect any rights granted via group membership. You will be warned if you remove access rights from a user (or group) but access is still granted via membership of a group (or via individual user rights)</p>

<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="30%">Forum Name</th>
		<th>Simple Access Control</th>
		<th>Moderator</th>
	</tr>
	<!-- BEGIN forums -->
	<tr>
		<td class="{forums.ROW_CLASS}" align="center"><a href="{forums.U_FORUM_AUTH}" onClick="open_new_window('{forums.U_FORUM_AUTH}');return false" target="_new">{forums.FORUM_NAME}</a></td>
		<td class="{forums.ROW_CLASS}" align="center">{forums.S_ACL_SELECT}</td>
		<td class="{forums.ROW_CLASS}" align="center">{forums.S_MOD_SELECT}</td>
	</tr>
	<!-- END forums -->
	<tr>
		<td colspan="4" align="center"><br clear="all">
{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Request Update">&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset Changes"></td>
	</tr>
</table></div>

</form>

<center>

<p><a href="{U_USER_OR_GROUP}">{L_USER_OR_GROUP} Authorisation Admin</a></p>
<p><a href="{U_FORUMAUTH}">Forum Authorisation Admin</a></p>

<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>

<br clear="all">

<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>