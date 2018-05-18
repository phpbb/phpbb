<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                                 ftp.tpl
 *                                 -------
 *   copyright            : (C) 2003 - 2005 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 79
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:52
 *
 ***************************************************************************/
<!-- END xs_file_version -->

<h1>{L_XS_FTP_TITLE}</h1>

<!-- BEGIN xs_ftp_local -->
<p>{L_XS_FTP_COMMENT1}</p>
<!-- END xs_ftp_local -->
<!-- BEGIN xs_ftp_nolocal -->
<p>{L_XS_FTP_COMMENT2}</p>
<!-- END xs_ftp_nolocal -->


<!-- BEGIN error -->
<table class="forumline" width="100%" cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th class="thHead" height="25">{L_ERROR}</th>
	</tr>
	<tr> 
		<td class="row1"><table width="100%" cellspacing="0" cellpadding="1" border="0">
			<tr> 
				<td>&nbsp;</td>
			</tr>
			<tr> 
				<td align="center"><span class="gen">{error.MSG}</span></td>
			</tr>
			<tr> 
				<td>&nbsp;</td>
			</tr>
		</table></td>
	</tr>
</table>
<!-- END error -->

<form name="ftp" action="{FORM_ACTION}" method="post">{S_HIDDEN_FIELDS}{S_EXTRA_FIELDS}<table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<!-- BEGIN xs_ftp_local -->
	<tr>
		<th class="thHead" colspan="2">{L_XS_FTP_CONFIG}</td>
	</tr>
	<tr>
		<td class="row1">{L_XS_FTP_SELECT_METHOD}:</td>
		<td class="row2" nowrap="nowrap">
			<input type="radio" name="xs_ftp_local" value="1" /> {L_XS_FTP_SELECT_LOCAL}<br />
			<input type="radio" name="xs_ftp_local" value="" checked="checked" /> {L_XS_FTP_SELECT_FTP}<br />
		</td>
	</tr>
	<tr>
		<th class="thHead" colspan="2">{L_XS_FTP_SETTINGS}</th>
	</tr>
	<!-- END xs_ftp_local -->
	<!-- BEGIN xs_ftp_nolocal -->
	<input type="hidden" name="xs_ftp_local" value="" />
	<tr>
		<th class="thHead" colspan="2">{L_XS_FTP_SETTINGS}</th>
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
