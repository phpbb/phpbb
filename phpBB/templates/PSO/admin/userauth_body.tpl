<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
            "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>phpBB - auth testing</title>
<style type="text/css">
<!--
	P {font-family:Verdana,serif;font-size:8pt}

	H1 {font-family:Arial,Helvetica,sans-serif;font-size:14pt;}
	H2 {font-family:Arial,Helvetica,sans-serif;font-size:12pt;}
	H3 {font-family:Arial,Helvetica,sans-serif;font-size:10pt;}

	TH {background-color:#CCCCCC;font-family:Verdana,serif;font-size:8pt}
	TD {font-family:Verdana,serif;font-size:8pt}

	TD.row1 {background-color:#DDDDDD}
	TD.row2 {background-color:#EEEEEE}

	TD.row1authuser {background-color:#FF8888;text-align:center}
	TD.row2authuser {background-color:#EE8888;text-align:center}

	TD.row1authgroup {background-color:#77FF77;text-align:center}
	TD.row2authgroup {background-color:#66EE66;text-align:center}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1>User Authorisation Control</h1>

<h2>Username: {USERNAME}</h2>
<p>This user is {USERTYPE} and {USER_GROUP_LIST}

<form method="post" action="{S_USER_AUTH_ACTION}">
<input type="checkbox" name="makeadmin" value="1"{S_ADMIN_CHECK_SELECTED}> Checked if user should be an Administrator<br> 
<input type="checkbox" name="makesupermod" value="1"{S_SUPERMOD_CHECK_SELECTED}> Checked if user should be a Super Moderator</p>

<h3>Access to Forums</h3>

<p>The Min Auth Reqd. field indicates the minimum authorisation rights required to carry out one or more basic forum operation, eg. view, read, post or reply. The colour coded rows in the table whether access is granted by the user or group rights, if access is granted by group rights then you should visit the Group Auth Admin page to alter it.</p>

<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="30%">Forum Name</th>
		<th>Min Auth Reqd.</th>
		<th>Simple Access Control</th>
		<th>Moderator</th>
	</tr>
	<!-- BEGIN forums -->
	<tr>
		<td class="{forums.ROW_CLASS}" align="center">{forums.FORUM_NAME}</td>
		<td class="{forums.ROW_CLASS}" align="center">{forums.MIN_AUTH}</td>
		<td class="{forums.AUTH_TYPE_ACL}">{forums.SELECT_GRANT_LIST}</td>
		<td class="{forums.AUTH_TYPE_MOD}">{forums.SELECT_MOD_LIST}</td>
	</tr>
	<!-- END forums -->
	<tr>
		<td colspan="4" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Request Update">&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset Changes"></td>
	</tr>
</table></div>

</form>

<div align="center"><p>The Min Auth Reqd. field indicates the minimum authorisation rights required to carry out one or more basic forum operation, eg. view, read or post. The colour coded rows in the table whether access is granted by the user or group rights.</p>

<table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<td class="row1authuser">Access rights are granted by User Auth Control</td>
	</tr>
	<tr>
		<td class="row1authgroup">Access rights are granted by Group Auth Control</td>
	</tr>
</table></div>

<br clear="all">

<center>
<p><a href="forumauth.php">Forum Authorisation Admin</a></p>

<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>
<br clear="all">
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>