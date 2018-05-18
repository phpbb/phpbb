<!-- INCLUDE overall_header.tpl -->

<script type="text/javascript">
<!--
function checkForm(formObj)
{
	formErrors = false;
	if (formObj.message.value.length < 2)
	{
		formErrors = "{L_EMPTY_MESSAGE_EMAIL}";
	}
	else if ( formObj.subject.value.length < 2)
	{
		formErrors = "{L_EMPTY_SUBJECT_EMAIL}";
	}
	if (formErrors)
	{
		alert(formErrors);
		return false;
	}
}
//-->
</script>

<form action="{S_POST_ACTION}" method="post" name="post" onsubmit="return checkForm(this)">

{ERROR_BOX}

{IMG_THL}{IMG_THC}<span class="forumlink">{L_SEND_EMAIL_MSG}</span>{IMG_THR}<table class="forumlinenb">
<tr>
	<td class="row1 tw22pct"><span class="gen"><b>{L_RECIPIENT}</b></span></td>
	<td class="row2 tw78pct"><span class="gen"><b>{USERNAME}</b></span></td>
</tr>
<tr>
	<td class="row1 tw22pct"><span class="gen"><b>{L_SUBJECT}</b></span></td>
	<td class="row2 tw78pct"><span class="gen"><input type="text" name="subject" size="45" maxlength="100" style="width: 450px;" tabindex="2" class="post" value="{SUBJECT}" /></span></td>
</tr>
<tr>
	<td class="row1">
		<span class="gen"><b>{L_MESSAGE_BODY}</b></span><br />
		<span class="gensmall">{L_MESSAGE_BODY_DESC}</span>
	</td>
	<td class="row2"><span class="gen"><textarea name="message" rows="25" cols="40" style="width: 500px;" tabindex="3" class="post">{MESSAGE}</textarea></span></td>
</tr>
<tr>
	<td class="row1"><span class="gen"><b>{L_OPTIONS}</b></span></td>
	<td class="row2"><input type="checkbox" name="cc_email" value="1" checked="checked" />&nbsp;<span class="gen">{L_CC_EMAIL}</span></td>
</tr>
<tr><td class="catBottom" colspan="2">{S_HIDDEN_FIELDS}<input type="submit" tabindex="6" name="submit" class="mainoption" value="{L_SEND_EMAIL}" /></td></tr>
</table>{IMG_TFL}{IMG_TFC}{IMG_TFR}
</form>
<div align="right">{JUMPBOX}</div>
{TPL_CONTENT_BOTTOM}

<!-- INCLUDE overall_footer.tpl -->