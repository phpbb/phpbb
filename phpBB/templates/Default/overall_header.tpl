<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
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
						<td align="left" valign="top"><a href="index.php"><img src="images/title.jpg" height="55" width="450" border="0"></a></td>
						<td align="right">{L_USERNAME} : <input type="text" name="username"><br>{L_PASSWORD} : <input type="password" name="password"><br><input type="submit" name="submit" value="Login"></td>
					</tr></form>
					<tr class="tablebody">
						<td colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td align="left" valign="bottom">{L_WELCOMETO} {SITENAME}<br>{LOGIN_STATUS}</td>
								<td align="right" valign="bottom">[<a href="profile.{PHPEX}?mode=register">{L_REGISTER}</a>]&nbsp;
								[<a href="profile.{PHPEX}?mode=editprofile">{L_PROFILE}</a>]&nbsp;
								[<a href="search.{PHPEX}">{L_SEARCH}</a>]&nbsp;
								[<a href="priv_msg.{PHPEX}?mode=read">{L_PRIVATEMSGS}</a>]&nbsp;
								[<a href="memberlist.{PHPEX}">{L_MEMBERLIST}</a>]&nbsp;
								[<a href="faq.{PHPEX}">{L_FAQ}</a>]</td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
