<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id cache.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_MANAGE_CACHE}</h1>

<p>
{L_XS_MANAGE_CACHE_EXPLAIN2}
{RESULT}
</p>

<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr>
	<th colspan="4">{L_XS_MANAGE_CACHE}</th>
</tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_TEMPLATE}</span></td>
	<td class="cat tdalignc"><span class="gen">{L_XS_STYLES}</span></td>
	<td class="cat tdalignc"><span class="gen"><span class="gen"><a href="{U_CLEAR_ALL}">{L_XS_CLEAR_ALL_LC}</a></span></td>
	<td class="catRight" align="center"><span class="gen"><span class="gen"><a href="{U_COMPILE_ALL}" onclick="return confirm('{L_XS_CACHE_CONFIRM}'); ">{L_XS_COMPILE_ALL_LC}</a></span></td>
</tr>
<!-- BEGIN styles -->
<tr> 
	<td class="{styles.ROW_CLASS}" align="left" valign="middle"><span class="gen">{styles.TPL}</span></td>
	<td class="{styles.ROW_CLASS}" align="left" valign="middle"><span class="gen">{styles.STYLES}</span></td>
	<td class="{styles.ROW_CLASS}" align="center" valign="middle" nowrap="nowrap"><span class="gen"><a href="{styles.U_CLEAR}">{L_XS_CLEAR_CACHE_LC}</a></span></td>
	<td class="{styles.ROW_CLASS}" align="center" valign="middle" nowrap="nowrap"><span class="gen"><a href="{styles.U_COMPILE}">{L_XS_COMPILE_CACHE_LC}</a></span></td>
</tr>
<!-- END styles -->
</table>
<br />