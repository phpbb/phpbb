
<br clear="all" />

<h1>{L_SMILEY_TITLE}</h1>

<P>{L_SMILEY_TEXT}</p>

<form method="GET" action="{S_SMILEY_ACTION}"><table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>{L_CODE}</th>
		<th>{L_SMILE}</th>
		<th>{L_EMOT}</th>
		<th colspan="2">{L_ACTION}</th>
	</tr>
	<!-- BEGIN smiles -->
	<tr>
		<td class="row2">{smiles.CODE}</td>
		<td class="row2"><img src="{smiles.SMILEY_IMG}" alt="{smiles.CODE}" /></td>
		<td class="row2">{smiles.EMOT}</td>
		<td class="row2"><a href="{smiles.U_SMILEY_EDIT}">{L_EDIT}</a></td>
		<td class="row2"><a href="{smiles.U_SMILEY_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END smiles -->
	<tr>
		<td class="cat" colspan="5" align="center">{S_HIDDEN_FIELDS}<input type="submit" value="{L_SMILEY_ADD}" /></td>
	</tr>
</table></form>
