
<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<tr>
		<th class="cat">phpBB Admin</th>
	</tr>
	<tr>
		<td class="row1"><a href="{U_ADMIN_INDEX}" target="main">{L_ADMIN_INDEX}</a></td>
	</tr>
	<tr>
		<td class="row2"><a href="{U_BOARD_INDEX}" target="_top">{L_BOARD_INDEX}</a></td>
	</tr>
	<!-- BEGIN catrow -->
	<tr>
		<td class="cat" align="center"><span class="cattitle"><b>{catrow.CATNAME}</b></span></td>
	</tr>
	<!-- BEGIN actionrow -->
	<tr>
		<td class="{catrow.actionrow.ROW_CLASS}"><a href="{catrow.actionrow.FILE}"  target="main">{catrow.actionrow.ACTIONNAME}</a></td>
	</tr>
	<!-- END actionrow -->
	<!-- END catrow -->
</table>

</body>
</html>