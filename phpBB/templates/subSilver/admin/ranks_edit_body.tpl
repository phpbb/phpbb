
<form action="{S_RANK_ACTION}" method="post">

<table width="98%" cellpadding="1" cellspacing="0" border="0" align="center">
	<tr>
		<td class="tablebg"><table border="0" cellpadding="3" cellspacing="1" width="100%">
			<tr>
				<td class="cat" colspan="2"><span class="cattitle"><b>{L_RANKS_TITLE}</b></span><br /><span class="gensmall">{L_RANKS_TEXT}</span></td>
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
				<td class="row1" width="38%"><span class="gen">{L_RANK_MAXIMUM}:</span></td>
				<td class="row2"><input type="text" name="max_posts" size="5" maxlength="10" value="{MAXIMUM}" /></td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_RANK_MINIMUM}:</span></td>
				<td class="row2"><input type="text" name="min_posts" size="5" maxlength="10" value="{MINIMUM}" /></td>
			</tr>
			<tr>
				<td class="row1" width="38%"><span class="gen">{L_RANK_IMAGE}:</span><br />
				<span class="gensmall">{L_RANK_IMAGE_EXPLAIN}</span></td>
				<td class="row2"><input type="text" name="rank_image" size="40" maxlength="255" value="{IMAGE}" /><br />{IMAGE_DISPLAY}</td>
			</tr>
			<tr>
				<td class="cat" colspan="2" align="center"><span class="cattitle"><input type="submit" name="submit" value="{L_SUBMIT}" />&nbsp;&nbsp;<input type="reset" value="{L_RESET}" /></span></td>
			</tr>
		</table></td>
	</tr>

{S_HIDDEN_FIELDS}
</table></form>