 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom" colspan="3"><span class="maintitle">{L_SEARCH} 
	  - {SEARCH_MATCHES} Matches</span><br />
	</td>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<table width="100%" cellpadding="4" cellspacing="1" border="0" class="forumline">
  <tr> 
	<th width="4%" height="25" class="thCornerL">&nbsp;</th>
	<th class="thTop">&nbsp;{L_FORUM}&nbsp;</th>
	<th class="thTop">&nbsp;{L_TOPICS}&nbsp;</th>
	<th class="thTop">&nbsp;{L_REPLIES}&nbsp;</th>
	<th class="thTop">&nbsp;{L_AUTHOR}&nbsp;</th>
	<th class="thTop">&nbsp;{L_VIEWS}&nbsp;</th>
	<th class="thCornerR">&nbsp;{L_LASTPOST}&nbsp;</th>
  </tr>
  <!-- BEGIN searchresults -->
  <tr> 
	<td class="row1" align="center" valign="middle">{searchresults.FOLDER}</td>
	<td class="row1"><span class="forumlink"><a href="{searchresults.U_VIEW_FORUM}" class="forumlink">{searchresults.FORUM_NAME}</a></span></td>
	<td class="row2"><span class="topictitle">{searchresults.NEWEST_POST_IMG}{searchresults.TOPIC_TYPE}<a href="{searchresults.U_VIEW_TOPIC}" class="topictitle">{searchresults.TOPIC_TITLE}</a></span><span class="gensmall">&nbsp;{searchresults.GOTO_PAGE}</span></td>
	<td class="row1" align="center" valign="middle"><span class="postdetails">{searchresults.REPLIES}</span></td>
	<td class="row2" align="center" valign="middle"><span class="name"><a href="{searchresults.U_TOPIC_POSTER_PROFILE}" class="name">{searchresults.TOPIC_POSTER}</a></span></td>
	<td class="row1" align="center" valign="middle"><span class="postdetails">{searchresults.VIEWS}</span></td>
	<td class="row2" align="center" valign="middle" nowrap="nowrap"><span class="postdetails">{searchresults.LAST_POST}</span></td>
  </tr>
  <!-- END searchresults -->
  <!-- BEGIN nosearchresults -->
  <tr> 
	<td class="row1" colspan="7" height="30" align="center" valign="middle"><span class="gen">{L_NO_TOPICS}</span></td>
  </tr>
  <!-- END nosearchresults -->
  <tr> 
	<td class="catBottom" colspan="7" height="28" valign="middle">&nbsp; </td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="top"><span class="nav"> {L_PAGE} <b>{ON_PAGE}</b> 
	  {L_OF} <b>{TOTAL_PAGES}</b></span></td>
	<td align="right" valign="top" nowrap="nowrap"><span class="nav">{PAGINATION}</span><span class="gensmall"><br />
	  {S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>
