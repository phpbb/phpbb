<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<!-- The variables that are available in this file are:
     SITENAME - Site name set in admin panel
     PAGE_TITLE - Specific title of the page, 'Forum Index', 'View Topic' etc etc.
     USERNAME - The current logged in users username (if the user is logged in)
     USERID - The current logged in users ID number (if the user is logged in)
//-->		 
<head>
<title>{SITENAME} - {PAGE_TITLE}</title>
{META_INFO}
<style type="text/css">
<!--
.mainbody {		 
	background: #FFFFFF;
	color: #000000;
	font-family: sans-serif;
	font-size: 8pt;
}
.tableheader {							
	background: #495FA8;
	color: #FFFFFF;
	font-size: 8pt;							
}
.tablebody {							
	font-size: 8pt;
}
.catheader {							
	font-size: 8pt;				
	background: #CCCCCC;
	color: #000000;
}
TD {
	font-size: 8pt;
}
A {						
    text-decoration: none;
} 
A:Hover {
	text-decoration: underline;
}
//-->
</style>
</head>
<body class="mainbody" bgcolor="#FFFFFF" text="#000000">

<table border="0" align="center" width="95%" cellspacing="2">
	<tr>
		<td bgcolor="#000000"><table width="100%" cellpadding="0" cellspacing="1" border="0">
			<tr>
				<td bgcolor="#CCCCCC"><table width="100%" cellspacing="1" cellpadding="3" border="0">
					<tr class="tablebody"><form method="POST" action="login.{PHPEX}">
						<td align="left" valign="top"><img src="images/title.jpg" height="55" width="450"></td>
						<td align="right">Username : <input type="text" name="username"><br>Password : <input type="password" name="password"><br><input type="submit" value="Login"></td>
					</tr></form>
					<tr class="tablebody">
						<td colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td align="left" valign="top">Welcome to {SITENAME}</td>
								<td align="right">[<a href="profile.{PHPEX}?mode=register">Register</a>]&nbsp;[<a href="profile.{PHPEX}?mode=editprofile">Edit Profile</a>]&nbsp;[<a href="search.{PHPEX}">Search</a>]&nbsp;[<a href="priv_msg.{PHPEX}?mode=read">Private Messages</a>]&nbsp;[<a href="memberlist.{PHPEX}">Memberslist</a>]&nbsp;[<a href="faq.{PHPEX}">FAQ</a>]</td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
