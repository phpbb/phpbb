<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                                update.tpl
 *                                ----------
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

<h1>{L_XS_UPDATES}</h1>

<p>{L_XS_UPDATES_COMMENT}</p>

<form action="{UPDATE_URL}" method="post">
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
<tr>
	<th colspan="4" class="thHead" align="center">{L_XS_UPDATES}</th>
</tr>
<tr>
	<td colspan="4" class="row1" width="100%" align="left">{L_XS_UPDATE_INFO1}</td>
</tr>
<tr>
	<th class="thCornerL" colspan="2" align="center" nowrap="nowrap">{L_XS_UPDATE_NAME}</th>
	<th class="thTop" align="center" nowrap="nowrap">{L_XS_UPDATE_TYPE}</th>
	<th class="thCornerR" align="center" nowrap="nowrap">{L_XS_UPDATE_CURRENT_VERSION}</th>
</tr>
<!-- BEGIN row -->
<input type="hidden" name="{row.VAR}item" value="{row.ITEM}" />
<tr>
	<td class="{row.ROW_CLASS}"><!-- BEGIN url --><input type="checkbox" name="{row.VAR}checked" checked="checked" /><!-- END url --><!-- BEGIN nourl --><input type="hidden" name="{row.VAR}checked" value="0" /><!-- END nourl --></td>
	<td class="{row.ROW_CLASS}" width="100%"><span class="gen">{row.NAME}</span></td>
	<td class="{row.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">{row.TYPE}</span></td>
	<td class="{row.ROW_CLASS}" align="center" nowrap="nowrap"><span class="gen">{row.VERSION}</span></td>
</tr>
<!-- END row -->
<tr>
	<td colspan="4" class="row1" align="left" valign="middle"><span class="gen">{L_XS_UPDATE_TIMEOUT} <input type="text" name="timeout" value="180" size="6" /></span></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="4" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_XS_UPDATE_CONTINUE}" class="mainoption" /></td>
	</tr>
</table>
</form>

<br />
