<!-- BEGIN xs_file_version -->
/**
*
* @package Icy Phoenix eXtreme Style 2.4.1
* @file $Id uninstall.tpl
* @author Vjacheslav Trushkin
* @copyright (C) 2003 - 2007
* @support http://www.stsoftware.biz/forum
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/
<!-- END xs_file_version -->

<h1>{L_XS_UNINSTALL_STYLES}</h1>

<p>{L_XS_UNINSTALL_STYLES_EXPLAIN2}</p>

<!-- BEGIN left_refresh -->
<script type="text/javascript">
<!--
top.nav.location = top.nav.location; // '{left_refresh.ACTION}';
//-->
</script>
<!-- END left_refresh -->

<!-- BEGIN removed -->
<table class="forumline">
	<tr>
		<th class="th25px">{L_Information}</th>
	</tr>
	<tr>
		<td class="row1"><table class="p2px">
			<tr>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="tdalignc"><span class="gen">{L_XS_STYLE_REMOVED}</span></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table></td>
	</tr>
</table>
<br />
<!-- END removed -->

<table cellpadding="4" cellspacing="1" border="0" class="forumline" align="center">
<tr>
	<th colspan="4">{L_XS_UNINSTALL_STYLES}</th>
</tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_TEMPLATE}</span></td>
	<td class="cat tdalignc"><span class="gen">{L_XS_STYLE}</span></td>
	<td class="cat tdalignc"><span class="gen">{L_XS_UNINSTALL}</span></td>
	<td class="catRight" align="center"><span class="gen">{L_XS_REMOVE_FILES}</span></td>
</tr>
<!-- BEGIN styles -->
<!-- BEGIN item -->
<tr>
	<td class="{styles.ROW_CLASS}" align="left"><span class="gen">{styles.TPL}</span></td>
	<td class="{styles.ROW_CLASS}" align="left"><span class="gen">{styles.item.THEME}</span></td>
	<td class="{styles.ROW_CLASS}" align="center"><span class="gen"><a href="{styles.item.U_DELETE}">{L_XS_UNINSTALL_LC}</a></td>
	<!-- BEGIN delete -->
	<td class="{styles.ROW_CLASS}" align="center"><span class="gen"><a href="{styles.item.delete.U_DELETE}">{L_XS_UNINSTALL2_LC}</a></span></td>
	<!-- END delete -->
	<!-- BEGIN nodelete -->
	<td class="{styles.ROW_CLASS}" align="center"><span class="gen">&nbsp;</td>
	<!-- END nodelete -->
</tr>
<!-- END item -->
<!-- END styles -->
</table>
<br />