
<br />

<h1>{L_SMILEY_TITLE}</h1>

<P>{L_SMILEY_INSTR}</p>
<script language="javascript">
<!--
function update_smiley(newimage)
{
	document.smiley_image.src = '{S_SMILEY_BASEDIR}/' + newimage;
}
-->
</script>
<form method="post" action="{S_SMILEY_ACTION}">
<input type="hidden" name="mode" value="{S_HIDDEN_VAR}">
<input type="hidden" name="id" value="{SMILEY_ID_VAL}">
<table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr>
		<th colspan="2" class="thHead">{L_SMILEY_CONFIG}</th>
	</tr>
	<tr>
		<td class="row2">{L_SMILEY_CODE_LBL}</td>
		<td class="row2"><input type="text" name="code" value="{SMILEY_CODE_VAL}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_SMILEY_URL_LBL}</td>
		<td class="row1"><select name="url" onchange="update_smiley(this.options[selectedIndex].value);">
			<!-- BEGIN smile_images -->
			<option value="{smile_images.FILENAME}" {smile_images.SELECTED}>{smile_images.FILENAME}</option>
			<!-- END smile_images -->
			</select>
			<img name='smiley_image' src="{S_SMILEY_BASEDIR}/{SMILEY_URL_VAL}" border=0 alt="smiley"></td>
	</tr>
	<tr>
		<td class="row2">{L_SMILEY_EMOTION_LBL}</td>
		<td class="row2"><input type="text" name="emotion" value="{SMILEY_EMOTION}" /></td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><input type="submit" value="{L_SUBMIT}" class="mainoption" /></td>
	</tr>
</table></form>
