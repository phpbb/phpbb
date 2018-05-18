<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                               export2.tpl
 *                               -----------
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

<h1>{L_XS_EXPORT_STYLE}</h1>

<p>{L_XS_EXPORT_STYLE_EXPLAIN}</p>

<form action="{FORM_ACTION}" method="post"><input type="hidden" name="export" value="{EXPORT_TEMPLATE}" /><table width="100%" cellpadding="4" cellspacing="1" border="0" align="center" class="forumline">
	<tr>
		<th class="thHead" colspan="2">{L_TITLE}</th>
	</tr>
	<tr>
		<td class="row1">{L_XS_EXPORT_TPL_NAME}:</td>
		<td class="row2"><input type="text" class="post" name="export_template" value="{EXPORT_TEMPLATE}" size="30" /></td>
	</tr>
	<!-- BEGIN switch_select_style -->
	<tr>
		<td class="row1">{L_XS_EXPORT_STYLE_NAMES}:</td>
		<td class="row2" nowrap="nowrap">
			<!-- BEGIN style -->
			<input type="hidden" name="export_style_id_{switch_select_style.style.NUM}" value="{switch_select_style.style.ID}" />
			<input type="checkbox" name="export_style_{switch_select_style.style.NUM}" checked="checked" />
			<input type="text" class="post" name="export_style_name_{switch_select_style.style.NUM}" value="{switch_select_style.style.NAME}" title="{switch_select_style.style.NAME}" size="30" /><br />
			<!-- END style -->
		</td>
	</tr>
	<!-- END switch_select_style -->
	<!-- BEGIN switch_select_nostyle -->
	<tr>
		<td class="row1">{L_XS_EXPORT_STYLE_NAME}:</td>
		<td class="row2" nowrap="nowrap">
			<input type="hidden" name="export_style_id_0" value="{STYLE_ID}" />
			<input type="hidden" name="export_style_0" value="checked" />
			<input type="text" class="post" name="export_style_name_0" value="{STYLE_NAME}" title="{STYLE_NAME}" size="30" />
		</td>
	</tr>
	<!-- END switch_select_nostyle -->
	<tr>
		<td class="row1">{L_XS_EXPORT_STYLE_COMMENT}:</td>
		<td class="row2"><input type="text" class="post" name="export_comment" maxlength="250" size="50" value="" /></td>
	</tr>
	<tr>
		<td class="row1">{L_XS_EXPORT_WHERE}:</td>
		<td class="row2" nowrap="nowrap"><table width="100%" cellspacing="0" cellpadding="1">
		<tr>
			<td colspan="2"><input type="radio" name="export_to" value="save" {SEND_METHOD_SAVE} /> {L_XS_EXPORT_WHERE_DOWNLOAD}</td>
		</tr>
		<tr><td colspan="2"><br /></td></tr>
		<tr>
			<td colspan="2"><input type="radio" name="export_to" value="file" {SEND_METHOD_FILE} /> {L_XS_EXPORT_WHERE_STORE}</td>
		</tr>
		<tr>
			<td width="20%" nowrap="nowrap">&nbsp;&nbsp;{L_XS_EXPORT_WHERE_STORE_DIR}:</td>
			<td width="60%"><input class="post" type="text" name="export_to_dir" value="{SEND_DATA_DIR}" size="30" /></td>
		</tr>
		<tr><td colspan="2"><br /></td></tr>
		<tr>
			<td colspan="2"><input type="radio" name="export_to" value="ftp" {SEND_METHOD_FTP} /> {L_XS_EXPORT_WHERE_FTP}</td>
		</tr>
		<tr>
			<td nowrap="nowrap">&nbsp;&nbsp;{L_XS_FTP_HOST}:</td>
			<td><input class="post" type="text" name="export_to_ftp_host" value="{SEND_DATA_HOST}" size="30" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap">&nbsp;&nbsp;{L_XS_FTP_LOGIN}:</td>
			<td><input class="post" type="text" name="export_to_ftp_login" value="{SEND_DATA_LOGIN}" size="30" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap">&nbsp;&nbsp;{L_XS_FTP_PASS}:</td>
			<td><input class="post" type="text" name="export_to_ftp_pass" value="" size="30" /></td>
		</tr>
		<tr>
			<td nowrap="nowrap">&nbsp;&nbsp;{L_XS_FTP_REMOTEDIR}:</td>
			<td><input class="post" type="text" name="export_to_ftp_dir" value="{SEND_DATA_FTPDIR}" size="30" /></td>
		</tr>
		</table></td>
	</tr>
	<tr>
		<td class="row1">{L_XS_EXPORT_FILENAME}:</td>
		<td class="row2"><input class="post" type="text" name="export_filename" value="{EXPORT_TEMPLATE}.style" size="30" /></td>
	</tr>
	<input type="hidden" name="total" value="{TOTAL}" />
	<tr>
		<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /></td>
	</tr>
</table></form>
