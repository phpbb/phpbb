
<br />

<h1>{L_WORDS_TITLE}</h1>

<P>{L_WORDS_TEXT}</p>

<form method="POST" action="{S_WORDS_ACTION}"><table cellspacing="1" cellpadding="4" border="0" align="center" class="forumline">
	<tr>
		<th class="thCornerL">{L_WORD}</th>
		<th class="thTop">{L_REPLACEMENT}</th>
		<th colspan="2" class="thCornerR">{L_ACTION}</th>
	</tr>
	<!-- BEGIN words -->
	<tr>
		<td class="{words.ROW_CLASS}" align="center">{words.WORD}</td>
		<td class="{words.ROW_CLASS}" align="center">{words.REPLACEMENT}</td>
		<td class="{words.ROW_CLASS}"><a href="{words.U_WORD_EDIT}">{L_EDIT}</a></td>
		<td class="{words.ROW_CLASS}"><a href="{words.U_WORD_DELETE}">{L_DELETE}</a></td>
	</tr>
	<!-- END words -->
	<tr>
		<td colspan="5" align="center" class="catBottom">{S_HIDDEN_FIELDS}<input type="submit" name="add" value="{L_ADD_WORD}" class="mainoption" /></td>
	</tr>
</table></form>
