<h1>Welcome to phpBB</h1>

<p>
	Thank you for choosing phpBB as your forum solution. This screen will give you a quick overview of all the various statistics of your board. You can get back to this page by clicking on the <i><u>overview</u></i> link in the left pane.<br />
	The other links on the left hand side of this screen will allow you to control every aspect of your forum experiance, each screen will have instructions on how to use the tools.
</p>


<h2>Forum Statistics</h2>
<table border="0" cellpadding="1" cellspacing="0" width="65%">
	<tr>
		<td class="tablebg">
			<table width="100%" cellpadding="4" cellspacing="1" border="0">
				<tr>
					<th width="50%">Statistic</th>
					<th width="50%">Value</th>
				</tr>
				<tr>
					<td class="row1">Current number of posts:</td>
					<td class="row2"><b>{NUMBER_OF_POSTS}</b></td>
				</tr>
				<tr>
					<td class="row1">Current number of topics:</td>
					<td class="row2"><b>{NUMBER_OF_TOPICS}</b></td>
				</tr>
				<tr>
					<td class="row1">Current number of users:</td>
					<td class="row2"><b>{NUMBER_OF_USERS}</b></td>
				</tr>
				<tr>
					<td class="row1">Board started on:</td>
					<td class="row2"><b>{STARTDATE}</b></td>
				</tr>
				<tr>
					<td class="row1">Posts per day:</td>
					<td class="row2"><b>{POSTS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1">Topics per day:</td>
					<td class="row2"><b>{TOPICS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1">Users per day:</td>
					<td class="row2"><b>{USERS_PER_DAY}</b></td>
				</tr>
				<tr>
					<td class="row1">Avatar directory size:</td>
					<td class="row2"><b>{AVATAR_DIR_SIZE}</b></td>
				</tr>
				<tr>
					<td class="row1">Database size:</td>
					<td class="row2"><b>{DB_SIZE}</b></td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<h2>Users Online</h2>
<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg">
			<table width="100%" cellpadding="4" cellspacing="1" border="0">
				<tr>
					<th width="25%">&nbsp;{L_USERNAME}&nbsp;</th>
					<th width="25%">&nbsp;{L_LAST_UPDATE}&nbsp;</th>
					<th width="25%">&nbsp;{L_LOCATION}&nbsp;</th>
					<th width="25%">&nbsp;{L_IPADDRESS}&nbsp;</th>
				</tr>
				<!-- BEGIN userrow -->
				<tr bgcolor="{userrow.ROW_COLOR}">
					<td width="25%">&nbsp;<span class="gen"><a href="{userrow.U_USER_PROFILE}">{userrow.USERNAME}</a></span>&nbsp;</td>
					<td width="25%" align="center">&nbsp;<span class="gen">{userrow.LASTUPDATE}</span>&nbsp;</td>
					<td width="25%">&nbsp;<span class="gen"><a href="{userrow.U_FORUM_LOCATION}">{userrow.LOCATION}</a></span>&nbsp;</td>
					<td width="25%">&nbsp;<span class="gen">{userrow.IPADDRESS}</span>&nbsp;</td>
				</tr>
				<!-- END userrow -->
			</table>
		</td>
	</tr>
</table>
		
<br clear="all">
