 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" valign="bottom" colspan="3"><span class="titlemedium">{L_SEARCH} 
	  - {SEARCH_MATCHES} Matches</span><br />
	</td>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" colspan="2" class="forumline"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr> 
		  <td class="innerline"> 
			<table border="0" cellpadding="3" cellspacing="1" width="100%">
			  <tr> 
				<th width="22%" height="28">{L_AUTHOR}</th>
				<th height="28">{L_MESSAGE}</th>
			  </tr>
			  <!-- BEGIN searchresults -->
			  <tr> 
				<td class="cat" colspan="2" height="28"><span class="topictitle"><img src="templates/subSilver/images/folder.gif" align="absmiddle">&nbsp;&nbsp;{L_TOPIC}:&nbsp;<a href="{searchresults.U_TOPIC}" class="topictitle">{searchresults.TOPIC_TITLE}</a></span></td>
			  </tr>
			  <tr> 
				<td width="22%" align="left" valign="top" class="row1"><span class="name"><a href="{searchresults.U_USER_PROFILE}" class="name"><b>{searchresults.POSTER_NAME}</b></a></span><br>
				  <br />
				  <span class="postdetails">{L_REPLIES}: <b>{searchresults.TOPIC_REPLIES}</b><br>
				  {L_VIEWS}: <b>{searchresults.TOPIC_VIEWS}</b></span><br />
				</td>
				<td valign="top" class="row1"> 
				  <table width="100%" cellspacing="0" cellpadding="3" border="0">
					<tr> 
					  <td valign="middle"><img src="templates/subSilver/images/icon_minipost.gif" alt="Post image icon"><span class="postdetails">{L_FORUM}:&nbsp;<b><a href="{U_FORUM}" class="postdetails">{searchresults.FORUM_NAME}</a></b>&nbsp;&nbsp;&nbsp;{L_POSTED}: 
						{searchresults.POST_DATE}&nbsp;&nbsp;&nbsp;Subject: <b><a href="{searchresults.U_POST}">{searchresults.POST_SUBJECT}</a></b></span></td>
					</tr>
					<tr> 
					  <td valign="top"> 
						<hr size="1" />
						<span class="postbody">{searchresults.MESSAGE}</span></td>
					</tr>
				  </table>
				</td>
			  </tr>
			  <!-- END searchresults -->
			  <tr> 
				<td class="cat" colspan="2" height="28" align="center">&nbsp; </td>
			  </tr>
			</table>
		  </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="top"><span class="nav"> {L_PAGE} <b>{ON_PAGE}</b> 
	  {L_OF} <b>{TOTAL_PAGES}</b></span></td>
	<td align="right" valign="top" nowrap><span class="nav">{PAGINATION}</span> 
	  <span class="gensmall"><br />
	  {S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr> 
	<td valign="top" align="right">{JUMPBOX}</td>
  </tr>
</table>
