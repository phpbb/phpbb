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

	TH {font-family:Verdana,serif;font-size:8pt}
	TD {font-family:Verdana,serif;font-size:8pt}

	SELECT.small	{width:140px;font-family:"Courier New",courier;font-size:8pt;}
	INPUT.text		{font-family:"Courier New",courier;font-size:8pt;}
//-->
</style>
</head>
<body bgcolor="#FFFFFF" text="#000000">

<div align="center"><h1>{L_USER_OR_GROUP} Authorisation Control</h1>

<table cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th bgcolor="#CCCCCC">Select a {L_USER_OR_GROUP}</th>
	</tr>
	<tr><form method="get" action="{S_USERAUTH_ACTION}">
		<td bgcolor="#DDDDDD" align="center">{S_USERS_SELECT}&nbsp;&nbsp;<input type="submit" value="Look up User">&nbsp;</td>
	</form></tr>
</table></div>

<center>
<p><a href="{U_FORUMAUTH}">Forum Authorisation Admin</a></p>

<font face="Verdana,serif" size="1">Powered By <a href="http://www.phpbb.com/" target="_phpbb">phpBB 2.0</a></font>
<br clear="all">
<font face="Verdana,serif" size="1">
Copyright &copy; 2001 phpBB Group, All Rights Reserved</font>
<br>

</body>
</html>
