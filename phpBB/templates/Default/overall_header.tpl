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
        TD {						 
				font-size: 8pt;
				}
        A {						
				    text-decoration: none;
						} 
				A:Hover {
     				text-decoration: underline;
						}
     </style>
  </head>
  <body class="mainbody">
<table border="0" align="center" width="95%" cellspacing="2">
<tr>
  <td>	
  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
  <tr>
     <td>
        <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
           <tr class="tablebody">
             <td align="left" valign="top"><img src="images/title.jpg" height="55" width="450"></td>
						 <td align="right" valign="top" width="35%"><form method="POST" action="login.php">Login: <input type="text" name="username" size="10" maxlength="45"> Password: <input type="password" name="password" size="10" maxlength="45"> <input type="submit" value="Submit"></form></td>
           </tr>
           <tr class="tablebody">
 					   <td align="left" valig="top">Welcome to {SITENAME}</td>
             <td align="right" valig="top">
						 [<a href="profile.php?mode=register">Register</a>]&nbsp;
						 [<a href="profile.php?mode=editprofile">Edit Profile</a>]&nbsp;
						 [<a href="search.php">Search</a>]&nbsp;
						 [<a href="priv_msg.php?mode=read">Private Messages</a>]&nbsp;
						 [<a href="memberlist.php">Memberslist</a>]&nbsp;
						 [<a href="faq.php">FAQ</a>]&nbsp;
            </td>
           </tr>
        </table>
     </td>
  </tr>
  </table>	
</td>
</tr>
