<tr>
  <td><form action="{S_MODCP_URL}" method="post">
  <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
	    <td>
	      <table border="0" width="100%" cellpadding="0" cellspacing="1">
	        <tr class="tableheader">
	           <td width="100%" align="center"><b>Moderator Contol Panel<b></td>
	        </tr>
	        <tr class="tablebody" bgcolor="#CCCCCC">
	        	<td width="100%" style="{padding: 5px; font-size: 10pt;}">
	        		{L_MOD_EXPLAIN}
	        	</td>
	        </tr>
	        <tr class="tablebody" bgcolor="#000000">
	        	<td>
	        		<table width="100%" border="0" cellspacing="1" cellpadding="2">
	        			<tr class="tableheader">
	        				<td align="center" width="25%">{L_TOPICS}</td>
	        				<td align="center" width="5%">{L_REPLIES}</td>
	        				<td align="center" width="15%">{L_LASTPOST}</td>
	        				<td align="center" width="5%">{L_SELECT}</td>
	        			</tr>
	        			<!-- BEGIN topicrow -->
	        			<tr class="tablebody">
	        				<td bgcolor="#CCCCCC"><a href="{topicrow.U_VIEW_TOPIC}">{topicrow.TOPIC_TITLE}</a></td>
	        				<td bgcolor="#DDDDDD" align="center">{topicrow.REPLIES}</td>
	        				<td bgcolor="#CCCCCC" align="center">{topicrow.LAST_POST}</td>
	        				<td bgcolor="#DDDDDD"><input type="checkbox" name="preform_op[]" value="{topicrow.TOPIC_ID}">{L_SELECT}</td>
	        			</tr>		
	        			<!-- END topicrow -->
	        			<tr class="tablebody">
	        				<td bgcolor="#CCCCCC" colspan="4">
	        				<table border="0" width="100%">
	        					<tr>
	        						<td>{L_PAGE} <b>{ON_PAGE}</b> {L_OF} <b>{TOTAL_PAGES}</b></td>
	        						<td align="right">{PAGINATION}</td>
	        					</tr>
	        				</table>
	        				</td>
	        			</tr>
	        			<tr class="tableheader">
	 	       			<td colspan="4" align="right">
	 	       				<input type="hidden" name="{POST_FORUM_URL}" value="{FORUM_ID}">
	 	       				<input type="submit" name="delete" value="{L_DELETE}">&nbsp;&nbsp;
	 	       				<input type="submit" name="move" value="{L_MOVE}">&nbsp;&nbsp;
	 	       				<input type="submit" name="lock" value="{L_LOCK}">&nbsp;&nbsp;
	 	       				<input type="submit" name="unlock" value="{L_UNLOCK}">
	 	       			</td>
	 	       		</tr>
	        		</table>
	        	</td>
	        </tr>
	       </table>
	      </td>
	    </tr>
	</table>
	</form></td>
</tr>
