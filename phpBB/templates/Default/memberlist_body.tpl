<tr>
	<td bgcolor="#000000" align="center"><table width="100%" cellpadding="0" cellspacing="1"  border="0">
		<td>
			<table width="100%" cellpadding="3" cellspacing="1" border="0">
				<tr class="tableheader"><form method="post" action="{S_MODE_ACTION}">
					<td colspan="7" align="right">{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}"></td>
				</form></tr>
				<tr class="tableheader">
					<td align="center">&nbsp;</td>
					<td align="center">{L_USERNAME}</td>
					<td align="center">{L_FROM}</td>
					<td align="center">{L_JOINED}</td>
					<td align="center">{L_POSTS}</td>
					<td align="center">{L_EMAIL}</td>
					<td align="center">{L_WEBSITE}</td>
				</tr>
				<!-- BEGIN memberrow -->
				<tr bgcolor="{memberrow.ROW_COLOR}" class="tablebody">
					<td align="center">&nbsp;<a href="{memberrow.U_PRIVATE_MESSAGE}"><img src="{PM_IMG}" alt="{L_SEND_PRIV_MSG}" border="0"></a>&nbsp;</td>
					<td align="center"><a href="{memberrow.U_VIEWPROFILE}">{memberrow.USERNAME}</a></td>
					<td align="center" valign="middle">{memberrow.FROM}</td>
					<td align="center" valign="middle">{memberrow.JOINED}</td>
					<td align="center" valign="middle">{memberrow.POSTS}</td>
					<td align="center" valign="middle">{memberrow.EMAIL}</td>
					<td align="center">{memberrow.WEBSITE}</a></td>
				</tr>
				<!-- END memberrow -->
				<tr class="catheader">
					<td colspan="7">{PAGINATION}</td>
				</tr>
			</table></td>
		</tr>
	</table></td>
</tr>