<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr><form method="POST" action="{S_GROUP_INFO_ACTION}">
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle">{L_GROUP_INFORMATION}</span></td>
			</tr>
			<tr>
				<td class="row1" width="20%"><span class="gen">{L_GROUP_NAME}:</span></td>
				<td class="row2"><span class="gen"><b>{GROUP_NAME}</b></span></td>
			</tr>
			<tr>
				<td class="row1" width="20%"><span class="gen">{L_GROUP_DESC}:</span></td>
				<td class="row2"><span class="gen">{GROUP_DESC}</span></td>
			</tr>
			<tr>
				<td class="row1" width="20%"><span class="gen">{L_GROUP_MEMBERSHIP}:</span></td>
				<td class="row2"><span class="gen">{GROUP_DETAILS}{S_HIDDEN_FIELDS}</span></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<br clear="all">

<form action="{S_PENDING_ACTION}" method="POST" name="post">
{S_PENDING_HIDDEN_FIELDS}
<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="7"><span class="cattitle">Group Moderator</span></td>
			</tr>
			<tr>
				<th width="8%">&nbsp;</th>
				<th><b>{L_USERNAME}</b></td>
				<th width="8%"><b>{L_EMAIL}</b></td>
				<th><b>{L_FROM}</b></td>
				<th><b>{L_POSTS}</b></td>
				<th width="8%" colspan="2"><b>{L_WEBSITE}</b></td>
				
			</tr>
			<tr>
				<td class="row1" width="8%" align="center"> {MOD_PM_IMG} </td>
				<td class="row1" align="center"><span class="gen"><a href="{U_MOD_VIEWPROFILE}">{MOD_USERNAME}</a></span></td>
				<td class="row1" width="8%" align="center" valign="middle"> {MOD_EMAIL_IMG} </td>
				<td class="row1" align="center" valign="middle"><span class="gen">{MOD_FROM}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{MOD_POSTS}</span></td>
				<td class="row1" width="8%" align="center" colspan="2"> {MOD_WWW_IMG} </a></td>
			</tr>
			<tr>
				<td class="cat" colspan="7"><span class="cattitle">Group Members</span></td>
			</tr>
			<tr>
				<th>&nbsp;</th>
				<th><b>{L_USERNAME}</b></td>
				<th><b>{L_EMAIL}</b></td>
				<th><b>{L_FROM}</b></td>
				<th><b>{L_POSTS}</b></td>
				<th><b>{L_WEBSITE}</b></td>
				<th width="10%">{L_SELECT}</th>
			</tr>
			<!-- BEGIN memberrow -->
			<tr>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center"> {memberrow.PM_IMG} </td>
				<td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEWPROFILE}">{memberrow.USERNAME}</a></span></td>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center" valign="middle"> {memberrow.EMAIL_IMG} </td>
				<td class="{memberrow.ROW_CLASS}" align="center"><span class="gen">{memberrow.FROM}</span></td>
				<td class="{memberrow.ROW_CLASS}" align="center"><span class="gen">{memberrow.POSTS}</span></td>
				<td class="{memberrow.ROW_CLASS}" width="8%" align="center"> {memberrow.WWW_IMG} </a></td>
			   <td class="{memberrow.ROW_CLASS}" align="center"> 
	  				<!-- BEGIN memberselect --> 
	  				<input type="checkbox" name="member[]" value="{memberrow.memberselect.USER_ID}" /> <span class="gen">{L_SELECT}</span> 
	  				<!-- END memberselect -->&nbsp;
	  			</td>
			</tr>
			<!-- END memberrow -->
			<!-- BEGIN modoption -->
			<tr>
				<td class="cat" colspan="7" align="right"><span class="cattitle">
					<input type="submit" name="remove" value="{L_REMOVESELECTED}" class="mainoption" />
				</td>
			</tr>
			<!-- END modoption -->
			<!-- BEGIN pendingmembers -->
			<tr>
				<td class="cat" colspan="7"><span class="cattitle">Pending Members</span></td>
			</tr>
			<tr>
				<th>{L_PM}</th>
				<th><b>{L_USERNAME}</b></td>
				<th><b>{L_EMAIL}</b></td>
				<th><b>{L_FROM}</b></td>
				<th><b>{L_POSTS}</b></td>
				<th><b>{L_SELECT}</b></td>
			</tr>
			<!-- END pendingmembers -->
			<!-- BEGIN pendingmembersrow -->
			<tr>
				<td class="{pendingmembersrow.ROW_CLASS}" width="8%" align="center"> {pendingmembersrow.PM_IMG} </td>
				<td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen"><a href="{pendingmembersrow.U_VIEWPROFILE}">{pendingmembersrow.USERNAME}</a></span></td>
				<td class="{pendingmembersrow.ROW_CLASS}" width="8%" align="center" valign="middle"> {pendingmembersrow.EMAIL_IMG} </td>
				<td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.FROM}</span></td>
				<td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.POSTS}</span></td>
				<td class="{pendingmembersrow.ROW_CLASS}" width="8%" align="center"><span class="gen"> {pendingmembersrow.SELECT} {L_SELECT} </span></td>
			</tr>
			<!-- END pendingmembersrow -->
			<!-- BEGIN pendingmembers -->
			<tr>
				<td class="cat" colspan="7" align="right"><span class="cattitle"><input type="submit" name="approve" value="{L_APPROVESELECTED}">&nbsp;<input type="submit" name="deny" value="{L_DENYSELECTED}"></span></td>
			</tr>
			<!-- END pendingmembers -->

			<!-- BEGIN nomembers -->
			<tr>
				<td class="row1" colspan="7" align="center">{L_NO_MEMBERS}</td>
			</tr>
			<!-- END nomembers -->
			<tr>
				<td class="cat" colspan="7"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td colspan="7"><span class="gen">&nbsp;
						<!-- BEGIN addmember -->
							<input type="text"  class="post" name="username" maxlength="50" size="20" />
							&nbsp; 
							<input type="submit" name="usersubmit" value="{L_FIND_USERNAME}" onClick="window.open('privmsg.php?mode=searchuser', '_phpbbsearch', 'HEIGHT=250,resizable=yes,WIDTH=400');return false;" />
							<input type="submit" name="add" value="{L_ADDMEMBER}" />
							</span>
						<!-- END addmember -->
						</td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>
</form>
<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
