<tr>
  <td><form action="posting.{PHPEX}" method="POST">
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
         <table border="0" width="100%" cellpadding="3" cellspacing="1">
	        <tr class="tablebody">
	           <td bgcolor="#DDDDDD" width="15%">{L_ABOUTPOST}</td>
	           <td bgcolor="#CCCCCC">{ABOUT_POSTING}</td>
	        </tr>
                <tr class="tablebody">
	           <td bgcolor="#DDDDDD">{L_USERNAME}</td>
	           <td bgcolor="#CCCCCC">{USERNAME_INPUT}</td>
                </tr>
                <tr class="tablebody">
                   <td bgcolor="#DDDDDD">{L_PASSWORD}</td>
                   <td bgcolor="#CCCCCC">{PASSWORD_INPUT}</td>
	        </tr>
                <tr class="tablebody">
                   <td bgcolor="#DDDDDD">{L_SUBJECT}</td>
                   <td bgcolor="#CCCCCC">{SUBJECT_INPUT}</td>
                </tr>
                <tr class="tablebody">
                    <td bgcolor="#DDDDDD">{L_MESSAGEBODY}<br><br>
                      {HTML_STATUS}<br>{BBCODE_STATUS}</td>
                    <td bgcolor="#CCCCCC">{MESSAGE_INPUT}</td>
                </tr>
                <tr class="tablebody">
                      <td bgcolor="#DDDDDD">{L_OPTIONS}</td>
                       <td bgcolor="#CCCCCC">
                       {HTML_TOGGLE}<br>{BBCODE_TOGGLE}<br>{SMILE_TOGGLE}<br>{SIG_TOGGLE}<br>{NOTIFY_TOGGLE}</td>
		</tr>
	        <tr class="tableheader">
		   <td align="center" colspan="2">
                   <input type="hidden" name="mode" value="{MODE}">
                   <input type="hidden" name="forum_id" value="{FORUM_ID}">
                   <input type="hidden" name="topic_id" value="{TOPIC_ID}">
                   <input type="submit" name="preview" value="{L_PREVIEW}">&nbsp;
                   <input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;
                   <input type="submit" name="cancel" value="{L_CANCEL}"></td>
		</tr>
	       </table>
	      </td>
	    </tr>
	</table>
    </form></td>
  </tr>
