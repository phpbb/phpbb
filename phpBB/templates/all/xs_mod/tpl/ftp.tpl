<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id ftp.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_FTP_TITLE}</h1>

<!-- BEGIN xs_ftp_local -->
<p>{L_XS_FTP_COMMENT1}</p>
<!-- END xs_ftp_local -->
<!-- BEGIN xs_ftp_nolocal -->
<p>{L_XS_FTP_COMMENT2}</p>
<!-- END xs_ftp_nolocal -->


<!-- BEGIN error -->
<table class="forumline">
	<tr>
		<th class="th25px">{L_ERROR}</th>
	</tr>
	<tr> 
		<td class="row1"><table class="p2px">
			<tr> 
				<td>&nbsp;</td>
			</tr>
			<tr> 
				<td class="tdalignc"><span class="gen">{error.MSG}</span></td>
			</tr>
			<tr> 
				<td>&nbsp;</td>
			</tr>
		</table></td>
	</tr>
</table>
<!-- END error -->

<form name="ftp" action="{FORM_ACTION}" method="post">{S_HIDDEN_FIELDS}{S_EXTRA_FIELDS}<table class="forumline">
	<!-- BEGIN xs_ftp_local -->
	<tr>
		<th colspan="2">{L_XS_FTP_CONFIG}</td>
	</tr>
	<tr>
		<td class="row1">{L_XS_FTP_SELECT_METHOD}:</td>
		<td class="row2 tdnw">
			<input type="radio" name="xs_ftp_local" value="1" /> {L_XS_FTP_SELECT_LOCAL}<br />
			<input type="radio" name="xs_ftp_local" value="" checked="checked" /> {L_XS_FTP_SELECT_FTP}<br />
		</td>
	</tr>
	<tr>
		<th colspan="2">{L_XS_FTP_SETTINGS}</th>
	</tr>
	<!-- END xs_ftp_local -->
	<!-- BEGIN xs_ftp_nolocal -->
	<input type="hidden" name="xs_ftp_local" value="" />
	<tr>
		<th colspan="2">{L_XS_FTP_SETTINGS}</th>
	</tr>
	<!-- END xs_ftp_nolocal -->
	<tr>
		<td class="row1">{L_XS_FTP_HOST}{HOST_GUESS}:</td>
		<td class="row2"><input class="post" type="text" name="xs_ftp_host" value="{XS_FTP_HOST}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_XS_FTP_LOGIN}{LOGIN_GUESS}:</td>
		<td class="row2"><input class="post" type="text" name="xs_ftp_login" value="{XS_FTP_LOGIN}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_XS_FTP_PATH}{PATH_GUESS}:</td>
		<td class="row2"><input class="post" type="text" name="xs_ftp_path" value="{XS_FTP_PATH}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_XS_FTP_PASS}:</td>
		<td class="row2"><input class="post" type="text" name="xs_ftp_pass" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /></td>
	</tr>
</table></form>
