<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id edit_data.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_EDIT_STYLES_DATA}</h1>

<p>{L_XS_EDITDATA_EXPLAIN}</p>

<form action="{U_ACTION}" method="post">{S_HIDDEN_FIELDS}<input type="hidden" name="edit" value="{ID}" />
<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr><th colspan="2">{L_XS_EDIT_STYLES_DATA}</th></tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_EDITDATA_VAR}</span></td>
	<td class="cat tdalignc"><span class="gen">{L_XS_EDITDATA_VALUE}</span></td>
</tr>
<!-- BEGIN row -->
<tr> 
	<td class="{row.ROW_CLASS}" align="left" width="40%"><span class="gen">{row.TEXT}:</span><!-- IF row.EXPLAIN --><span class="gensmall"><br />{row.EXPLAIN}</span><!-- ENDIF --></td>
	<td class="{row.ROW_CLASS}" align="left"><input type="text" class="post" name="edit_{row.VAR}" maxlength="{row.LEN}" size="{row.SIZE}" value="{row.VALUE}" /></td>
</tr>
<!-- END row -->
<tr>
	<td class="catBottom" colspan="2" align="center"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /> <input type="reset" name="reset" value="{L_RESET}" class="liteoption" /></td>
</tr>
</table>
</form>
<br />