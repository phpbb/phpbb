<!-- DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"  />
<meta http-equiv="Content-Style-Type" content="text/css" />

<title>{L_INSTALLATION}</title>
<style type="text/css">
<!--
/* subSilver Theme for phpBB2
 * Created by subBlue design
 * http://www.subBlue.com
 */

body { 	background-color:#E5E5E5;
		scrollbar-face-color: #C8D1D7; scrollbar-highlight-color: #EAF0F7;
		scrollbar-shadow-color: #95AFC4; scrollbar-3dlight-color: #D6DDE2;
		scrollbar-arrow-color:  #006699; scrollbar-track-color: #EFEFEF;
		scrollbar-darkshadow-color: #7294AF;
}

font	{ font-family: Verdana, Arial, Helvetica, sans-serif }
td		{ font-family: Verdana, Arial, Helvetica, sans-serif }
th		{ font-family: Verdana, Arial, Helvetica, sans-serif }
P		{ font-family: Verdana, Arial, Helvetica, sans-serif }
hr		{ height: 1px; color:#c2cdd6 }

/* Forum colours */
.bodyline	{ background-color:#FFFFFF; border: #AEBDC4; border-style: solid; border-top-width: 1px; border-right-width: 1px; border-bottom-width: 1px; border-left-width: 1px }
.forumline	{ background-color:#FFFFFF; border: 2px #006699 solid }

/* Main table cell colours and backgrounds */
TH			{ background-color: #1B7CAD; height: 25px; font-size: 11px; line-height : 100%; font-weight: bold; color: #FFB163; background-image: url(templates/subSilver/images/cellpic3.gif) }
TD.cat		{ background-color: #CBD3D9; height: 28px; background-image: url(templates/subSilver/images/cellpic1.gif) }
TD.row1		{ background-color: #EFEFEF }
TD.row2		{ background-color: #DEE3E7 }

/* General normal text */
.gen		{ font-size : 12px; color : #000000; }
a.gen		{ color: #006699; text-decoration: none; }
a:hover.gen	{ color: #C23030; text-decoration: underline; }

/* General small */
.gensmall		{ font-size : 10px; color : #000000; }
a.gensmall		{ color: #006699; text-decoration: none; }
a:hover.gensmall	{ color: #C23030; text-decoration: underline; }

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

.copyright		{ font-family: Verdana, Arial, Helvetica, sans-serif; color: #555555; font-size: 10px; letter-spacing: -1px;}
a.copyright		{ color: #333333; text-decoration: none;}
a.copyright:hover { color: #000000; text-decoration: underline;}

.maintitle	{ font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif; font-size : 22px; font-weight : bold; text-decoration : none; line-height : 120%; color : #000000;}

-->
</style>
</head>

<body bgcolor="#E5E5E5" text="#000000" link="#006699" vlink="#5584AA">

<table width="100%" border="0" cellspacing="0" cellpadding="10" align="center"> 
	<tr>
		<td class="bodyline" width="100%"><table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td><table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td><img src="templates/subSilver/images/logo_phpBB.gif" border="0" alt="Forum Home" vspace="1" /></td>
						<td align="center" width="100%" valign="middle"><span class="maintitle">{L_INSTALLATION}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><br /><br /></td>
			</tr>
			<tr>
				<td colspan="2"><table width="90%" border="0" align="center" cellspacing="0" cellpadding="0">
					<tr>
						<td><span class="gen">{L_INSTRUCTION_TEXT}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><br /><br /></td>
			</tr>
			<tr>
				<td width="100%"><form action="{S_FORM_ACTION}" name="install_form" method="post"><table width="100%" cellpadding="2" cellspacing="1" border="0" class="forumline">
					<!-- BEGIN switch_stage_one_install -->
					<tr>
						<th colspan="2">{L_INITIAL_CONFIGURATION}</th>
					</tr>
					<tr>
						<td class="row1" align="right" width="30%"><span class="gen">{L_LANGUAGE}: </span></td>
						<td class="row2">{S_LANG_SELECT}</td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DBMS}: </span></td>
						<td class="row2">{S_DBMS_SELECT}</td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_UPGRADE}:</span></td>
						<td class="row2">{S_UPGRADE_SELECT}</td>
					</tr>
					<!-- tr>
						<td class="row1" align="right"><span class="gen">{L_DOMAIN_NAME}: </span></td>
						<td class="row2"><input type="text" name="cookiedomain" value="{COOKIE_DOMAIN}" /></td>
					</tr -->
					<tr>
						<th colspan="2">{L_DATABASE_CONFIGURATION}</th>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DB_HOST}: </span></td>
						<td class="row2"><input type="text" name="dbhost" value="{DB_HOST}" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DB_NAME}: </span></td>
						<td class="row2"><input type="text" name="dbname" value="{DB_NAME}" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DB_USER}: </span></td>
						<td class="row2"><input type="text" name="dbuser" value="{DB_USER}" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DB_PASSWORD}: </span></td>
						<td class="row2"><input type="password" name="dbpasswd" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_DB_PREFIX}: </span></td>
						<td class="row2"><input type="text" name="prefix" value="{DB_PREFIX}" /></td>
					</tr>
					<tr>
						<th colspan="2">{L_ADMIN_CONFIGURATION}</th>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_ADMIN_USERNAME}: </span></td>
						<td class="row2"><input type="text" name="admin_name" value="{ADMIN_USERNAME}" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_ADMIN_PASSWORD}: </span></td>
						<td class="row2"><input type="password" name="admin_pass1" /></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_ADMIN_CONFIRM_PASSWORD}: </span></td>
						<td class="row2"><input type="password" name="admin_pass2" /></td>
					</tr>
					<!-- END switch_stage_one_install -->
					<!-- BEGIN switch_error_install -->
					<tr>
						<th>{L_ERROR_TITLE}</th>
					</tr>
					<tr>
						<td class="row1" align="center"><span class="gen">{L_ERROR}</span></td>
					</tr>
					<!-- END switch_error_install -->
					<!-- BEGIN switch_ftp_file -->
					<tr>
						<th colspan="2">{L_FTP_INFO}</th>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_FTP_PATH}</span></td>
						<td class="row2"><input type="text" name="ftp_dir"></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_FTP_USER}</span></td>
						<td class="row2"><input type="text" name="ftp_user"></td>
					</tr>
					<tr>
						<td class="row1" align="right"><span class="gen">{L_FTP_PASS}</span></td>
						<td class="row2"><input type="password" name="ftp_pass"></td>
					</tr>
					<!-- END switch_ftp_file -->
					<!-- BEGIN switch_ftp_option -->
					<tr>
						<th colspan="2">{L_CHOOSE_FTP}</th>
					</tr>
					<tr>
						<td class="row1" align="right" width="50%"><span class="gen">{L_ATTEMPT_FTP}</span></td>
						<td class="row2"><input type="radio" name="send_file" value="2"></td>
					</tr>
					<tr>
						<td class="row1" align="right" width="50%"><span class="gen">{L_SEND_FILE}</span></td>
						<td class="row2"><input type="radio" name="send_file" value="1"></td>
					</tr>
					<!-- END switch_ftp_option -->
					<!-- BEGIN switch_common_install -->
					<tr> 
					  <td class="cat" align="center" colspan="2">{S_HIDDEN_FIELDS}<input class="mainoption" type="submit" value="{L_SUBMIT}" /></td>
					</tr>
					<!-- END switch_common_install -->
				</table></form></td>
			</tr>
		</table></td>
	</tr>
</table>

</body>
</html>
