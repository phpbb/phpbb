
<h1>{L_WELCOME}</h1>

<p>{L_ADMIN_INTRO}</p>

<h1>{L_FORUM_STATS}</h1>

<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  <tr> 
	<th width="25%" nowrap="nowrap" height="25" class="thCornerL">{L_STATISTIC}</th>
	<th width="25%" height="25" class="thTop">{L_VALUE}</th>
	<th width="25%" nowrap="nowrap" height="25" class="thTop">{L_STATISTIC}</th>
	<th width="25%" height="25" class="thCornerR">{L_VALUE}</th>
  </tr>
  <tr> 
	<td class="row1" nowrap="nowrap">{L_NUMBER_POSTS}:</td>
	<td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
	<td class="row1" nowrap="nowrap">{L_POSTS_PER_DAY}:</td>
	<td class="row2"><b>{POSTS_PER_DAY}</b></td>
  </tr>
  <tr> 
	<td class="row1" nowrap="nowrap">{L_NUMBER_TOPICS}:</td>
	<td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
	<td class="row1" nowrap="nowrap">{L_TOPICS_PER_DAY}:</td>
	<td class="row2"><b>{TOPICS_PER_DAY}</b></td>
  </tr>
  <tr> 
	<td class="row1" nowrap="nowrap">{L_NUMBER_USERS}:</td>
	<td class="row2"><b>{NUMBER_OF_USERS}</b></td>
	<td class="row1" nowrap="nowrap">{L_USERS_PER_DAY}:</td>
	<td class="row2"><b>{USERS_PER_DAY}</b></td>
  </tr>
  <tr> 
	<td class="row1" nowrap="nowrap">{L_BOARD_STARTED}:</td>
	<td class="row2"><b>{START_DATE}</b></td>
	<td class="row1" nowrap="nowrap">{L_AVATAR_DIR_SIZE}:</td>
	<td class="row2"><b>{AVATAR_DIR_SIZE}</b></td>
  </tr>
  <tr> 
	<td class="row1" nowrap="nowrap">{L_DB_SIZE}:</td>
	<td class="row2"><b>{DB_SIZE}</b></td>
	<td class="row1" nowrap="nowrap">{L_GZIP_COMPRESSION}:</td>
	<td class="row2"><b>{GZIP_COMPRESSION}</b></td>
  </tr>
</table>
<h1>{L_WHO_IS_ONLINE}</h1>

<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  <tr> 
	<th width="20%" class="thCornerL" height="25">&nbsp;{L_USERNAME}&nbsp;</th>
	<th width="20%" height="25" class="thTop">&nbsp;{L_STARTED}&nbsp;</th>
	<th width="20%" class="thTop">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
	<th width="20%" class="thCornerR">&nbsp;{L_FORUM_LOCATION}&nbsp;</th>
	<th width="20%" height="25" class="thCornerR">&nbsp;{L_IP_ADDRESS}&nbsp;</th>
  </tr>
  <!-- BEGIN reg_user_row -->
  <tr> 
	<td width="20%" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_USER_PROFILE}" class="gen">{reg_user_row.USERNAME}</a></span>&nbsp;</td>
	<td width="20%" align="center" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen">{reg_user_row.STARTED}</span>&nbsp;</td>
	<td width="20%" align="center" nowrap="nowrap" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen">{reg_user_row.LASTUPDATE}</span>&nbsp;</td>
	<td width="20%" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_FORUM_LOCATION}" class="gen">{reg_user_row.FORUM_LOCATION}</a></span>&nbsp;</td>
	<td width="20%" class="{reg_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{reg_user_row.U_WHOIS_IP}" class="gen" target="_phpbbwhois">{reg_user_row.IP_ADDRESS}</a></span>&nbsp;</td>
  </tr>
  <!-- END reg_user_row -->
  <tr> 
	<td colspan="5" height="1" class="row3"><img src="../templates/subSilver/images/spacer.gif" width="1" height="1" alt="."></td>
  </tr>
  <!-- BEGIN guest_user_row -->
  <tr> 
	<td width="20%" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.USERNAME}</span>&nbsp;</td>
	<td width="20%" align="center" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.STARTED}</span>&nbsp;</td>
	<td width="20%" align="center" nowrap="nowrap" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen">{guest_user_row.LASTUPDATE}</span>&nbsp;</td>
	<td width="20%" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{guest_user_row.U_FORUM_LOCATION}" class="gen">{guest_user_row.FORUM_LOCATION}</a></span>&nbsp;</td>
	<td width="20%" class="{guest_user_row.ROW_CLASS}">&nbsp;<span class="gen"><a href="{guest_user_row.U_WHOIS_IP}" target="_phpbbwhois">{guest_user_row.IP_ADDRESS}</a></span>&nbsp;</td>
  </tr>
  <!-- END guest_user_row -->
</table>

<br />
