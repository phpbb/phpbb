 
<script language="Javascript" type="text/javascript">
	//
	// Should really check the browser to stop this whining ...
	//
	function select_switch(status)
	{
		for (i = 0; i < document.privmsg_list.length; i++)
		{
			document.privmsg_list.elements[i].checked = status;
		}
	}
</script>

<table border="0" cellspacing="0" cellpadding="0" align="center" width="100%">
  <tr> 
	<td valign="top" align="center" width="100%"> 
	  <table height="40" cellspacing="2" cellpadding="2" border="0">
		<tr valign="middle"> 
		  <td>{INBOX_IMG}</td>
		  <td><span class="cattitle">{INBOX_LINK}&nbsp;&nbsp;</span></td>
		  <td>{SENTBOX_IMG}</td>
		  <td><span class="cattitle">{SENTBOX_LINK}&nbsp;&nbsp;</span></td>
		  <td>{OUTBOX_IMG}</td>
		  <td><span class="cattitle">{OUTBOX_LINK}&nbsp;&nbsp;</span></td>
		  <td>{SAVEBOX_IMG}</td>
		  <td><span class="cattitle">{SAVEBOX_LINK}&nbsp;&nbsp;</span></td>
		</tr>
	  </table>
	</td>
	<td align="right"> 
	  <!-- BEGIN box_size_notice -->
	  <table width="175" cellspacing="1" cellpadding="2" border="0" class="bodyline">
		<tr> 
		  <td colspan="3" width="100%" class="row1" nowrap="nowrap"><span class="gensmall">{BOX_SIZE_STATUS}</span></td>
		</tr>
		<tr> 
		  <td colspan="3" width="100%" class="row2">
			<table cellspacing="0" cellpadding="1" border="0">
			  <tr> 
				<td bgcolor="{T_TD_COLOR2}"><img src="templates/subSilver/images/spacer.gif" width="{INBOX_LIMIT_IMG_WIDTH}" height="8" alt="{INBOX_LIMIT_PERCENT}" /></td>
			  </tr>
			</table>
		  </td>
		</tr>
		<tr> 
		  <td width="33%" class="row1"><span class="gensmall">0%</span></td>
		  <td width="34%" align="center" class="row1"><span class="gensmall">50%</span></td>
		  <td width="33%" align="right" class="row1"><span class="gensmall">100%</span></td>
		</tr>
	  </table>
	  <!-- END box_size_notice -->
	</td>
  </tr>
</table>

<br clear="all" />

<form method="post" name="privmsg_list" action="{S_PRIVMSGS_ACTION}">
  <table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
	<tr> 
	  <td align="left" valign="middle">{POST_PM_IMG}</td>
	  <td align="left" width="100%">&nbsp;<span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a></span></td>
	  <td align="right" nowrap="nowrap"><span class="gensmall">{L_DISPLAY_MESSAGES}: 
		<select name="msgdays">{S_MSG_DAYS_OPTIONS}
		</select>
		<input type="submit" value="{L_GO}" name="submit_msgdays" class="liteoption" />
		</span></td>
	</tr>
  </table>

  <table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
	<tr> 
	  <th width="5%" height="25" class="thCornerL">&nbsp;{L_FLAG}&nbsp;</th>
	  <th width="55%" class="thTop">&nbsp;{L_SUBJECT}&nbsp;</th>
	  <th width="20%" class="thTop">&nbsp;{L_FROM_OR_TO}&nbsp;</th>
	  <th width="15%" class="thTop">&nbsp;{L_DATE}&nbsp;</th>
	  <th width="5%" class="thCornerR">&nbsp;{L_MARK}&nbsp;</th>
	</tr>
	<!-- BEGIN listrow -->
	<tr> 
	  <td width="5%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.ICON_FLAG_IMG}</span></td>
	  <td width="55%" valign="middle" class="{listrow.ROW_CLASS}"><span class="topictitle">&nbsp;<a href="{listrow.U_READ}" class="topictitle">{listrow.SUBJECT}</a></span></td>
	  <td width="20%" valign="middle" align="center" class="{listrow.ROW_CLASS}"><span class="name">&nbsp;<a href="{listrow.U_FROM_USER_PROFILE}" class="name">{listrow.FROM}</a></span></td>
	  <td width="15%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.DATE}</span></td>
	  <td width="5%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails"> 
		<input type="checkbox" name="mark[]2" value="{listrow.S_MARK_ID}" />
		</span></td>
	</tr>
	<!-- END listrow -->
	<!-- BEGIN nomessages -->
	<tr> 
	  <td class="row1" colspan="5" align="center" valign="middle"><span class="gen">{L_NO_MESSAGES}</span></td>
	</tr>
	<!-- END nomessages -->
	<tr> 
	  <td class="catBottom" colspan="5" height="28" align="right"> {S_HIDDEN_FIELDS} 
		<input type="submit" name="save" value="{L_SAVE_MARKED}" class="mainoption" />
		&nbsp; 
		<input type="submit" name="delete" value="{L_DELETE_MARKED}" class="liteoption" />
		&nbsp; 
		<input type="submit" name="deleteall" value="{L_DELETE_ALL}" class="liteoption" />
	  </td>
	</tr>
  </table>

  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
	<tr> 
	  <td align="left" valign="middle"><span class="nav">{POST_PM_IMG}</span></td>
	  <td align="left" valign="middle" width="100%"><span class="nav">{PAGE_NUMBER}</span></td>
	  <td align="right" valign="top" nowrap="nowrap"><b><span class="gensmall"><a href="javascript:select_switch(true);" class="gensmall">{L_MARK_ALL}</a> :: <a href="javascript:select_switch(false);" class="gensmall">{L_UNMARK_ALL}</a></span></b><br /><span class="nav">{PAGINATION}<br /></span><span class="gensmall">{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>

<table width="100%" border="0">
  <tr> 
	<td align="right" valign="top">{JUMPBOX}</td>
  </tr>
</table>
