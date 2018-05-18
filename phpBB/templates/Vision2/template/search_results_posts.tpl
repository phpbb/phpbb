 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom"><span class="maintitle">{L_SEARCH_MATCHES}</span><br /></td>
  </tr>
</table>

<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td>
  </tr>
</table>

<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline" align="center">
  <tr> 
	<td width="150" height="25" class="row3" nowrap="nowrap" align="center"><span class="fontrow3">{L_AUTHOR}</span></td>
	<td width="100%" class="row3" nowrap="nowrap" align="center"><span class="fontrow3">{L_MESSAGE}</span></td>
  </tr>
  <!-- BEGIN searchresults -->
  <tr> 
	<td class="catHead" colspan="2" height="28"><span class="topictitle"><img src="templates/Vision2/images/folder.gif" align="absmiddle" />&nbsp; {L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}" class="topictitle">{searchresults.TOPIC_TITLE}</a></span></td>
  </tr>
  <tr> 
	<td width="150" align="left" valign="top" class="row1" rowspan="2"><span class="name"><b>{searchresults.POSTER_NAME}</b></span><br />
	  <br />
	  <span class="postdetails">{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br />
	  {L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b></span><br />
	</td>
	<td width="100%" valign="top"><span class="postdetails">{L_FORUM}:&nbsp;<b><a href="{searchresults.U_FORUM}" class="postdetails">{searchresults.FORUM_NAME}</a></b><br>{L_POSTED}: {searchresults.POST_DATE}<br>{L_SUBJECT}: <b><a href="{searchresults.U_POST}">{searchresults.POST_SUBJECT}</a></b></span><hr></td>
  </tr>
  <tr>
	<td valign="top"><span class="postbody">{searchresults.MESSAGE}</span></td>
  </tr>
    <tr>
        <td height="1" colspan="2"><table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td background="templates/Vision2/images/line_cols_2.gif" style="padding:0px"></td></tr></table></td>
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
