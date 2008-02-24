
<h1>{L_SMILEY_TITLE}</h1>

<p>{L_SMILEY_EXPLAIN}</p>

<form method="post" action="{S_SMILEY_ACTION}"><table class="forumline" cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th class="thHead" colspan="2">{L_SMILEY_IMPORT}</th>
	</tr>
	<tr>
		<td class="row2">{L_SELECT_LBL}</td>
		<td class="row2">{S_SMILE_SELECT}</td>
	</tr>
	<tr>
		<td class="row1">{L_DEL_EXISTING}</td>
		<td class="row1"><input type="checkbox" name="clear_current" value="1" /></td>
	</tr>
	<tr>
		<td class="row2" colspan="2" align="center">{L_CONFLICTS}<br /><input type="radio" name="replace" value="1" checked="checked"/> {L_REPLACE_EXISTING} &nbsp; <input type="radio" name="replace" value="0" /> {L_KEEP_EXISTING}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center">{S_HIDDEN_FIELDS}<input class="mainoption" name="import_pack" type="submit" value="{L_IMPORT}" /></td>
	</tr>
</table></form>
