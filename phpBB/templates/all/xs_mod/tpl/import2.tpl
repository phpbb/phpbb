<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id import2.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<form action="{FORM_ACTION}" method="post">{S_RETURN}<input type="hidden" name="import" value="{IMPORT_FILENAME}" />
<table class="forumline">
<tr><th colspan="2">{L_XS_IMPORT_TPL}</th></tr>
<tr><td class="row1" align="left" colspan="2"><span class="gensmall">{L_XS_IMPORT_TPL_COMMENT}<br /><br />{L_XS_IMPORT_TPL_FILENAME} {STYLE_FILENAME}<br />{L_XS_IMPORT_TPL_TPLNAME} {STYLE_TEMPLATE}<br />{L_XS_IMPORT_TPL_COMMENT2} {STYLE_COMMENT}<br /></span></td></tr>
<!-- BEGIN switch_select_style -->
<tr>
	<td class="row1"><span class="gen">{L_XS_IMPORT_SELECT_STYLES}</span></td>
	<td class="row2 tdnw">
		<table class="p2px">
		<!-- BEGIN style -->
		<tr>
			<td class="tdnw"><span class="gen"><label><input type="checkbox" name="import_install_{switch_select_style.style.NUM}" checked="checked" /> {switch_select_style.style.NAME}</label></span></td>
			<td class="tdnw"><span class="gen">&nbsp;&nbsp;&nbsp;&nbsp;(<label><input type="radio" name="import_default" value="{switch_select_style.style.NUM}" /> {L_XS_IMPORT_INSTALL_DEF_LC}</label>)</span></td>
		</tr>
		<!-- END style -->
		</table>
	</td>
</tr>
<!-- END switch_select_style -->
<!-- BEGIN switch_select_nostyle -->
<tr>
	<td class="row1"><span class="gen">{L_XS_IMPORT_INSTALL_STYLE}</span></td>
	<td class="row2 tdnw"><span class="gen"><label><input type="checkbox" name="import_install_0" /> {STYLE_NAME}</label> &nbsp;&nbsp;&nbsp;&nbsp;(<label><input type="radio" name="import_default_0" /> {L_XS_IMPORT_INSTALL_DEF_LC}</label>)</span></td>
</tr>
<!-- END switch_select_nostyle -->
<input type="hidden" name="total" value="{TOTAL}" />
<tr>
	<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="submit" value="{L_XS_IMPORT}" class="mainoption" /></td>
</tr>
</table>
</form>
