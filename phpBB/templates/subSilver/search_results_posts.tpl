 
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
<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
  <tr> 
	<th width="22%" height="25" class="thCornerL">{L_AUTHOR}</th>
	<th class="thCornerR">{L_MESSAGE}</th>
  </tr>
  <!-- BEGIN searchresults -->
  <tr> 
	<td class="cat" colspan="2" height="28"><span class="topictitle"><img src="templates/subSilver/images/folder.gif" align="absmiddle">&nbsp;&nbsp;{L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}" class="topictitle">{searchresults.TOPIC_TITLE}</a></span></td>
  </tr>
  <tr> 
	<td width="22%" align="left" valign="top" class="row1" rowspan="2"><span class="name"><a href="{searchresults.U_USER_PROFILE}" class="name"><b>{searchresults.POSTER_NAME}</b></a></span><br />
	  <br />
	  <span class="postdetails">{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br />
	  {L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b></span><br />
	</td>
	<td valign="top" class="row1"> <img src="templates/subSilver/images/icon_minipost.gif" alt="Post image icon"><span class="postdetails">{L_FORUM}:&nbsp;<b><a href="{U_FORUM}" class="postdetails">{searchresults.FORUM_NAME}</a></b>&nbsp;&nbsp;&nbsp;{L_POSTED}: 
	  {searchresults.POST_DATE}&nbsp;&nbsp;&nbsp;Subject: <b><a href="{searchresults.U_POST}">{searchresults.POST_SUBJECT}</a></b></span></td>
  </tr>
  <tr>
	<td valign="top" class="row1"><span class="postbody">{searchresults.MESSAGE}</span></td>
  </tr>
  <!-- END searchresults -->
  <tr> 
	<td class="catBottom" colspan="2" height="28" align="center">&nbsp; </td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="top"><span class="nav">{PAGE_NUMBER}</span></td>
	<td align="right" valign="top" nowrap="nowrap"><span class="nav">{PAGINATION}</span><br /><span class="gensmall">{S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>
