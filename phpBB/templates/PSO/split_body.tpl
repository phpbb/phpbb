<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr><form method="post" action="{S_SPLIT_ACTION}">
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="cattitle">{L_SPLIT_TOPIC}</span><br><span class="gensmall">{L_SPLIT_TOPIC_EXPLAIN}</span></td>
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
				<th>{L_AUTHOR}</th>
				<th>{L_MESSAGE}</th>
				<th>{L_SELECT}</th>
			</tr>
			<!-- BEGIN postrow -->
			<tr bgcolor="{postrow.ROW_COLOR}">
				<td width="20%" align="left" valign="top"><table height="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{postrow.POSTER_NAME}</b></font><br><br></td>
					</tr>
				</table></td>
				<td width="75%" height="100%"><table width="100%" height="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td><img src="images/icon_minipost.gif" alt="Post image icon"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_POSTED}: {postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Post Subject: {postrow.POST_SUBJECT}</font><hr></td>
					</tr>
					<tr>
						<td height="100%" valign="top"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{postrow.MESSAGE}</font></td>
					</tr>
				</table></td>
				<td width="5%" align="center">&nbsp;<input type="checkbox" name="preform_op[]" value="{postrow.POST_ID}">&nbsp;</td>
			</tr>
			<!-- END postrow -->
			<tr>
				<td class="cat" colspan="5" align="right">{S_HIDDEN_FIELDS}<input type="submit" name="split_type_all" value="{L_SPLIT_POSTS}">&nbsp;&nbsp;<input type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}"></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="98%">
	<tr>
		<td width="40%"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
	</tr>
</table></div>