<script language="Javascript">
<!--
function x ()
{
	return;
}

function addBBcode(bbCode)
{
	document.posting.message.value=document.posting.message.value+bbCode;
	document.posting.message.focus();
	return;
}
-->
</script>
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
  <td><form action="{S_POST_ACTION}" method="post" name="posting">
   <table border="0" align="center" width="100%" bgcolor="#000000" cellpadding="0" cellspacing="1">
    <tr>
      <td>
         <table border="0" width="100%" cellpadding="3" cellspacing="1">
         	<tr class="tableheader">
         	  <td colspan="2">{L_POST_A}</td>
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
                    <td bgcolor="#CCCCCC">
                    <table border="0" with="100%">
                    		<tr><td>{MESSAGE_INPUT}</td>
                    			 <td width="60%">Instant BBCode<br>
                    			 	<a href="javascript: x()" onclick="addBBcode('[url] [/url]');"><img src="templates/Default/images//url.gif" width="72" height="16" border="0" alt="Insert URL BBCode"></a>&nbsp;
                            	<a href="javascript: x()" onclick="addBBcode('[email] [/email]');"><img src="templates/Default/images//email_url.gif" width="72" height="16" border="0" alt="Insert Email Address"></a>
										<br>
										<a href="javascript: x()" onclick="addBBcode('[b] [/b]');"><img src="templates/Default/images//bold.gif" width="72" height="16" border="0" alt="Bold"></a>&nbsp;
										<a href="javascript: x()" onclick="addBBcode('[i] [/i]');"><img src="templates/Default/images//italics.gif" width="72" height="16" border="0 alt="Italics"></a>
										<br>
										<a href="javascript: x()" onclick="addBBcode('[quote] [/quote]');"><img src="templates/Default/images//quote.gif" width="72" height="16" border="0" alt="Quote"></a>&nbsp;
                           	<a href="javascript: x()" onclick="addBBcode('[code] [/code]');"><img src="templates/Default/images//code.gif" width="72" height="16" border="0" alt="Code - UBBCode&#153;"></a>
										<br>
										<a href="javascript: x()" onclick="addBBcode('[list]');"><img src="templates/Default/images//list-start.gif" width="72" height="16" border="0" alt="Start List"></a>&nbsp;
                           	<a href="javascript: x()" onclick="addBBcode('[*]');"><img src="templates/Default/images//list-item.gif" width="72" height="16" border="0" alt="List Item"></a>
										<br>
										<a href="javascript: x()" onclick="addBBcode('[/list]');"><img src="templates/Default/images//list-end.gif" width="72" height="16" border="0" alt="End List"></a>&nbsp;
                           	<a href="javascript: x()" onclick="addBBcode('[img] [/img]');"><img src="templates/Default/images//image.gif" width="72" height="16" border="0" alt="Display Image"></a>
                        	</td>
                        </tr>
                   </table>
                   </td>
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
