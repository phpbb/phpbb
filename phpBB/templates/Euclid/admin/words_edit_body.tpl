
<br clear="all" />

<h1>{L_WORDS_TITLE}</h1>

<p>{L_WORDS_TEXT}</p>

<form method="post" action="{S_WORDS_ACTION}"><table cellspacing="1" cellpadding="4" border="0" align="center">
	<tr>
		<th colspan="2">{L_WORD_CENSOR}</th>
	</tr>
	<tr>
		<td class="row2">{L_WORD}</td>
		<td class="row2"><input type="text" name="word" value="{WORD}" /></td>
	</tr>
	<tr>
		<td class="row1">{L_REPLACEMENT}</td>
		<td class="row1"><input type="text" name="replacement" value="{REPLACEMENT}" /></td>
	</tr>
	<tr>
		<td class="cat" colspan="2" align="center">{S_HIDDEN_FIELDS}<input type="submit" name="save" value="{L_SUBMIT}" /></td>
	</tr>
</table></form>
