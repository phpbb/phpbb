<br clear="all" />

<h1>Edit Forum</h1>

<p>The form below will allow you to customize all the general board options. For User and Forum configurations use the related links on the left hand side.</p>

<form action="{S_FORUM_ACTION}" method="POST">

<table width="99%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg" width="100%"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle">General Forum Settings</span></td>
			</tr>
			<tr>
				<td class="row1">Forum Name:</td>
				<td class="row2"><input type="text" size="25" name="forumname" value="{FORUMNAME}"></td>
			</tr>
			<tr>
				<td class="row1">Description:</td>
				<td class="row2"><textarea ROWS="5" COLS="45" WRAP="VIRTUAL" name="forumdesc">{DESCRIPTION}</TEXTAREA></td>
			</tr>
			<tr>
				<td class="row1">Category:</td>
				<td class="row2">
					<select name="cat_id">
					{S_CATLIST}
					</select>
				</td>
			</tr>
			<tr>
				<td class="row1">Forum Status:</td>
				<td class="row2">
					<select name="forumstatus">
					{S_STATUSLIST}
					</select>
				</td>
			</tr>
			<tr>
				<td class="row1">Auto Pruning:</td>
				<td class="row2">
				 	<table>
						<tr>
							<td align="right" valign="middle">{L_ENABLED}</td>
							<td align="left" valign="middle"><input type="checkbox" name="prune_enable" value="1" {S_PRUNE_EN}></td>
						</tr>
						<tr>
							<td align="right" valign="middle">{L_PRUNE_DAYS}</td>
							<td align="left" valign="middle">&nbsp;<input type="text" name="prune_days" value="{S_PRUNE_DAYS}" size="5">&nbsp;{L_DAYS}</td>
						</tr>
						<tr>
							<td align="right" valign="middle">{L_PRUNE_FREQ}</td>
							<td align="left" valign="middle">&nbsp;<input type="text" name="prune_freq" value="{S_PRUNE_FREQ}" size="5">&nbsp;{L_DAYS}</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td class="row2" colspan="2" align="center">
					<input type="hidden" name="mode" value="{S_NEWMODE}">
					<input type="hidden" name="forum_id" value="{S_FORUMID}">
					<input type="submit" name="submit" value="{BUTTONVALUE}">
				</td>
			</tr>
		</table></td>
	</tr>
</table>

</form>
		
<br clear="all">
