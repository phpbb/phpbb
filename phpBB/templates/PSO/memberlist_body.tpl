
<form method="post" action="{S_MODE_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
		<td align="right" nowrap="nowrap"><span class="gen">{L_SELECT_SORT_METHOD}:&nbsp;{S_MODE_SELECT}&nbsp;&nbsp;{L_ORDER}&nbsp;{S_ORDER_SELECT}&nbsp;&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}" /></span></td>
	</tr>
</table>

<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<th>&nbsp;</th>
				<th>{L_USERNAME}</th>
				<th>{L_EMAIL}</th>
				<th>{L_FROM}</th>
				<th>{L_JOINED}</th>
				<th>{L_POSTS}</th>
				<th>{L_WEBSITE}</th>
			</tr>
			<!-- BEGIN memberrow -->
			<tr>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center">&nbsp;{memberrow.PM_IMG}&nbsp;</td>
				<td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEWPROFILE}">{memberrow.USERNAME}</a></span></td>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center" valign="middle">&nbsp;{memberrow.EMAIL_IMG}&nbsp;</td>
				<td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.FROM}</span></td>
				<td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gensmall">{memberrow.JOINED}</span></td>
				<td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.POSTS}</span></td>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center">&nbsp;{memberrow.WWW_IMG}&nbsp;</td>
			</tr>
			<!-- END memberrow -->
			<tr>
				<td class="cat" colspan="7"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="gen">&nbsp;{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span></td>
						<td align="right"><span class="gen">{PAGINATION}&nbsp;</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}</td>
	</tr>
</table>
