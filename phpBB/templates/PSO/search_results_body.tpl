<div align="center"><table width="98%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></font></td>
	</tr>
</table></div>

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}" align="center"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">&nbsp;<b>{L_SEARCH} - {SEARCH_MATCHES} Matches</b>&nbsp;</font></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<br clear="all">

<div align="center"><table border="0" cellpadding="1" cellspacing="0" width="98%">
	<tr>
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="0" cellspacing="1" width="100%">
			<!-- BEGIN searchresults -->
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR2}" align="left"><table cellpadding="4" cellspacing="1" border="0">
					<tr>
						<td><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}"><img src="images/folder.gif">&nbsp;{L_FORUM}:&nbsp;<a href="{U_FORUM}">{searchresults.FORUM_NAME}</a></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td rowspan="2" width="20%" bgcolor="{T_TD_COLOR1}"><table height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b><a href="{searchresults.U_USER_PROFILE}">{searchresults.POSTER_NAME}</a></b></font><br><br><font face="{T_FONTFACE2}" size="{T_FONTSIZE2}">{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br>{L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b><br></td>
					</tr>
				</table></td>
				<td width="80%"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}">{searchresults.TOPIC_TITLE}</a></font></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><table width="100%" height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td bgcolor="{T_TD_COLOR2}"><a href="{searchresults.U_POST}"><img src="images/posticon.gif" alt="Post image icon" border="0"></a><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{L_POSTED}: {searchresults.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Post Subject: {searchresults.POST_SUBJECT}</font><hr></td>
					</tr>
					<tr>
						<td height="100%" bgcolor="{T_TD_COLOR2}"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}" color="{T_FONTCOLOR1}">{searchresults.MESSAGE}</font><br><br></td>
					</tr>
				</table></td>
			</tr>
			<!-- END searchresults -->
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR2}"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td align="left" valign="middle">&nbsp;<font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></font>&nbsp;</td>
						<td align="right" valign="middle"><font face="{T_FONTFACE2}" size="{T_FONTSIZE2}">{PAGINATION}</font></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table></div>

<div align="center"><table width="98%" border="0">
	<tr>
		<td align="left" valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>