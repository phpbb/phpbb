<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="2" align="center"><span class="cattitle">&nbsp;<b>{L_SEARCH} - {SEARCH_MATCHES} Matches</b>&nbsp;</span></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="0" cellspacing="1" width="100%">
			<!-- BEGIN searchresults -->
			<tr>
				<td class="cat" colspan="2" align="left"><table cellpadding="4" cellspacing="1" border="0">
					<tr>
						<td><span class="gen"><img src="images/folder.gif">&nbsp;{L_FORUM}:&nbsp;<a href="{U_FORUM}">{searchresults.FORUM_NAME}</a></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1" rowspan="2" width="20%"><table height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td valign="top"><span class="gen"><b><a href="{searchresults.U_USER_PROFILE}">{searchresults.POSTER_NAME}</a></b></span><br><br><span class="gen">{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br>{L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b><br></td>
					</tr>
				</table></td>
				<td width="80%"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td class="row1"><span class="gen">{L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}">{searchresults.TOPIC_TITLE}</a></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td><table width="100%" height="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td class="row2"><img src="images/icon_minipost.gif" alt="Post image icon" border="0"></a><span class="gensmall">{L_POSTED}: {searchresults.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Post Subject: <a href="{searchresults.U_POST}">{searchresults.POST_SUBJECT}</a></span><hr></td>
					</tr>
					<tr>
						<td height="100%" class="row2"><span class="gen">{searchresults.MESSAGE}</span><br><br></td>
					</tr>
				</table></td>
			</tr>
			<!-- END searchresults -->
			<tr>
				<td class="cat" colspan="2"><table width="100%" cellspacing="0" cellpadding="4" border="0">
					<tr>
						<td align="left" valign="middle">&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="middle"><span class="gen">{PAGINATION}</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<div align="center"><table width="98%" border="0">
	<tr>
		<td align="left" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap="nowrap">{JUMPBOX}</td>
	</tr>
</table>