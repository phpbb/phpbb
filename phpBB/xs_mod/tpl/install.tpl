<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                               install.tpl
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

<h1>{L_XS_INSTALL_STYLES}</h1>

<p>{L_XS_INSTALL_STYLES_EXPLAIN2}</p>

<form action="{U_ACTION}" method="post" style="display: inline">{S_HIDDEN_FIELDS}<input type="hidden" name="total" value="{TOTAL}" />
<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr>
	<th class="thHead" colspan="4">{L_XS_INSTALL_STYLES}</th>
</tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_TEMPLATE}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_STYLE}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_INSTALL}</span></td>
	<td class="catRight" align="center"><span class="gen">{L_XS_SELECT}</span></td>
</tr>
<!-- BEGIN styles -->
<tr> 
	<td class="{styles.ROW_CLASS}" align="left"><span class="gen">{styles.STYLE}</span></td>
	<td class="{styles.ROW_CLASS}" align="left"><span class="gen">{styles.THEME}</span></td>
	<td class="{styles.ROW_CLASS}" align="center"><span class="gen"><a href="{styles.U_INSTALL}">{L_XS_INSTALL_LC}</a></span></td>
	<td class="{styles.ROW_CLASS}" align="center"><input type="checkbox" name="{styles.CB_NAME}" /><input type="hidden" name="{styles.CB_NAME}_style" value="{styles.STYLE}" /><input type="hidden" name="{styles.CB_NAME}_num" value="{styles.NUM}" /></td>
</tr>
<!-- END styles -->
<tr>
	<td class="catBottom" colspan="4" align="center"><input type="submit" name="submit" value="{L_XS_INSTALL}" class="mainoption" /></td>
</tr>
</table>
</form>
<br />