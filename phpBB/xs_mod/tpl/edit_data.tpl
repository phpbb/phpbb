<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                              edit_data.tpl
 *                              -------------
 *   copyright            : (C) 2003 - 2005 Vjacheslav Trushkin
 *   support              : http://www.stsoftware.biz/forum
 *
 *   version              : 2.4.0
 *
 *   file revision        : 79
 *   project revision     : 83
 *   last modified        : 12 Mar 2007  10:28:53
 *
 ***************************************************************************/
<!-- END xs_file_version -->

<h1>{L_XS_EDIT_STYLES_DATA}</h1>

<p>{L_XS_EDITDATA_EXPLAIN}</p>

<form action="{U_ACTION}" method="post">{S_HIDDEN_FIELDS}<input type="hidden" name="edit" value="{ID}" />
<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr>
	<th class="thHead" colspan="3">{L_XS_EDIT_STYLES_DATA}</th>
</tr>
<tr>
		<td class="catLeft" align="center"><span class="gen">{L_XS_EDITDATA_VAR}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_EDITDATA_VALUE}</span></td>
	<td class="catRight" align="center"><span class="gen">{L_XS_EDITDATA_COMMENT}</span></td>
</tr>
<!-- BEGIN row -->
<tr> 
	<td class="{row.ROW_CLASS}" align="left" width="40%"><span class="gen">{row.TEXT}:</span><!-- IF row.EXPLAIN --><span class="gensmall"><br />{row.EXPLAIN}</span><!-- ENDIF --></td>
	<td class="{row.ROW_CLASS}" align="left"><input type="text" class="post" name="edit_{row.VAR}" maxlength="{row.LEN}" size="{row.SIZE}" value="{row.VALUE}" /></td>
	<!-- BEGIN name -->
	<td class="{row.ROW_CLASS}" align="left"><input type="text" class="post" name="name_{row.VAR}" maxlength="50" value="{row.name.DATA}" size="50" title="{row.name.DATA}" /></td>
	<!-- END name -->
	<!-- BEGIN noname -->
	<td class="{row.ROW_CLASS}"><span class="gen">&nbsp;</span></td>
	<!-- END noname -->
</tr>
<!-- END row -->
<tr>
	<td class="catBottom" colspan="3" align="center"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" /> <input type="reset" name="reset" value="{L_RESET}" class="liteoption" /></td>
</tr>
</table>
</form>
<br />