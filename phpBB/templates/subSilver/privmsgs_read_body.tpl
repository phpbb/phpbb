
<table width="100%" cellspacing="2" cellpadding="2" border="0" align="center">
  <tr> 
	<form method="post" action="{S_MSG_DAYS_ACTION}">
	  <td align="left"><span class="nav"><a href="{U_INDEX}" class="nav">{SITENAME}&nbsp;{L_INDEX}</a></span></td>
	</form>
  </tr>
</table>
<table width="100%" cellspacing="2" cellpadding="2" border="0">
  <tr> 
	<td valign="middle">&nbsp;<span class="forumlink">{INBOX}&nbsp;&nbsp;&nbsp;{SENTBOX}&nbsp;&nbsp;&nbsp;{OUTBOX}&nbsp;&nbsp;&nbsp;{SAVEBOX}</span></td>
	<td align="right" valign="bottom"><span class="cattitle">{S_POST_REPLY_MSG}&nbsp;&nbsp;{S_POST_NEW_MSG}</span></td>
  </tr>
</table>
<table width="100%" cellspacing="0" cellpadding="2" border="0" align="center">
  <tr>
	<td align="left" colspan="2" class="forumline"> 
	  <table width="100%" border="0" cellspacing="0" cellpadding="1">
		<tr><form method="post" action="{S_PRIVMSGS_ACTION}">
		  <td class="innerline">
			<table border="0" cellpadding="4" cellspacing="1" width="100%">
  <tr> 
				  <th width="22%" height="26">{L_FROM_OR_TO}</th>
	<th>{L_MESSAGE}</th>
  </tr>

  <tr> 
				<td width="22%" align="left" valign="top" class="row1"><span class="name"><a name="{postrow.U_POST_ID}"></a><b>{POSTER_NAME}</b></span><br />
				  <span class="postdetails">{POSTER_RANK}<br />
				  {RANK_IMAGE}{POSTER_AVATAR}<br />
	  <br />
				  {POSTER_JOINED}<br />
				  {POSTER_POSTS}<br />
				  {POSTER_FROM}</span><br />
	</td>
	<td valign="top" class="row1"> 
	  <table width="100%" cellspacing="0" cellpadding="3" border="0">
		<tr> 
		  			  <td valign="middle"><img src="images/icon_minipost.gif" alt="Post image icon"><span class="postdetails">{L_POSTED}: 
						{POST_DATE}&nbsp;&nbsp;&nbsp;&nbsp;{L_SUBJECT}: {POST_SUBJECT}</span></td>
		  			  <td align="right" valign="middle" nowrap>&nbsp; {EDIT_IMG} 
						{QUOTE_IMG}&nbsp;</td>
		</tr>
		<tr> 
		  <td valign="top" colspan="2"> 
			<hr size="1" />
						<span class="postbody">{MESSAGE}</span></td>
		</tr>
	  </table>
	</td>
  </tr>
  <tr> 
				<td width="22%" align="left" valign="middle" class="row1">&nbsp;</td>
	<td width="78%" height="28" valign="bottom" class="row1"> 
	  			  <table cellspacing="0" cellpadding="0" border="0" height="18" width="100%">
					<tr> 
					  <td valign="middle" nowrap>&nbsp;{PROFILE_IMG} {PM_IMG} 
						{EMAIL_IMG} {WWW_IMG} {AIM_IMG} {YIM_IMG} {MSN_IMG}&nbsp;</td>
					  <td valign="top" align="left">{ICQ_STATUS_IMG}{ICQ_ADD_IMG}</td>
					</tr>
				  </table>
	</td>
  </tr>

			  <tr> 
				<td class="cat" colspan="2"> 
				    <table width="100%" cellspacing="0" cellpadding="0" border="0">
					  <tr> 
						<td align="right" valign="middle">{S_HIDDEN_FIELDS} 
						  <input type="submit" name="save" value="Save Post" class="liteoption" />
						  &nbsp; 
						  <input type="submit" name="delete" value="Delete Post" class="liteoption" />
						</td>
					  </tr>
					</table>
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
	<td valign="top">{S_POST_NEW_MSG}&nbsp;&nbsp;{S_POST_REPLY_MSG}</td>
	<td align="right" valign="top"><span class="gensmall">{S_TIMEZONE}&nbsp;<br />
	  {JUMPBOX}</span></td>
  </tr>
</table>
