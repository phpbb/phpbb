<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left" valign="bottom" nowrap><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> {FORUM_NAME}</span></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr><form method="post" action="{S_MODCP_URL}">
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="3" align="center"><span class="cattitle">Split Topic</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_SUBJECT}</b></span></td>
				<td class="row2" colspan="2"><span class="courier"><input type="text" size="50" maxlength="100" name="subject"></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_FORUM}</b></span></td>
				<td class="row2" colspan="2"><span class="courier">{FORUM_INPUT}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_AUTHOR}</b></span></td>
				<td class="row2"><span class="gen"><b>{L_MESSAGE}</b></span></td>
				<td class="row1"><span class="gen"><b>{L_SELECT}</b></span></td>
			</tr>
			<!-- BEGIN postrow -->
			<tr>
				<td class="row1" width="20%" align="left" valign="top"><table height="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{postrow.POSTER_NAME}</b></font><br><br></td>
					</tr>
				</table></td>
				<td class="row2" width="80%" height="100%"><table width="100%" height="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td><img src="images/icon_minipost.gif" alt="Post image icon"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_POSTED}: {postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Post Subject: {postrow.POST_SUBJECT}</font><hr></td>
					</tr>
					<tr>
						<td height="100%" valign="top"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{postrow.MESSAGE}</font></td>
					</tr>
					<tr>
						<td><hr></td>
					</tr>
				</table></td>
				<td class="row1" width="20%" align="center">
					<input type="checkbox" name="preform_op[]" value="{postrow.POST_ID}">&nbsp;<font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_SELECT}</font>
				</td>
			</tr>
			<!-- END postrow -->
			<tr>
				<th colspan="5" align="right">&nbsp;&nbsp;<input type="hidden" name="{POST_FORUM_URL}" value="{FORUM_ID}">&nbsp;<input type="hidden" name="mode" value="split">
					<input type="radio" name="split_type" value="split"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_SPLIT_POSTS}</font><br />
					<input type="radio" name="split_type" value="split_after"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{L_SPLIT_AFTER}</font><br />
					<input type="submit" name="split" value="{L_SUBMIT}"></form>
				</th>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="98%">
	<tr>
		<td width="40%"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
	</tr>
</table></div>