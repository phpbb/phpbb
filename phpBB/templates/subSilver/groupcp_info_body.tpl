 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<form method="POST" action="{S_GROUP_INFO_ACTION}">
  <table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <th colspan="7" class="thHead" height="25"><span class="tableTitle">{L_GROUP_INFORMATION}</span></th>
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
  </table>
</form>

<form action="{S_PENDING_ACTION}" method="POST">
  <table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
	<tr> 
	  <th class="thCornerL" height="25">Private Message</th>
	  <th class="thTop">{L_USERNAME}</th>
	  <th class="thTop">{L_POSTS}</th>
	  <th class="thTop">{L_FROM}</th>
	  <th class="thTop">{L_EMAIL}</th>
	  <th colspan="2" class="thCornerR">{L_WEBSITE}</th>
	</tr>
	<tr> 
	  <td class="catSides" colspan="7" height="28"><span class="cattitle">Group Moderator</span></td>
	</tr>
	<tr> 
	  <td class="row1" align="center"> {MOD_PM_IMG} </td>
	  <td class="row1" align="center"><span class="gen"><a href="{U_MOD_VIEWPROFILE}" class="gen">{MOD_USERNAME}</a></span></td>
	  <td class="row1" align="center" valign="middle"><span class="gen">{MOD_POSTS}</span></td>
	  <td class="row1" align="center" valign="middle"><span class="gen">{MOD_FROM}</span></td>
	  <td class="row1" align="center" valign="middle"><span class="gen">{MOD_EMAIL_IMG}</span></td>
	  <td class="row1" align="center" colspan="2">{MOD_WWW_IMG}</td>
	</tr>
	<tr> 
	  <td class="catSides" colspan="8" height="28"><span class="cattitle">Group Members</span></td>
	</tr>
	<!-- BEGIN memberrow -->
	<tr> 
	  <td class="{memberrow.ROW_CLASS}" align="center"> {memberrow.PM_IMG} </td>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"><a href="{memberrow.U_VIEWPROFILE}" class="gen">{memberrow.USERNAME}</a></span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen">{memberrow.POSTS}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center"><span class="gen"> {memberrow.FROM} 
		</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" valign="middle"><span class="gen">{memberrow.EMAIL_IMG}</span></td>
	  <td class="{memberrow.ROW_CLASS}" align="center" colspan="2"> {memberrow.WWW_IMG}</td>
	</tr>
	<!-- END memberrow -->
	<!-- BEGIN pendingmembers -->
	<tr> 
	  <td class="catSides" colspan="6" height="28"><span class="cattitle">Pending Members</span></td>
	  <td class="catSides" align="center"><span class="gen"><b>Action</b></span></td>
	</tr>
	<!-- END pendingmembers -->
	<!-- BEGIN pendingmembersrow -->
	<tr> 
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"> {pendingmembersrow.PM_IMG} 
	  </td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen"><a href="{pendingmembersrow.U_VIEWPROFILE}" class="gen">{pendingmembersrow.USERNAME}</a></span></td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.POSTS}</span></td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.FROM}</span></td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.EMAIL_IMG}</span></td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gen">{pendingmembersrow.WWW_IMG}</span></td>
	  <td class="{pendingmembersrow.ROW_CLASS}" align="center"><span class="gensmall">{pendingmembersrow.SELECT} 
		{L_SELECT}</span></td>
	</tr>
	<!-- END pendingmembersrow -->
	<!-- BEGIN pendingmembers -->
	<tr> 
	  <td class="cat" colspan="8" align="right"><span class="cattitle"> 
		<input type="submit" name="approve" value="{L_APPROVESELECTED}" class="mainoption" />
		&nbsp; 
		<input type="submit" name="deny" value="{L_DENYSELECTED}" class="liteoption" />
		</span></td>
	</tr>
	<!-- END pendingmembers -->
	<!-- BEGIN nomembers -->
	<tr> 
	  <td class="row1" colspan="7" align="center">{L_NO_MEMBERS}</td>
	</tr>
	<!-- END nomembers -->
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="right" valign="top"><span class="gensmall">{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>
