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
.tablefooter {
	background: #C9C9D8;
	color:	#000000;
	font-size:	8pt;
}
.catheader {							
	font-size: 8pt;				
	background: #C5C9CD;
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
					<tr class="tablebody"><form method="post" action="{S_LOGIN_ACTION}">
						<td align="left" valign="top"><a href="{U_INDEX}"><img src="images/title.jpg" height="55" width="450" border="0"></a></td>
						<td align="right">{S_LOGINBOX}</td>
					</tr></form>
					<tr class="tablebody">
						<td colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td align="left" valign="bottom">{L_WELCOMETO} {SITENAME}<br>{LOGIN_STATUS}</td>
								<td align="right" valign="bottom">[<a href="{U_REGISTER}">{L_REGISTER}</a>]&nbsp;
								[<a href="{U_PROFILE}">{L_PROFILE}</a>]&nbsp;
								[<a href="{U_SEARCH}">{L_SEARCH}</a>]&nbsp;
								[<a href="{U_PRIVATEMSGS}">{L_PRIVATEMSGS}</a>]&nbsp;
								[<a href="{U_MEMBERSLIST}">{L_MEMBERLIST}</a>]&nbsp;
								[<a href="{U_FAQ}">{L_FAQ}</a>]</td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
