
<form method="post" action="{S_MODCP_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table>

<table width="98%" cellspacing="0" cellpadding="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellspacing="1" cellpadding="4" border="0">
			<tr>
				<td class="cat" colspan="5"><table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
					<tr>
						<td class="cat" align="center"><span class="cattitle">{L_MOD_CP}</span><br /><span class="gensmall">{L_MOD_CP_EXPLAIN}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="4%" height="25">&nbsp;</th>
				<th height="25">{L_TOPICS}</th>
				<th width="8%" height="25">{L_REPLIES}</th>
				<th width="17%" height="25">{L_LASTPOST}</th>
				<th width="5%" height="25">{L_SELECT}</th>
			</tr>
			<!-- BEGIN topicrow -->
			<tr>
				<td class="row1" align="center" valign="middle">{topicrow.FOLDER_IMG}</td>
				<td class="row2">&nbsp;<span class="gensmall">{topicrow.TOPIC_TYPE}<a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a></span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{topicrow.REPLIES}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gensmall">{topicrow.LAST_POST}</span></td>
				<td class="row1" align="center" valign="middle"><input type="checkbox" name="topic_id_list[]" value="{topicrow.TOPIC_ID}"></td>
			</tr>
			<!-- END topicrow -->
			<tr>
				<td class="cat" colspan="5" height="30"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="left" valign="middle"><span class="gensmall">{PAGE_NUMBER}</b></span></td>
						<td align="right" valign="middle"><table cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td align="right"><span class="gensmall">{PAGINATION}</span></td>
								<td>&nbsp;&nbsp;</td>
								<td align="right"><input class="liteoptiontable" type="submit" name="delete" value="{L_DELETE}">&nbsp;<input class="liteoptiontable" type="submit" name="move" value="{L_MOVE}">&nbsp;<input class="liteoptiontable" type="submit" name="lock" value="{L_LOCK}">&nbsp;<input class="liteoptiontable" type="submit" name="unlock" value="{L_UNLOCK}"></td>
							</tr>
						</table></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

{S_HIDDEN_FIELDS}

</form>

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}</td>
	</tr>
</table>
