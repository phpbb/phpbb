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
<p>This user is {USERTYPE} and {USER_GROUP_LIST}</p>


<h3>Restricted Forums</h3>

<p>These forums need users to be granted specific access for one or more auth fields. Please keep in mind that when you grant access you are giving a user the maximum rights to the forum. So, if this forum has auth fields set for admin only access the user will be made an admin! So think before granting rights!</p>

<div align="center"><table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th width="25%">Forum Name</th>
		<th>Simple Access Control</th>
		<th>Moderator</th>
	</tr>
	<!-- BEGIN restrictedforums -->
	<tr>
		<td class="{restrictedforums.ROW_CLASS}">{restrictedforums.FORUM_NAME}</td>
		<td class="{restrictedforums.ROW_CLASS}">{restrictedforums.SELECT_GRANT_LIST}</td>
		<td class="{restrictedforums.ROW_CLASS}">{restrictedforums.SELECT_MOD_LIST}</td>
	</tr>
	<!-- END restrictedforums -->
</table></div>

<br clear="all">

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