 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}" class="nav">{FORUM_NAME}</a></span></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr> 
	<td align="left" colspan="2" class="forumline"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr><form method="post" action="{S_MODCP_ACTION}">
		  <td class="innerline"> 
			  <table width="100%" cellpadding="4" cellspacing="1" border="0">
				<tr> 
				  <td class="cat" colspan="5" align="center" height="28"><span class="cattitle">{L_MOD_CP}</span> 
				  </td>
				</tr>
				<tr>
				  <td class="row3" colspan="5" align="center"><span class="gensmall">{L_MOD_CP_EXPLAIN}</span></td>
				</tr>
				<tr> 
				  <th width="4%">&nbsp;</th>
				  <th>&nbsp;{L_TOPICS}&nbsp;</th>
				  <th width="8%">&nbsp;{L_REPLIES}&nbsp;</th>
				  <th width="17%">&nbsp;{L_LASTPOST}&nbsp;</th>
				  <th width="5%">&nbsp;{L_SELECT}&nbsp;</th>
				</tr>
				<!-- BEGIN topicrow -->
				<tr> 
				  <td class="row1" align="center" valign="middle">{topicrow.FOLDER_IMG}</td>
				  <td class="row1">&nbsp;<span class="topictitle"><a href="{topicrow.U_VIEW_TOPIC}" class="topictitle">{topicrow.TOPIC_TITLE}</a></span></td>
				  <td class="row2" align="center" valign="middle"><span class="postdetails">{topicrow.REPLIES}</span></td>
				  <td class="row1" align="center" valign="middle"><span class="postdetails">{topicrow.LAST_POST}</span></td>
				  <td class="row2" align="center" valign="middle"> 
					<input type="checkbox" name="preform_op[]" value="{topicrow.TOPIC_ID}" />
				  </td>
				</tr>
				<!-- END topicrow -->
				<tr align="right"> 
				  <td class="cat" colspan="5" height="28"> {S_HIDDEN_FIELDS} 
					<input type="submit" name="delete" class="liteoption" value="{L_DELETE}" />
					&nbsp; 
					<input type="submit" name="move" class="liteoption" value="{L_MOVE}" />
					&nbsp; 
					<input type="submit" name="lock" class="liteoption" value="{L_LOCK}" />
					&nbsp; 
					<input type="submit" name="unlock" class="liteoption" value="{L_UNLOCK}" />
				  </td>
				</tr>
			  </table>
		  </td>
		</form></tr>
	  </table>
	</td>
  </tr>
</table>
<table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
  <tr> 
	<td align="left" valign="middle"><span class="nav">{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span></td>
	<td align="right" valign="top" nowrap><span class="nav">{PAGINATION}</span> 
	  <span class="gensmall"><br />
	  {S_TIMEZONE}</span></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr> 
	<td align="right">{JUMPBOX}</td>
  </tr>
</table>
