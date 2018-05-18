<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                              edit_file.tpl
 *                              -------------
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

<script language="JavaScript" type="text/javascript">
<!--

function xs_replace()
{
	var old = document.edit.content.value;
	var search = document.edit.edit_replace1.value;
	var replace = document.edit.edit_replace2.value;
	var data = '';
	var pos = old.indexOf(search);
	if(pos == -1)
	{
		alert('{L_XS_FILEEDIT_SEARCH_NOMATCH}');
	}
	else
	{
		data = old.substring(0, pos) + replace + old.substring(pos + search.length, old.length);
		document.edit.edit_replace3.value = old;
		document.edit.content.value = data;
		alert('{L_XS_FILEEDIT_SEARCH_MATCH1}');
	}
}

function xs_replace_all()
{
	var old = document.edit.content.value;
	var search = document.edit.edit_replace1.value;
	var replace = document.edit.edit_replace2.value;
	var data = '';
	var pos = old.indexOf(search);
	var count = 0;
	var backup = old;
	if(pos == -1)
	{
		alert('{L_XS_FILEEDIT_SEARCH_NOMATCH}');
	}
	else
	{
		while(pos >= 0)
		{
			data = data + old.substring(0, pos) + replace;
			old = old.substring(pos + search.length, old.length);
			pos = old.indexOf(search);
			count ++;
		}
		data = data + old;
		document.edit.edit_replace3.value = backup;
		document.edit.content.value = data;
		alert('{L_XS_FILEEDIT_SEARCH_MATCHES}');
	}
}

function xs_replace_restore()
{
	var data = document.edit.edit_replace3.value;
	if(data.length < 1)
	{
		alert('{L_XS_FILEEDIT_NOUNDO}');
	}
	else
	{
		document.edit.content.value = data;
		alert('{L_XS_FILEEDIT_UNDO_COMPLETE}');
	}
}

//-->
</script>

<h1>{L_XS_EDIT_TEMPLATES}</h1>

<p>{L_XS_EDIT_TEMPLATE_COMMENT2}</p>

<form action="{U_ACTION}" method="post" enctype="multipart/form-data" name="edit" style="display: inline;">{S_HIDDEN_FIELDS}{S_FILTER}<input type="hidden" name="dir" value="{DIR}" /><input type="hidden" name="edit" value="{FILE}" />
<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center" width="100%">
<tr>
	<th>{L_XS_FILEEDIT_EDIT_NAME} {FULLFILE}</th>
</tr>
<tr>
	<td class="row3" align="left" valign="middle"><span class="genmed">
	{L_XS_FILEEDIT_LOCATION}
	<!-- BEGIN tree -->
	{tree.SEPARATOR}
	<a href="{tree.URL}">{tree.ITEM}</a>
	<!-- END tree -->
	/ <a href="{U_EDIT}">{FILE}</a>
	&nbsp;
	|
	&nbsp;
	[<a href="{U_EDIT}">{L_XS_FILEEDIT_RELOAD_LC}</a>]
	[<a href="{U_DOWNLOAD}">{L_XS_FILEEDIT_DOWNLOAD_LC}</a>]
	</span></td>
</tr>
<tr>
	<td class="row1" align="left"><textarea name="content" cols="120" rows="35" style="width: 100%">{CONTENT}</textarea></td>
</tr>
<tr>
	<td class="row1" align="left"><span class="gen"><label><input type="checkbox" name="trim" checked="checked" /> {L_XS_FILEEDIT_TRIM}</label></span></td>
</tr>
<tr>
	<th>{L_XS_FILEEDIT_FUNCTIONS}</th>
</tr>
<input type="hidden" name="edit_replace3" value="" />
<tr>
	<td class="row1" align="left"><span class="genmed">
	{L_XS_FILEEDIT_REPLACE1}<textarea name="edit_replace1" class="post" cols="20" rows="2"></textarea>{L_XS_FILEEDIT_REPLACE2}<textarea name="edit_replace2" class="post" cols="20" rows="2"></textarea> [<a href="javascript:void(0);" onclick="xs_replace();">{L_XS_FILEEDIT_REPLACE_FIRST_LC}</a>] [<a href="javascript:void(0);" onclick="xs_replace_all();">{L_XS_FILEEDIT_REPLACE_ALL_LC}</a>] [<a href="javascript:void(0)" onclick="xs_replace_restore();">{L_XS_FILEEDIT_REPLACE_UNDO_LC}</a>]<br />
	</span></td>
</tr>
<tr>
	<th>{L_XS_FILEEDIT_BACKUPS}</th>
</tr>
<tr>
	<td class="row1" align="left"><span class="genmed">
	[<a href="{U_BACKUP}">{L_XS_FILEEDIT_BACKUPS_SAVE_LC}</a>]<br />
	<!-- BEGIN backup -->
	{backup.TIME} [<a href="{backup.U_VIEW}">{L_XS_FILEEDIT_BACKUPS_SHOW_LC}</a>] [<a href="{backup.U_RESTORE}">{L_XS_FILEEDIT_BACKUPS_RESTORE_LC}</a>] [<a href="{backup.U_DOWNLOAD}">{L_XS_FILEEDIT_BACKUPS_DOWNLOAD_LC}</a>] [<a href="{backup.U_DELETE}">{L_XS_FILEEDIT_BACKUPS_DELETE_LC}</a>]<br />
	<!-- END backup -->
	</span></td>
</tr>
<tr>
	<th>{L_XS_FILEEDIT_UPLOAD}</th>
</tr>
<tr>
	<td class="row1" align="left" valign="middle"><span class="genmed">
	{L_XS_FILEEDIT_UPLOAD_FILE} <input type="file" name="upload" class="post" />
	</span></td>
</tr>
<tr>
	<td class="catBottom" align="center"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /> <input type="reset" name="reset" value="{L_RESET}" class="liteoption" /></td>
</tr>
</table>
</form>
<br />
