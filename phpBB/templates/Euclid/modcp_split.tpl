<form method="post" action="{S_SPLIT_ACTION}"><table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="cattitle">{L_SPLIT_TOPIC}</span><br /><span class="gensmall">{L_SPLIT_TOPIC_EXPLAIN}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SPLIT_SUBJECT}</span></td>
				<td class="row2" colspan="2"><span class="courier"><input type="text" size="50" maxlength="100" name="subject"></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen">{L_SPLIT_FORUM}</span></td>
				<td class="row2" colspan="2"><span class="courier">{FORUM_INPUT}</span></td>
			</tr>
			<tr>
				<td class="cat" colspan="3"><table width="60%" cellspacing="0" cellpadding="0" border="0" align="center">
					<tr>
						<td width="50%" align="center"><input class="button" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}"></td>
						<td width="50%" align="center"><input class="button" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}"></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th>{L_AUTHOR}</th>
				<th>{L_MESSAGE}</th>
				<th>{L_SELECT}</th>
			</tr>
			<!-- BEGIN postrow -->
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td align="left" valign="top"><a name="{postrow.U_POST_ID}"></a><table width="170" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><span class="gen"><b>{postrow.POSTER_NAME}</b></span><br /></td>
					</tr>
				</table></td>
				<td width="100%" valign="top"><table width="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td valign="top"><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td valign="middle"><img src="images/icon_minipost.gif" alt="Post image icon" /><span class="gensmall">{L_POSTED}: {postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
							</tr>
							<tr>
								<td colspan="2"><hr /></td>
							</tr>
						</table></td>
					</tr>
					<tr>
						<td width="100%" height="100%" valign="top"><span class="gen">{postrow.MESSAGE}</span></td>
					</tr>
				</table></td>
				<td width="5%" align="center">&nbsp;<input type="checkbox" name="post_id_list[]" value="{postrow.POST_ID}">&nbsp;</td>
			</tr>
			<!-- END postrow -->
			<tr>
				<td class="cat" colspan="3"><table width="60%" cellspacing="0" cellpadding="0" border="0" align="center">
					<tr>
						<td width="50%" align="center"><input class="button" type="submit" name="split_type_all" value="{L_SPLIT_POSTS}"></td>
						<td width="50%" align="center"><input class="button" type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}"></td>
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
	</tr>
</table>
