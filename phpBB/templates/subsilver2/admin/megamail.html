<script language="javascript" type="text/javascript">
// <![CDATA[

message = new Array();
<!-- BEGIN mail_sessions -->
message[{mail_sessions.ID}] = "{mail_sessions.MESSAGE_BODY}";
<!-- END mail_sessions -->

function disableForm(theform)
{
	if (document.all || document.getElementById)
	{
		for (i = 0; i < theform.length; i++)
		{
			var tempobj = theform.elements[i];
			if (tempobj.type.toLowerCase() == "submit" || tempobj.type.toLowerCase() == "reset")
			{
				tempobj.disabled = true;
			}
		}
		return true;
	}
	else
	{
		alert("The form has been submitted. Please do NOT resubmit.");
		return false;
	}
}

function compileForm(m_id)
{
	str_find = new Array("&q_mg;", "&lt_mg;", "&gt_mg;");
	str_replace = new Array("\\\"", "<", ">");
	for(var i = 0; i < message[m_id].length; i++)
	{
		for (var j = 0; j < str_find.length; j++)
		{
			if (message[m_id].search(str_find[j]) != -1)
			{
				message[m_id] = message[m_id].replace(str_find[j],str_replace[j]);
			}
		}
	}
	document.post.message.value = message[m_id];
	document.post.message.focus();
	return;
}

// ]]>
</script>

<h1>{L_MAIL_SESSION_HEADER}</h1>
<form method="post" name="post" action="{S_USER_ACTION}" onsubmit="return disableForm(this);">
<table class="forumline">
<tr>
	<th>{L_ID}</th>
	<th>{L_GROUP}</th>
	<th>{L_EMAIL_SUBJECT}</th>
	<th>{L_MASS_PM}</th>
	<th>{L_TEXT_FORMAT}</th>
	<th>{L_BATCH_START}</th>
	<th>{L_BATCH_SIZE}</th>
	<th>{L_BATCH_WAIT}</th>
	<th>{L_SENDER}</th>
	<th>{L_STATUS}</th>
	<th>{L_ACTIONS}</th>
</tr>
<!-- BEGIN mail_sessions -->
<tr>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.ID}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.GROUP}</td>
	<td class="{mail_sessions.ROW}"><a href="javascript:compileForm({mail_sessions.ID});">{mail_sessions.SUBJECT}</a></td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.MASS_PM}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.EMAIL_FORMAT}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.BATCHSTART}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.BATCHSIZE}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.BATCHWAIT}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.SENDER}</td>
	<td class="{mail_sessions.ROW} row-center">{mail_sessions.STATUS}</td>
	<td class="{mail_sessions.ROW} row-center"><a href="{mail_sessions.U_DELETE}"><img src="{IMG_CMS_ICON_DELETE}" alt="{L_DELETE}" title="{L_DELETE}" /></a></td>
</tr>
<!-- END mail_sessions -->
<!-- BEGIN switch_no_sessions -->
<tr><td class="row2 row-center" colspan="11">{switch_no_sessions.EMPTY}</td></tr>
<!-- END switch_no_sessions -->
</table>

<h1>{L_EMAIL_TITLE}</h1>
<p>{L_EMAIL_EXPLAIN}</p>
{ERROR_BOX}
<table class="forumline">
<tr><th colspan="2">{L_COMPOSE}</th></tr>
<tr>
	<td class="row1 tdalignr"><b>{L_RECIPIENTS}</b></td>
	<td class="row2">{S_GROUP_SELECT}</td>
</tr>
<tr>
	<td class="row1 tdalignr"><b>{L_BATCH_SIZE}</b></td>
	<td class="row2"><span class="gen"><input type="text" name="batchsize" size="4" maxlength="4" tabindex="2" class="post" value="{DEFAULT_SIZE}" /></span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b>{L_BATCH_WAIT}</b></td>
	<td class="row2"><span class="gen"><input type="text" name="batchwait" size="4" maxlength="4" tabindex="3" class="post" value="{DEFAULT_WAIT}" /> s.</span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b>{L_MASS_PM}</b></td>
	<td class="row2"><span class="gen"><input type="radio" name="mass_pm" class="post" value="0" checked="checked" />&nbsp;{L_NO}&nbsp;&nbsp;<input type="radio" name="mass_pm" class="post" value="1" />&nbsp;{L_YES}</span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b>{L_TEXT_FORMAT}</b></td>
	<td class="row2"><span class="gen"><input type="radio" name="email_format" class="post" value="1" />&nbsp;{L_BBCODE}&nbsp;&nbsp;<input type="radio" name="email_format" class="post" value="0" checked="checked" />&nbsp;{L_HTML}&nbsp;&nbsp;<input type="radio" name="email_format" class="post" value="2" />&nbsp;{L_FULL_HTML}</span></td>
</tr>
<tr>
	<td class="row1 tdalignr"><b>{L_EMAIL_SUBJECT}</b></td>
	<td class="row2"><span class="gen"><input type="text" name="subject" size="45" maxlength="160" tabindex="4" class="post" value="{SUBJECT}" /></span></td>
</tr>
<tr>
	<td class="row1" align="right" valign="top"><span class="gen"><b>{L_EMAIL_MSG}</b></span></td>
	<td class="row2"><span class="gen"><textarea id="message" name="message" rows="15" cols="35" style="width:450px" tabindex="5" class="post">{MESSAGE}</textarea></span></td>
</tr>
<tr><td class="cat" colspan="2"><input type="submit" value="{L_SEND}" name="submit" class="mainoption" /></td></tr>
</table>

</form>
