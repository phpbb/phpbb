 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  <tr> 
	<th width="35%" class="thCornerL" height="25">&nbsp;{L_USERNAME}&nbsp;</th>
	<th width="25%" class="thTop">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
	<th width="40%" class="thCornerR">&nbsp;{L_LOCATION}&nbsp;</th>
  </tr>
  <tr> 
	<td class="catSides" colspan="3" height="28"><span class="cattitle"><b>{TOTAL_REGISTERED_USERS_ONLINE}</b></span></td>
  </tr>
  <!-- BEGIN reg_user_row -->
  <tr> 
	<td width="35%" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_USER_PROFILE}" class="gen">{reg_user_row.USERNAME}</a></span>&nbsp;</td>
	<td width="25%" align="center" nowrap="nowrap" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen">{reg_user_row.LASTUPDATE}</span>&nbsp;</td>
	<td width="40%" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_FORUM_LOCATION}" class="gen">{reg_user_row.LOCATION}</a></span>&nbsp;</td>
  </tr>
  <!-- END reg_user_row -->
  <tr> 
	<td colspan="3" height="1" class="row3"><img src="templates/subSilver/images/spacer.gif" width="1" height="1" alt="."></td>
  </tr>
  <tr> 
	<td class="catSides" colspan="3" height="28"><span class="cattitle"><b>{TOTAL_GUEST_USERS_ONLINE}</b></span></td>
  </tr>
  <!-- BEGIN guest_user_row -->
  <tr> 
	<td width="35%" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{guest_user_row.U_USER_PROFILE}" class="gen">{guest_user_row.USERNAME}</a></span>&nbsp;</td>
	<td width="25%" align="center" nowrap="nowrap" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.LASTUPDATE}</span>&nbsp;</td>
	<td width="40%" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{guest_user_row.U_FORUM_LOCATION}" class="gen">{guest_user_row.LOCATION}</a></span>&nbsp;</td>
  </tr>
  <!-- END guest_user_row -->
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="top"><span class="gensmall">{L_ONLINE_EXPLAIN}</span></td>
	<td align="right" valign="top"><span class="gensmall">{S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>

