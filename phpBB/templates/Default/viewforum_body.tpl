<tr>
	<td><table border="0" width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td rowspan="2" valign="top"><table border="0" align="left" width="20%" bgcolor="#000000" cellpadding="0" cellspacing="1">
				<tr>
					<td><table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
						<tr>
							<td align="left" style="{font-size: 8pt; height: 55px;}" nowrap><div style="{font-size:10pt; font-weight: bold}">{FORUM_NAME}</div>Moderated by - {MODERATORS}<br><a href="{U_INDEX}">{SITENAME} - Forum Index</a> >> {FORUM_NAME}</td>
						</tr>
					</table></td>
				</tr>
			</table></td>
			<td valign="middle"><table border="0" align="right" bgcolor="#000000" cellpadding="0" cellspacing="1">
				<tr>
					<td><table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
						<tr>
							<td align="center" style="{font-size: 8pt;}"><a href="{U_POST_NEW_TOPIC}"><img src="images/newpost.gif" alt="Post New Topic" border="0"></a></td>
						</tr>
					</table></td>
				</tr>
			</table></td>
		</tr>
		<tr><form method="post" action="{S_POST_DAYS_ACTION}">
			<td align="right" valign="bottom" style="{font-size: 8pt; height: 55px;}" nowrap>View Topics in Previous:&nbsp;{S_SELECT_POST_DAYS}&nbsp;<input type="submit" value="Go"></td>
		</form></tr>
	</table></td>
</tr>
<tr>
  <td>
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
	    <td>
	      <table border="0" width="100%" cellpadding="3" cellspacing="1">
	        <tr class="tableheader">
	           <td width="5%">&nbsp;</td>
	           <td>Topic</td>
	           <td align="center" width="5%">Replies</td>
                   <td align="center" width="10%">Topic Poster</td>
	           <td align="center" width="5%">Views</td>
	           <td align="center" width="15%">Last Post</td>
	        </tr>
	        <!-- BEGIN topicrow -->
	        <tr bgcolor="#DDDDDD" class="tablebody">
	          <td width="5%" align="center" valign="middle">{topicrow.FOLDER}</td>
              <td><a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a>&nbsp;{topicrow.GOTO_PAGE}</td>
              <td width="5%" align="center" valign="middle">{topicrow.REPLIES}</td>
              <td width="10%" align="center" valign="middle">{topicrow.TOPIC_POSTER}</td>
	          <td width="5%" align="center" valign="middle">{topicrow.VIEWS}</td>
	          <td width="15%" align="center" valign="middle">{topicrow.LAST_POST_TIME}<br />{L_BY} <a href="{topicrow.U_LAST_POST_USER_PROFILE}">{topicrow.LAST_POST_USER}</a></td>
           </tr>
	        <!-- END topicrow -->
	       </table>
	      </td>
	    </tr>
	</table>
    </td>
  </tr>

	<tr>
		<td align="center" bgcolor="#000000"><table border="0" width="100%" cellpadding="0" cellspacing="1">
			<tr>
				<td bgcolor="#CCCCCC"><table border="0" width="100%" cellpadding="3" cellspacing="1">
					<tr class="tablebody">
						<td align="left"><b>{S_TIMEZONE}</b></td>
						<td align="right">{PAGINATION}</td>
					</tr>
				</table></td>
			</tr>
		</table></td>
	</tr>
<tr>
	<td>
	<table border="0" width="100%" align="center" cellpadding="0" cellspacing="0">
	<tr>
	<td>
	  <table border="0" align="left" width="20%" bgcolor="#000000" cellpadding="0" cellspacing="1">
	  <tr>
	    <td>
	      <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
	        <tr>
	          <td align="center" style="{font-size: 8pt;}"><a href="{U_POST_NEW_TOPIC}"><img src="images/newpost.gif" alt="Post New Topic" border="0"></a></td>
	        </tr>
	      </table>
	    </td>
	  </tr>
	  </table>

	   <table border="0" align="right" width="20%" bgcolor="#000000" cellpadding="0" cellspacing="1">
	    <tr>
	      <td>
	        <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
	          <tr>
	            <td align="right" style="{font-size: 8pt; height: 55px;}">{JUMPBOX}</td>
	          </tr>
	        </table>
	      </td>
	    </tr>
	    </table>

	 </td>
	</tr>
	<tr>
		<td align="left" style="{font-size: 8pt; height: 55px;}">{S_AUTH_LIST}</td>
	</tr>
  </table>
 </td>
</tr>