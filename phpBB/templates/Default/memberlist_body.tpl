<tr>
	<td><table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr><form method="post" action="{S_MODE_ACTION}">
			<td align="right" valign="bottom" style="{font-size: 8pt; height: 55px;}" nowrap>{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}"></td>
		</form></tr>
	</table></td>
</tr>
<tr>
	<td bgcolor="#000000" align="center"><table width="100%" cellpadding="0" cellspacing="1"  border="0">
		<td>
			<table width="100%" cellpadding="3" cellspacing="1" border="0">
				<tr class="tableheader">
					<td width="8%" align="center">&nbsp;</td>
					<td align="center">{L_USERNAME}</td>
					<td width="8%" align="center">{L_EMAIL}</td>
					<td align="center">{L_FROM}</td>
					<td align="center">{L_JOINED}</td>
					<td align="center">{L_POSTS}</td>
					<td width="8%" align="center">{L_WEBSITE}</td>
				</tr>
				<!-- BEGIN memberrow -->
				<tr bgcolor="{memberrow.ROW_COLOR}" class="tablebody">
					<td align="center">&nbsp;{memberrow.PM_IMG}&nbsp;</td>
					<td align="center"><a href="{memberrow.U_VIEWPROFILE}">{memberrow.USERNAME}</a></td>
					<td align="center" valign="middle">&nbsp;{memberrow.EMAIL_IMG}&nbsp;</td>
					<td align="center" valign="middle">{memberrow.FROM}</td>
					<td align="center" valign="middle">{memberrow.JOINED}</td>
					<td align="center" valign="middle">{memberrow.POSTS}</td>
					<td align="center">&nbsp;{memberrow.WWW_IMG}&nbsp;</td>
				</tr>
				<!-- END memberrow -->
				<tr class="catheader">
					<td colspan="7">{PAGINATION}</td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>