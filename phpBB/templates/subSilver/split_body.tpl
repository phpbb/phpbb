<form method="post" action="{S_SPLIT_ACTION}">
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" valign="bottom" nowrap="nowrap"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a> 
		-> <a href="{U_VIEW_FORUM}" class="nav">{FORUM_NAME}</a></span></td>
	</tr>
  </table>
  <table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
	<tr align="center"> 
	  <td class="catHead" colspan="3" height="28"><b><span class="cattitle">{L_SPLIT_TOPIC}</span></b></td>
	</tr>
	<tr align="center"> 
	  <td class="row2" colspan="3"><span class="gensmall">{L_SPLIT_TOPIC_EXPLAIN}</span></td>
	</tr>
	<tr> 
	  <td class="row1"><span class="gen">{L_SPLIT_SUBJECT}</span></td>
	  <td class="row2" colspan="2"><span class="genmed"> 
		<input type="text" size="45" maxlength="100" name="subject" style="width:450px" class="post">
		</span></td>
	</tr>
	<tr> 
	  <td class="row1"><span class="gen">{L_SPLIT_FORUM}</span></td>
	  <td class="row2" colspan="2"><span class="genmed">{FORUM_INPUT}</span></td>
	</tr>
	{POLL_DISPLAY} 
	<tr> 
	  <th width="22%" height="26" class="thLeft">{L_AUTHOR}</th>
	  <th>{L_MESSAGE}</th>
	  <th class="thRight">{L_SELECT}</th>
	</tr>
	<!-- BEGIN postrow -->
	<tr> 
	  <td width="22%" align="left" valign="top" class="row1" rowspan="2"><span class="name">{postrow.POSTER_NAME}</span> 
	  </td>
	  <td valign="top" class="row2"> <img src="images/icon_minipost.gif" alt="Post image icon"><span class="postdetails">{L_POSTED}: 
		{postrow.POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_POST_SUBJECT}: {postrow.POST_SUBJECT}</span></td>
	  <td valign="middle" class="row1" align="center" rowspan="2"> 
		<input type="checkbox" name="preform_op[]" value="{postrow.POST_ID}">
	  </td>
	</tr>
	<tr> 
	  <td valign="top" class="row2"><span class="postbody">{postrow.MESSAGE}</span></td>
	</tr>
	<tr> 
	  <td colspan="3" height="1" class="spaceRow"><img src="templates/subSilver/images/spacer.gif" width="1" height="1" alt="."></td>
	</tr>
	<!-- END postrow -->
	<tr> 
	  <td class="catBottom" colspan="3" height="28" align="center">{S_HIDDEN_FIELDS} 
		<input type="submit" name="split_type_all" value="{L_SPLIT_POSTS}" class="liteoption">
		&nbsp;&nbsp; 
		<input type="submit" name="split_type_beyond" value="{L_SPLIT_AFTER}" class="liteoption">
	  </td>
	</tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="right" valign="top" nowrap="nowrap"><span class="gensmall">{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>