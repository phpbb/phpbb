<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr><form method="post" action="{S_PRIVMSGS_ACTION}">
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
		        <td class="cat" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0"> 
					<tr>
						<td><span class="cattitle"> {INBOX}&nbsp;&nbsp;&nbsp;{SENTBOX}&nbsp;&nbsp;&nbsp;{OUTBOX}&nbsp;&nbsp;&nbsp;{SAVEBOX} </span></td>
						<td align="right">{S_POST_REPLY_MSG}&nbsp;&nbsp;{S_POST_NEW_MSG}</td>
					</tr>
      			</table></td>
			</tr>
			<tr>
				<th width="22%"<b>{L_FROM_OR_TO}</b></th>
				<th><b>{L_MESSAGE}</b></th>
			</tr>
			<tr>
				<td class="row2" width="20%" align="left" valign="top"><a name="{U_POST_ID}"></a><table height="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td valign="top"><span class="gen"><b>{POSTER_NAME}</b></span><br><span class="gensmall">{POSTER_RANK}<br>{RANK_IMAGE}<br><br>{POSTER_AVATAR}<br><br>{POSTER_JOINED}<br>{POSTER_POSTS}<br>{POSTER_FROM}</span></td>
					</tr>
				</table></td>
				<td class="row2" width="80%" height="100%"><table width="100%" height="100%" cellspacing="1" cellpadding="0" border="0">
					<tr>
						<td><table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td><img src="images/posticon.gif" alt="Post image icon"><span class="gensmall">{L_POSTED}: {POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_SUBJECT}: {POST_SUBJECT}</span></td>
								<td align="right" valign="middle">&nbsp; {EDIT_IMG} {QUOTE_IMG}&nbsp;</td>
							</tr>
						</table></td>
					</tr>
					<tr>
						<td height="100%" valign="top"><hr><span class="gen">{MESSAGE}</span></td>
					</tr>
					<tr>
						<td><hr> {PROFILE_IMG} {EMAIL_IMG} {WWW_IMG} {ICQ_STATUS_IMG} {ICQ_ADD_IMG} {AIM_IMG} {YIM_IMG} {MSN_IMG} </td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="cat" colspan="2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td width="140" align="left" valign="middle" nowrap>{S_POST_NEW_MSG}&nbsp;&nbsp;{S_POST_REPLY_MSG}</td>
						<td align="right" valign="middle">{S_HIDDEN_FIELDS}<input type="submit" name="save" value="Save Post">&nbsp;<input type="submit" name="delete" value="Delete Post"></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table width="98%" cellspacing="2" border="0">
	<tr>
		<td width="40%" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table></div>