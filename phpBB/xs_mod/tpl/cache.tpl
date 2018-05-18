<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                                cache.tpl
 *                                ---------
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

<h1>{L_XS_MANAGE_CACHE}</h1>

<p>
{L_XS_MANAGE_CACHE_EXPLAIN2}
{RESULT}
</p>

<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr>
	<th class="thHead" colspan="4">{L_XS_MANAGE_CACHE}</th>
</tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_TEMPLATE}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_STYLES}</span></td>
	<td class="cat" align="center"><span class="gen"><span class="gen"><a href="{U_CLEAR_ALL}">{L_XS_CLEAR_ALL_LC}</a></span></td>
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