<tr>
  <td>
	  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
	    <td>
	      <table border="0" width="100%" cellpadding="3" cellspacing="1">
	        <tr class="tableheader">
	           <td width="5%">&nbsp;</td>
	           <td>{L_FORUM}</td>
	           <td align="center" width="5%">{L_TOPICS}</td>
	           <td align="center" width="5%">{L_POSTS}</td>
	           <td align="center" width="15%">{L_LASTPOST}</td>
	           <td align="center" width="5%">{L_MODERATOR}</td>
	        </tr>
	        <!-- BEGIN catrow -->
	        <tr class="catheader">
	          <td colspan="6"><a href="{catrow.PHP_SELF}?viewcat={catrow.CAT_ID}">{catrow.CAT_DESC}</a></td>
	        </tr>
	        <!-- BEGIN forumrow -->
	        <tr bgcolor="{catrow.forumrow.ROW_COLOR}" class="tablebody">
	          <td width="5%" align="center" valign="middle">{catrow.forumrow.FOLDER}</td>
            <td><a href="viewforum.{PHPEX}?{catrow.POST_FORUM_URL}={catrow.forumrow.FORUM_ID}&{catrow.forumrow.POSTS}">{catrow.forumrow.FORUM_NAME}</a><br>{catrow.forumrow.FORUM_DESC}</td>
            <td width="5%" align="center" valign="middle">{catrow.forumrow.TOPICS}</td>
	          <td width="5%" align="center" valign="middle">{catrow.forumrow.POSTS}</td>
	          <td width="15%" align="center" valign="middle">{catrow.forumrow.LAST_POST}</td>
	          <td width="5%" align="center" valign="middle">{catrow.forumrow.MODERATORS}</td>
          </tr>
	        <!-- END forumrow -->
	        <!-- END catrow -->
	       </table>
	      </td>
	    </tr>
	</table>
	</td>
</tr>
