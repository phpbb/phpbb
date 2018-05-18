
<script type="text/javascript">
<!--
function emoticon(text) {
	text = ' ' + text + ' ';
	if (opener.document.forms['news'].news_text.createTextRange && opener.document.forms['news'].news_text.caretPos) {
		var caretPos = opener.document.forms['post'].message.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		opener.document.forms['news'].news_text.focus();
	} else {
	opener.document.forms['news'].news_text.value  += text;
	opener.document.forms['news'].news_text.focus();
	}
}
window.resizeTo({W_WIDTH_SMILIES},{W_HEIGHT_SMILIES});
//-->
</script>
<br />
<table class="forumline">
<tr><th>{L_EMOTICONS}</th></tr>
<tr>
	<td class="row1"><table width="100" border="0" cellspacing="0" cellpadding="5">
		<!-- BEGIN smilies_row -->
		<tr align="center" valign="middle">
			<!-- BEGIN smilies_col -->
			<td><a href="javascript:emoticon('{smilies_row.smilies_col.SMILEY_CODE}')"><img src="{smilies_row.smilies_col.SMILEY_IMG}" alt="{smilies_row.smilies_col.SMILEY_DESC}" title="{smilies_row.smilies_col.SMILEY_DESC}" /></a></td>
			<!-- END smilies_col -->
		</tr>
		<!-- END smilies_row -->
		<!-- BEGIN switch_smilies_extra -->
		<tr align="center"><td colspan="{S_SMILIES_COLSPAN}"><span  class="nav"><a href="{U_MORE_SMILIES}" onclick="open_window('{U_MORE_SMILIES}', 250, 300);return false" target="_smilies" class="nav">{L_MORE_SMILIES}</a></td></tr>
		<!-- END switch_smilies_extra -->
	</table></td>
</tr>
<tr>
	<td class="row" align="center" valign="middle"><span class="genmed"><a href="javascript:window.close();" class="genmed">{L_CLOSE_WINDOW}</a></span></td>
</tr>
</table>

<br class="clear" />
<br />

