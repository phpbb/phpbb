<tr>
  <td>
  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
  <tr>
    <td>
      <table border="0" width="100%" cellpadding="3" cellspacing="1">
        <tr><td wdith="90%" class="tablebody" bgcolor="#CCCCCC">This topic is {PAGES} long. {PAGINATION}</td>
            <td width="5%" class="tableheader" align="center"><a href="viewtopic.{PHPEX}?{S_TOPICS_URL}={TOPIC_ID}&view=older"><img src="images/prev.gif" alt="View previous topic" border="0"></a></td>
            <td width="5%" class="tableheader" align="center"><a href="viewtopic.{PHPEX}?{S_TOPICS_URL}={TOPIC_ID}&view=newer"><img src="images/next.gif" alt="View next topic" border="0"></a></td>
        </tr>
      </table>
    </td>
  </tr>
  </table>
  </td>
  </tr>
  <tr>
  <td>
  
  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
	    <td>
	      <table border="0" width="100%" cellpadding="3" cellspacing="1">
	        <tr class="tableheader">
	           <td width="15%">Author</td>
	           <td>{TOPIC_TITLE}</td>
	        </tr>
	        <!-- BEGIN postrow -->
	        <tr bgcolor="{postrow.ROW_COLOR}" class="tablebody">
	          <td width="20%" align="left" valign="top">
                   <font style="{font-size: 10pt; font-weight: bold;}">{postrow.POSTER_NAME}</font><br>
                   {postrow.POSTER_RANK}<br>
                   {postrow.RANK_IMAGE}<br>
                   <br>
                   <font style="{font-size: 8pt;}">
                   {L_JOINED}: {postrow.POSTER_JOINED}<br>{L_POSTS}: {postrow.POSTER_POSTS}<br>{postrow.POSTER_FROM}</font>
                  </td>
	          <td>
                    <img src="images/posticon.gif"><font style="{font-size: 8pt;}">{postrow.L_POSTED}: {postrow.POST_DATE}</font><hr>
                    {postrow.MESSAGE}<hr>
                    {postrow.PROFILE_IMG}&nbsp;{postrow.EMAIL_IMG}&nbsp;{postrow.WWW_IMG}&nbsp;{postrow.ICQ_STATUS_IMG}&nbsp;{postrow.ICQ_ADD_IMG}&nbsp;{postrow.AIM_IMG}&nbsp;{postrow.YIM_IMG}&nbsp;{postrow.MSN_IMG}&nbsp;<img src="images/div.gif">&nbsp;{postrow.EDIT_IMG}&nbsp;{postrow.QUOTE_IMG}&nbsp;{postrow.PMSG_IMG}&nbsp;<img src="images/div.gif">&nbsp;{postrow.IP_IMG}&nbsp;{postrow.DELPOST_IMG}
                  </td>
          </tr>
	        <!-- END postrow -->
	       </table>
	      </td>
	    </tr>
	</table>
	</td>
</tr>
<tr>
  <td><table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
        <table border="0" width="100%" cellpadding="3" cellspacing="1">
          <tr>
 			  <td width="90%" class="tablebody" bgcolor="#CCCCCC"><table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td align="left" nowrap><b>{S_TIMEZONE}</b></td>
					<td align="right" nowrap>This topic is {PAGES} long. {PAGINATION}</td>
				</tr>
			  </table></td>
              <td width="5%" class="tableheader" align="center"><a href="viewtopic.{PHPEX}?{S_TOPICS_URL}={TOPIC_ID}&view=older"><img src="images/prev.gif" alt="View previous topic" border="0"></a></td>
              <td width="5%" class="tableheader" align="center"><a href="viewtopic.{PHPEX}?{S_TOPICS_URL}={TOPIC_ID}&view=newer"><img src="images/next.gif" alt="View next topic" border="0"></a></td>
          </tr>
        </table>
      </td>
    </tr>
  </table></td>
</tr>
