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
			<!-- BEGIN anon_user -->
            <tr class="tablebody">
	           <td bgcolor="#DDDDDD">{L_USERNAME}</td>
	           <td bgcolor="#CCCCCC"><input type="text" name="username" size="25" maxlength="25" value="{USERNAME}" /></td>
            </tr>
			<!-- END anon_user -->
                <tr class="tablebody">
                   <td bgcolor="#DDDDDD">{L_SUBJECT}</td>
                   <td bgcolor="#CCCCCC"><input type="text" name="subject" size="50" maxlength="100" value="{SUBJECT}" /></td>
                </tr>
                <tr class="tablebody">
                    <td bgcolor="#DDDDDD">{L_MESSAGE_BODY}<br><br>
                      {L_HTML_IS} <u>{HTML_STATUS}</u><br>{L_BBCODE_IS} <u>{BBCODE_STATUS}</u><br>{L_SMILIES_ARE} <u>{SMILIES_STATUS}</u></td>
                    <td bgcolor="#CCCCCC">
                    <table border="0" with="100%">
                    		<tr><td><textarea name="message" rows="10" cols="40" wrap="virtual">{MESSAGE}</textarea></td>
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
                       <td bgcolor="#CCCCCC"><table cellspacing="0" cellpadding="1" border="0">
					<!-- BEGIN html_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_html" {S_HTML_CHECKED} /></td>
						<td>{L_DISABLE_HTML}</td>
					</tr>
					<!-- END html_checkbox -->
					<!-- BEGIN bbcode_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_bbcode" {S_BBCODE_CHECKED} /></td>
						<td>{L_DISABLE_BBCODE}</td>
					</tr>
					<!-- END bbcode_checkbox -->
					<!-- BEGIN smilies_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_smilies" {S_SMILIES_CHECKED} /></td>
						<td>{L_DISABLE_SMILIES}</td>
					</tr>
					<!-- END smilies_checkbox -->
					<!-- BEGIN signature_checkbox -->
					<tr>
						<td><input type="checkbox" name="attach_sig" {S_SIGNATURE_CHECKED} /></td>
						<td>{L_ATTACH_SIGNATURE}</td>
					</tr>
					<!-- END signature_checkbox -->
					<!-- BEGIN notify_checkbox -->
					<tr>
						<td><input type="checkbox" name="notify" {S_NOTIFY_CHECKED} /></td>
						<td>{L_NOTIFY_ON_REPLY}</td>
					</tr>
					<!-- END notify_checkbox -->
					<!-- BEGIN delete_checkbox -->
					<tr>
						<td><input type="checkbox" name="delete" /></td>
						<td>{L_DELETE_POST}</td>
					</tr>
					<!-- END delete_checkbox -->
					<!-- BEGIN type_toggle -->
					<tr>
						<td></td>
						<td><br />{S_TYPE_TOGGLE}</td>
					</tr>
					<!-- END type_toggle -->
				</table></td>
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
