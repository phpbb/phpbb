
<br clear="all" />

<table width="98%" cellpadding="0" cellspacing="0" border="0" align="center">
	<tr>
		<td width="100%" class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr> 
				<th height="25">{L_PM}</th>
				<th height="25">{L_USERNAME}</th>
				<th height="25">{L_POSTS}</th>
				<th height="25">{L_FROM}</th>
				<th height="25">{L_EMAIL}</th>
				<th height="25">{L_WEBSITE}</th>
				<th height="25">{L_SELECT}</th>
			</tr>
			<tr> 
				<td class="cat" colspan="8" height="30"><span class="cattitle">{L_PENDING_MEMBERS}</span></td>
			</tr>
			<!-- BEGIN pending_members_row -->
			<tr> 
				<td class="{pending_members_row.ROW_CLASS}" align="center">{pending_members_row.PM_IMG}</td>
				<td class="{pending_members_row.ROW_CLASS}" align="center"><span class="gen"><a href="{pending_members_row.U_VIEWPROFILE}" class="gen">{pending_members_row.USERNAME}</a></span></td>
				<td class="{pending_members_row.ROW_CLASS}" align="center"><span class="gen">{pending_members_row.POSTS}</span></td>
				<td class="{pending_members_row.ROW_CLASS}" align="center"><span class="gen"> {pending_members_row.FROM}</span></td>
				<td class="{pending_members_row.ROW_CLASS}" align="center" valign="middle"><span class="gen">{pending_members_row.EMAIL_IMG}</span></td>
				<td class="{pending_members_row.ROW_CLASS}" align="center">{pending_members_row.WWW_IMG}</td>
				<td class="{pending_members_row.ROW_CLASS}" align="center"> <input type="checkbox" name="members[]" value="{pending_members_row.USER_ID}" /> </td>
			</tr>
			<!-- END pending_members_row -->
			<tr> 
				<td class="cat" colspan="8" height="30" align="right"><input class="liteoptiontable" type="submit" name="approve" value="{L_APPROVE_SELECTED}" /> &nbsp; <input class="liteoptiontable" type="submit" name="deny" value="{L_DENY_SELECTED}" /></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />
