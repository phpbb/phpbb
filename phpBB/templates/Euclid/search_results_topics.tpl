 
<table width="98%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{L_INDEX}</a></span></td>
	</tr>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="98%" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="4" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="7"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td><span class="cattitle">{L_SEARCH_MATCHES}</span><br /><span class="gensmall">{PAGINATION}&nbsp;</span></td>
						<td align="right" valign="bottom"><span class="gensmall">{PAGE_NUMBER}</span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<th width="4%" height="25"></th>
				<th width="15%">{L_FORUM}</th>
				<th>{L_TOPICS}</th>
				<th width="15%" height="25">{L_AUTHOR}</th>
				<th width="8%" height="25">{L_REPLIES}</th>
				<th width="6%" height="25">{L_VIEWS}</th>
				<th width="15%" height="25">{L_LASTPOST}</th>
			</tr>
			<!-- BEGIN searchresults -->
			<tr>
				<td class="row1" align="center" valign="middle">{searchresults.FOLDER}</td>
				<td class="row2"><span class="gensmall"><a href="{searchresults.U_VIEW_FORUM}" class="forumlink">{searchresults.FORUM_NAME}</a></span></td>
				<td class="row1">&nbsp;<span class="gensmall">{searchresults.NEWEST_POST_IMG}{searchresults.TOPIC_TYPE}<a href="{searchresults.U_VIEW_TOPIC}">{searchresults.TOPIC_TITLE}</a>&nbsp;{searchresults.GOTO_PAGE}</span></td>
				<td class="row1" align="center" valign="middle"><span class="gen"><a href="{searchresults.U_TOPIC_POSTER_PROFILE}">{searchresults.TOPIC_POSTER}</a></span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{searchresults.REPLIES}</span></td>
				<td class="row2" align="center" valign="middle"><span class="gen">{searchresults.VIEWS}</span></td>
				<td class="row1" align="center" valign="middle" NOWRAP><span class="gensmall">{searchresults.LAST_POST}</span></td>
			</tr>
			<!-- END searchresults -->
			<!-- BEGIN nosearchresults -->
			<tr> 
				<td class="row1" colspan="7" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
			</tr>
			<!-- END nosearchresults -->
			<tr>
				<td class="cat" colspan="7"><table width="100%" cellspacing="0" cellpadding="2" border="0">
					<tr>
						<td valign="middle"><span class="gensmall">{PAGE_NUMBER}</span></td>
						<td align="right" height="28" valign="middle"><span class="cattitle"><span class="gensmall">{PAGINATION}</span></td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
</table>

<br clear="all" />

<table width="98%" cellspacing="2" border="0" align="center">
	<tr>
		<td><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right">{JUMPBOX}</td>
	</tr>
</table>
