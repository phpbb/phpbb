<form method="post" action="{S_MODCP_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
					<tr>
						<td class="cat" align="center"><span class="cattitle">{L_MOD_CP}</span><br /><span class="gensmall">{L_MOD_CP_EXPLAIN}</span></td>
					</tr>
					<tr>
						<td><table width="100%" cellspacing="0" cellpadding="4" border="0">
							<tr>
								<td align="center" valign="bottom"><input class="button" type="submit" name="delete" value="{L_DELETE}"> &nbsp; <input class="button" type="submit" name="move" value="{L_MOVE}"> &nbsp; <input class="button" type="submit" name="lock" value="{L_LOCK}"> &nbsp; <input class="button" type="submit" name="unlock" value="{L_UNLOCK}"></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="4%">&nbsp;</th>
				<th>&nbsp;{L_TOPICS}&nbsp;</th>
				<th width="8%">&nbsp;{L_REPLIES}&nbsp;</th>
				<th width="17%">&nbsp;{L_LASTPOST}&nbsp;</th>
				<th width="5%">&nbsp;{L_SELECT}&nbsp;</th>
			</tr>
			<!-- BEGIN topicrow -->
			<tr>
				<td class="row1" align="center" valign="middle">&nbsp;{topicrow.FOLDER_IMG}&nbsp;</td>
				<td class="row2">&nbsp;<span class="gensmall">{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a></span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{topicrow.REPLIES}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gensmall">{topicrow.LAST_POST}</span></td>
				<td class="row1" align="center" valign="middle"><input type="checkbox" name="topic_id_list[]" value="{topicrow.TOPIC_ID}"></td>
			</tr>
			<!-- END topicrow -->
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="left" valign="middle" nowrap>&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td width="100%" align="right" valign="middle"><span class="gen">{PAGINATION}</span>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2"><table width="100%" cellspacing="0" cellpadding="4" border="0">
							<tr>
								<td align="center" valign="bottom"><input class="button" type="submit" name="delete" value="{L_DELETE}"> &nbsp; <input class="button" type="submit" name="move" value="{L_MOVE}"> &nbsp; <input class="button" type="submit" name="lock" value="{L_LOCK}"> &nbsp; <input class="button" type="submit" name="unlock" value="{L_UNLOCK}"></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

{S_HIDDEN_FIELDS}</form>

<table cellspacing="2" border="0" width="98%" align="center">
	<tr>
		<td width="40%"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}<br><span class="gensmall">{S_AUTH_LIST}</span></td>
	</tr>
</table>
