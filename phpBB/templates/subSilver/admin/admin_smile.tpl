
<br clear="all" />

<h1>{L_SMILEY_TITLE}</h1>

<P>{L_SMILEY_TEXT}</p>

<table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="5"><a href="{S_SMILEY_URL}&mode=add">{L_SMILEY_ADD}</a></th>
	</tr>
	<tr>
		<th>{L_CODE}</th>
		<th>{L_SMILE}</th>
		<th>{L_EMOT}</th>
		<th colspan="2">{L_ACTION}</th>
	</tr>
	<!-- BEGIN smiles -->
	<tr>
		<td class="row2">{smiles.CODE}</td>
		<td class="row2"><img src='{S_SMILEY_BASEDIR}/{smiles.URL}' alt='{smiles.CODE}'></td>
		<td class="row2">{smiles.EMOT}</td>
		<td class="row2"><a href="{S_SMILEY_URL}&mode=edit&id={smiles.ID}">{L_EDIT}</a></td>
		<td class="row2"><a href="{S_SMILEY_URL}&mode=delete&id={smiles.ID}">{L_DELETE}</a></td>
	</tr>
	<!-- END smiles -->
</table>
