<tr>
	<td>
	  <table border="0" align="right" width="20%" bgcolor="#000000" cellpadding="0" cellspacing="1">
	  <tr>
	    <td>
	      <table border="0" width="100%" bgcolor="#CCCCCC" cellpadding="1" cellspacing="1">
	        <tr>
                  <td align="center" style="{font-size: 8pt;}">{L_POSTNEWIN}<br><a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></td>
	        </tr>
	      </table>
	    </td>
	  </tr>
	  </table>
	 </td>
	</tr>
	<tr>
  <td><form action="{S_POST_ACTION}" method="post">
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
         <table border="0" width="100%" cellpadding="3" cellspacing="1">
	        <tr class="tablebody">
	           <td bgcolor="#DDDDDD" width="15%">{L_ABOUT_POST}</td>
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
                    <td bgcolor="#DDDDDD">{L_MESSAGE_BODY}<br><br>
                      {HTML_STATUS}<br>{BBCODE_STATUS}</td>
                    <td bgcolor="#CCCCCC">{MESSAGE_INPUT}</td>
                </tr>
                <tr class="tablebody">
                      <td bgcolor="#DDDDDD">{L_OPTIONS}</td>
                       <td bgcolor="#CCCCCC">
                       {HTML_TOGGLE}<br>{BBCODE_TOGGLE}<br>{SMILE_TOGGLE}<br>{SIG_TOGGLE}<br>{NOTIFY_TOGGLE}</td>
		</tr>
        <tr class="tableheader">
		   <td align="center" colspan="2">{S_HIDDEN_FORM_FIELDS}
		   <input type="submit" name="preview" value="{L_PREVIEW}">&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;<input type="submit" name="cancel" value="{L_CANCEL}"></td>
		</tr>
	       </table>
	      </td>
	    </tr>
	</table>
    </form></td>
  </tr>
