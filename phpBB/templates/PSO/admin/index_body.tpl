
<br clear="all" />

<h1>{L_WELCOME}</h1>

<p>{L_ADMIN_INTRO}</p>

<h2>{L_FORUM_STATS}</h2>

<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg">
			<table width="100%" cellpadding="4" cellspacing="1" border="0">
				<tr>
					<th width="25%" nowrap>{L_STATISTIC}</th>
					<th width="25%">{L_VALUE}</th>
					<th width="25%" nowrap>{L_STATISTIC}</th>
					<th width="25%">{L_VALUE}</th>
				</tr>
				<tr>
					<td class="row1" nowrap>{L_NUMBER_POSTS}:</td>
					<td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
					<td class="row1" nowrap>{L_POSTS_PER_DAY}:</td>
					<td class="row2"><b>{POSTS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1" nowrap>{L_NUMBER_TOPICS}:</td>
					<td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
					<td class="row1" nowrap>{L_TOPICS_PER_DAY}:</td>
					<td class="row2"><b>{TOPICS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1" nowrap>{L_NUMBER_USERS}:</td>
					<td class="row2"><b>{NUMBER_OF_USERS}</b></td>
					<td class="row1" nowrap>{L_USERS_PER_DAY}:</td>
					<td class="row2"><b>{USERS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1" nowrap>{L_BOARD_STARTED}:</td>
					<td class="row2"><b>{START_DATE}</b></td>
					<td class="row1" nowrap>{L_AVATAR_DIR_SIZE}:</td>
					<td class="row2"><b>{AVATAR_DIR_SIZE}</b></td>
				</tr>
				<tr>
					<td class="row1" nowrap>{L_DB_SIZE}:</td>
					<td class="row2"><b>{DB_SIZE}</b></td>
					<td class="row1" nowrap>&nbsp;</td>
					<td class="row2">&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<h2>{L_WHO_IS_ONLINE}</h2>

<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<th width="25%">&nbsp;{L_USERNAME}&nbsp;</th>
				<th width="25%">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
				<th width="25%">&nbsp;{L_LOCATION}&nbsp;</th>
				<th width="25%">&nbsp;{L_IP_ADDRESS}&nbsp;</th>
			</tr>
			<!-- BEGIN userrow -->
			<tr bgcolor="{userrow.ROW_COLOR}">
				<td width="25%">&nbsp;<span class="gen"><a href="{userrow.U_USER_PROFILE}">{userrow.USERNAME}</a></span>&nbsp;</td>
				<td width="25%" align="center">&nbsp;<span class="gen">{userrow.LASTUPDATE}</span>&nbsp;</td>
				<td width="25%">&nbsp;<span class="gen"><a href="{userrow.U_FORUM_LOCATION}">{userrow.LOCATION}</a></span>&nbsp;</td>
				<td width="25%">&nbsp;<span class="gen">{userrow.IP_ADDRESS}</span>&nbsp;</td>
			</tr>
			<!-- END userrow -->
		</table></td>
	</tr>
</table>
		
<br clear="all" />
