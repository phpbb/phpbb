<tr>
  <td>
	  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
	    <td>
	      <table border="0" width="100%" cellpadding="0" cellspacing="1">
	        <tr class="tableheader">
	           <td width="5%">&nbsp;</td>
	           <td>Forum</td>
	           <td align="center" width="5%">Topics</td>
	           <td align="center" width="5%">Posts</td>
	           <td align="center" width="15%">Last Post</td>
	           <td align="center" width="5%">Moderator</td>
	        </tr>
	        <!-- BEGIN catrow -->
	        <tr class="catheader">
	          <td colspan="6"><a href="{PHP_SELF}?viewcat={CAT_ID}">{CAT_DESC}</a></td>
	        </tr>
	        <!-- BEGIN forumrow -->
	        <tr bgcolor="{ROW_COLOR}" class="tablebody">
	          <td width="5%" align="center" valign="middle">{FOLDER}</td>
            <td><a href="viewforum.{PHPEX}?forum_id={FORUM_ID}&{POSTS}">{FORUM_NAME}</a><br>{FORUM_DESC}</td>
            <td width="5%" align="center" valign="middle">{TOPICS}</td>
	          <td width="5%" align="center" valign="middle">{POSTS}</td>
	          <td width="15%" align="center" valign="middle">{LAST_POST}</td>
	          <td width="5%" align="center" valign="middle">{MODERATORS}</td>
          </tr>
	        <!-- END forumrow -->
	        <!-- END catrow -->
	       </table>
	      </td>
	    </tr>
	</table>
	</td>
</tr>
