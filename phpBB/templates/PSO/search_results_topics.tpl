
<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left" valign="bottom" nowrap="nowrap"><span class="gensmall"><a href="{U_INDEX}">{SITENAME} {L_INDEX}</span></td>
	</tr>
</table>

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table width="100%" cellpadding="4" cellspacing="1" border="0">
			<tr>
				<td class="cat" colspan="7" align="center"><span class="cattitle"><b>{L_SEARCH} {L_FOUND} <u>{SEARCH_MATCHES}</u> {L_MATCHES}</b></span></td>
			</tr>
			<tr>
				<th width="4%">&nbsp;</th>
				<th>&nbsp;{L_FORUM}&nbsp;</th>
				<th>&nbsp;{L_TOPICS}&nbsp;</th>
				<th>&nbsp;{L_REPLIES}&nbsp;</th>
				<th>&nbsp;{L_AUTHOR}&nbsp;</th>
				<th>&nbsp;{L_VIEWS}&nbsp;</th>
				<th>&nbsp;{L_LASTPOST}&nbsp;</th>
			</tr>
			<!-- BEGIN searchresults -->
			<tr>
				<td class="row1" align="center" valign="middle">&nbsp;{searchresults.FOLDER}&nbsp;</td>
				<td class="row2">&nbsp;<span class="gensmall"><a href="{searchresults.U_VIEW_FORUM}">{searchresults.FORUM_NAME}</a></span></td>
				<td class="row2">&nbsp;<span class="gensmall">{searchresults.NEWEST_POST_IMG}{searchresults.TOPIC_TYPE}<a href="{searchresults.U_VIEW_TOPIC}">{searchresults.TOPIC_TITLE}</a>&nbsp;{searchresults.GOTO_PAGE}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{searchresults.REPLIES}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen"><a href="{searchresults.U_TOPIC_POSTER_PROFILE}">{searchresults.TOPIC_POSTER}</a></span></td>
				<td class="row1" align="center" valign="middle"><span class="gen">{searchresults.VIEWS}</span></td>
				<td class="row2" align="center" valign="middle" nowrap="nowrap"><span class="gensmall">{searchresults.LAST_POST}</span></td>
			</tr>
			<!-- END searchresults -->
			<tr>
				<td class="cat" colspan="7"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td align="left" valign="middle">&nbsp;<span class="gen">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span>&nbsp;</td>
						<td align="right" valign="middle"><span class="gen">{PAGINATION}&nbsp;</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table width="98%" border="0" align="center">
	<tr>
		<td align="left" valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
