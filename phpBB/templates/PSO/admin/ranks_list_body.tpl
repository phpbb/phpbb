<br clear="all" />

<h1>{L_RANKS_TITLE}</h1>

<p>{L_RANKS_TEXT}</p>

<table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<td class="cat" align="center"><span class="cattitle"><b>{L_RANK}</b></span></td>
		<td class="cat" align="center"><span class="cattitle"><b>{L_SPECIAL_RANK}</b></span></td>
		<td class="cat" align="center"><span class="cattitle"><b>{L_EDIT}</b></span></td>
		<td class="cat" align="center"><span class="cattitle"><b>{L_DELETE}</b></span></td>
	</tr>
	<!-- BEGIN ranks -->
	<tr>
		<td class="{ranks.ROW_CLASS}" align="center" style="bgcolor: {ranks.ROW_COLOR};">{ranks.RANK}</td>
		<td class="{ranks.ROW_CLASS}" align="center" style="bgcolor: {ranks.ROW_COLOR};">{ranks.SPECIAL_RANK}</td>
		<td class="{ranks.ROW_CLASS}" align="center" style="bgcolor: {ranks.ROW_COLOR};"><a href="{ranks.U_RANK_EDIT}">{L_EDIT}</td>
		<td class="{ranks.ROW_CLASS}" align="center" style="bgcolor: {ranks.ROW_COLOR};"><a href="{ranks.U_RANK_DELETE}">{L_DELETE}</td>
	</tr>
	<!-- END ranks -->			
	<tr>
		<td class="row2" align="center" colspan="4"><a href="admin_ranks.php?mode=add">{L_ADD_RANK}</a></td>
	</tr>
</table>

<br clear="all" />