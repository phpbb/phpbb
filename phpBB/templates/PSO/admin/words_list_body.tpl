
<br clear="all" />

<h1>{L_WORDS_TITLE}</h1>

<P>{L_WORDS_TEXT}</p>

<form method="POST" action="{S_WORDS_ACTION}"><table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th>{L_WORD}</th>
		<th>{L_REPLACEMENT}</th>
		<th colspan="2">{L_ACTION}</th>
	</tr>
	<!-- BEGIN words -->
	<tr>
		<td class="row2" align="center">{words.WORD}</td>
		<td class="row2" align="center">{words.REPLACEMENT}</td>
		<td class="row2"><a href="{words.U_WORD_EDIT}">{L_EDIT}</a></td>
		<td class="row2"><a href="{words.U_WORD_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END words -->
	<tr>
		<td colspan="5" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="add" value="{L_WORD_ADD}" /></td>
	</tr>
</table></form>
