<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id update.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_UPDATES}</h1>

<p>{L_XS_UPDATES_COMMENT}</p>

<form action="{UPDATE_URL}" method="post">
<table class="forumline">
<tr>
	<th colspan="4" class="thHead" align="center">{L_XS_UPDATES}</th>
</tr>
<tr>
	<td colspan="4" class="row1" width="100%" align="left">{L_XS_UPDATE_INFO1}</td>
</tr>
<tr>
	<th colspan="2" align="center" nowrap="nowrap">{L_XS_UPDATE_NAME}</th>
	<th class="tdnw">{L_XS_UPDATE_TYPE}</th>
	<th class="tdnw">{L_XS_UPDATE_CURRENT_VERSION}</th>
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
	<td colspan="4" class="row1 tvalignm"><span class="gen">{L_XS_UPDATE_TIMEOUT} <input type="text" name="timeout" value="180" size="6" /></span></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="4" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_XS_UPDATE_CONTINUE}" class="mainoption" /></td>
	</tr>
</table>
</form>

<br />
