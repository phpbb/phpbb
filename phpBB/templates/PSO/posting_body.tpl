<div align="center"><table width="80%" cellspacing="0" cellpadding="4" border="0">
	<tr>
		<td align="left"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}" color="{T_FONTCOLOR1}"><a href="{U_INDEX}">{SITENAME}&nbsp;{L_INDEX}</a> -> <a href="{U_VIEW_FORUM}">{FORUM_NAME}</a></font></td>
	</tr>
</table></div>

<script language="JavaScript" type="text/javascript">
<!--
function insertCode(formObj, selectObj){
	formObj.message.value += selectObj.options[selectObj.selectedIndex].value;
	return;
}
//-->
</script>

<div align="center"><table width="80%" cellpadding="1" cellspacing="0" border="0">
	<tr><form action="{S_POST_ACTION}" method="POST">
		<td bgcolor="{T_TH_COLOR1}"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_POST_A}</b></font></td>
	        </tr>
            <tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_SUBJECT}</b></font></td>
				<td bgcolor="{T_TD_COLOR2}"><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}">{SUBJECT_INPUT}</font></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_MESSAGE_BODY}</b></font><br><br><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}">{HTML_STATUS}<br>{BBCODE_STATUS}</font></td>
				<td bgcolor="{T_TD_COLOR2}"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><font face="{T_FONTFACE3}" size="{T_FONTSIZE2}">{MESSAGE_INPUT}</font></td>
						<td valign="top">&nbsp;&nbsp;<font face="{T_FONTFACE2}" size="{T_FONTSIZE1}">BBcodes:</font><br><font face="{T_FONTFACE3}" size="{T_FONTSIZE1}"><select class="small" name="addbbcode" size="6" onchange="insertCode(this.form, this);"> <option value="[b][/b]">[b] [/b]</option> <option value="[i][/i]">[i] [/i]</option> <option value="[quote][/quote]">[quote] [/quote]</option> <option value="[code][/code]">[code] [/code]</option> <option value="[list][/list]">[list] [/list]</option> <option value="[list=][/list]">[list=] [/list]</option>	<option value="[img][/img]">[img] [/img]</option> <option value="[url][/url]">[url] [/url]</option></select></font><br clear="all">&nbsp;&nbsp;<font face="{T_FONTFACE2}" size="{T_FONTSIZE1}">Smiley codes:</font><br><font face="{T_FONTFACE3}" size="{T_FONTSIZE1}"><select class="small" name="addsmiley" size="1" onchange="insertCode(this.form, this);"> <option value=":)">Smiley</option> <option value=":(">Frown</option> <option value=":d">Big Grin</option> <option value=";)">Wink</option> <option value=":o">Eek!</option> <option value="8)">Cool</option> <option value=":?">Confused</option> <option value=":p">Razz</option> <option value=":|">Mad</option></select></font></td>
					</tr>
				</table></td>
			</tr>
			<tr>
				<td bgcolor="{T_TD_COLOR1}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}"><b>{L_OPTIONS}</b></font></td>
				<td bgcolor="{T_TD_COLOR2}"><font face="{T_FONTFACE1}" size="{T_FONTSIZE2}">{HTML_TOGGLE}<br>{BBCODE_TOGGLE}<br>{SMILE_TOGGLE}<br>{SIG_TOGGLE}<br>{STICKY_TOGGLE}<br>{ANNOUNCE_TOGGLE}<br>{NOTIFY_TOGGLE}</font></td>
			</tr>
			<tr>
				<td colspan="2" bgcolor="{T_TH_COLOR3}" align="center">{S_HIDDEN_FORM_FIELDS}<input type="submit" name="preview" value="{L_PREVIEW}">&nbsp;<input type="submit" name="submit" value="{L_SUBMIT}">&nbsp;<input type="submit" name="cancel" value="{L_CANCEL}"></td>
			</tr>
		</table></td>
	</form></tr>
</table></div>

<div align="center"><table cellspacing="2" border="0" width="80%">
	<tr>
		<td valign="top"><font face="{T_FONTFACE1}" size="{T_FONTSIZE1}"><b>{S_TIMEZONE}</b></font></td>
		<td align="right" valign="top" nowrap>{JUMPBOX}</td>
	</tr>
</table></div>