<form action="{S_POST_ACTION}" method="POST"><table width="80%" cellspacing="0" cellpadding="4" border="0" align="center">
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
//-->
</script>

<table width="80%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle"><b>{L_POST_A}</b></span></td>
	        </tr>
			<!-- BEGIN anon_user -->
			<tr>
				<td class="row1"><span class="gen"><b>{L_USERNAME}</b></span></td>
				<td class="row2"><span class="courier"><input type="text" name="username" size="25" maxlength="25" value="{USERNAME}" /></span></td>
			</tr>
			<!-- END anon_user -->
            <tr>
				<td class="row1"><span class="gen"><b>{L_SUBJECT}</b></span></td>
				<td class="row2"><span class="courier"><input type="text" name="subject" size="50" maxlength="100" value="{SUBJECT}" /></span></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_MESSAGE_BODY}</b></span><br><br><span class="gensmall">{L_HTML_IS} <u>{HTML_STATUS}</u><br />{L_BBCODE_IS} <u>{BBCODE_STATUS}</u><br />{L_SMILIES_ARE} <u>{SMILIES_STATUS}</u></span></td>
				<td class="row2"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><span class="gen"><textarea name="message" rows="10" cols="45" wrap="virtual">{MESSAGE}</textarea></span></td>
						<td valign="top">&nbsp;<span class="gensmall">BBcodes:</span><br><span class="couriersmall"><select class="small" name="addbbcode" size="6" onchange="insertCode(this.form, this);"> <option value="[b][/b]">[b] [/b]</option> <option value="[i][/i]">[i] [/i]</option> <option value="[quote][/quote]">[quote] [/quote]</option> <option value="[code][/code]">[code] [/code]</option> <option value="[list][/list]">[list] [/list]</option> <option value="[list=][/list]">[list=] [/list]</option> <option value="[img][/img]">[img] [/img]</option> <option value="[url][/url]">[url] [/url]</option></select></span> <br clear="all" />&nbsp;<span class="gensmall">Smiley codes:</span><br><span class="couriersmall"><select class="small" name="addsmiley" size="1" onchange="insertCode(this.form, this);"> <option value=":)">Smiley</option> </option> <option value=";)">Wink</option> <option value=":d">Big Grin</option> <option value=":lol:">Laugh Out Loud</option> <option value=":(">Sad</option> <option value=":o">Eek!</option> <option value=":">Eek!</option> <option value=":oops:">Opps!</option> <option value="8)">Cool</option> <option value=":?">Confused</option> <option value=":roll:">Rolling Eyes</option> <option value=":p">Razz</option> <option value=":x">Mad</option> <option value=":|">Neutral</option> <option value=":!:">Exclamation</option> <option value=":?:">Question</option> <option value=":idea:">Idea</option> <option value=":arrow:">Arrow</option></select></span></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td class="row1"><span class="gen"><b>{L_OPTIONS}</b></span></td>
				<td class="row2"><table cellspacing="0" cellpadding="1" border="0">
					<!-- BEGIN html_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_html" {S_HTML_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_HTML}</span></td>
					</tr>
					<!-- END html_checkbox -->
					<!-- BEGIN bbcode_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_bbcode" {S_BBCODE_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_BBCODE}</span></td>
					</tr>
					<!-- END bbcode_checkbox -->
					<!-- BEGIN smilies_checkbox -->
					<tr>
						<td><input type="checkbox" name="disable_smilies" {S_SMILIES_CHECKED} /></td>
						<td><span class="gen">{L_DISABLE_SMILIES}</span></td>
					</tr>
					<!-- END smilies_checkbox -->
					<!-- BEGIN signature_checkbox -->
					<tr>
						<td><input type="checkbox" name="attach_sig" {S_SIGNATURE_CHECKED} /></td>
						<td><span class="gen">{L_ATTACH_SIGNATURE}</span></td>
					</tr>
					<!-- END signature_checkbox -->
					<!-- BEGIN notify_checkbox -->
					<tr>
						<td><input type="checkbox" name="notify" {S_NOTIFY_CHECKED} /></td>
						<td><span class="gen">{L_NOTIFY_ON_REPLY}</span></td>
					</tr>
					<!-- END notify_checkbox -->
					<!-- BEGIN delete_checkbox -->
					<tr>
						<td><input type="checkbox" name="delete" /></td>
						<td><span class="gen">{L_DELETE_POST}</span></td>
					</tr>
					<!-- END delete_checkbox -->
					<!-- BEGIN type_toggle -->
					<tr>
						<td></td>
						<td><br /><span class="gen">{S_TYPE_TOGGLE}</span></td>
					</tr>
					<!-- END type_toggle -->
				</table></td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center">{S_HIDDEN_FORM_FIELDS}<input type="submit" name="preview" value="{L_PREVIEW}">&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;<input type="submit" name="cancel" value="{L_CANCEL}"></td>
			</tr>
		</table></td>
	</tr>
</table></form>

<table width="80%" cellspacing="2" border="0" align="center">
	<tr>
		<td valign="top"><span class="gensmall"><b>{S_TIMEZONE}</b></span></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table>
