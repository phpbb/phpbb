 
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr><form method="post" action="{S_MSG_DAYS_ACTION}">
	<td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	<td align="right" nowrap><span class="gensmall">{L_DISPLAY_MESSAGES}: {S_SELECT_MSG_DAYS} 
	  <input type="submit" value="Go" name="submit" class="liteoption" />
	  </span></td>
  </form></tr>
</table>
<form method="post" action="{S_PRIVMSGS_ACTION}">
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td>&nbsp;<span class="cattitle">{INBOX}&nbsp;&nbsp;&nbsp;{SENTBOX}&nbsp;&nbsp;&nbsp;{OUTBOX}&nbsp;&nbsp;&nbsp;{SAVEBOX}</span></td>
	  <td align="right" valign="bottom">{S_POST_NEW_MSG}</td>
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
				  <th width="5%" height="25">&nbsp;{L_FLAG}&nbsp;</th>
				  <th width="55%">&nbsp;{L_SUBJECT}&nbsp;</th>
				  <th width="20%">&nbsp;{L_FROM_OR_TO}&nbsp;</th>
				  <th width="15%">&nbsp;{L_DATE}&nbsp;</th>
				  <th width="5%">&nbsp;{L_MARK}&nbsp;</th>
				</tr>
				<!-- BEGIN listrow -->
				<tr> 
				  <td width="5%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.ICON_FLAG_IMG}</span></td>
				  <td width="55%" valign="middle" class="{listrow.ROW_CLASS}"><span class="topictitle">&nbsp;<a href="{listrow.U_READ}" class="topictitle">{listrow.SUBJECT}</a></span></td>
				  <td width="20%" valign="middle" align="center" class="{listrow.ROW_CLASS}"><span class="name">&nbsp;<a href="{listrow.U_FROM_USER_PROFILE}" class="name">{listrow.FROM}</a></span></td>
 				  <td width="15%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.DATE}</span></td>
				  <td width="5%" align="center" valign="middle" class="{listrow.ROW_CLASS}"><span class="postdetails">{listrow.S_DEL_CHECKBOX}</span></td>
				</tr>
				<!-- END listrow -->
				<!-- BEGIN nomessages -->
				<tr> 
				  <td class="row1" colspan="5" align="center" valign="middle"><span class="gen">{L_NO_MESSAGES}</span></td>
				</tr>
				<!-- END nomessages -->
				<tr> 
				  <td class="cat" colspan="5"> 
					<table width="100%" cellspacing="0" cellpadding="0" border="0">
					  <tr> 
						<td align="right" valign="middle">&nbsp;<span class="gen">&nbsp;</span>{S_HIDDEN_FIELDS} 
						  <input type="submit" name="save" value="Save Marked" class="mainoption" />
						  &nbsp; 
						  <input type="submit" name="delete" value="Delete Marked" class="liteoption" />
						  &nbsp; 
						  <input type="submit" name="deleteall" value="Delete All" class="liteoption" />
						</td>
					  </tr>
					</table>
				  </td>
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
	  <td align="left" valign="middle"><span class="nav">{S_POST_NEW_MSG}&nbsp;&nbsp;&nbsp;{L_PAGE} 
						  <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></span></td>
	  <td align="right" valign="top"><span class="nav">{PAGINATION}</span> <span class="gensmall"><br />
		{S_TIMEZONE}</span></td>
	</tr>
  </table>
</form>
<table width="100%" border="0">
  <tr> 
	<td align="right" valign="top">{JUMPBOX}</td>
  </tr>
</table>
