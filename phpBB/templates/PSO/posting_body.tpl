<table width="80%" cellspacing="0" cellpadding="4" border="0" align="center">
	<tr>
		<td align="left"><span class="gensmall"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></span></td>
	</tr>
</table>

<script language="JavaScript" type="text/javascript">
<!--
function insertCode(formObj, selectObj)
{
	formObj.message.value += selectObj.options[selectObj.selectedIndex].value;
	return;
}
function submit_check_alert(formObj)
{
<!--
	if(formObj.elements["delete"] == "defined")
	{
		if(formObj.elements["delete"].checked)
		{
			result = confirm("{L_CONFIRM_DELETE}");
			if(!result)
			{
				return false;
			}
		}
	}
//-->
	return true;
}
//-->
</script>

<table width="80%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr><form action="{S_POST_ACTION}" method="POST" onSubmit="return submit_check_alert(this);">
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<th class="secondary" colspan="2"><b>{L_POST_A}</b></td>
	        </tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
				<td class="row2">{USERNAME_INPUT}</td>
			</tr>
            <tr>
				<td class="row1"><span class="gen"><b>{L_SUBJECT}</b></span></td>
				<td class="row2"><span class="courier">{SUBJECT_INPUT}</span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_MESSAGE_BODY}</b></span><br><br><span class="gensmall">{HTML_STATUS}<br>{BBCODE_STATUS}</span></td>
				<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="gen">{MESSAGE_INPUT}</span></td>
						<td valign="top">&nbsp;<span class="gensmall">BBcodes:</span><br><span class="couriersmall"><select class="small" name="addbbcode" size="6" onchange="insertCode(this.form, this);"> <option value="[b][/b]">[b] [/b]</option> <option value="[i][/i]">[i] [/i]</option> <option value="[quote][/quote]">[quote] [/quote]</option> <option value="[code][/code]">[code] [/code]</option> <option value="[list][/list]">[list] [/list]</option> <option value="[list=][/list]">[list=] [/list]</option>	<option value="[img][/img]">[img] [/img]</option> <option value="[url][/url]">[url] [/url]</option></select></span><br clear="all">&nbsp;<span class="gensmall">Smiley codes:</span><br><span class="couriersmall"><select class="small" name="addsmiley" size="1" onchange="insertCode(this.form, this);"> <option value=":)">Smiley</option> <option value=":(">Frown</option> <option value=":d">Big Grin</option> <option value=";)">Wink</option> <option value=":o">Eek!</option> <option value="8)">Cool</option> <option value=":?">Confused</option> <option value=":p">Razz</option> <option value=":|">Mad</option></select></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_OPTIONS}</b></span></td>
				<td class="row2"><span class="gen">{HTML_TOGGLE}<br>{BBCODE_TOGGLE}<br>{SMILE_TOGGLE}<br>{SIG_TOGGLE}<br>{NOTIFY_TOGGLE}<br>{DELETE_TOGGLE}<br> &nbsp;&nbsp;&nbsp;&nbsp;{TYPE_TOGGLE}</span></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center">{S_HIDDEN_FORM_FIELDS}<input type="submit" name="preview" value="{L_PREVIEW}">&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;<input type="submit" name="cancel" value="{L_CANCEL}"></td>
			</tr>
		</table></td>
	</form></tr>
</table>

<table cellspacing="2" border="0" width="80%" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
