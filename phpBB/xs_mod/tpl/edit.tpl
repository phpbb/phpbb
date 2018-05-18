<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                                 edit.tpl
 *                                 --------
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

<h1>{L_XS_EDIT_TEMPLATES}</h1>

<p>{L_XS_EDIT_TEMPLATE_COMMENT1}</p>

<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center" width="100%">
<tr>
	<th colspan="4">{L_XS_FILEMAN_BROWSER}</th>
</tr>
<tr>
	<td class="row3" colspan="4" align="left" valign="middle"><span class="genmed">
	{L_XS_FILEMAN_DIRECTORY}
	<!-- BEGIN tree -->
	{tree.SEPARATOR}
	<a href="{tree.URL}">{tree.ITEM}</a>
	<!-- END tree -->
	</span></td>
</tr>
<!-- BEGIN begin_dirs -->
<tr>
	<td class="row2" colspan="4" align="left"><span class="gensmall">
	{begin_dirs.L_COUNT}<br />
	<!-- BEGIN dir -->
	&raquo; <a href="{begin_dirs.dir.URL}">{begin_dirs.dir.NAME}</a><br />
	<!-- END dir -->
	</span></td>
</tr>
<!-- END begin_dirs -->
<tr>
	<th colspan="4">{L_XS_FILEMAN_FILTER}</th>
</tr>
<tr>
	<td class="row1" colspan="4" align="left"><span class="genmed"><form action="{FILTER_URL}" method="post" style="display: inline;" name="filter">{S_HIDDEN_FIELDS}<input type="hidden" name="dir" value="{FILTER_DIR}" /><input type="hidden" name="filter_update" value="1" />
	<table border="0" cellspacing="0" cellpadding="1">
	<tr>
		<td><span class="genmed">{L_XS_FILEMAN_FILTER_EXT}</span></td>
		<td><input type="text" name="filter_ext" value="{FILTER_EXT}" size="10" class="post" /></td>
	</tr>
	<tr>
		<td><span class="genmed">{L_XS_FILEMAN_FILTER_CONTENT}</span></td>
		<td><textarea name="filter_data" cols="40" rows="3" class="post" />{FILTER_DATA}</textarea></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" class="mainoption" value="{L_SUBMIT}" /> <input type="button" class="liteoption" value="{L_XS_FILEMAN_FILTER_CLEAR}" onclick="document.filter.filter_ext.value=''; document.filter.filter_data.value=''; document.filter.submit();" /></td>
	</tr>
	</table>
	</form></span></td>
</tr>
<!-- BEGIN begin_files -->
<tr>
	<th>{L_XS_FILEMAN_FILENAME}</th>
	<th>{L_XS_FILEMAN_FILESIZE}</th>
	<th>{L_XS_FILEMAN_FILETIME}</th>
	<th>{L_XS_FILEMAN_OPTIONS}</th>
</tr>
<!-- BEGIN file -->
<tr>
	<td class="{begin_files.file.ROW_CLASS}" align="left"><span class="gen"><a href="{begin_files.file.URL}">{begin_files.file.NAME}</a></span></td>
	<td class="{begin_files.file.ROW_CLASS}" align="center"><span class="gen">{begin_files.file.SIZE}</span></td>
	<td class="{begin_files.file.ROW_CLASS}" align="center"><span class="gen">{begin_files.file.TIME}<!-- BEGIN today --> <span class="ast">{L_XS_FILEMAN_TIME_TODAY}</span><!-- END today --></span></td>
	<td class="{begin_files.file.ROW_CLASS}" align="center"><span class="gen"><a href="{begin_files.file.URL}">{L_XS_FILEMAN_EDIT_LC}</a></span></td>
</tr>
<!-- END file -->
<!-- END begin_files -->
</table>

