<!-- BEGIN xs_file_version -->
/***************************************************************************
 *                              uninstall.tpl
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

<h1>{L_XS_UNINSTALL_STYLES}</h1>

<p>{L_XS_UNINSTALL_STYLES_EXPLAIN2}</p>

<!-- BEGIN left_refresh -->
<script language="JavaScript">
<!--
top.nav.location = top.nav.location; // '{left_refresh.ACTION}';
//-->
</script>
<!-- END left_refresh -->

<!-- BEGIN removed -->
<table class="forumline" width="100%" cellspacing="1" cellpadding="4" border="0">
	<tr>
		<th class="thHead" height="25">{L_Information}</th>
	</tr>
	<tr> 
		<td class="row1"><table width="100%" cellspacing="0" cellpadding="1" border="0">
			<tr> 
				<td>&nbsp;</td>
			</tr>
			<tr> 
				<td align="center"><span class="gen">{L_XS_STYLE_REMOVED}</span></td>
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
	<th class="thHead" colspan="4">{L_XS_UNINSTALL_STYLES}</th>
</tr>
<tr>
	<td class="catLeft" align="center"><span class="gen">{L_XS_TEMPLATE}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_STYLE}</span></td>
	<td class="cat" align="center"><span class="gen">{L_XS_UNINSTALL}</span></td>
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