<tr>
	<td bgcolor="#000000" align="center"><table width="100%" cellpadding="0" cellspacing="1"  border="0">
		<td>
			<table width="100%" cellpadding="3" cellspacing="1" border="0">
				<tr class="tableheader">
					<td colspan="6" align="right"><a href="{U_VIEW_TOP10}">{L_VIEW_TOP10}</a> | <a href="{U_SORTALPHA}">{L_SORTALPHA}</a></td>
				</tr>
				<tr class="tableheader">
					<td align="center">{L_USERNAME}</td>
					<td align="center">{L_FROM}</td>
					<td align="center">{L_JOINED}</td>
					<td align="center">{L_POSTS}</td>
					<td align="center">{L_EMAIL}</td>
					<td align="center">{L_WEBSITE}</td>
				</tr>
				<!-- BEGIN memberrow -->
				<tr bgcolor="{memberrow.ROW_COLOR}" class="tablebody">
					<td align="center"><a href="{memberrow.U_VIEWPROFILE}">{memberrow.USERNAME}</a></td>
					<td align="center" valign="middle">{memberrow.FROM}</td>
					<td align="center" valign="middle">{memberrow.JOINED}</td>
					<td align="center" valign="middle">{memberrow.POSTS}</td>
					<td align="center" valign="middle">{memberrow.EMAIL}</td>
					<td align="center">{memberrow.WEBSITE}</a></td>
				</tr>
				<!-- END memberrow -->
				<tr class="catheader">
					<td colspan="6">{PAGINATION}</td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>