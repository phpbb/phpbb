
<h1>{L_RANKS_TITLE}</h1>

<p>{L_RANKS_TEXT}</p>

<form action="{S_RANK_ACTION}" method="post"><table class="forumline" cellpadding="4" cellspacing="1" border="0" align="center">
	<tr>
		<th class="thTop" colspan="2">{L_RANKS_TITLE}</th>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_RANK_TITLE}:</span></td>
		<td class="row2"><input type="text" name="title" size="35" maxlength="40" value="{RANK}" /></td>
	</tr>
	<tr>
		<td class="row1"><span class="gen">{L_RANK_SPECIAL}</span></td>
		<td class="row2"><input type="radio" name="special_rank" value="1" {SPECIAL_RANK} />{L_YES} &nbsp;&nbsp;<input type="radio" name="special_rank" value="0" {NOT_SPECIAL_RANK} /> {L_NO}</td>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_RANK_MINIMUM}:</span></td>
		<td class="row2"><input type="text" name="min_posts" size="5" maxlength="10" value="{MINIMUM}" /></td>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_RANK_MAXIMUM}:</span></td>
		<td class="row2"><input type="text" name="max_posts" size="5" maxlength="10" value="{MAXIMUM}" /></td>
	</tr>
	<tr>
		<td class="row1" width="38%"><span class="gen">{L_RANK_IMAGE}:</span><br />
		<span class="gensmall">{L_RANK_IMAGE_EXPLAIN}</span></td>
		<td class="row2"><input type="text" name="rank_image" size="40" maxlength="255" value="{IMAGE}" /><br />{IMAGE_DISPLAY}</td>
	</tr>
	<tr>
		<td class="catBottom" colspan="2" align="center"><input type="submit" name="submit" value="{L_SUBMIT}" class="mainoption" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" class="liteoption" /></td>
	</tr>
</table>
{S_HIDDEN_FIELDS}</form>
