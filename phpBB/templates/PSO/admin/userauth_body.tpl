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

	TD.cell1 {background-color:#DDDDDD}
	TD.cell2 {background-color:#EEEEEE}

	TD.authall {background-color:#EEEEEE;text-align:center}
	TD.authacl {background-color:#DDDDDD;text-align:center}
	TD.authmod {background-color:#CCCCCC;text-align:center}
	TD.authadmin {background-color:#BBBBBB;text-align:center}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<h1>User Authorisation Control</h1>

<p>Remember that users are also granted access via usergroups so be sure to check group auth control when assigning and changing access rights!</p>

<h2>Username: {USERNAME}</h2>
<p>This user is {USERTYPE} and {USER_GROUP_LIST}

<form method="post" action="{S_USER_AUTH_ACTION}">
<input type="checkbox" name="makeadmin" value="1"{S_ADMIN_CHECK_SELECTED}> Checked if user should be an Administrator<br> 
<input type="checkbox" name="makesupermod" value="1"{S_SUPERMOD_CHECK_SELECTED}> Checked if user should be a Super Moderator</p>

<h3>Access to Forums</h3>

<p>The following table lists all forums on you board. Different colour rows indicate different levels of authorisation required for a user to do one or more basic function, eg. view, read, post, reply. By design Administrators have access to and are moderators of every forum (you cannot alter individual settings for Administrators, you must first set them as users by unchecking the box above)</p>

<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="25%">Forum Name</th>
		<th>Simple Access Control</th>
		<th>Moderator</th>
	</tr>
	<!-- BEGIN forums -->
	<tr>
		<td class="{forums.ROW_CLASS}">{forums.FORUM_NAME}</td>
		<td class="{forums.ROW_CLASS}">{forums.SELECT_GRANT_LIST}</td>
		<td class="{forums.ROW_CLASS}">{forums.SELECT_MOD_LIST}</td>
	</tr>
	<!-- END forums -->
	<tr>
		<td colspan="3" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="Request Update">&nbsp;&nbsp;&nbsp;<input type="reset" value="Reset Changes"></td>
	</tr>
</table></div>

</form>

<div align="center"><p>The colour coded rows in the table indicate the access level required to view, read, post or reply in the forum.</p>

<table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<td class="authall">Any or registered users can access this forum</td>
	</tr>
	<tr>
		<td class="authacl">Users must be granted special access</td>
	</tr>
	<tr>
		<td class="authmod">Users must be moderators</td>
	</tr>
	<tr>
		<td class="authadmin">Users must be admins</td>
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