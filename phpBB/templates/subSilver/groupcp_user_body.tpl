 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" colspan="2" class="forumline"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr> 
		  <td class="innerline"> 
			<table width="100%" cellpadding="4" cellspacing="1" border="0">
			  <tr> 
				<th colspan="2" align="center" height="25" class="secondary">{L_GROUP_MEMBERSHIP_DETAILS}</th>
			  </tr>
			  <tr> 
				<td class="row1"><span class="gen">{L_YOU_BELONG_GROUPS}</span></td>
				<td class="row2" align="right"> 
				  <table width="90%" cellspacing="0" cellpadding="0" border="0">
					<tr> 
					  <form method="post" action="{S_USERGROUP_ACTION}">
						<td width="40%"><span class="gensmall">{GROUP_MEMBER_SELECT}</span></td>
						<td align="center" width="30%">
<input type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}" class="liteoption" /></td>
						<td align="center" width="30%">
<input type="submit" name="unsubjoin" value="{L_UNSUBSCRIBE}" class="liteoption" /></td>
					  </form>
					</tr>
				  </table>
				</td>
			  </tr>
			  <tr> 
				<td class="row1"><span class="gen">{L_PENDING_GROUPS}</span></td>
				<td class="row2" align="right"> 
				  <table width="90%" cellspacing="0" cellpadding="0" border="0">
					<tr> 
					  <form method="post" action="{S_USERGROUP_ACTION}">
						<td width="40%"><span class="gensmall">{GROUP_PENDING_SELECT}</span></td>
						<td align="center" width="30%"> 
						  <input type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}" class="liteoption" /></td>
						<td align="center" width="30%"> 
						  <input type="submit" name="unsubpending" value="{L_UNSUBSCRIBE}" class="liteoption" /></td>
					  </form>
					</tr>
				  </table>
				</td>
			  </tr>
			  <tr> 
				<td class="cat" colspan="2" align="center" height="28"><span class="cattitle"><b>{L_JOIN_A_GROUP}</b></span></td>
			  </tr>
			  <tr> 
				<td class="row1"><span class="gen">{L_SELECT_A_GROUP}</span></td>
				<td class="row2" align="right"> 
				  <table width="90%" cellspacing="0" cellpadding="0" border="0">
					<tr> 
					  <form method="post" action="{S_USERGROUP_ACTION}">
						<td width="40%"><span class="gensmall">{GROUP_LIST_SELECT}</span></td>
						<td align="center" width="30%"> 
						  <input type="submit" name="viewinfo" value="{L_VIEW_INFORMATION}" class="liteoption" /></td>
						<td align="center" width="30%"> 
						  <input type="submit" name="subnew" value="{L_SUBSCRIBE}" class="liteoption" /></td>
					  </form>
					</tr>
				  </table>
				</td>
			  </tr>
			</table>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
